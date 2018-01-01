<?php
/*
file: Allpa_service_model.php (歐Pa卡)
*/
require_once(ALTOB_TWGC_FILE);

class Allpa_service_model extends CI_Model
{
	function __construct()
	{
		parent::__construct();
		$this->load->database();

		define('PRODUCT_CODE', "allpa"); 				// 產品代碼: 歐Pa卡
		define('ADMIN_PRODUCT_CODE', "allpa_admin"); 	// 產品代碼: 歐Pa卡 (管理者)

		/*
		// ----- TWGC 測試環境 -----
		define('TWGC_issuerIdentity', "52856206"); // 發行商認證碼8碼 (測試環境填52856206)
		define('TWGC_IssuerID', "20016");       // 發行商代碼，依禮物卡公司與各發行商的約定設定其值 (測試環境填20016)
		define('TWGC_StoreID', "001");        // 店號，依禮物卡公司與各發行商的約定設定其值 (測試環境填001)
		define('TWGC_POSID', "1");          // 機號，依禮物卡公司與各發行商的約定設定其值 (測試環境填1)
		define('TWGC_ServiceURL', "https://issuer-test.twgiftcard.com/TWNGC/WebServices/DataProcessor.asmx");
		// ----- TWGC 測試環境 (END) -----
		*/

		// ----- TWGC 正式環境 -----
		define('TWGC_issuerIdentity', "70876800"); // 發行商認證碼8碼 (測試環境填52856206)
		define('TWGC_IssuerID', "20016");       // 發行商代碼，依禮物卡公司與各發行商的約定設定其值 (測試環境填20016)
		define('TWGC_StoreID', "A046");        // 店號，依禮物卡公司與各發行商的約定設定其值 (測試環境填001)
		define('TWGC_POSID', "1");          // 機號，依禮物卡公司與各發行商的約定設定其值 (測試環境填1)
		define('TWGC_ServiceURL', "https://ws.twgiftcard.com/TWNGC/Webservices/DataProcessor.asmx");
		// ----- TWGC 正式環境 (END) -----


		// ----- 回傳訊息 -----
		define('ALLPA_RESULT_CODE_OK', "OK");
		define('ALLPA_RESULT_MSG_OK', "成功");
		define('ALLPA_RESULT_CODE_NOT_FOUND', "-1");
		define('ALLPA_RESULT_MSG_NOT_FOUND', "找不到資料");
		define('ALLPA_RESULT_CODE_NOT_DEFINED', "-2");
		define('ALLPA_RESULT_MSG_NOT_DEFINED', "產品資料未定義");
		define('ALLPA_RESULT_CODE_LPR_NOT_FOUND', "-3");
		define('ALLPA_RESULT_MSG_LPR_NOT_FOUND', "找不到車牌");
		define('ALLPA_RESULT_CODE_UNKNOWN_ERROR', "-99");
		define('ALLPA_RESULT_MSG_UNKNOWN_ERROR', "發生未預期錯誤");
		define('ALLPA_RESULT_CODE_ERROR_virtual_card_activation', "-100");
		define('ALLPA_RESULT_CODE_ERROR_get_otpin', "-101");
		define('ALLPA_RESULT_CODE_ERROR_balance_inquiry', "-102");
		define('ALLPA_RESULT_CODE_ERROR_pin_reload', "-103");
		define('ALLPA_RESULT_CODE_ERROR_allpa_register', "-104");
		define('ALLPA_RESULT_CODE_INVALID_CARD', "-200");
		define('ALLPA_RESULT_MSG_INVALID_CARD', "卡片未開通");
		define('ALLPA_RESULT_CODE_INVALID_LPR', "-201");
		define('ALLPA_RESULT_MSG_INVALID_LPR', "車牌已註冊, 是否轉移點數?");
		// -- 內部代碼 --
		define('ALLPA_GO_RESULT_CODE_OK', 0);
		define('ALLPA_GO_RESULT_MSG_OK', "成功");
		define('ALLPA_GO_RESULT_CODE_CK_ERROR', 10);
		define('ALLPA_GO_RESULT_MSG_CK_ERROR', "CK ERROR");
		define('ALLPA_GO_RESULT_CODE_USER_NOT_FOUND', 11);
		define('ALLPA_GO_RESULT_MSG_USER_NOT_FOUND', "查無歐Pa卡用戶");
		define('ALLPA_GO_RESULT_CODE_NO_MONEY', 12);
		define('ALLPA_GO_RESULT_MSG_NO_MONEY', "餘額不足");
		define('ALLPA_GO_RESULT_CODE_CONSUME_ERROR', 13);
		define('ALLPA_GO_RESULT_MSG_CONSUME_ERROR', "扣款失敗");
		define('ALLPA_GO_RESULT_CODE_DEBT', 14);
		define('ALLPA_GO_RESULT_MSG_DEBT', "有欠款");
		define('ALLPA_GO_RESULT_CODE_BILL_NOT_FOUND', 15);
		define('ALLPA_GO_RESULT_MSG_BILL_NOT_FOUND', "查無歐Pa卡帳單");
		// ----- 回傳訊息 (END) -----
    }

	// 產生交易序號
	private function gen_trx_no()
	{
		return time().rand(10000,99999);
	}

	// TWGC 回傳字串轉換為值
	private function parse_twgc_number($value)
	{
		return str_replace( ',', '', (string) $value);
	}

	// 判斷錢是否夠扣
	private function is_money_enough($balance, $bonus, $price)
	{
		return ($balance >= $price || $bonus >= $price || ($balance + $bonus) >= $price); // 點數一律一比一
	}

	// 異常卡片處理
	private function twgc_notfound_handler($result_code, $lpr, $barcode)
	{
		 if($result_code == "-11"){
			// 找不到禮物卡資料
			$data = array();
			$data['status'] = 44; // '狀態: 0:剛建立, 1:啟用中, 2:啟用記名失敗, 4:手動關閉, 44:異常停用, 99:已停用'
			$this->db->update('allpa_user', $data, array('lpr' => $lpr, 'barcode' => $barcode));
		 }
	}

	// TWGC: 消費
	public function allpa_consume($lpr, $barcode, $amount, $order_no)
	{
		$result = array();

		// 產生交易序號
		$custTrxNo = $this->gen_trx_no();
		$result["cust_trx_no"] = $custTrxNo;

		// 使用TWGCAgent
		$oTWGCAgent = new TWGCAgent();
		$oTWGCAgent->issuerIdentity = TWGC_issuerIdentity;
		$oTWGCAgent->IssuerID = TWGC_IssuerID;
		$oTWGCAgent->StoreID = TWGC_StoreID;
		$oTWGCAgent->POSID = TWGC_POSID;
		$oTWGCAgent->ServiceURL = TWGC_ServiceURL;

		try{
		  $data = array(
			'CustTrxNo' => $custTrxNo,
			'BarCode' => $barcode,
			'MemberID' => $lpr,
			'Amount' => $amount
		  );

		  $resultXml = $oTWGCAgent->BalanceMaintenance3($data, TRUE); // 使用紅利點數

		  if($resultXml->RespCode == "00"){
			$result["result_code"] = ALLPA_RESULT_CODE_OK;
			$result["result_msg"] = ALLPA_RESULT_MSG_OK;
			$result["barcode"] = (string) $resultXml->Barcode;
			$result["prev_balance"] = $this->parse_twgc_number($resultXml->PrevBalance);//str_replace( ',', '', (string) $resultXml->PrevBalance);
			$result["balance"] = $this->parse_twgc_number($resultXml->Balance);//str_replace( ',', '', (string) $resultXml->Balance);
			$result["auth_code"] = (string) $resultXml->AuthCode;
			$result["amount_due"] = (string) $resultXml->AmountDue;
			$result["bonus"] = $this->parse_twgc_number($resultXml->Bonus);//str_replace( ',', '', (string) $resultXml->Bonus);

		  }else{
			$result["result_code"] = (string) $resultXml->RespCode;
			$result["result_msg"] = (string) $resultXml->ErrorMessage;
			$result["auth_code"] = (string) $resultXml->AuthCodeForInsufficientFund;
		  }

		}catch (Exception $e){
			trigger_error(__FUNCTION__.', CustTrxNo=>' . $custTrxNo.'<br>'.$e->getMessage());
			$result["result_code"] = ALLPA_RESULT_CODE_UNKNOWN_ERROR;
			$result["result_msg"] = ALLPA_RESULT_MSG_UNKNOWN_ERROR;
			$result["auth_code"] = "";
		}

		// API LOG
		$data = array();
		$data['cust_trx_no'] = $custTrxNo;
		$data['api_no'] = TWGC_API_NO::BalanceMaintenance3;
		$data['result_code'] = $result["result_code"];
		$data['result_msg'] = $result["result_msg"];
		$data['auth_code'] = $result["auth_code"];
		$data['barcode'] = $barcode;
		$data['order_no'] = $order_no;
		$this->db->insert('twgc_api_log', $data);

		return $result;
	}

	// TWGC: 卡片綁定
	public function allpa_register($lpr, $barcode)
	{
		$result = array();

		// 產生交易序號
		$custTrxNo = $this->gen_trx_no();
		$result["cust_trx_no"] = $custTrxNo;

		// 使用TWGCAgent
		$oTWGCAgent = new TWGCAgent();
		$oTWGCAgent->issuerIdentity = TWGC_issuerIdentity;
		$oTWGCAgent->IssuerID = TWGC_IssuerID;
		$oTWGCAgent->StoreID = TWGC_StoreID;
		$oTWGCAgent->POSID = TWGC_POSID;
		$oTWGCAgent->ServiceURL = TWGC_ServiceURL;

		try{
		  $data = array(
			'CustTrxNo' => $custTrxNo,
			'BarCode' => $barcode,
			'UserID' => $lpr
		  );

		  $resultXml = $oTWGCAgent->Register($data);

		  if($resultXml->RespCode == "00"){
			$result["result_code"] = ALLPA_RESULT_CODE_OK;
			$result["result_msg"] = "卡片綁定完成";
			$result["register_barcode"] = $barcode;
			$result["register_lpr"] = $lpr;
			$result["register_no"] = $custTrxNo;

		  }else{
			$result["result_code"] = (string) $resultXml->RespCode;
			$result["result_msg"] = (string) $resultXml->ErrorMessage;
		  }

		}catch (Exception $e){
			trigger_error(__FUNCTION__.', CustTrxNo=>' . $custTrxNo.'<br>'.$e->getMessage());
			$result["result_code"] = ALLPA_RESULT_CODE_UNKNOWN_ERROR;
			$result["result_msg"] = ALLPA_RESULT_MSG_UNKNOWN_ERROR;
		}

		// API LOG
		$data = array();
		$data['cust_trx_no'] = $custTrxNo;
		$data['api_no'] = TWGC_API_NO::Register;
		$data['result_code'] = $result["result_code"];
		$data['result_msg'] = $result["result_msg"];
		$data['barcode'] = $barcode;
		$this->db->insert('twgc_api_log', $data);

		return $result;
	}

	// TWGC: 虛擬卡開卡
	public function virtual_card_activation($cardEAN, $amount, $order_no)
	{
		$result = array();

		// 產生交易序號
		$custTrxNo = $this->gen_trx_no();
		$result["cust_trx_no"] = $custTrxNo;

		// 使用TWGCAgent
		$oTWGCAgent = new TWGCAgent();
		$oTWGCAgent->issuerIdentity = TWGC_issuerIdentity;
		$oTWGCAgent->IssuerID = TWGC_IssuerID;
		$oTWGCAgent->StoreID = TWGC_StoreID;
		$oTWGCAgent->POSID = TWGC_POSID;
		$oTWGCAgent->ServiceURL = TWGC_ServiceURL;

		try{
		  $data = array(
			'CustTrxNo' => $custTrxNo,
			'EAN' => $cardEAN,
			'Amount' => $amount
		  );

		  $resultXml = $oTWGCAgent->VirtualCardActivation($data);

		  if($resultXml->Detail->Card->RespCode == "00"){ // 白目的結構
			$result["result_code"] = ALLPA_RESULT_CODE_OK;
			$result["result_msg"] = "開卡完成";
			$result["barcode"] = (string) $resultXml->Detail->Card->Barcode;
			$result["amount"] = $this->parse_twgc_number($resultXml->Detail->Card->Amount);//str_replace( ',', '', (string) $resultXml->Detail->Card->Amount);

		  }else{
			$result["result_code"] = (string) $resultXml->RespCode;
			$result["result_msg"] = (string) $resultXml->ErrorMessage;
		  }

		}catch (Exception $e){
			trigger_error(__FUNCTION__.', CustTrxNo=>' . $custTrxNo.'<br>'.$e->getMessage());
			$result["result_code"] = ALLPA_RESULT_CODE_UNKNOWN_ERROR;
			$result["result_msg"] = ALLPA_RESULT_MSG_UNKNOWN_ERROR;
		}

		// API LOG
		$data = array();
		$data['cust_trx_no'] = $custTrxNo;
		$data['api_no'] = TWGC_API_NO::VirtualCardActivation;
		$data['result_code'] = $result["result_code"];
		$data['result_msg'] = $result["result_msg"];
		$data['order_no'] = $order_no;
		$this->db->insert('twgc_api_log', $data);

		return $result;
	}

	// TWGC: 取得儲值的PIN碼
	public function get_otpin($barcode, $order_no)
	{
		$result = array();

		// 產生交易序號
		$custTrxNo = $this->gen_trx_no();
		$result["cust_trx_no"] = $custTrxNo;

		// 使用TWGCAgent
		$oTWGCAgent = new TWGCAgent();
		$oTWGCAgent->issuerIdentity = TWGC_issuerIdentity;
		$oTWGCAgent->IssuerID = TWGC_IssuerID;
		$oTWGCAgent->StoreID = TWGC_StoreID;
		$oTWGCAgent->POSID = TWGC_POSID;
		$oTWGCAgent->ServiceURL = TWGC_ServiceURL;

		try{
		  $data = array(
			'BarCode' => $barcode,
			'Size' => 'S' // *Size:回傳圖檔大小 (L/M/S)
		  );

		  $resultXml = $oTWGCAgent->GetOTPin2($data);

		  if($resultXml->RespCode == "00"){
			$result["result_code"] = ALLPA_RESULT_CODE_OK;
			$result["result_msg"] = ALLPA_RESULT_MSG_OK;
			$result["password_type"] = (string) $resultXml->PasswordType; // PasswordType:不處理
			$result["encoded_pic"] = (string) $resultXml->EncodedPIC; // EncodedPIC:密碼圖檔,以base64編碼 (文件少一個 d)
			$result["valid_time"] = (string) $resultXml->ValidTime; // ValidTime:密碼有效時間 (分)
			$result["valid_before"] = date('Y-m-d H:i:s', strtotime((string) $resultXml->ValidBefore)); // ValidBefore:相對於上傳的本機時間, 密碼到期時間

		  }else{
			$result["result_code"] = (string) $resultXml->RespCode;
			$result["result_msg"] = (string) $resultXml->ErrorMessage;
		  }

		}catch (Exception $e){
			trigger_error(__FUNCTION__.', barcode=>' . $barcode.'<br>'.$e->getMessage());
			$result["result_code"] = ALLPA_RESULT_CODE_UNKNOWN_ERROR;
			$result["result_msg"] = ALLPA_RESULT_MSG_UNKNOWN_ERROR;
		}

		// API LOG
		$data = array();
		$data['cust_trx_no'] = $custTrxNo;
		$data['api_no'] = TWGC_API_NO::GetOTPin2;
		$data['result_code'] = $result["result_code"];
		$data['result_msg'] = $result["result_msg"];
		$data['order_no'] = $order_no;
		$data['barcode'] = $barcode;
		$this->db->insert('twgc_api_log', $data);

		return $result;
	}

	// TWGC: PIN 儲值
	public function pin_reload($pin, $amount, $order_no, $barcode)
	{
		$result = array();

		// 產生交易序號
		$custTrxNo = $this->gen_trx_no();
		$result["cust_trx_no"] = $custTrxNo;

		// 使用TWGCAgent
		$oTWGCAgent = new TWGCAgent();
		$oTWGCAgent->issuerIdentity = TWGC_issuerIdentity;
		$oTWGCAgent->IssuerID = TWGC_IssuerID;
		$oTWGCAgent->StoreID = TWGC_StoreID;
		$oTWGCAgent->POSID = TWGC_POSID;
		$oTWGCAgent->ServiceURL = TWGC_ServiceURL;

		try{
		  $data = array(
			'CustTrxNo' => $custTrxNo,
			'Amount' => $amount,
			'PIN' => $pin
		  );

		  $resultXml = $oTWGCAgent->Reload2($data);

		  if($resultXml->RespCode == "00"){
			$result["result_code"] = ALLPA_RESULT_CODE_OK;
			$result["result_msg"] = ALLPA_RESULT_MSG_OK;
			$result["auth_code"] = (string) $resultXml->AuthCode; // 授權碼
			$result["prev_balance"] = $this->parse_twgc_number($resultXml->PrevBalance);//str_replace( ',', '', (string) $resultXml->PrevBalance); // 卡片儲值前餘額
			$result["balance"] = $this->parse_twgc_number($resultXml->Balance);//str_replace( ',', '', (string) $resultXml->Balance); // 卡片儲值後新餘額

		  }else{
			$result["result_code"] = (string) $resultXml->ErrorCode;
			$result["result_msg"] = (string) $resultXml->ErrorMessage;
			$result["auth_code"] = "";
		  }

		}catch (Exception $e){
			trigger_error(__FUNCTION__.', custTrxNo=>' . $custTrxNo.'<br>'.$e->getMessage());
			$result["result_code"] = ALLPA_RESULT_CODE_UNKNOWN_ERROR;
			$result["result_msg"] = ALLPA_RESULT_MSG_UNKNOWN_ERROR;
			$result["auth_code"] = "";
		}

		// API LOG
		$data = array();
		$data['cust_trx_no'] = $custTrxNo;
		$data['api_no'] = TWGC_API_NO::Reload2;
		$data['result_code'] = $result["result_code"];
		$data['result_msg'] = $result["result_msg"];
		$data['auth_code'] = $result["auth_code"];
		$data['order_no'] = $order_no;
		$data['barcode'] = $barcode;
		$this->db->insert('twgc_api_log', $data);

		return $result;
	}

	// TWGC: 查詢 barcode
	public function balance_inquiry($barcode, $order_no=NULL)
	{
		$result = array();

		// 產生交易序號
		$custTrxNo = $this->gen_trx_no();
		$result["cust_trx_no"] = $custTrxNo;

		// 使用TWGCAgent
		$oTWGCAgent = new TWGCAgent();
		$oTWGCAgent->issuerIdentity = TWGC_issuerIdentity;
		$oTWGCAgent->IssuerID = TWGC_IssuerID;
		$oTWGCAgent->StoreID = TWGC_StoreID;
		$oTWGCAgent->POSID = TWGC_POSID;
		$oTWGCAgent->ServiceURL = TWGC_ServiceURL;

		try{
		  $data = array(
			'CustTrxNo' => $custTrxNo,
			'BarCode' => $barcode
		  );

		  $resultXml = $oTWGCAgent->BalanceInquiry($data);

		  if($resultXml->RespCode == "00"){
			$result["result_code"] = ALLPA_RESULT_CODE_OK;
			$result["result_msg"] = ALLPA_RESULT_MSG_OK;
			$result["van19"] = (string) $resultXml->Detail->Card->VAN19;
			$result["balance"] = $this->parse_twgc_number($resultXml->Detail->Card->Balance);//str_replace( ',', '', (string) $resultXml->Detail->Card->Balance);
			$result["status_code"] = (string) $resultXml->Detail->Card->StatusCode;
			$result["card_status"] = (string) $resultXml->Detail->Card->CardStatus;
			$result["bonus"] = $this->parse_twgc_number($resultXml->Detail->Card->Bonus);//str_replace( ',', '', (string) $resultXml->Detail->Card->Bonus);
			$result["cash_per_point"] = (string) $resultXml->Detail->Card->CashPerPoint;

		  }else{
			$result["result_code"] = (string) $resultXml->RespCode;
			$result["result_msg"] = (string) $resultXml->ErrorMessage;
		  }

		}catch (Exception $e){
			trigger_error(__FUNCTION__.', custTrxNo=>' . $custTrxNo.'<br>'.$e->getMessage());
			$result["result_code"] = ALLPA_RESULT_CODE_UNKNOWN_ERROR;
			$result["result_msg"] = ALLPA_RESULT_MSG_UNKNOWN_ERROR;
		}

		// API LOG
		$data = array();
		$data['cust_trx_no'] = $custTrxNo;
		$data['api_no'] = TWGC_API_NO::BalanceInquiry;
		$data['result_code'] = $result["result_code"];
		$data['result_msg'] = $result["result_msg"];
		$data['order_no'] = $order_no;
		$data['barcode'] = $barcode;
		$this->db->insert('twgc_api_log', $data);

		return $result;
	}

	// 新增卡片使用記錄 (開卡)
	public function create_allpa_init_log($cust_trx_no, $lpr, $barcode, $order_no)
	{
		$this->create_allpa_log($cust_trx_no, $lpr, $barcode, "01", 0, 0, $order_no, 0); // 訂單種類: 0:開卡, 1:儲值, 2:扣款, 10:實體卡記名, 44:未知
	}

	// 新增卡片使用記錄 (儲值)
	public function create_allpa_reload_log($cust_trx_no, $lpr, $barcode, $pre_status_code, $pre_balance, $pre_bonus, $order_no)
	{
		$this->create_allpa_log($cust_trx_no, $lpr, $barcode, $pre_status_code, $pre_balance, $pre_bonus, $order_no, 1); // 訂單種類: 0:開卡, 1:儲值, 2:扣款, 10:實體卡記名, 44:未知
	}

	// 新增卡片使用記錄 (扣款)
	public function create_allpa_consume_log($cust_trx_no, $lpr, $barcode, $pre_status_code, $pre_balance, $pre_bonus, $order_no)
	{
		$this->create_allpa_log($cust_trx_no, $lpr, $barcode, $pre_status_code, $pre_balance, $pre_bonus, $order_no, 2); // 訂單種類: 0:開卡, 1:儲值, 2:扣款, 10:實體卡記名, 44:未知
	}

	// 新增卡片使用記錄 (實體卡記名)
	public function create_allpa_card_register_log($cust_trx_no, $lpr, $barcode)
	{
		$this->create_allpa_log($cust_trx_no, $lpr, $barcode, "01", 0, 0, 0, 10); // 訂單種類: 0:開卡, 1:儲值, 2:扣款, 10:實體卡記名, 44:未知
	}

	// 新增卡片使用記錄 (未知)
	public function create_allpa_unknown_log($cust_trx_no, $lpr, $barcode, $pre_status_code, $pre_balance, $pre_bonus, $order_no)
	{
		$this->create_allpa_log($cust_trx_no, $lpr, $barcode, $pre_status_code, $pre_balance, $pre_bonus, $order_no, 44); // 訂單種類: 0:開卡, 1:儲值, 2:扣款, 10:實體卡記名, 44:未知
	}

	// 新增卡片使用記錄
	private function create_allpa_log($cust_trx_no, $lpr, $barcode, $pre_status_code, $pre_balance, $pre_bonus, $order_no, $order_type)
	{
		$data = array();
		// 重新查詢一次
		$balance_inquiry_result = $this->balance_inquiry($barcode);
		if($balance_inquiry_result["result_code"] == ALLPA_RESULT_CODE_OK){
			$data['status_code'] = $balance_inquiry_result["status_code"];
			$data['balance'] = $balance_inquiry_result["balance"];
			$data['bonus'] = $balance_inquiry_result["bonus"];
		}else{
			$data['status_code'] = $pre_status_code;
			$data['balance'] = $pre_balance;
			$data['bonus'] = $pre_bonus;
		}
		// 更新用戶資訊
		$this->db->update('allpa_user', $data, array('lpr' => $lpr, 'barcode' => $barcode));

		// 更新使用記錄
		$data["cust_trx_no"] = $cust_trx_no;
		$data['lpr'] = $lpr;
		$data['barcode'] = $barcode;
		$data['pre_status_code'] = $pre_status_code;
		$data['pre_balance'] = $pre_balance;
		$data['pre_bonus'] = $pre_bonus;
		$data['order_no'] = $order_no;
		$data['order_type'] = $order_type;
		$this->db->insert('allpa_balance_log', $data);
	}

	// 儲值
	public function allpa_reload($order_no, $pin, $pin_check_id)
	{
		$check_result = $this->db->select('allpa_pin_check.lpr as lpr, allpa_pin_check.barcode as barcode, product_bill.product_plan as product_plan')
        		->from('allpa_pin_check')
				->join('product_bill', 'allpa_pin_check.order_no = product_bill.order_no', 'left')
                ->where(array(
					'allpa_pin_check.allpa_pin_check_id' => $pin_check_id,
					'allpa_pin_check.order_no' => $order_no,
					'product_bill.status' => 1)) // '狀態: 0:剛建立, 1:結帳完成, 2:錢沒對上, 3:發票沒建立, 4:手動調整, 99:訂單逾期作廢, 100:交易進行中, 111:產品已領取'
                ->limit(1)
                ->get()
                ->row_array();

		// 查無結帳記錄
		if(empty($check_result)){
			$result = array();
			$result["result_code"] = ALLPA_RESULT_CODE_NOT_FOUND;
			$result["result_msg"] = ALLPA_RESULT_MSG_NOT_FOUND;
			return $result; // NOT FOUND
		}

		// 產品內容未定義
		if (empty($check_result['product_plan']))
		{
			$result = array();
			$result["result_code"] = ALLPA_RESULT_CODE_NOT_DEFINED;
			$result["result_msg"] = ALLPA_RESULT_MSG_NOT_DEFINED;
			return $result; // NOT DEFINED
		}

		$lpr = $check_result['lpr'];
		$barcode = $check_result['barcode'];
		$product_plan = json_decode($check_result['product_plan'], true);
		$cardEAN = $product_plan["EAN"];
		$cardAmount = $product_plan["Amount"];

		// 查遠端餘額
		$pre_status_code = "";
		$pre_balance = 0;
		$pre_bonus = 0;
		$balance_inquiry_result = $this->balance_inquiry($barcode);
		if($balance_inquiry_result["result_code"] == ALLPA_RESULT_CODE_OK){
			$pre_status_code = $balance_inquiry_result["status_code"];
			$pre_balance = $balance_inquiry_result["balance"];
			$pre_bonus = $balance_inquiry_result["bonus"];
		}else{
			// 未知卡片處理
			$this->twgc_notfound_handler($balance_inquiry_result["result_code"], $lpr, $barcode);

			// 查詢barcode失敗
			$result = array();
			$result["result_code"] = ALLPA_RESULT_CODE_ERROR_balance_inquiry;
			$result["result_msg"] = $balance_inquiry_result["result_msg"];
			return $result;
		}

		// 查本地餘額
		$recent_balance_log = $this->db->select('status_code, balance, bonus')
        		->from('allpa_balance_log')
                ->where(array('lpr' => $lpr, 'barcode' => $barcode))
				->order_by("create_time", "desc")
                ->limit(1)
                ->get()
                ->row_array();

		// 檢記錄是否出現未知斷層
		if(	$pre_status_code != $recent_balance_log["status_code"] ||
			$pre_balance != $recent_balance_log["balance"] ||
			$pre_bonus != $recent_balance_log["bonus"]) {
			$unknown_trx_no = $this->gen_trx_no();
			// 產生一筆未知的balance_log
			$this->create_allpa_unknown_log(
				$unknown_trx_no, $lpr, $barcode,
				$recent_balance_log["status_code"], $recent_balance_log["balance"], $recent_balance_log["bonus"],
				$order_no);
		}

		// 開始儲值
		$pin_reload_result = $this->pin_reload($pin, $cardAmount, $order_no, $barcode);

		if($pin_reload_result["result_code"] == ALLPA_RESULT_CODE_OK){
			// 已領取
			$this->transfer_money_done_and_finished($order_no);

			// 新增卡片使用記錄
			$this->create_allpa_reload_log(
				$pin_reload_result["cust_trx_no"], $lpr, $barcode,
				$pre_status_code, $pre_balance, $pre_bonus,
				$order_no);

			// 跳到顯示個資的流程??
			return $this->get_allpa_info($lpr);

		}else{
			// 儲值barcode失敗
			$result = array();
			$result["result_code"] = ALLPA_RESULT_CODE_ERROR_pin_reload;
			$result["result_msg"] = $pin_reload_result["result_msg"];
			return $result;
		}
	}

	// 卡片記名
	public function card_register($lpr, $barcode)
	{
		$user = $this->get_valid_user($lpr);

        if(empty($user)){
			// 新開立
			$allpa_register_result = $this->allpa_register($lpr, $barcode);

			if($allpa_register_result["result_code"] == ALLPA_RESULT_CODE_OK){
				$data = array();
				$data['lpr'] = $lpr;
				$data['barcode'] = $barcode;
				$data['status'] = 1; // '狀態: 0:剛建立, 1:啟用中, 2:啟用記名失敗, 99:已停用'
				$this->db->insert('allpa_user', $data);

				// 新增卡片記名記錄
				$this->create_allpa_card_register_log($allpa_register_result["cust_trx_no"], $lpr, $barcode);

				// 跳到顯示個資的流程??
				return $this->get_allpa_info($lpr);

			}else{
				// 記名失敗
				$result = array();
				$result["result_code"] = ALLPA_RESULT_CODE_ERROR_allpa_register;
				$result["result_msg"] = $allpa_register_result["result_msg"];
				return $result;
			}

		}else{
			// 車牌已註冊, 詢問點數移轉
			$result = array();
			$result["result_code"] = ALLPA_RESULT_CODE_INVALID_LPR;
			$result["result_msg"] = ALLPA_RESULT_MSG_INVALID_LPR;
			return $result;
		}
	}
	
	// 領貨 (新帳號, 開卡, 記名)
	public function activate_bill_for_new_register($order_no)
	{
		$bill = $this->db->select('order_no, lpr, product_bill.product_plan as product_plan, tx_time, product_name, product_desc, remarks')
        		->from('product_bill')
				->join('products', 'products.product_id = product_bill.product_id', 'left')
				->where(array('order_no' => $order_no, 'status' => 1)) // '狀態: 0:剛建立, 1:結帳完成, 2:錢沒對上, 3:發票沒建立, 4:手動調整, 99:訂單逾期作廢, 100:交易進行中, 111:產品已領取'
				->limit(1)
                ->get()
                ->row_array();

		if(empty($bill))
		{
			$result = array();
			$result["result_code"] = ALLPA_RESULT_CODE_NOT_FOUND;
			$result["result_msg"] = ALLPA_RESULT_MSG_NOT_FOUND;
			return $result; // NOT FOUND
		}

		if (empty($bill['product_plan']))
		{
			$result = array();
			$result["result_code"] = ALLPA_RESULT_CODE_NOT_DEFINED;
			$result["result_msg"] = ALLPA_RESULT_MSG_NOT_DEFINED;
			return $result; // NOT DEFINED
		}

		if (empty($bill['lpr']))
		{
			$result = array();
			$result["result_code"] = ALLPA_RESULT_CODE_LPR_NOT_FOUND;
			$result["result_msg"] = ALLPA_RESULT_MSG_LPR_NOT_FOUND;
			return $result; // 車牌 NOT FOUND
		}

		$lpr = $bill['lpr'];
		$product_name = $bill['product_name'];
		$product_desc = $bill['product_desc'];
		$remarks = $bill['remarks'];
		$product_plan = json_decode($bill['product_plan'], true);
		$cardEAN = $product_plan["EAN"];
		$cardAmount = $product_plan["Amount"];
		$userAmount = 0;

		// 取得有效用戶
		$user = $this->get_valid_user($lpr);

        if(empty($user)){
			// A. 新帳號, 開卡
			$virtual_card_activation_result = $this->virtual_card_activation($cardEAN, $cardAmount, $order_no);

			if($virtual_card_activation_result["result_code"] == ALLPA_RESULT_CODE_OK){
				// 開卡完成
				$barcode = $virtual_card_activation_result["barcode"];
				$amount = $virtual_card_activation_result["amount"];
				$custTrxNo = $virtual_card_activation_result["cust_trx_no"];

				// 卡片記名
				$allpa_register_result = $this->allpa_register($lpr, $barcode);

				if($allpa_register_result["result_code"] == ALLPA_RESULT_CODE_OK){
					$data = array();
					$data['lpr'] = $lpr;
					$data['barcode'] = $barcode;
					$data['status'] = 1; // '狀態: 0:剛建立, 1:啟用中, 2:啟用記名失敗, 99:已停用'
					$this->db->insert('allpa_user', $data);
				}else{
					$data = array();
					$data['lpr'] = $lpr;
					$data['barcode'] = $barcode;
					$data['status'] = 2; // '狀態: 0:剛建立, 1:啟用中, 2:啟用記名失敗, 99:已停用'
					$this->db->insert('allpa_user', $data);
				}

				// 訂單編號, 已領取
				$this->transfer_money_done_and_finished($order_no);

				// 新增卡片使用記錄
				$this->create_allpa_init_log($custTrxNo, $lpr, $barcode, $order_no);

				// 成功
				$result = array();
				$result["result_code"] = ALLPA_RESULT_CODE_OK;
				$result["result_msg"] = ALLPA_RESULT_MSG_OK;
				return $result;

			}else{
				// 開卡失敗
				$result = array();
				$result["result_code"] = ALLPA_RESULT_CODE_ERROR_virtual_card_activation;
				$result["result_msg"] = $virtual_card_activation_result["result_msg"];
				return $result;
			}
			
		}else{
			// 車牌已註冊, 詢問點數移轉
			$result = array();
			$result["result_code"] = ALLPA_RESULT_CODE_INVALID_LPR;
			$result["result_msg"] = ALLPA_RESULT_MSG_INVALID_LPR;
			return $result;
		}
	}

	// 領貨 (通用)
	public function activate_bill($order_no)
	{
		$bill = $this->db->select('order_no, lpr, product_bill.product_plan as product_plan, tx_time, product_name, product_desc, remarks')
        		->from('product_bill')
				->join('products', 'products.product_id = product_bill.product_id', 'left')
				->where(array('order_no' => $order_no, 'status' => 1)) // '狀態: 0:剛建立, 1:結帳完成, 2:錢沒對上, 3:發票沒建立, 4:手動調整, 99:訂單逾期作廢, 100:交易進行中, 111:產品已領取'
				->limit(1)
                ->get()
                ->row_array();

		if(empty($bill)){
			$result = array();
			$result["result_code"] = ALLPA_RESULT_CODE_NOT_FOUND;
			$result["result_msg"] = ALLPA_RESULT_MSG_NOT_FOUND;
			return $result; // NOT FOUND
		}

		if (empty($bill['product_plan']))
		{
			$result = array();
			$result["result_code"] = ALLPA_RESULT_CODE_NOT_DEFINED;
			$result["result_msg"] = ALLPA_RESULT_MSG_NOT_DEFINED;
			return $result; // NOT DEFINED
		}

		if (empty($bill['lpr']))
		{
			$result = array();
			$result["result_code"] = ALLPA_RESULT_CODE_LPR_NOT_FOUND;
			$result["result_msg"] = ALLPA_RESULT_MSG_LPR_NOT_FOUND;
			return $result; // 車牌 NOT FOUND
		}

		$lpr = $bill['lpr'];
		$product_name = $bill['product_name'];
		$product_desc = $bill['product_desc'];
		$remarks = $bill['remarks'];
		$product_plan = json_decode($bill['product_plan'], true);
		$cardEAN = $product_plan["EAN"];
		$cardAmount = $product_plan["Amount"];
		$userAmount = 0;

		// 取得有效用戶
		$user = $this->get_valid_user($lpr);

        if(empty($user)){
			// A. 新帳號, 開卡
			$virtual_card_activation_result = $this->virtual_card_activation($cardEAN, $cardAmount, $order_no);

			if($virtual_card_activation_result["result_code"] == ALLPA_RESULT_CODE_OK){
				// 開卡完成
				$barcode = $virtual_card_activation_result["barcode"];
				$amount = $virtual_card_activation_result["amount"];
				$custTrxNo = $virtual_card_activation_result["cust_trx_no"];

				// 卡片記名
				$allpa_register_result = $this->allpa_register($lpr, $barcode);

				if($allpa_register_result["result_code"] == ALLPA_RESULT_CODE_OK){
					$data = array();
					$data['lpr'] = $lpr;
					$data['barcode'] = $barcode;
					$data['status'] = 1; // '狀態: 0:剛建立, 1:啟用中, 2:啟用記名失敗, 99:已停用'
					$this->db->insert('allpa_user', $data);
				}else{
					$data = array();
					$data['lpr'] = $lpr;
					$data['barcode'] = $barcode;
					$data['status'] = 2; // '狀態: 0:剛建立, 1:啟用中, 2:啟用記名失敗, 99:已停用'
					$this->db->insert('allpa_user', $data);
				}

				// 訂單編號, 已領取
				$this->transfer_money_done_and_finished($order_no);

				// 新增卡片使用記錄
				$this->create_allpa_init_log($custTrxNo, $lpr, $barcode, $order_no);

				// 跳到顯示個資的流程??
				return $this->get_allpa_info($lpr);

			}else{
				// 開卡失敗
				$result = array();
				$result["result_code"] = ALLPA_RESULT_CODE_ERROR_virtual_card_activation;
				$result["result_msg"] = $virtual_card_activation_result["result_msg"];
				return $result;
			}

		}else{
			$barcode = $user["barcode"];

			// B.1 查詢barcode
			$balance_inquiry_result = $this->balance_inquiry($barcode, $order_no);

			if($balance_inquiry_result["result_code"] == ALLPA_RESULT_CODE_OK){
				if($balance_inquiry_result["status_code"] == "01"){
					$userAmount = $balance_inquiry_result["balance"]; // 目前卡片餘額

				}else{
					// 卡片未開通
					$result = array();
					$result["result_code"] = ALLPA_RESULT_CODE_INVALID_CARD;
					$result["result_msg"] = ALLPA_RESULT_MSG_INVALID_CARD." : ".$result["card_status"];
					return $result;
				}

			}else{
				// 未知卡片處理
				$this->twgc_notfound_handler($balance_inquiry_result["result_code"], $lpr, $barcode);

				// 查詢barcode失敗
				$result = array();
				$result["result_code"] = ALLPA_RESULT_CODE_ERROR_balance_inquiry;
				$result["result_msg"] = $balance_inquiry_result["result_msg"];
				return $result;
			}

			// B.2 取得儲值的PIN碼
			$get_otpin_result = $this->get_otpin($barcode, $order_no);

			if($get_otpin_result["result_code"] == ALLPA_RESULT_CODE_OK){
				$password_type = $get_otpin_result["password_type"]; // PasswordType:不處理
				$encoded_pic = $get_otpin_result["encoded_pic"]; // EncodedPIC:密碼圖檔,以base64編碼 (文件少一個 d);
				$valid_time = $get_otpin_result["valid_time"]; // ValidTime:密碼有效時間 (分)
				$valid_before = $get_otpin_result["valid_before"]; // ValidBefore:相對於上傳的本機時間, 密碼到期時間

				// 建立PIN CHECK
				$data = array();
				$data['lpr'] = $lpr;
				$data['barcode'] = $barcode;
				$data['order_no'] = $order_no;
				$data['password_type'] = $password_type;
				$data['valid_time'] = $valid_time;
				$data['valid_before'] = $valid_before;
				$this->db->insert('allpa_pin_check', $data);
				$pin_check_id = $this->db->insert_id();

				// 回傳
				$result = array();
				$result['pin_check_id'] = $pin_check_id;
				$result['lpr'] = $lpr;
				$result['barcode'] = $barcode;
				$result['order_no'] = $order_no;
				$result['product_name'] = $product_name;
				$result['product_desc'] = $product_desc;
				$result['remarks'] = $remarks;
				$result["amount_before"] = $userAmount;
				$result['amt'] = $cardAmount;
				$result["encoded_pic"] = $encoded_pic;
				$result["valid_before"] = $valid_before;
				$result["result_code"] = ALLPA_RESULT_CODE_OK;
				$result["result_msg"] = ALLPA_RESULT_MSG_OK;
				return $result;

			}else{
				// 取得儲值的PIN碼失敗
				$result = array();
				$result["result_code"] = ALLPA_RESULT_CODE_ERROR_get_otpin;
				$result["result_msg"] = $get_otpin_result["result_msg"];
				return $result;
			}
		}

	}

	// 查詢, 卡片資訊
	public function get_barcode_info($barcode)
	{
		$data = array();

		// A. 取得卡片資訊
		$results = $this->db->select('lpr, barcode, status')
        		->from('allpa_user')
                ->where('barcode', $barcode)
                ->get()
                ->result_array();

		foreach($results as $idx => $rows)
		{
			switch($rows['status']){
				case 1:	// 啟用中

					// 跳到顯示個資的流程??
					return $this->get_allpa_info($rows['lpr']);

					break;
				default: // 其它
					$data['result']['allpa_other'][$idx] = array
					(
						'lpr' => $rows['lpr'],
						'status' => $rows['status']
					);
			}
		}

		// B.1 查詢barcode
		$balance_inquiry_result = $this->balance_inquiry($barcode);

		if($balance_inquiry_result["result_code"] == ALLPA_RESULT_CODE_OK){
			$data["balance"] = $balance_inquiry_result["balance"];
			$data["card_status"] = $balance_inquiry_result["card_status"];
			$data["bonus"] = $balance_inquiry_result["bonus"];

		}else{
			// 查詢barcode失敗
			$result = array();
			$result["result_code"] = ALLPA_RESULT_CODE_ERROR_balance_inquiry;
			$result["result_msg"] = $balance_inquiry_result["result_msg"];
			return $result;
		}

		$data["barcode"] = $barcode;
		$data["result_code"] = ALLPA_RESULT_CODE_OK;
		$data["result_msg"] = ALLPA_RESULT_MSG_OK;
        return $data;
	}


	// 查詢, 歐Pa卡資訊
	public function get_allpa_info($user_lpr)
	{
		$data = array();

		// A. 取得卡片資訊
		$results = $this->db->select('lpr, barcode, status')
        		->from('allpa_user')
                ->where('lpr', $user_lpr)
                ->get()
                ->result_array();

        foreach($results as $idx => $rows)
        {
			switch($rows['status']){
				case 1:	// 啟用中

					// B.1 查詢barcode
					$barcode = $rows['barcode'];
					$balance_inquiry_result = $this->balance_inquiry($barcode);
					$balance = 0;
					$bonus = 0;
					if($balance_inquiry_result["result_code"] == ALLPA_RESULT_CODE_OK){
						$balance = $balance_inquiry_result["balance"]; // 卡片餘額
						$bonus = $balance_inquiry_result["bonus"]; // 卡片紅利點數

						if($balance_inquiry_result["status_code"] == "01"){
							// 唯一, 有效
							$data['result']['allpa_current'] = array
							(
								'lpr' => $user_lpr,
								'barcode' => $barcode,
								'balance' => $balance,
								'bonus' => $bonus,
								'card_status' => $balance_inquiry_result["card_status"]
							);

						}else{
							// 卡片未開通
							$data['result']['allpa_invalid'] = array
							(
								'lpr' => $user_lpr,
								'barcode' => $barcode,
								'balance' => $balance,
								'bonus' => $bonus,
								'card_status' => $balance_inquiry_result["card_status"]
							);
						}

					}else{
						// 未知卡片處理
						$this->twgc_notfound_handler($balance_inquiry_result["result_code"], $user_lpr, $barcode);

						// 查詢barcode失敗
						$data['result']['allpa_error'][$idx] = array
						(
							'lpr' => $user_lpr,
							'barcode' => $barcode,
							'result_code' => $balance_inquiry_result["result_code"],
							'result_msg' => $balance_inquiry_result["result_msg"]
						);
					}

					break;
				default: // 其它
					$data['result']['allpa_other'][$idx] = array
					(
						'lpr' => $user_lpr,
						'barcode' => $rows['barcode'],
						'status' => $rows['status']
					);
			}
        }

		// B. 取得帳單資訊
    	$results = $this->db->select('order_no, product_bill.amt as amt, product_bill.product_plan as product_plan, tx_time, status, product_name, product_desc')
        		->from('product_bill')
				->join('products', 'products.product_id = product_bill.product_id', 'left')
                ->where('product_bill.lpr', $user_lpr)
				//->where('product_bill.product_code', PRODUCT_CODE)
				->where_in('product_bill.status', array(1, 111))
                ->order_by("status ASC, update_time DESC")
				->limit(3)
				->get()
                ->result_array();

        foreach($results as $idx => $rows)
        {
			switch($rows['status']){  // '狀態: 0:剛建立, 1:結帳完成, 2:錢沒對上, 3:發票沒建立, 4:手動調整, 99:訂單逾期作廢, 100:交易進行中, 111:產品已領取'
				case 1:	// 結帳完成
					$data['result']['bill_ready'][$idx] = array
					(
						'order_no' => $rows['order_no'],
						'amt' => $rows['amt'],
						'product_plan' => $rows['product_plan'] ,
						'product_name' => $rows['product_name'] ,
						'product_desc' => $rows['product_desc'] ,
						'tx_time' => $rows['tx_time'] ,
						'status' => $rows['status']
					);
					break;
				case 111: // 產品已領取
					$data['result']['bill_finished'][$idx] = array
					(
						'order_no' => $rows['order_no'],
						'amt' => $rows['amt'],
						'product_plan' => $rows['product_plan'] ,
						'product_name' => $rows['product_name'] ,
						'product_desc' => $rows['product_desc'] ,
						'tx_time' => $rows['tx_time'] ,
						'status' => $rows['status']
					);
					break;
			}
        }

		// C. 取得歐Pa卡用戶帳單資訊
		$results = $this->db->select('order_no, barcode, station_no, in_time, balance_time, amt, status')
        		->from('allpa_user_bill')
                ->where(array('lpr' => $user_lpr))
                ->order_by("balance_time DESC")
				->limit(3)
				->get()
                ->result_array();

        foreach($results as $idx => $rows)
        {
			switch($rows['status']){ // 狀態: 0:剛建立, 1:結帳完成, 2:錢沒對上, 3:交易失敗
				case 1:	// 結帳完成
					$data['result']['allpa_user_bill_finished'][$idx] = array
					(
						'order_no' => $rows['order_no'],
						'barcode' => $rows['barcode'],
						'station_no' => $rows['station_no'],
						'in_time' => $rows['in_time'],
						'balance_time' => $rows['balance_time'],
						'amt' => $rows['amt'],
						'status' => $rows['status']
					);
					break;
				case 2:	// 錢沒對上
					$data['result']['allpa_user_bill_gg'][$idx] = array
					(
						'order_no' => $rows['order_no'],
						'barcode' => $rows['barcode'],
						'station_no' => $rows['station_no'],
						'in_time' => $rows['in_time'],
						'balance_time' => $rows['balance_time'],
						'amt' => $rows['amt'],
						'status' => $rows['status']
					);
					break;
				default: // 未付款
					$data['result']['allpa_user_bill_debt'][$idx] = array
					(
						'order_no' => $rows['order_no'],
						'barcode' => $rows['barcode'],
						'station_no' => $rows['station_no'],
						'in_time' => $rows['in_time'],
						'balance_time' => $rows['balance_time'],
						'amt' => $rows['amt'],
						'status' => $rows['status']
					);
					break;
			}
        }

		// 判斷是否有資料
		if(empty($data)){
			$result = array();
			$result["result_code"] = ALLPA_RESULT_CODE_NOT_FOUND;
			$result["result_msg"] = ALLPA_RESULT_MSG_NOT_FOUND;
			return $result; // NOT FOUND
		}

		$data["result_code"] = ALLPA_RESULT_CODE_OK;
		$data["result_msg"] = ALLPA_RESULT_MSG_OK;
        return $data;
	}
	
	// 取得歐Pa卡產品清單 (管理者專用)
	public function get_allpa_admin_products()
	{
        return $this->get_allpa_products(ADMIN_PRODUCT_CODE);
    }

	// 取得歐Pa卡產品清單
	public function get_allpa_products($product_code=PRODUCT_CODE)
	{
    	$data = array();
		$now = date('Y/m/d H:i:s');
    	$result = $this->db->select('product_id, product_name, product_desc, amt, remarks')
        		->from('products')
                ->where(array(
					'start_time <= ' => $now,
					'valid_time > ' => $now,
					'product_code' => $product_code
					))
                ->get()
                ->result_array();
        return $result;
    }
	
	// 建立歐Pa卡帳單 (管理者專用)
	public function create_admin_bill($product_id)
	{
		return $this->create_bill($product_id, ADMIN_PRODUCT_CODE);
	}

	// 建立歐Pa卡帳單
	public function create_bill($product_id, $product_code=PRODUCT_CODE)
	{
		$now = date('Y/m/d H:i:s');
		$product = $this->db->select('product_id, product_name, product_desc, amt, remarks, product_code, product_plan')
        		->from('products')
                ->where(array(
					'product_id' => $product_id,
					'start_time <= ' => $now,
					'valid_time > ' => $now,
					'product_code' => $product_code
					))
                ->limit(1)
                ->get()
                ->row_array();

		if(empty($product)){
			$result = array();
			$result["result_code"] = ALLPA_RESULT_CODE_NOT_FOUND;
			$result["result_msg"] = ALLPA_RESULT_MSG_NOT_FOUND;
			return $result; // NOT FOUND
		}

		// create product_bill
		$data = array();
		$data['order_no'] = $this->gen_trx_no();
		$data['product_id'] = $product["product_id"];
		$data['product_code'] = $product["product_code"];
		$data['product_plan'] = $product["product_plan"];
		$data['invoice_remark'] = $product["product_name"];
		$data['amt'] = $product["amt"];
		$data['valid_time'] = date('Y-m-d H:i:s', strtotime($now) + 60 * 15); // 15 min
		$this->db->insert('product_bill', $data);

		$data['product_name'] = $product["product_name"];
		$data['product_desc'] = $product["product_desc"];
		$data['remarks'] = $product["remarks"];
		$data["result_code"] = ALLPA_RESULT_CODE_OK;
		$data["result_msg"] = ALLPA_RESULT_MSG_OK;
		return $data;
	}

	// 建立產品帳單 (限定 CTBC)
	public function pay_bill($lpr, $order_no, $invoice_receiver, $company_no, $email, $mobile)
	{
		$data = $this->db
				->from('product_bill')
				->where(array('order_no' => $order_no))
				->limit(1)
				->get()
				->row_array();

		if (!empty($data['valid_time']))
		{
			$data['lpr'] = $lpr; // 車牌號碼

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
			if(strtotime($data['valid_time']) - $txTime > 0){
				$data['status'] = 100; //狀態: 0:剛建立, 1:結帳完成, 2:錢沒對上, 3:發票沒建立, 4:手動調整, 99:訂單逾期作廢, 100:交易進行中, 101: 交易失敗, 111:產品已領取
				$data['tx_time'] = date('Y/m/d H:i:s', $txTime);
				$data['tx_type'] = 60; // 交易種類: 0:未定義, 1:現金, 40:博辰人工模組, 41:博辰自動繳費機, 50:歐付寶轉址刷卡, 51:歐付寶APP, 52:歐付寶轉址WebATM, 60:中國信託刷卡轉址
				$this->db->update('product_bill', $data, array('order_no' => $order_no));
				return $data;
			}
			$data['status'] = 99; //狀態: 0:剛建立, 1:結帳完成, 2:錢沒對上, 3:發票沒建立, 4:手動調整, 99:訂單逾期作廢, 100:交易進行中, 101: 交易失敗, 111:產品已領取
			$this->db->update('product_bill', $data, array('order_no' => $order_no));
			return null;
		}

		trigger_error(__FUNCTION__."|{$order_no}|無資料");
    }

	// 取得產品帳單
	public function get_product_bill($order_no)
	{
		$result = $this->db->from('product_bill')
			->where(array('order_no' => $order_no))
			->limit(1)
			->get()
			->row_array();
		return $result;
    }

	// 取得使用中的歐Pa用戶
	public function get_valid_user($lpr)
	{
		$user = $this->db->select('lpr, status_code, balance, barcode, bonus')
        		->from('allpa_user')
                ->where(array('lpr' => $lpr, 'status' => 1)) // '狀態: 0:剛建立, 1:啟用中, 2:啟用記名失敗, 99:已停用'
                ->limit(1)
                ->get()
                ->row_array();
		return $user;
	}

	// 狀態: 產品已領取
	public function transfer_money_done_and_finished($order_no)
	{
		$data = array();
		$data['status'] = 111; // 狀態: 0:剛建立, 1:結帳完成, 2:錢沒對上, 3:發票沒建立, 4:手動調整, 99:訂單逾期作廢, 100:交易進行中, 101: 交易失敗, 111:產品已領取
		$this->db->update('product_bill', $data, array('order_no' => $order_no));
		return true;
    }

	// 狀態: 結帳完成
	public function transfer_money_done($order_no)
	{
		$data = array();
		$data['status'] = 1; // 狀態: 0:剛建立, 1:結帳完成, 2:錢沒對上, 3:發票沒建立, 4:手動調整, 99:訂單逾期作廢, 100:交易進行中, 101: 交易失敗, 111:產品已領取
		$this->db->update('product_bill', $data, array('order_no' => $order_no));
		return true;
    }

	// 狀態: 錢沒對上
	public function transfer_money_done_with_amt_error($order_no)
	{
    	$data = array();
		$data['status'] = 2; // 狀態: 0:剛建立, 1:結帳完成, 2:錢沒對上, 3:發票沒建立, 4:手動調整, 99:訂單逾期作廢, 100:交易進行中, 101: 交易失敗, 111:產品已領取
		$this->db->update('product_bill', $data, array('order_no' => $order_no));
		return true;
    }

	// 狀態: 交易失敗
	public function transfer_money_done_with_tx_error($order_no)
	{
    	$data = array();
		$data['status'] = 101; // 狀態: 0:剛建立, 1:結帳完成, 2:錢沒對上, 3:發票沒建立, 4:手動調整, 99:訂單逾期作廢, 100:交易進行中, 101: 交易失敗, 111:產品已領取
		$this->db->update('product_bill', $data, array('order_no' => $order_no));
		return true;
    }

	// 狀態: 發票沒建立
	public function transfer_money_set_invoice_error($order_no)
	{
		$data = array();
		$data['status'] = 3; // 狀態: 0:剛建立, 1:結帳完成, 2:錢沒對上, 3:發票沒建立, 4:手動調整, 99:訂單逾期作廢, 100:交易進行中, 101: 交易失敗, 111:產品已領取
		$this->db->update('product_bill', $data, array('order_no' => $order_no));
		return true;
    }

	// [公司內部呼叫] 歐Pa卡 - 開門
	public function allpa_go($in_time, $lpr, $station_no, $check_mac)
	{
		if(empty($check_mac) || md5($in_time.$lpr.$station_no) != $check_mac){
			// check mac fail
			$result = array();
			$result["result_code"] = ALLPA_GO_RESULT_CODE_CK_ERROR;
			$result["result_msg"] = ALLPA_GO_RESULT_MSG_CK_ERROR;
			return $result; // CK ERROR
		}

		require_once(ALTOB_BILL_FILE); // 臨停費率

		$oPayment = new AltobPayment();
		$oPayment->ServiceURL = "http://localhost/txdata.html";
		$in_time = date('Y-m-d H:i:s', $in_time);
		$balance_time = date('Y-m-d H:i:s');
		$bill = $oPayment->getBill($in_time, $balance_time, $station_no);
		$price = $bill[BillResultKey::price];

		// 查有效用戶
		$user = $this->get_valid_user($lpr);
		$barcode = $user["barcode"];

		if(empty($user)){
			$result = array();
			$result["result_code"] = ALLPA_GO_RESULT_CODE_USER_NOT_FOUND;
			$result["result_msg"] = ALLPA_GO_RESULT_MSG_USER_NOT_FOUND;
			return $result; // USER NOT FOUND
		}

		// 查欠款
		$allpa_user_bill = $this->db->select('station_no, amt')
        		->from('allpa_user_bill')
                ->where(array('lpr' => $lpr))
				->where_not_in('status', 1) // '狀態: 0:剛建立, 1:結帳完成, 2:錢沒對上, 3:交易失敗'
                ->limit(1)
                ->get()
                ->row_array();

		if(! empty($allpa_user_bill)){
			$result = array();
			$result["result_code"] = ALLPA_GO_RESULT_CODE_DEBT;
			$result["result_msg"] = ALLPA_GO_RESULT_MSG_DEBT;
			return $result; // DEBT
		}

		// 檢查扣款條件
		if(! $this->is_money_enough($user["balance"], $user["bonus"], $price)){
			$result = array();
			$result["result_code"] = ALLPA_GO_RESULT_CODE_NO_MONEY;
			$result["result_msg"] = ALLPA_GO_RESULT_MSG_NO_MONEY;
			$result["amt"] = $price;
			return $result; // NO MONEY
		}
		/* 2016/03/28 不查log了
		$recent_balance_log = $this->db->select('balance, bonus')
        		->from('allpa_balance_log')
                ->where(array('lpr' => $lpr, 'barcode' => $barcode))
				->order_by("create_time", "desc")
                ->limit(1)
                ->get()
                ->row_array();
		// 檢查扣款條件
		if(! $this->is_money_enough($recent_balance_log["balance"], $recent_balance_log["bonus"], $price)){
			$result = array();
			$result["result_code"] = ALLPA_GO_RESULT_CODE_NO_MONEY;
			$result["result_msg"] = ALLPA_GO_RESULT_MSG_NO_MONEY;
			$result["amt"] = $price;
			return $result; // NO MONEY
		}
		*/

		// 產生交易序號
		$order_no = $this->gen_trx_no();

		// 產生用戶帳單
		$data = array();
		$data['order_no'] = $order_no;
		$data['barcode'] = $barcode;
		$data['lpr'] = $lpr;
		$data['station_no'] = $station_no;
		$data['in_time'] = $in_time;
		$data['balance_time'] = $balance_time;
		$data['amt'] = $price;
		$this->db->insert('allpa_user_bill', $data);

		// 可以開門了
		$result = array();
		$result["result_code"] = ALLPA_GO_RESULT_CODE_OK;
		$result["result_msg"] = ALLPA_GO_RESULT_MSG_OK;
		$result["order_no"] = $order_no;
		$result["amt"] = $price;
		return $result;
	}

	// 歐Pa卡帳單 - 扣款
	public function allpa_pay_bill($order_no)
	{
		// 取得歐Pa卡帳單
		$allpa_user_bill = $this->db->select('lpr, barcode, amt')
        		->from('allpa_user_bill')
                ->where(array('order_no' => $order_no))
				->where_not_in('status', 1) // '狀態: 0:剛建立, 1:結帳完成, 2:錢沒對上, 3:交易失敗'
                ->limit(1)
                ->get()
                ->row_array();

		if(empty($allpa_user_bill)){
			$result = array();
			$result["result_code"] = ALLPA_GO_RESULT_CODE_BILL_NOT_FOUND;
			$result["result_msg"] = ALLPA_GO_RESULT_MSG_BILL_NOT_FOUND;
			return $result; // BILL NOT FOUND
		}

		$lpr = $allpa_user_bill["lpr"];
		$order_barcode = $allpa_user_bill["barcode"]; // 當時產生帳單時的barcode (規則是要唯一, 但意外可能發生)
		$price = $allpa_user_bill["amt"];

		// 查有效用戶
		$user = $this->get_valid_user($lpr);
		$barcode = $user["barcode"];

		if(empty($user)){
			$result = array();
			$result["result_code"] = ALLPA_GO_RESULT_CODE_USER_NOT_FOUND;
			$result["result_msg"] = ALLPA_GO_RESULT_MSG_USER_NOT_FOUND;
			return $result; // USER NOT FOUND
		}

		// 查遠端餘額
		$pre_status_code = "";
		$pre_balance = 0;
		$pre_bonus = 0;
		$balance_inquiry_result = $this->balance_inquiry($barcode);
		if($balance_inquiry_result["result_code"] == ALLPA_RESULT_CODE_OK){
			$pre_status_code = $balance_inquiry_result["status_code"];
			$pre_balance = $balance_inquiry_result["balance"];
			$pre_bonus = $balance_inquiry_result["bonus"];
		}else{
			// 未知卡片處理
			$this->twgc_notfound_handler($balance_inquiry_result["result_code"], $lpr, $barcode);

			// 查詢barcode失敗
			$result = array();
			$result["result_code"] = ALLPA_RESULT_CODE_ERROR_balance_inquiry;
			$result["result_msg"] = $balance_inquiry_result["result_msg"];
			return $result;
		}

		// 記錄是否出現未知斷層
		if(	$pre_status_code != $user["status_code"] ||
			$pre_balance != $user["balance"] ||
			$pre_bonus != $user["bonus"]) {
			$unknown_trx_no = $this->gen_trx_no();
			// 產生一筆未知的balance_log
			$this->create_allpa_unknown_log(
				$unknown_trx_no, $lpr, $barcode,
				$user["status_code"], $user["balance"], $user["bonus"],
				$order_no);
		}

		/* 2016/03/28 不查log了
		$recent_balance_log = $this->db->select('status_code, balance, bonus')
        		->from('allpa_balance_log')
                ->where(array('lpr' => $lpr, 'barcode' => $barcode))
				->order_by("create_time", "desc")
                ->limit(1)
                ->get()
                ->row_array();

		// 記錄是否出現未知斷層
		if(	$pre_status_code != $recent_balance_log["status_code"] ||
			$pre_balance != $recent_balance_log["balance"] ||
			$pre_bonus != $recent_balance_log["bonus"]) {
			$unknown_trx_no = $this->gen_trx_no();
			// 產生一筆未知的balance_log
			$this->create_allpa_unknown_log(
				$unknown_trx_no, $lpr, $barcode,
				$recent_balance_log["status_code"], $recent_balance_log["balance"], $recent_balance_log["bonus"],
				$order_no);
		}*/

		// 檢查扣款條件
		if(! $this->is_money_enough($pre_balance, $pre_bonus, $price)){
			$result = array();
			$result["result_code"] = ALLPA_GO_RESULT_CODE_NO_MONEY;
			$result["result_msg"] = ALLPA_GO_RESULT_MSG_NO_MONEY;
			return $result; // NO MONEY
		}

		// 扣款
		$allpa_consume_result = $this->allpa_consume($lpr, $barcode, $price, $order_no);

		if($allpa_consume_result["result_code"] == ALLPA_RESULT_CODE_OK){
			// 新增卡片使用記錄
			$this->create_allpa_consume_log(
				$allpa_consume_result["cust_trx_no"], $lpr, $barcode,
				$pre_status_code, $pre_balance, $pre_bonus,
				$order_no);

			// 更新帳單資訊
			if($pre_balance + $pre_bonus == $allpa_consume_result["balance"] + $allpa_consume_result["bonus"] + $price){
				$data = array();
				$data['status'] = 1; // '狀態: 0:剛建立, 1:結帳完成, 2:錢沒對上, 3:交易失敗'
				$this->db->update('allpa_user_bill', $data, array('order_no' => $order_no));
			}else{
				$data = array();
				$data['status'] = 2; // '狀態: 0:剛建立, 1:結帳完成, 2:錢沒對上, 3:交易失敗'
				$this->db->update('allpa_user_bill', $data, array('order_no' => $order_no));
			}

			$result = array();
			$result["result_code"] = ALLPA_GO_RESULT_CODE_OK;
			$result["result_msg"] = ALLPA_GO_RESULT_MSG_OK;
			$result["lpr"] = $lpr;
			return $result;

		}else{
			// 更新帳單資訊
			$data = array();
			$data['status'] = 3; // '狀態: 0:剛建立, 1:結帳完成, 2:錢沒對上, 3:交易失敗'
			$this->db->update('allpa_user_bill', $data, array('order_no' => $order_no));

			$result = array();
			$result["result_code"] = ALLPA_GO_RESULT_CODE_CONSUME_ERROR;
			$result["result_msg"] = $allpa_consume_result["result_msg"];
			return $result; // CONSUME ERROR
		}

	}


	// 歐Pa卡 - 判斷有效用戶
	public function get_allpa_valid_user($lpr, $check_mac)
	{
		if(empty($check_mac) || md5($lpr) != $check_mac){
			// check mac fail
			$result = array();
			$result["result_code"] = ALLPA_GO_RESULT_CODE_CK_ERROR;
			$result["result_msg"] = ALLPA_GO_RESULT_MSG_CK_ERROR;
			return $result; // CK ERROR
		}

		// 查有效用戶
		$user = $this->get_valid_user($lpr);

		if(empty($user)){
			$result = array();
			$result["result_code"] = ALLPA_GO_RESULT_CODE_USER_NOT_FOUND;
			$result["result_msg"] = ALLPA_GO_RESULT_MSG_USER_NOT_FOUND;
			return $result; // USER NOT FOUND
		}

		$result = array();
		$result["result_code"] = ALLPA_GO_RESULT_CODE_OK;
		$result["result_msg"] = ALLPA_GO_RESULT_MSG_OK;
		$result["lpr"] = $user["lpr"];
		$result["barcode"] = $user["barcode"];
		$result["balance"] = $user["balance"];
		$result["bonus"] = $user["bonus"];
		return $result;
	}

}
