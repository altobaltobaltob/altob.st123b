<?php             
/*
file: Payment_model.php 付費系統 (臨停)
*/           
class Payment_model extends CI_Model 
{        
	function __construct()
	{
		parent::__construct(); 
		$this->load->database(); 
		
		define("PAYMENT_INVOICE_REMARK", "臨停繳交帳單");
    }

	// 狀態: 結帳完成
	public function transfer_money_done($order_no)
	{
		$data = array();
		$data['status'] = TX_BILL_STATUS_PAID;
		$this->db->update('tx_bill', $data, array('order_no' => $order_no));
		return true;
    }

	// 狀態: 錢沒對上
	public function transfer_money_done_with_amt_error($order_no)
	{
    	$data = array();
		$data['status'] = TX_BILL_STATUS_ERROR_AMT;
		$this->db->update('tx_bill', $data, array('order_no' => $order_no));
		return true;
    }
	
	// 狀態: 發票沒建立
	public function transfer_money_set_invoice_error($order_no)
	{
		$data = array();
		$data['status'] = TX_BILL_STATUS_ERROR_INVOICE;
		$this->db->update('tx_bill', $data, array('order_no' => $order_no));
		return true;
    }
	
	// 狀態: 訂單逾期作廢
	public function transfer_money_timeout($order_no)
	{
    	$data = array();
		$data['status'] = TX_BILL_STATUS_TIME_OUT;
		$this->db->update('tx_bill', $data, array('order_no' => $order_no));
		return true;
    }

	// 狀態: 交易失敗
	public function transfer_money_done_with_tx_error($order_no)
	{
    	$data = array();
		$data['status'] = TX_BILL_STATUS_FAIL;
		$this->db->update('tx_bill', $data, array('order_no' => $order_no));
		return true;
    }
	
	// 狀態: 產品已領取
	public function transfer_money_done_and_finished($order_no)
	{
		$data = array();
		$data['status'] = TX_BILL_STATUS_DONE;
		$this->db->update('tx_bill', $data, array('order_no' => $order_no));
		return true;
    }
	
	// 建立帳單 (臨停)
	public function create_cario_bill($lpr) 
	{                    
		$result = $this->db->select('station_no, cario_no, in_time, pay_time, out_time, out_before_time, member_no')
        		->from('cario')	
                ->where(array('obj_type' => 1, 'obj_id' => $lpr, 'finished' => 0, 'err' => 0))
                ->order_by('cario_no', 'desc') 
                ->limit(1)
                ->get()
                ->row_array();
		
		if(!empty($result['member_no'])){
			// 會員不算臨停帳單
			$data = array();
			$data['member_no'] = $result['member_no'];
			return $data;
		}
		else if (!empty($result['out_before_time']))
		{
			$inTime = $result['out_before_time']; // 進場時間認這個欄位
			$balanceTime = date('Y-m-d H:i:s');
			$stationNo = $result['station_no'];
			
			require_once(ALTOB_BILL_FILE); // 臨停費率
			
			$oPayment = new AltobPayment();
			$oPayment->ServiceURL = ALTOB_PAYMENT_TXDATA_URL;
			$bill = $oPayment->getBill($inTime, $balanceTime, $stationNo);
			
			// create tx_bill
			$data = array();
			$data['invoice_remark'] = PAYMENT_INVOICE_REMARK;
			$data['order_no'] = $result['cario_no'].time();	// 交易序號
			$data['cario_no'] = $result['cario_no']; 		// 進出場序號 (金流結束後開門使用)
			//$data['amt'] = ($bill[BillResultKey::price] > 0) ? 10 : 0; // 測試用
			$data['amt'] = $bill[BillResultKey::price];
			$data['balance_time_limit'] = date("Y-m-d H:i:s", strtotime('+ 15 minutes')); // 15 min
			$data['balance_time'] = $balanceTime;
			$data['in_time'] = $inTime;
			$data['lpr'] = strtoupper($lpr);
			$data['station_no'] = $stationNo;
			
			if($bill[BillResultKey::price] > 0){
				// 有費用才建立到tx_bill
				$this->db->insert('tx_bill', $data);
			}else{
				trigger_error(APP_NAME.', '.__FUNCTION__.', order_no=>' . $order_no."|{$lpr}|尚未產生款項");
			}
				
			// return data
			$data['bill_days'] = $bill[BillResultKey::days];
			$data['bill_hours'] = $bill[BillResultKey::hours];
			$data['bill_mins'] = $bill[BillResultKey::mins];
			$data['price_detail'] = $bill[BillResultKey::price_detail];
			return $data;	
        }
        trigger_error(APP_NAME.', '.__FUNCTION__.', order_no=>' . $order_no."|{$lpr}|無車牌帳單資料");
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
				$data['status'] = TX_BILL_STATUS_PROCESSING;
				$data['tx_time'] = date('Y/m/d H:i:s', $txTime);
				$data['tx_type'] = $tx_type; // 交易種類: 0:未定義, 1:現金, 40:博辰人工模組, 41:博辰自動繳費機, 50:歐付寶轉址刷卡, 51:歐付寶APP, 52:歐付寶轉址WebATM, 60:中國信託刷卡轉址
				$data['client_back_url'] = $clientBackUrl;
				$data['order_result_url'] = $orderResultUrl;
				$data['service_url'] = $serviceUrl;
				$this->db->update('tx_bill', $data, array('order_no' => $order_no));
				return $data;
			}
			
			$this->transfer_money_timeout($order_no); // 訂單逾期作廢
			return null;
		}
		
		trigger_error(APP_NAME.', '.__FUNCTION__.', order_no=>' . $order_no."|無帳單資料");
    }
	
	// 取得臨停帳單資料	
	public function get_tx_bill($order_no) 
	{          
		$result = $this->db
				  ->from('tx_bill')
				  ->where(array('order_no' => $order_no))
				  ->limit(1)
				  ->get()
				  ->row_array();
		return $result;
    }
}
