<?php             
/*
file: Ctbcbank_model.php 付費系統 (CTBC 中國信託)
*/       

class Ctbcbank_model extends CI_Model 
{        
	function __construct()
	{
		parent::__construct(); 
		$this->load->database(); 
		
		/*
		// ----- 中國信託 - 測試環境 -----
		define('CTBC_SSLAuthUI', "https://testepos.ctbcbank.com/auth/SSLAuthUI.jsp"); // URL授權介面
		define('CTBC_TYPE1_merID', "10063"); // 特店編號 (交易類型：一般授權)
		define('CTBC_TYPE1_MerchantID', "8220276806667"); // 銀行所授與的特店代號，純數字，固定 13 碼。
		define('CTBC_TYPE1_TerminalID', "90008466");  // 銀行所授與的終端機代號，純數字，固定 8 碼。
		define('CTBC_TYPE1_Key', "ZQFsGRIzb7NqsPcWOLKOL3sj"); // 壓碼 (可由後台重新產生)
		
		define('CTBC_TYPE2_merID', "10064"); // 特店編號 (交易類型：分期付款)
		define('CTBC_TYPE2_MerchantID', "8220878791047");
		define('CTBC_TYPE2_TerminalID', "91001008");
		
		define('CTBC_TYPE3_merID', "10065"); // 特店編號 (交易類型：紅利折抵)
		define('CTBC_TYPE3_MerchantID', "8220800211437");
		define('CTBC_TYPE3_TerminalID', "92000235");
		// ----- 中國信託 - 測試環境 (END) -----
		*/
		
		// ----- 中國信託 - 正式環境 -----
		define('CTBC_SSLAuthUI', "https://epos.chinatrust.com.tw/auth/SSLAuthUI.jsp"); // URL授權介面
		define('CTBC_TYPE1_merID', "10012"); // 特店編號 (交易類型：一般授權)
		define('CTBC_TYPE1_MerchantID', "8220131400023"); // 銀行所授與的特店代號，純數字，固定 13 碼。
		define('CTBC_TYPE1_TerminalID', "99810789");  // 銀行所授與的終端機代號，純數字，固定 8 碼。
		define('CTBC_TYPE1_Key', "4qH0cNXhsmk6jKdTT4hjwYCX"); // 壓碼 (可由後台重新產生)
		// ----- 中國信託 - 正式環境 (END) -----
		
    }
	
	// 3.中國信託
	public function transfer_money_ctbc($data, $return_url)
	{
		try{
			$lidm = $data['order_no']; // 訂單編號 (<=19 碼)
			$purchAmt = $data['amt'];
			$txType = "0"; // 交易方式，長度為一碼數字。(一般交易：0, 分期交易：1, 紅利折抵一般交易：2, 紅利折抵分期交易：4)
			$debug = "0"; // 預設(進行交易時)請填0，偵錯時請填1。

			// 使用 CTBCAgent
			include_once(ALTOB_CTBC_FILE); 
			$oCTBCAgent = new CTBCAgent();
			$oCTBCAgent->MerchantID = CTBC_TYPE1_MerchantID;
			$oCTBCAgent->TerminalID = CTBC_TYPE1_TerminalID;
			$oCTBCAgent->AuthResURL = $return_url;// 從收單行端取得授權碼後，要導回的網址，請勿填入特殊字元@、#、%、?、&等。
			$oCTBCAgent->SSLAuthUI = CTBC_SSLAuthUI;  // URL授權介面
			$oCTBCAgent->Option = "1"; // 一般交易請填「1」。 , 分期交易請填一到兩碼的分期期數。, 紅利交易請填固定兩碼的產品代碼。, 紅利分期交易請填第一至二碼固定為產品代碼，第三碼或三至四碼為分期期數。
			$oCTBCAgent->Key = CTBC_TYPE1_Key;
			$oCTBCAgent->MerchantName = iconv("UTF-8", "big5", "歐特儀停車場");
			$oCTBCAgent->OrderDetail = iconv("UTF-8", "big5", "歐Pa卡");
			$oCTBCAgent->AutoCap = "1"; 	// (0–不自動請款, 1–自動請款)
			$oCTBCAgent->Customize = "1";	// 設定刷卡頁顯示特定語系或客制化頁面。(1–繁體中文, 2–簡體中文, 3–英文, 5–客制化頁面)

			$data = array(
				'merID' => CTBC_TYPE1_merID,
				'lidm' => $lidm,
				'purchAmt' => $purchAmt,
				'txType' => $txType,
				'debug' => $debug,
				'target' => "_self"
				);
			
			$oCTBCAgent->CheckOut($data);
			
		}catch (Exception $e){
			// 例外錯誤處理。
			throw $e;
		}
	}
	
	// 3.中國信託 - 回傳
	public function ctbcbank_return_handler($resenc, $merid)
	{
		try{
			// 使用 CTBCAgent
			include_once(ALTOB_CTBC_FILE);
			$oCTBCAgent = new CTBCAgent();
			$oCTBCAgent->Key = CTBC_TYPE1_Key;
			$debug = "0";
			
			// 解密
			$data = array(
			  'encRes' => $resenc,
			  'debug' => $debug
			);
			$EncArray = $oCTBCAgent->Decrypt($data);
			
			/*
			foreach($EncArray AS $name => $val){
			  echo $name ."=>". urlencode(trim($val,"\x00..\x08")) ."\n";
			}
			*/

			$errdesc = isset($EncArray['errdesc']) ? $EncArray['errdesc'] : "";
			$authresurl = isset($EncArray['authresurl']) ? $EncArray['authresurl'] : "";
			$xid = isset($EncArray['xid']) ? $EncArray['xid'] : "";
			$awardedpoint = isset($EncArray['awardedpoint']) ? $EncArray['awardedpoint'] : "";
			$status = isset($EncArray['status']) ? $EncArray['status'] : "";
			$errcode = isset($EncArray['errcode']) ? $EncArray['errcode'] : "";
			$authcode = isset($EncArray['authcode']) ? $EncArray['authcode'] : "";
			$authamt = isset($EncArray['authamt']) ? $EncArray['authamt'] : "";
			$lidm = isset($EncArray['lidm']) ? $EncArray['lidm'] : "";
			$offsetamt = isset($EncArray['offsetamt']) ? $EncArray['offsetamt'] : "";
			$originalamt = isset($EncArray['originalamt']) ? $EncArray['originalamt'] : "";
			$utilizedpoint = isset($EncArray['utilizedpoint']) ? $EncArray['utilizedpoint'] : "";
			$numberofpay = isset($EncArray['numberofpay']) ? $EncArray['numberofpay'] : "";	// option: 一般交易, 分期交易
			$prodcode = isset($EncArray['prodcode']) ? $EncArray['prodcode'] : ""; 			// option: 紅利交易
			$last4digitpan = isset($EncArray['last4digitpan']) ? $EncArray['last4digitpan'] : "";
			$pidresult= isset($EncArray['pidresult']) ? $EncArray['pidresult'] : "";
			$cardnumber = isset($EncArray['cardnumber']) ? $EncArray['cardnumber'] : "";
			$outmac = isset($EncArray['outmac']) ? $EncArray['outmac'] : "";
			
			$data = array(
				'status' => $status,
				'errcode' => $errcode,
				'authcode' => $authcode,
				'authamt' => $authamt,
				'lidm' => $lidm,
				'offsetamt' => $offsetamt,
				'originalamt' => $originalamt,
				'utilizedpoint' => $utilizedpoint,
				'numberofpay' => $numberofpay,
				'prodcode' => $prodcode,
				'last4digitpan' => $last4digitpan,
				'debug' => $debug
			);
			
			// 取得 check mac
			$MACString = $oCTBCAgent->CheckMac($data);

			//echo "checkm=$MACString\n";
			//echo "outmac=$outmac\n";
			
			// 驗証內容正確性
			if(strcmp($MACString, $outmac) == 0){
				$data = array(
					'errdesc' => $errdesc,
					'authresurl' => $authresurl,
					'xid' => $xid,
					'awardedpoint' => $awardedpoint,
					'prodcode' => $prodcode,		
					'merid' => $merid,
					'status' => $status,
					'errcode' => $errcode,
					'authcode' => $authcode,
					'authamt' => $authamt,
					'lidm' => $lidm,
					'offsetamt' => $offsetamt,
					'originalamt' => $originalamt,
					'utilizedpoint' => $utilizedpoint,
					'numberofpay' => $numberofpay,
					'last4digitpan' => $last4digitpan,
					'pidresult' => $pidresult,
					'cardnumber' => $cardnumber,
					'outmac' => $outmac
				);
			}else{
				// CHECK MAC FAIL
				$data = array(
					'errdesc' => "[CHECK MAC FAIL] MACString=$MACString",
					'merid' => $merid,
					'outmac' => $outmac
				);
			}
			
			$this->db->insert('ctbc_feedback_log', $data); // 記錄 log
			return $data;
			
		}catch (Exception $e){
			// 例外錯誤處理。
			trigger_error(__FUNCTION__.$e->getMessage());	
		}
	}

}
