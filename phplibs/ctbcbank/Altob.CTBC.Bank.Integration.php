<?php
require_once 'auth_mpi_mac.php'; // 24 位元
//require_once 'auth_mpi_mac8.php'; // 8 位元

/**
 * 中國信託金流介接
 */
class CTBCAgent {

  public $SSLAuthUI = "SSLAuthUI";    // URL授權介面

  public $MerchantID = "MerchantID";  // 銀行所授與的特店代號，純數字，固定 13 碼。
  public $TerminalID = "TerminalID";  // 銀行所授與的終端機代號，純數字，固定 8 碼。
  public $Option = "Option"; // 純數字欄位，依交易方式不同填入不同的資料，說明如下：
  /*
     一般交易請填「1」。
     分期交易請填一到兩碼的分期期數。
     紅利交易請填固定兩碼的產品代碼。
     紅利分期交易請填第一至二碼固定為產品代碼，第三碼或三至四碼為分期期數。
  */
  public $Key = "Key";  // 此為貴特店在URL 帳務管理後台登錄的壓碼字串。
  public $MerchantName = "MerchantName";  // 特店所要顯示的商店名稱，中文請填BIG5 碼。
  public $OrderDetail = "OrderDetail";  // 訂單描述，中文請填BIG5 碼。
  public $AutoCap = "AutoCap";  //  是否自動請款 (0–不自動請款, 1–自動請款)
  public $AuthResURL = "AuthResURL";  // 從收單行端取得授權碼後，要導回的網址，請勿填入特殊字元@、#、%、?、&等。
  public $Customize = "Customize";    // 設定刷卡頁顯示特定語系或客制化頁面。(1–繁體中文, 2–簡體中文, 3–英文, 5–客制化頁面)

  function __construct() {
      $this->CTBCAgent();
  }

  function CTBCAgent() {
  }

  /**
   * 1. CTBC 結帳
   *
   * [data]
   *  *merID : 特店網站專用代號
   *  *URLEnc: 交易訊息的密文(URLEnc)
   */
  public function CheckOut($data) {
    extract($data);

    // 取得url enc
    $urlEnc = $this->URLEnc($data);

    // 轉址
    $szHtml = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
    $szHtml .= '<div style="text-align:center;" ><form id="__ctbcForm" method="post" target="' . $target . '" action="' . $this->SSLAuthUI . '">';
    $szHtml .="<input type='hidden' name='URLEnc' value='$urlEnc' />";
    $szHtml .="<input type='hidden' name='merID' value='$merID' />";
    $szHtml .= '<script type="text/javascript">document.getElementById("__ctbcForm").submit();</script>';
    $szHtml .= '</form></div>';
    echo $szHtml;
  }

  /**
    * 2. 解密URL 交易訊息的密文(URLDec)
    *
    * [data]
    *  *encRes= : 密文
    *  debug: 預設(進行交易時)請填0，偵錯時請填1。
   */
  public function Decrypt($data) {
    extract($data);

    if (empty($debug)) { $debug = "0"; }

    return gendecrypt(
      $encRes,
      $this->Key,
      $debug);
  }

  /**
   * 3. 取得 checkmac
   */
  public function CheckMac($data) {
    extract($data);

    if (empty($debug)) { $debug = "0"; }

    if (!empty($prodcode)) {
      $option = $prodcode;    // option: 紅利交易
    }else{
      $option = $numberofpay; // option: 一般交易, 分期交易
    }

    return auth_out_mac(
			  $status, $errcode, $authcode, $authamt, $lidm, $offsetamt, $originalamt, $utilizedpoint,
			  $option,
			  $last4digitpan,
        $this->Key,
        $debug);
  }

  /**
   * 解密URL 交易訊息的密文(URLDec)
   *
   * [data]
   *  *encRes= : 密文
   *  debug: 預設(進行交易時)請填0，偵錯時請填1。
   */
  function URLDec($data) {
    extract($data);

    if (empty($debug)) { $debug = "0"; }

    $EncArray = gendecrypt(
      $encRes,
      $this->Key,
      $debug);

    $MACString = '';
    $URLEnc = '';

    //echo "<BR>\n";

    foreach($EncArray AS $name => $val){
      echo $name ."=>". urlencode(trim($val,"\x00..\x08")) ."\n";
    }

    if(isset($EncArray['status'])){
      $status = isset($EncArray['status']) ? $EncArray['status'] : "";
      $errCode = isset($EncArray['errcode']) ? $EncArray['errcode'] : "";
      $authCode = isset($EncArray['authcode']) ? $EncArray['authcode'] : "";
      $authAmt = isset($EncArray['authamt']) ? $EncArray['authamt'] : "";
      $lidm = isset($EncArray['lidm']) ? $EncArray['lidm'] : "";
      $OffsetAmt = isset($EncArray['offsetamt']) ? $EncArray['offsetamt'] : "";
      $OriginalAmt = isset($EncArray['originalamt']) ? $EncArray['originalamt'] : "";
      $UtilizedPoint = isset($EncArray['utilizedpoint']) ? $EncArray['utilizedpoint'] : "";
      $Option = isset($EncArray['numberofpay']) ? $EncArray[' numberofpay'] : "";
      //紅利交易時請帶入prodcode
      //$Option = isset($EncArray['prodcode']) ? $EncArray['prodcode'] : "";
      $Last4digitPAN = isset($EncArray['last4digitpan']) ? $EncArray['last4digitpan'] : "";
      $pidResult= isset($EncArray['pidResult']) ? $EncArray['pidResult'] : "";
      $CardNumber = isset($EncArray['CardNumber']) ? $EncArray['CardNumber'] : "";

      $MACString = auth_out_mac(
        $status, $errCode, $authCode, $authAmt, $lidm, $OffsetAmt, $OriginalAmt, $UtilizedPoint,
        $Option,
        //$this->Option,
        $Last4digitPAN,
        $this->Key,
        $debug);

      echo "MACString=$MACString\n";

      $outmac = isset($EncArray['outmac']) ? $EncArray['outmac'] : "";
      echo "outmac=$outmac\n";

    }else{
        // do nothing
        foreach ($EncArray as $key => $value){
					switch ($key){
					/* 支付後的回傳的基本參數 */
					case "status": $status = $value; break;
					case "errcode": $errcode = $value; break;
					case "authcode": $authcode = $value; break;
					case "authamt": $authamt = $value; break;
					case "lidm": $lidm = $value; break;
					case "offsetamt": $offsetamt = $value; break;
					case "originalamt": $originalamt = $value; break;
					case "utilizedpoint": $utilizedpoint = $value; break;
					case "numberofpay": $numberofpay = $value; break;
					case "last4digitpan": $last4digitpan = $value; break;
					case "pidResult": $pid_result = $value; break;
					case "CardNumber": $card_number = $value; break;
					default: break;
					}
				}
        // 一律記錄log。
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
				  'last4digitpan' => $last4digitpan,
				  'pid_result' => $pid_result,
				  'card_number' => $card_number
				);
        echo json_encode($data);
    }

  }

  /**
   * 產生URL 交易訊息的密文(URLEnc)
   *
   * [data]
   *  *lidm : 訂單編號
   *  *purchAmt: 總金額
   *  txType: 交易方式，長度為一碼數字。(一般交易：0, 分期交易：1, 紅利折抵一般交易：2, 紅利折抵分期交易：4)
   *  debug: 預設(進行交易時)請填0，偵錯時請填1。
   */
  function URLEnc($data) {
  	extract($data);

    if (empty($txType)) { $txType = "0"; }  // 預設 0
    if (empty($debug)) { $debug = "0"; }    // 預設 0

    $MACString = auth_in_mac(
      $this->MerchantID,
      $this->TerminalID,
      $lidm,
      /* 為電子商場的應用程式所給予此筆交易的訂單編號，
      資料型態為最長19 個字元的文字串。
      訂單編號字串之字元僅接受一般英文字母、數字及底線’_’的組合，
      不可出現其餘符號字元
      */
      $purchAmt,// 為消費者此筆交易所購買商品欲授權總金額，正整數格式的字串。
      $txType,  // 交易方式
      $this->Option,
      $this->Key,
      $this->MerchantName,
      $this->AuthResURL,
      $this->OrderDetail,
      $this->AutoCap,
      $this->Customize,
      $debug);

    //echo "InMac=$MACString\n";

    $URLEnc = get_auth_urlenc(
      $this->MerchantID,
      $this->TerminalID,
      $lidm,
      $purchAmt,
      $txType,
      $this->Option,
      $this->Key,
      $this->MerchantName,
      $this->AuthResURL,
      $this->OrderDetail,
      $this->AutoCap,
      $this->Customize,
      $MACString,
      $debug);

    //echo "UrlEnc=$URLEnc\n";

    return $URLEnc;
  }

}
