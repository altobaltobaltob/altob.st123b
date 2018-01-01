<?php             
/*
file: Payment_ats_model.php 繳費機
*/           
class Payment_ats_model extends CI_Model 
{        
	function __construct()
	{
		parent::__construct(); 
		$this->load->database(); 
		
		define('PAYMENT_ATS_INVOICE_REMARK', '繳費機帳單');
		define('TABLE_NAME_TX_BILL_ATS', 'tx_bill_ats');
    }

	// 狀態: 結帳完成
	public function transfer_money_done($order_no)
	{
		$data = array();
		$data['status'] = 1; // 狀態: 0:剛建立, 1:結帳完成, 2:錢沒對上, 3:發票沒建立, 4:手動調整, 99:訂單逾期作廢, 100:交易進行中, 101: 交易失敗, 111:產品已領取
		$this->db->update(TABLE_NAME_TX_BILL_ATS, $data, array('order_no' => $order_no));
		return true;
    }

	// 狀態: 錢沒對上
	public function transfer_money_done_with_amt_error($order_no)
	{
    	$data = array();
		$data['status'] = 2; // 狀態: 0:剛建立, 1:結帳完成, 2:錢沒對上, 3:發票沒建立, 4:手動調整, 99:訂單逾期作廢, 100:交易進行中, 101: 交易失敗, 111:產品已領取
		$this->db->update(TABLE_NAME_TX_BILL_ATS, $data, array('order_no' => $order_no));
		return true;
    }
	
	// 狀態: 發票沒建立
	public function transfer_money_set_invoice_error($order_no)
	{
		$data = array();
		$data['status'] = 3; // 狀態: 0:剛建立, 1:結帳完成, 2:錢沒對上, 3:發票沒建立, 4:手動調整, 99:訂單逾期作廢, 100:交易進行中, 101: 交易失敗, 111:產品已領取
		$this->db->update(TABLE_NAME_TX_BILL_ATS, $data, array('order_no' => $order_no));
		return true;
    }
	
	// 狀態: 訂單逾期作廢
	public function transfer_money_timeout($order_no)
	{
    	$data = array();
		$data['status'] = 99; // 狀態: 0:剛建立, 1:結帳完成, 2:錢沒對上, 3:發票沒建立, 4:手動調整, 99:訂單逾期作廢, 100:交易進行中, 101: 交易失敗, 111:產品已領取
		$this->db->update(TABLE_NAME_TX_BILL_ATS, $data, array('order_no' => $order_no));
		return true;
    }

	// 狀態: 交易失敗
	public function transfer_money_done_with_tx_error($order_no)
	{
    	$data = array();
		$data['status'] = 101; // 狀態: 0:剛建立, 1:結帳完成, 2:錢沒對上, 3:發票沒建立, 4:手動調整, 99:訂單逾期作廢, 100:交易進行中, 101: 交易失敗, 111:產品已領取
		$this->db->update(TABLE_NAME_TX_BILL_ATS, $data, array('order_no' => $order_no));
		return true;
    }
	
	// 狀態: 產品已領取
	public function transfer_money_done_and_finished($order_no)
	{
		$data = array();
		$data['status'] = 111; // 狀態: 0:剛建立, 1:結帳完成, 2:錢沒對上, 3:發票沒建立, 4:手動調整, 99:訂單逾期作廢, 100:交易進行中, 101: 交易失敗, 111:產品已領取
		$this->db->update(TABLE_NAME_TX_BILL_ATS, $data, array('order_no' => $order_no));
		return true;
    }
	
	// 建立帳單 (會員)
	public function create_member_bill($lpr) 
	{                    
		$result = $this->db->select("station_no, member_no, member_name, date_format(end_date, '%Y-%m-%d') as end_date, amt, remarks, 
					date_add(date_format(end_date, '%Y-%m-%d'), INTERVAL 1 day) as next_start, 
					date_add(date_format(end_date, '%Y-%m-%d'), INTERVAL 1 MONTH) as next_end", false)
        		->from('members')	
				->where('lpr', $lpr) 
				->get()
				->row_array(); 
		
		if(empty($result['member_no'])){
			// 查無會員資料
			$data = array();
			$data['member_no'] = 0;
			return $data;
		}
		else
		{
			// 建立會員帳單
			$data = array();
			$data['invoice_remark'] = PAYMENT_ATS_INVOICE_REMARK;
			$data['order_no'] = $result['member_no'].time();	// 交易序號
			$data['station_no'] = $result['station_no'];		// 場站編號 
			$data['member_no'] = $result['member_no'];			// 會員編號   
			$data['member_name'] = $result['member_name'];		// 車主姓名
			//$data['amt'] = 10;									// 租金金額   (test only)
			$data['amt'] = $result['amt'];						// 租金金額   
			$data['remarks'] = $result['remarks'];				// 備註
			$data['end_time'] = $result['end_date'] . ' 23:59:59';			// 到期日
			$data['next_start_time'] = $result['next_start']. ' 00:00:00';	// 次期起始日                       
			$data['next_end_time'] = $result['next_end'] . ' 23:59:59';		// 次期到期日 
			$data['lpr'] = strtoupper($lpr);					// 車牌號碼
			$data['balance_time_limit'] = date("Y-m-d H:i:s", strtotime('+ 15 minutes')); // 帳單有效期限 15 min
			$this->db->insert(TABLE_NAME_TX_BILL_ATS, $data);
			return $data;
        }
        trigger_error(APP_NAME.', '.__FUNCTION__.', order_no=>' . $order_no."|{$lpr}|無資料");
    }
	
	// 繳交帳單 (帳單)
	public function pay_bill($order_no, $invoice_receiver, $company_no, $email, $mobile, $clientBackUrl, $orderResultUrl, $serviceUrl, $tx_type=0) 
	{       
		$data = $this->get_tx_bill($order_no);
				
		if (!empty($data))
		{
			if($data['status'] != 0){
				trigger_error(APP_NAME.', '.__FUNCTION__.', order_no=>' . $order_no."|error status: ". $data['status']);
				return null;
			}
			
			if(strlen($invoice_receiver) >= 7){	// 手機載具編號
				$data['invoice_receiver'] = '/'.$invoice_receiver;
			}
			if(strlen($company_no) >= 8){ // 公司統編
				$data['company_no'] = $company_no;	
				$data['company_receiver'] = "公司名稱";
				$data['company_address'] = "公司地址";
			}
			if(strlen($email) >= 5){ // a@b.c
				$data['email'] = $email;
			}
			if(strlen($mobile) >= 10){ // 手機
				$data['mobile'] = $mobile;
			}
			
			$txTime = time(); // 產生交易時間
			if(strtotime($data['balance_time_limit']) - $txTime > 0){	
				$data['status'] = 100; //狀態: 0:剛建立, 1:結帳完成, 2:錢沒對上, 3:發票沒建立, 4:手動調整, 99:訂單逾期作廢, 100:交易進行中
				$data['tx_time'] = date('Y/m/d H:i:s', $txTime);
				$data['tx_type'] = $tx_type; // 交易種類: 0:未定義, 1:現金, 40:博辰人工模組, 41:博辰自動繳費機, 50:歐付寶轉址刷卡, 51:歐付寶APP, 52:歐付寶轉址WebATM, 60:中國信託刷卡轉址
				$data['client_back_url'] = $clientBackUrl;
				$data['order_result_url'] = $orderResultUrl;
				$data['service_url'] = $serviceUrl;
				$this->db->update(TABLE_NAME_TX_BILL_ATS, $data, array('order_no' => $order_no));
				return $data;
			}
			
			$this->transfer_money_timeout($order_no); // 訂單逾期作廢
			return null;
		}
		
		trigger_error(APP_NAME.', '.__FUNCTION__.', order_no=>' . $order_no."|無帳單資料");
    }
	
	// 取得帳單資料	
	public function get_tx_bill($order_no) 
	{          
		$result = $this->db
				  ->from(TABLE_NAME_TX_BILL_ATS)
				  ->where(array('order_no' => $order_no))
				  ->limit(1)
				  ->get()
				  ->row_array();
		return $result;
    }
}
