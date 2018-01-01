<?php             
/*
file: Allpay_invoice_model.php 電子發票 (歐付寶)
*/       
require_once(ALLPAY_INVOICE_FILE) ;

class Allpay_invoice_model extends CI_Model 
{        
	function __construct()
	{
		parent::__construct(); 
		$this->load->database(); 
		
		// ----- 測試環境設定 -----
		define('ALLPAY_INVOICE_TEST_MerchantID', "2000132");
		define('ALLPAY_INVOICE_TEST_HashKey', "ejCk326UnaZWKisg");
		define('ALLPAY_INVOICE_TEST_HashIV', "q9jcZX8Ib9LM8wYk");
		define('ALLPAY_INVOICE_TEST_SERVICE_PATH', "https://einvoice-stage.allpay.com.tw");
		// ----- 測試環境設定(end) -----
		
		// ----- 正式環境設定 (總公司) -----
		define('ALLPAY_INVOICE_80682490_MerchantID', "1148391");			// 80682490
		define('ALLPAY_INVOICE_80682490_HashKey', "Pjkm8Tun7neLqTtj");
		define('ALLPAY_INVOICE_80682490_HashIV', "eLKm6GgvetijrRcc");
		define('ALLPAY_INVOICE_80682490_SERVICE_PATH', "https://einvoice.allpay.com.tw/");
		// ----- 正式環境設定(end) -----
		
		// ----- 正式環境設定 (場站) -----
		define('ALLPAY_INVOICE_MerchantID', "1148391");						// 80682490 (待切換為對應場站)
		define('ALLPAY_INVOICE_HashKey', "Pjkm8Tun7neLqTtj");
		define('ALLPAY_INVOICE_HashIV', "eLKm6GgvetijrRcc");
		define('ALLPAY_INVOICE_SERVICE_PATH', "https://einvoice.allpay.com.tw/");
		// ----- 正式環境設定(end) -----
		
		
		// 切換 FLAG
		define('ALLPAY_INVOICE_TEST_FLAG', "test");
		define('ALLPAY_INVOICE_MAIN_FLAG', "80682490");
		define('ALLPAY_INVOICE_STATION_FLAG', "station");
		
		
		// 5.一般開立發票 API
		define('ALLPAY_INVOICE_Invoice_Issue_Method', "INVOICE");
		define('ALLPAY_INVOICE_Invoice_Issue_Url', "/Invoice/Issue");
		// 7.開立折讓 API
		define('ALLPAY_INVOICE_Allowance_Method', "ALLOWANCE");
		define('ALLPAY_INVOICE_Allowance_Url', "/Invoice/Allowance");
		// 8.發票作廢 API
		define('ALLPAY_INVOICE_Invoice_Void_Method', "INVOICE_VOID");
		define('ALLPAY_INVOICE_Invoice_Void_Url', "/Invoice/IssueInvalid");
		// 10.折讓作廢 API
		define('ALLPAY_INVOICE_Allowance_Void_Method', "ALLOWANCE_VOID");
		define('ALLPAY_INVOICE_Allowance_Void_Url', "/Invoice/AllowanceInvalid");
		// 14.通知 API
		define('ALLPAY_INVOICE_Invoice_Notify_Method', "INVOICE_NOTIFY");
		define('ALLPAY_INVOICE_Invoice_Notify_Url', "/Notify/InvoiceNotify");
		
		// ----- 回傳訊息 -----
		define('ALLPAY_INVOICE_RESULT_CODE_OK', "OK");
		define('ALLPAY_INVOICE_RESULT_MSG_OK', "成功");
		define('ALLPAY_INVOICE_RESULT_CODE_NOT_FOUND', "-1");
		define('ALLPAY_INVOICE_RESULT_MSG_NOT_FOUND', "找不到資料");
		define('ALLPAY_INVOICE_RESULT_CODE_INVOICE_ERROR', "-2");
		define('ALLPAY_INVOICE_RESULT_MSG_INVOICE_ERROR', "錯誤回傳");
		define('ALLPAY_INVOICE_RESULT_CODE_COMPANY_NO_ERROR', "-10");
		define('ALLPAY_INVOICE_RESULT_MSG_COMPANY_NO_ERROR', "統編有誤");
		define('ALLPAY_INVOICE_RESULT_CODE_GG', "-99");
		define('ALLPAY_INVOICE_RESULT_MSG_GG', "異常");
		// ----- 回傳訊息 (END) -----
    }
	
	// 載入歐付寶電子發票
	function load_allpay_invoice_by_flag($flag)
	{
		// 1.載入 SDK 程式
		$allpay_invoice = new AllInvoice ;
		$allpay_invoice->MerchantID = $this->get_allpay_merchant_id($flag) ;
		
		// 2.寫入基本介接參數
		switch($flag)
		{
			case ALLPAY_INVOICE_TEST_FLAG:		// 測試環境
				$allpay_invoice->HashKey = ALLPAY_INVOICE_TEST_HashKey ;
				$allpay_invoice->HashIV = ALLPAY_INVOICE_TEST_HashIV ;
				break;
			case ALLPAY_INVOICE_MAIN_FLAG:		// 總公司
				$allpay_invoice->HashKey = ALLPAY_INVOICE_80682490_HashKey ;
				$allpay_invoice->HashIV = ALLPAY_INVOICE_80682490_HashIV ;
				break;
			default:							// 場站
				$allpay_invoice->HashKey = ALLPAY_INVOICE_HashKey ;
				$allpay_invoice->HashIV = ALLPAY_INVOICE_HashIV ;
				break;
		}
		return $allpay_invoice;
	}
	
	// 載入歐付寶電子發票 (由歐付寶廠商編號)
	function load_allpay_invoice_by_merchant_id($merchant_id)
	{
		// 1.載入 SDK 程式
		$allpay_invoice = new AllInvoice ;
		$allpay_invoice->MerchantID = $merchant_id;
		
		// 2.寫入基本介接參數
		switch($merchant_id)
		{
			case ALLPAY_INVOICE_TEST_MerchantID:		// 測試環境
				$allpay_invoice->HashKey = ALLPAY_INVOICE_TEST_HashKey ;
				$allpay_invoice->HashIV = ALLPAY_INVOICE_TEST_HashIV ;
				break;
			case ALLPAY_INVOICE_80682490_MerchantID:	// 總公司
				$allpay_invoice->HashKey = ALLPAY_INVOICE_80682490_HashKey ;
				$allpay_invoice->HashIV = ALLPAY_INVOICE_80682490_HashIV ;
				break;
			default:									// 場站
				$allpay_invoice->HashKey = ALLPAY_INVOICE_HashKey ;
				$allpay_invoice->HashIV = ALLPAY_INVOICE_HashIV ;
				break;
		}
		return $allpay_invoice;
	}
	
	// 取得歐付寶店家編號
	function get_allpay_merchant_id($flag)
	{
		$id = 0;
		switch($flag)
		{
			case ALLPAY_INVOICE_TEST_FLAG:		// 測試環境
				$id = ALLPAY_INVOICE_TEST_MerchantID ;
				break;
			case ALLPAY_INVOICE_MAIN_FLAG:		// 總公司
				$id = ALLPAY_INVOICE_80682490_MerchantID ;
				break;
			default:							// 場站
				$id = ALLPAY_INVOICE_MerchantID ;
				break;
		}
		return $id;
	}
	
	// Z.1 產生交易編號
	function gen_tx_bill_ats_order_no($tx_bill_no)
	{
		return time().str_pad($tx_bill_no, 10, '0', STR_PAD_LEFT);
	}
	
	// Z.2 由交易編號取得會員交易編號
	function get_tx_bill_no_from_order_no($order_no)
	{
		return intval(substr($order_no, -10));	
	}
	
	// M.1 開立月租系統發票 (情境: 1. 新增會員開立, 2. 繳租開立, 3. 退租戶補開立, 4. 拆分的金額接續開立)
	public function create_member_tx_bill_invoice($station_no, $tx_bill_no, $amt, $member_company_no, $company_no, $email, $mobile, $lpr='')
	{
		
		// 暫不開放
		$result = array();
		$result["result_code"] = ALLPAY_INVOICE_RESULT_CODE_GG;
		$result["result_msg"] = ALLPAY_INVOICE_RESULT_MSG_GG;
		return $result;
		
		
		// 統編不能一樣
		if($member_company_no == $company_no)
		{
			$result = array();
			$result["result_code"] = ALLPAY_INVOICE_RESULT_CODE_COMPANY_NO_ERROR;
			$result["result_msg"] = ALLPAY_INVOICE_RESULT_MSG_COMPANY_NO_ERROR;
			return $result;
		}
		
		$order_no = $this->gen_tx_bill_ats_order_no($tx_bill_no);	// 交易編號
		
		$invoice_flag = ($company_no == '80682490') ? ALLPAY_INVOICE_MAIN_FLAG : ALLPAY_INVOICE_STATION_FLAG;	// 賣方統編切換 （總公司或場站）
		$company_no = $member_company_no; // 買方統編
		
		$this->db->trans_start();
		
		// 1. 建立交易記錄
		$this->create_bill_handler('tx_bill_ats', $station_no, $order_no, $amt, 
			$company_no, $email, $mobile, $lpr, 'TODO', '停車費用帳單', 100);
		
		// 2. 列印歐付寶電子發票
		$result = $this->invoice_issue_handler('tx_bill_ats', $order_no, $amt, $invoice_flag);
		
		$this->db->trans_complete();
		if ($this->db->trans_status() === FALSE)
		{
			trigger_error(__FUNCTION__ . '..trans_error..data:' . "{$station_no}, {$tx_bill_no}". '| last_query: ' . $this->db->last_query());
			
			$result = array();
			$result["result_code"] = ALLPAY_INVOICE_RESULT_CODE_GG;
			$result["result_msg"] = ALLPAY_INVOICE_RESULT_MSG_GG;
		}
		
		return $result;
	}
	
	// M.2 作廢月租系統發票 (tx_bill_ats)
	public function void_member_tx_bill_invoice($station_no, $order_no, $invoice_no)
	{		
		// 檢查
		$bill = $this->db
				->select('invoice_no, status')
				->from('tx_bill_ats')
				->where(array('invoice_no' => $invoice_no, 'order_no' => $order_no, 'station_no' => $station_no))
				->limit(1)
				->get()
				->row_array();
			
		if (empty($bill))
		{
			$result = array();
			$result["result_code"] = ALLPAY_INVOICE_RESULT_CODE_NOT_FOUND;
			$result["result_msg"] = ALLPAY_INVOICE_RESULT_MSG_NOT_FOUND;
			return $result;
		}
		else if($bill['status'] != TX_BILL_ATS_STATUS_PAID)
		{
			$result = array();
			$result["result_code"] = ALLPAY_INVOICE_RESULT_CODE_INVOICE_ERROR;
			$result["result_msg"] = ALLPAY_INVOICE_RESULT_MSG_INVOICE_ERROR;
			return $result;
		}
	
		$result = $this->invoice_void($invoice_no, '月租發票作廢');
		
		if($result["result_code"] == ALLPAY_INVOICE_RESULT_CODE_OK)
		{
			$this->db->trans_start();
			
			// 於 tx_bill_ats 註記
			$this->db->update('tx_bill_ats', array('status' => TX_BILL_ATS_STATUS_INVOICE_VOID), array('station_no' => $station_no, 'invoice_no' => $invoice_no));
			
			$this->db->trans_complete();
			if ($this->db->trans_status() === FALSE)
			{
				trigger_error(__FUNCTION__ . '..trans_error..data:' . "{$station_no}, {$order_no}, {$invoice_no}". '| last_query: ' . $this->db->last_query());
				
				$result = array();
				$result["result_code"] = ALLPAY_INVOICE_RESULT_CODE_GG;
				$result["result_msg"] = ALLPAY_INVOICE_RESULT_MSG_GG;
			}
			else
			{
				$result['tx_bill_no'] = $this->get_tx_bill_no_from_order_no($order_no); // 帶上 tx_bill_no
			}
		}
		
		return $result;
	}
	
	// M.3 折讓月租系統發票 (tx_bill_ats)
	public function allowance_member_tx_bill_invoice($station_no, $invoice_no, $allowance_amt)
	{		
		// 檢查
		$bill = $this->db
				->select('invoice_no, status')
				->from('tx_bill_ats')
				->where(array('invoice_no' => $invoice_no, 'station_no' => $station_no))
				->limit(1)
				->get()
				->row_array();
			
		if (empty($bill))
		{
			$result = array();
			$result["result_code"] = ALLPAY_INVOICE_RESULT_CODE_NOT_FOUND;
			$result["result_msg"] = ALLPAY_INVOICE_RESULT_MSG_NOT_FOUND;
			return $result;
		}
		else if($bill['status'] != TX_BILL_ATS_STATUS_PAID)
		{
			$result = array();
			$result["result_code"] = ALLPAY_INVOICE_RESULT_CODE_INVOICE_ERROR;
			$result["result_msg"] = ALLPAY_INVOICE_RESULT_MSG_INVOICE_ERROR;
			return $result;
		}
	
		$result = $this->invoice_allowance($invoice_no, $allowance_amt, "折讓月租發票");
		
		if($result["result_code"] == ALLPAY_INVOICE_RESULT_CODE_OK)
		{
			$this->db->trans_start();
			
			// 於 tx_bill_ats 註記
			$this->db->update('tx_bill_ats', array('status' => TX_BILL_ATS_STATUS_INVOICE_ALLOWANCE), array('station_no' => $station_no, 'invoice_no' => $invoice_no));
			
			$this->db->trans_complete();
			if ($this->db->trans_status() === FALSE)
			{
				trigger_error(__FUNCTION__ . '..trans_error..data:' . "{$station_no}, {$invoice_no}, {$allowance_amt}". '| last_query: ' . $this->db->last_query());
				
				$result = array();
				$result["result_code"] = ALLPAY_INVOICE_RESULT_CODE_GG;
				$result["result_msg"] = ALLPAY_INVOICE_RESULT_MSG_GG;
			}
		}
		
		return $result;
	}
	
	/*
		建立消費記錄
	*/
	function create_bill_handler($target_table_name, $station_no, $order_no, $amt, 
		$company_no, $email, $mobile, $lpr, $invoice_receiver, $invoice_remark, $tx_type)
	{
		$txTime = time(); // 產生交易時間
		
		$data = array();
		$data['invoice_remark'] = $invoice_remark;
		$data['order_no'] = $order_no; 
		$data['station_no'] = $station_no;		// 場站編號 
		$data['amt'] = $amt;					// 金額
		$data['lpr'] = $lpr;					// 車牌號碼
		
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
			
		$data['status'] = TX_BILL_ATS_STATUS_PAID; //狀態: 0:剛建立, 1:結帳完成, 2:錢沒對上, 3:發票沒建立, 4:手動調整, 99:訂單逾期作廢, 100:交易進行中
		$data['tx_time'] = date('Y/m/d H:i:s', $txTime);
		$data['tx_type'] = $tx_type; // 交易種類: 0:未定義, 1:現金, 40:博辰人工模組, 41:博辰自動繳費機, 50:歐付寶轉址刷卡, 51:歐付寶APP, 52:歐付寶轉址WebATM, 60:中國信託刷卡轉址, 100:月租系統開立歐付寶發票
		
		$this->db->insert($target_table_name, $data);
		return $data;
	}
	
	// A.1 開立歐Pa卡發票 (product_bill)
	public function invoice_issue_for_product_bill($order_no, $amt)
	{		
		return $this->invoice_issue_handler('product_bill', $order_no, $amt);
	}

	// A.2 開立臨停發票 (tx_bill)
	public function invoice_issue_for_tx_bill($order_no, $amt)
	{		
		return $this->invoice_issue_handler('tx_bill', $order_no, $amt);
	}
	
	// A.3 開立繳款機發票 (tx_bill_ats)
	public function invoice_issue_for_tx_bill_ats($order_no, $amt)
	{		
		return $this->invoice_issue_handler('tx_bill_ats', $order_no, $amt);
	}
	
	/*
		開立發票處理入口
		
		$bill 總之要包含這些欄位
		
			order_no			交易編號
			status				交易狀態
			amt					金額
			company_no			公司統編
			company_receiver	公司收件人
			company_address		公司地址
			mobile				手機號碼
			email				信箱
			mobile_receiver		手機載具編號
			natural_receiver	自然人憑證條碼
			love_code			愛心碼
			invoice_remark		備註
			invoice_no			發票號碼
	*/
	protected function invoice_issue_handler($target_table_name, $order_no, $amt, $invoice_flag=ALLPAY_INVOICE_STATION_FLAG)
	{
		try{
			$bill = $this->db
				->select('
						order_no, status, amt, 
						company_no, company_receiver, company_address, 
						mobile, email, mobile_receiver, natural_receiver, love_code,
						invoice_remark, invoice_no
					')
				->from($target_table_name)
				->where(array('order_no' => $order_no, 'amt' => $amt))
				->limit(1)
				->get()
				->row_array();
			
			if (! empty($bill))
			{
				$invoice_issue_result = '';
				
				switch($bill['status'])
				{
					case 1: // 狀態: 0:剛建立, 1:結帳完成, 2:錢沒對上, 3:發票沒建立, 4:手動調整, 99:訂單逾期作廢, 100:交易進行中
					
						// 印發票流程
						if(empty($bill['invoice_no']))
						{
							$invoice_issue_result = $this->invoice_issue($bill, $bill["invoice_remark"], $invoice_flag);
						}
						else
						{
							$sMsg = ' invoice_no is not NULL?? : '.$bill['invoice_no'];
							trigger_error(__FUNCTION__ .', order_no=>' . $order_no.'<br>'.$sMsg);	
						}
						break;
						
					default:
						// 對方多傳一次時??
						$sMsg = ' status != 1';
						trigger_error(__FUNCTION__ .', order_no=>' . $order_no.'<br>'.$sMsg);	
				}
				
				if(strlen($invoice_issue_result) == 10)
				{	        	
					$data = array();
					$data['invoice_no'] = $invoice_issue_result;
					$this->db->update($target_table_name, $data, array('order_no' => $order_no));
					
					$result = array();
					$result["invoice_no"] = $invoice_issue_result;
					$result["result_code"] = ALLPAY_INVOICE_RESULT_CODE_OK;
					$result["result_msg"] = ALLPAY_INVOICE_RESULT_MSG_OK;
					return $result;
				}
				else
				{
					$result = array();
					$result["result_code"] = ALLPAY_INVOICE_RESULT_CODE_INVOICE_ERROR;
					$result["result_msg"] = ALLPAY_INVOICE_RESULT_MSG_INVOICE_ERROR;
					return $result;
				}
			}
			else
			{
				$result = array();
				$result["result_code"] = ALLPAY_INVOICE_RESULT_CODE_NOT_FOUND;
				$result["result_msg"] = ALLPAY_INVOICE_RESULT_MSG_NOT_FOUND;
				return $result;
			}
		}
		catch (Exception $e)
		{
			// 例外錯誤處理。
			$sMsg = $e->getMessage();
			trigger_error(__FUNCTION__ .', order_no=>' . $order_no .'<br>'.$sMsg);
			
			$result = array();
			$result["result_code"] = ALLPAY_INVOICE_RESULT_CODE_GG;
			$result["result_msg"] = ALLPAY_INVOICE_RESULT_MSG_GG;
			return $result;
		}
	}
	
	// 5.一般開立發票 API
	protected function invoice_issue($data, $invoice_remark, $invoice_flag)
	{
		$order_no = $data['order_no'];
		$amt = $data['amt'];
		
		// 1.載入 SDK 程式
		$allpay_invoice = $this->load_allpay_invoice_by_flag($invoice_flag);
		// 2.寫入基本介接參數
		$allpay_invoice->Invoice_Method = ALLPAY_INVOICE_Invoice_Issue_Method ;
		$allpay_invoice->Invoice_Url = ALLPAY_INVOICE_SERVICE_PATH.ALLPAY_INVOICE_Invoice_Issue_Url ;
		
		// 3.寫入發票傳送資訊
		$aItems = array();
		array_push(
			$allpay_invoice->Send['Items'], 
			array(
				'ItemName' => $invoice_remark, 
				'ItemCount' => 1,
				'ItemWord' => '筆',
				'ItemPrice' => $amt, 
				'ItemTaxType' => 1, 
				'ItemAmount' => $amt
			)
		);

		$company_no = '';
		$mobile = '';
		$email = '';
		$company_receiver = '';
		$company_address = '';
		$mobile_receiver = '';
		$natural_receiver = '';
		$love_code = '';

		if(strlen($data['company_no']) == 8){	        	
			$company_no = $data['company_no'];
		}
		if(strlen($data['mobile']) > 0){	        	
			$mobile = $data['mobile'];
		}
		if(strlen($data['email']) > 0){	        	
			$email = $data['email'];
		}
		if(strlen($data['company_receiver']) > 0){	        	
			$company_receiver = $data['company_receiver'];
		}
		if(strlen($data['company_address']) > 0){	        	
			$company_address = $data['company_address'];
		}
		if(strlen($data['mobile_receiver']) > 0){	        	
			$mobile_receiver = $data['mobile_receiver'];
		}
		if(strlen($data['natural_receiver']) > 0){	        	
			$natural_receiver = $data['natural_receiver'];
		}
		if(strlen($data['love_code']) > 0){	        	
			$love_code = $data['love_code'];
		}

		$donation = '2' ;		// 捐贈註記 1.捐贈 2.不捐贈
		$print = '0';		// 列印註記 0.不列印 1.列印
		$carruer_type = '';		// 載具類別
		$carruer_num = '';

		if(strlen($company_no) > 0){
			// 打統編
			$print = '1';

		}else if(strlen($love_code) > 0){
			// 捐贈
			$donation = '1';

		}else if(strlen($natural_receiver) > 0){
			// 載具類別: 自然人憑證
			$carruer_type = '2';
			$carruer_num = $natural_receiver;

		}else if(strlen($mobile_receiver) > 0){
			// 載具類別: 手機條碼
			$carruer_type = '3';
			$carruer_num = $mobile_receiver;
			//$carruer_num = str_replace ('+', ' ', $mobile_receiver); // 如果有+可能會出錯
		}

		$allpay_invoice->Send['RelateNumber'] = $order_no ; // 廠商自訂編號
		$allpay_invoice->Send['CustomerID'] = '' ; // 客戶代號
		$allpay_invoice->Send['CustomerIdentifier'] = $company_no ; // 統一編號
		$allpay_invoice->Send['CustomerName'] = $company_receiver ; // 客戶名稱
		$allpay_invoice->Send['CustomerAddr'] = $company_address ; // 客戶地址
		$allpay_invoice->Send['CustomerPhone'] = $mobile ; // 客戶手機號碼
		$allpay_invoice->Send['CustomerEmail'] = $email ; // 客戶電子信箱
		$allpay_invoice->Send['ClearanceMark'] = '' ; // 通關方式
		$allpay_invoice->Send['Print'] = $print;	// 列印註記 0.不列印 1.列印
		$allpay_invoice->Send['Donation'] = $donation ;	// 捐贈註記 1.捐贈 2.不捐贈
		$allpay_invoice->Send['LoveCode'] = $love_code ;// 愛心碼
		$allpay_invoice->Send['CarruerType'] = $carruer_type ; // 載具類別
		$allpay_invoice->Send['CarruerNum'] =  $carruer_num; // 載具編號
		$allpay_invoice->Send['TaxType'] = 1 ; // 課稅類別 1.應稅 2.零稅率 3.免稅
		$allpay_invoice->Send['SalesAmount'] = $amt ; // 發票金額
		$allpay_invoice->Send['InvoiceRemark'] = $invoice_remark ; // 備註
		$allpay_invoice->Send['InvType'] = '07' ; // 字軌類別 07.一般稅額 08.特種稅額
		$allpay_invoice->Send['InvCreateDate'] = '' ; // 發票開立時間
		$allpay_invoice->Send['vat'] = '' ; // 商品單價是否含稅
		
		trigger_error(__FUNCTION__. ' $allpay_invoice->Send : ' . print_r($allpay_invoice->Send, true));
		
		$aReturn_Info = $allpay_invoice->Check_Out();

		$sMsg = '';
		foreach ($aReturn_Info as $key => $value){
			$sMsg .= $key . ' => ' . $value . '<br>' ;
			switch ($key){
				case "RelateNumber": $eRelateNumber = $value; break;
				case "InvoiceDate": $eInvoiceDate = $value; break;
				case "InvoiceNumber": $eInvoiceNumber = $value; break;
				case "RandomNumber": $eRandomNumber = $value; break;
				case "RtnCode": $eRtnCode = $value; break;
				case "RtnMsg": $eRtnMsg = $value; break;
				case "CheckMacValue": $eCheckMacValue = $value; break;
				default: break;
			}
		}
		
		trigger_error(__FUNCTION__ .', return: '.$sMsg);

		$invoice_data = array(
			'order_no' => $order_no,
			//'relate_number' => $eRelateNumber,   // ?? 沒拿到
			'invoice_date' => $eInvoiceDate,
			'invoice_number' => $eInvoiceNumber,
			'random_number' => $eRandomNumber,
			'rtn_code' => $eRtnCode,
			'rtn_msg' => $eRtnMsg,
			'check_mac_value' => $eCheckMacValue,
			'merchant_id' => $this->get_allpay_merchant_id($invoice_flag)
		);

		// 建立log
		$this->db->insert('allpay_invoice_log', $invoice_data);

		// eInvoiceNumber記到tx_bill
		if($eRtnCode == '1'){
			// 發送通知
			/* (歐付寶後台有提供對應設定, 無需自行手動送出?? But, 測試環境設定後無效 ??) 
			if(strlen($email) > 0 && strlen($mobile) > 0){
				$this->invoice_notify($eInvoiceNumber, 'A', 'I', 'C', $email, $mobile);
			}else if(strlen($mobile) > 0){
				$this->invoice_notify($eInvoiceNumber, 'S', 'I', 'C', $email, $mobile);
			}else if(strlen($email) > 0){
				$this->invoice_notify($eInvoiceNumber, 'E', 'I', 'C', $email, $mobile);
			}else{
				$sMsg = 'empty tx_bill.mobile && tx_bill.email can not send notify';
				trigger_error(APP_NAME.'[allpay_invoice_issue] order_no=>' . $order_no.'<br>'.$sMsg);
			}*/
			
			// 回傳發票號碼
			return $eInvoiceNumber;

		}else{
			trigger_error(__FUNCTION__ .', order_no=>' . $order_no.'<br>'.$sMsg);
		}
		
		return ''; // 不是發票號碼就是空字串
	}
	
	// 7.開立折讓 API
	public function invoice_allowance($invoice_no, $allowance_amt, $item_name="停車費用帳單")
	{	
		// 取得開立記錄
		$invoice_log = $this->db
				->select('merchant_id')
				->from('allpay_invoice_log')
				->where(array('invoice_number' => $invoice_no))
				->limit(1)
				->get()
				->row_array();
			
		if (empty($invoice_log))
		{
			$result = array();
			$result["result_code"] = ALLPAY_INVOICE_RESULT_CODE_NOT_FOUND;
			$result["result_msg"] = ALLPAY_INVOICE_RESULT_MSG_NOT_FOUND;
			return $result;
		}
		else if(empty($invoice_log['merchant_id']))
		{
			$result = array();
			$result["result_code"] = ALLPAY_INVOICE_RESULT_CODE_INVOICE_ERROR;
			$result["result_msg"] = ALLPAY_INVOICE_RESULT_MSG_INVOICE_ERROR;
			return $result;
		}
		else
		{
			trigger_error(__FUNCTION__ .', invoice_no: ' . $invoice_no.', merchant_id: ' . $invoice_log['merchant_id'] . '..start..');
		}
	
		try {
			$sMsg = '' ;
			
			$merchant_id = $invoice_log['merchant_id'];
			// 1.載入 SDK 程式
			$allpay_invoice = $this->load_allpay_invoice_by_merchant_id($merchant_id);
			// 2.寫入基本介接參數
			$allpay_invoice->Invoice_Method = ALLPAY_INVOICE_Allowance_Method ;
			$allpay_invoice->Invoice_Url = ALLPAY_INVOICE_SERVICE_PATH.ALLPAY_INVOICE_Allowance_Url ;
			
			// 3.寫入發票傳送資訊
			array_push($allpay_invoice->Send['Items'], 					// 商品資訊
				array(
					'ItemName' => $item_name, 'ItemTaxType' => 1, 'ItemCount' => 1, 'ItemWord' => '筆',
					'ItemPrice' => $allowance_amt, 'ItemAmount' => $allowance_amt)
			);
			$allpay_invoice->Send['CustomerName'] = '' ; 				// 買受人姓名
			$allpay_invoice->Send['InvoiceNo'] = $invoice_no;			// 發票號碼
			$allpay_invoice->Send['AllowanceNotify'] = 'N';				// 通知類別 S.簡訊 E.電子郵件 A.皆通知 N.皆不通知
			//$allpay_invoice->Send['NotifyMail'] = $notify_mail; 		// 通知電子信箱
			//$allpay_invoice->Send['NotifyPhone'] = $notify_phone; 	// 通知手機號碼
			$allpay_invoice->Send['AllowanceAmount'] = $allowance_amt; 	// 含稅總金額

			// 4.送出
			$aReturn_Info = $allpay_invoice->Check_Out();

			// 5.返回
			foreach($aReturn_Info as $key => $value){
				$sMsg .= $key . ' => ' . $value . '<br>' ;
				switch ($key){
					case "RtnCode": $rtn_code = $value; break;
					case "RtnMsg": $rtn_msg = $value; break;
					case "CheckMacValue": $check_mac_value = $value; break;					// 驗證碼
					case "IA_Allow_No": $allowance_no = $value; break;						// 折讓單號
					case "IA_Invoice_No": $allowance_invoice_no = $value; break;			// 折讓發票號碼
					case "IA_Date": $allowance_date = $value; break;						// 折讓時間
					case "IA_Remain_Allowance_Amt": $allowance_remain_amt = $value; break;	// 折讓剩餘金額
					default: break;
				}
			}
			
			trigger_error(__FUNCTION__ .', invoice_no: ' . $invoice_no.', allowance_amt: ' . $allowance_amt.
				', item_name: ' . $item_name.'| return: '.$sMsg);
			
			if($rtn_code == '1')
			{
				$allowance_data = array(
					'allowance_no' => $allowance_no,
					'invoice_no' => $allowance_invoice_no,
					'allowance_date' => $allowance_date,
					'allowance_remain_amt' => $allowance_remain_amt,
					'check_mac_value' => $check_mac_value,
					'merchant_id' => $merchant_id
				);
				
				// 建立log
				$this->db->insert('allpay_allowance_log', $allowance_data);
				
				$result = array();
				$result['result_code'] = ALLPAY_INVOICE_RESULT_CODE_OK;
				$result['result_msg'] = $rtn_msg;
				$result['check_mac_value'] = $check_mac_value;
				$result['allowance_no'] = $allowance_no;
				$result['allowance_invoice_no'] = $allowance_invoice_no;
				$result['allowance_date'] = $allowance_date;
				$result['allowance_remain_amt'] = $allowance_remain_amt;
				return $result;
			}
			else
			{
				$result = array();
				$result['result_code'] = ALLPAY_INVOICE_RESULT_CODE_GG;
				$result['result_msg'] = $rtn_msg;
				return $result;
			}
			
		}catch (Exception $e){
			// 例外錯誤處理。
			$sMsg = $e->getMessage();
			trigger_error(__FUNCTION__ .', invoice_no: ' . $invoice_no.', allowance_amt: ' . $allowance_amt.
				', item_name: ' . $item_name.'| return: '.$sMsg);
		}
		
		$result = array();
		$result['result_code'] = ALLPAY_INVOICE_RESULT_CODE_GG;
		$result['result_msg'] = ALLPAY_INVOICE_RESULT_MSG_GG;
		return $result;
	}
	
	// 8.發票作廢 API
	protected function invoice_void($invoice_no, $reason_str)
	{	
		// 取得開立記錄
		$invoice_log = $this->db
				->select('merchant_id')
				->from('allpay_invoice_log')
				->where(array('invoice_number' => $invoice_no))
				->limit(1)
				->get()
				->row_array();
			
		if (empty($invoice_log))
		{
			$result = array();
			$result["result_code"] = ALLPAY_INVOICE_RESULT_CODE_NOT_FOUND;
			$result["result_msg"] = ALLPAY_INVOICE_RESULT_MSG_NOT_FOUND;
			return $result;
		}
		else if(empty($invoice_log['merchant_id']))
		{
			$result = array();
			$result["result_code"] = ALLPAY_INVOICE_RESULT_CODE_INVOICE_ERROR;
			$result["result_msg"] = ALLPAY_INVOICE_RESULT_MSG_INVOICE_ERROR;
			return $result;
		}
		else
		{
			trigger_error(__FUNCTION__ .', invoice_no: ' . $invoice_no.', merchant_id: ' . $invoice_log['merchant_id'] . '..start..');
		}
	
		try {
			$sMsg = '' ;
			
			$merchant_id = $invoice_log['merchant_id'];
			// 1.載入 SDK 程式
			$allpay_invoice = $this->load_allpay_invoice_by_merchant_id($merchant_id);
			// 2.寫入基本介接參數
			$allpay_invoice->Invoice_Method = ALLPAY_INVOICE_Invoice_Void_Method ;
			$allpay_invoice->Invoice_Url = ALLPAY_INVOICE_SERVICE_PATH.ALLPAY_INVOICE_Invoice_Void_Url ;
			
			// 3.寫入發票傳送資訊
			$allpay_invoice->Send['InvoiceNumber'] = $invoice_no;	// 發票號碼
			$allpay_invoice->Send['Reason'] = $reason_str;			// 原因

			// 4.送出
			$aReturn_Info = $allpay_invoice->Check_Out();

			// 5.返回
			foreach($aReturn_Info as $key => $value){
				$sMsg .= $key . ' => ' . $value . '<br>' ;
				switch ($key){
					case "RtnCode": $rtn_code = $value; break;
					case "RtnMsg": $rtn_msg = $value; break;
					case "CheckMacValue": $check_mac_value = $value; break;					// 驗證碼
					case "InvoiceNumber": $invoice_number = $value; break;					// 發票號碼
					default: break;
				}
			}
			
			trigger_error(__FUNCTION__.', invoice_no: ' . $invoice_no.', reason_str: ' . $reason_str.'| return: '.$sMsg);
			
			if($rtn_code == '1')
			{
				$result = array();
				$result['result_code'] = ALLPAY_INVOICE_RESULT_CODE_OK;
				$result['result_msg'] = $rtn_msg;
				$result['check_mac_value'] = $check_mac_value;
				$result['invoice_number'] = $invoice_number;
				return $result;
			}
			else
			{
				$result = array();
				$result['result_code'] = ALLPAY_INVOICE_RESULT_CODE_GG;
				$result['result_msg'] = $rtn_msg;
				return $result;
			}
			
		}catch (Exception $e){
			// 例外錯誤處理。
			$sMsg = $e->getMessage();
			trigger_error(__FUNCTION__ .', invoice_no: ' . $invoice_no.', reason_str: ' . $reason_str.'| error: '.$sMsg);
		}
		
		$result = array();
		$result['result_code'] = ALLPAY_INVOICE_RESULT_CODE_GG;
		$result['result_msg'] = ALLPAY_INVOICE_RESULT_MSG_GG;
		return $result;
	}
	
	// 10.折讓作廢 API
	public function allowance_void($invoice_no, $allowance_no, $reason_str)
	{	
		// 取得折讓記錄
		$invoice_log = $this->db
				->select('merchant_id')
				->from('allpay_allowance_log')
				->where(array('invoice_no' => $invoice_no, 'allowance_no' => $allowance_no))
				->limit(1)
				->get()
				->row_array();
			
		if (empty($invoice_log))
		{
			$result = array();
			$result["result_code"] = ALLPAY_INVOICE_RESULT_CODE_NOT_FOUND;
			$result["result_msg"] = ALLPAY_INVOICE_RESULT_MSG_NOT_FOUND;
			return $result;
		}
		else if(empty($invoice_log['merchant_id']))
		{
			$result = array();
			$result["result_code"] = ALLPAY_INVOICE_RESULT_CODE_INVOICE_ERROR;
			$result["result_msg"] = ALLPAY_INVOICE_RESULT_MSG_INVOICE_ERROR;
			return $result;
		}
		else
		{
			trigger_error(__FUNCTION__ .', invoice_no: ' . $invoice_no.', merchant_id: ' . $invoice_log['merchant_id'] . '..start..');
		}
	
		try {
			$sMsg = '' ;
			
			$merchant_id = $invoice_log['merchant_id'];
			// 1.載入 SDK 程式
			$allpay_invoice = $this->load_allpay_invoice_by_merchant_id($merchant_id);
			// 2.寫入基本介接參數
			$allpay_invoice->Invoice_Method = ALLPAY_INVOICE_Allowance_Void_Method ;
			$allpay_invoice->Invoice_Url = ALLPAY_INVOICE_SERVICE_PATH.ALLPAY_INVOICE_Allowance_Void_Url ;
			
			/*
			// 1.載入 SDK 程式
			$allpay_invoice = new AllInvoice ;
			// 2.寫入基本介接參數
			$allpay_invoice->Invoice_Method = ALLPAY_INVOICE_Allowance_Void_Method ;
			$allpay_invoice->Invoice_Url = ALLPAY_INVOICE_SERVICE_PATH.ALLPAY_INVOICE_Allowance_Void_Url ;
			$allpay_invoice->MerchantID = ALLPAY_INVOICE_MerchantID ;
			$allpay_invoice->HashKey = ALLPAY_INVOICE_HashKey ;
			$allpay_invoice->HashIV = ALLPAY_INVOICE_HashIV ;
			*/
			
			// 3.寫入發票傳送資訊
			$allpay_invoice->Send['InvoiceNo'] = $invoice_no;		// 發票號碼
			$allpay_invoice->Send['AllowanceNo'] = $allowance_no;	// 折讓編號
			$allpay_invoice->Send['Reason'] = $reason_str;			// 作廢原因

			// 4.送出
			$aReturn_Info = $allpay_invoice->Check_Out();

			// 5.返回
			foreach($aReturn_Info as $key => $value){
				$sMsg .= $key . ' => ' . $value . '<br>' ;
				switch ($key){
					case "RtnCode": $rtn_code = $value; break;
					case "RtnMsg": $rtn_msg = $value; break;
					case "CheckMacValue": $check_mac_value = $value; break;					// 驗證碼
					case "IA_Invoice_No": $invoice_number = $value; break;					// 發票號碼
					default: break;
				}
			}
			
			trigger_error(__FUNCTION__ .', invoice_no: ' . $invoice_no.', allowance_no: ' . $allowance_no.
				', reason_str: ' . $reason_str.'| return: '.$sMsg);
			
			if($rtn_code == '1')
			{
				$result = array();
				$result['result_code'] = ALLPAY_INVOICE_RESULT_CODE_OK;
				$result['result_msg'] = $rtn_msg;
				$result['check_mac_value'] = $check_mac_value;
				$result['invoice_number'] = $invoice_number;
				return $result;
			}
			else
			{
				$result = array();
				$result['result_code'] = ALLPAY_INVOICE_RESULT_CODE_GG;
				$result['result_msg'] = $rtn_msg;
				return $result;
			}
			
		}catch (Exception $e){
			// 例外錯誤處理。
			$sMsg = $e->getMessage();
			trigger_error(__FUNCTION__ .', invoice_no: ' . $invoice_no.', allowance_no: ' . $allowance_no.
				', reason_str: ' . $reason_str.'| return: '.$sMsg);
		}
		
		$result = array();
		$result['result_code'] = ALLPAY_INVOICE_RESULT_CODE_GG;
		$result['result_msg'] = ALLPAY_INVOICE_RESULT_MSG_GG;
		return $result;
	}
	
	/*
	// 通知
	protected function invoice_notify($eInvoiceNo, $eNotify, $eInvoiceTag, $eNotified, $eNotifyMail, $ePhone)
	{
		//$eInvoiceNo = $this->uri->segment(3);		// 發票號碼
		//$eNotify = $this->uri->segment(4); 		// 發送方式 S.簡訊 E.電子郵件 A.皆通知
		//$eInvoiceTag = $this->uri->segment(5); 	// 發送類型 I.開立 II.作廢 A.折讓 AI.折讓作廢 AW.發票中獎
		//$eNotified = $this->uri->segment(6); 		// 發送對象 C.客戶 M.廠商 A.皆通知
		//$eNotifyMail = $this->uri->segment(7); 	// 發送電子信箱
		//$ePhone = $this->uri->segment(8); 		// 發送手機號碼
		
		try {
			$sMsg = '' ;
			
			// 1.載入 SDK 程式
			$allpay_invoice = new AllInvoice ;
			// 2.寫入基本介接參數
			$allpay_invoice->Invoice_Method = ALLPAY_INVOICE_Invoice_Notify_Method ;
			$allpay_invoice->Invoice_Url = ALLPAY_INVOICE_SERVICE_PATH.ALLPAY_INVOICE_Invoice_Notify_Url ;
			$allpay_invoice->MerchantID = ALLPAY_INVOICE_MerchantID ;
			$allpay_invoice->HashKey = ALLPAY_INVOICE_HashKey ;
			$allpay_invoice->HashIV = ALLPAY_INVOICE_HashIV ;
			
			// 3.寫入發票傳送資訊
			$allpay_invoice->Send['InvoiceNo'] = $eInvoiceNo; 	// 發票號碼
			$allpay_invoice->Send ['Notify'] = $eNotify; 		// 發送方式 S.簡訊 E.電子郵件 A.皆通知
			$allpay_invoice->Send['InvoiceTag'] = $eInvoiceTag; // 發送類型 I.開立 II.作廢 A.折讓 AI.折讓作廢 AW.發票中獎
			$allpay_invoice->Send ['Notified'] = $eNotified; 	// 發送對象 C.客戶 M.廠商 A.皆通知
			$allpay_invoice->Send['NotifyMail'] = $eNotifyMail;	// 發送電子信箱
			$allpay_invoice->Send['Phone'] = $ePhone;			// 發送手機號碼

			// 4.送出
			$aReturn_Info = $allpay_invoice->Check_Out();

			// 5.返回
			foreach($aReturn_Info as $key => $value){
				$sMsg .= $key . ' => ' . $value . '<br>' ;
				switch ($key){
					case "RtnCode": $eRtnCode = $value; break;
					case "RtnMsg": $eRtnMsg = $value; break;
					case "MerchantID": $eMerchantID = $value; break;
					default: break;
				}
			}
			
			// error log
			if($eRtnCode == '1'){
				// do nothing
			}else{
				trigger_error(__FUNCTION__.', eInvoiceNo=>' . $eInvoiceNo.'<br>'.$sMsg);
			}
			
		}catch (Exception $e){
			// 例外錯誤處理。
			$sMsg = $e->getMessage();
			trigger_error(__FUNCTION__.', eInvoiceNo=>' . $eInvoiceNo.'<br>'.$sMsg.'<br> email: '.$eNotifyMail.', mobile: '.$ePhone);
		}
	}
	*/
	
	
	// 測試遠端呼叫
	public function test_curl($station_no, $tx_bill_no, $amt, $company_no, $email, $mobile)
	{
		try{
			$param = array(
					'station_no' => $station_no,
					'tx_bill_no' => $tx_bill_no,
					'amt' => $amt,
					'company_no' => $company_no,
					'email' => $email,
					'mobile' => $mobile
				);
				
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'http://localhost/allpay_invoice.html/create_member_tx_bill_invoice');
            curl_setopt($ch, CURLOPT_HEADER, FALSE);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_POST, TRUE);
            //curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,10);
            //curl_setopt($ch, CURLOPT_TIMEOUT, 10); //timeout in seconds
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($param));
            $data = curl_exec($ch);
            curl_close($ch);
			
			if(!empty($data))
			{
    			$data_decode = json_decode($data, true);
				
				if($data_decode['result_code'] == 'OK')
				{
					$result = array();
					$result['einvoice_track'] = substr($data_decode['invoice_no'], 0, 2);		// 發票字軌 
					$result['einvoice_no'] = substr($data_decode['invoice_no'], 2, 8);			// 發票號碼			
					return $result;
				}
				
				//trigger_error(__FUNCTION__ . ', test 2: '. print_r($data_decode, true));
    		}

		}catch (Exception $e){
			trigger_error(__FUNCTION__ . 'error:'.$e->getMessage());
		}
		
		$result = array();
		$result['einvoice_track'] = '';			// 發票字軌 
		$result['einvoice_no'] = '';			// 發票號碼	
		return $result;
	}
  
  
}
