<?php

/**
 * API 代碼
 */
 abstract class TWGC_API_NO
 {
   const BalanceInquiry = 1;       // 1: 查詢 BarCode
   const Register = 2;             // 2: 卡片記名
   const VirtualCardActivation = 3;// 3: 虛擬卡開卡
   const Refund = 4;               // 4: 退款
   const CheckResult = 5;          // 5: 確認交易結果
   const BalanceMaintenance3 = 6;  // 6: 會員綁定卡片消費
   const UnRegister = 7;           // 7: 取消卡片記名
   const Reload2 = 8;              // 8: 卡片儲值
   const GetOTPin2 = 9;            // 9: 拿PIN碼
   const Suspend = 10;             // 10: 停用
 }

/**
 * 取得APP TOKEN服務的類別。
 */
class TWGCAgent {

  public $issuerIdentity = "issuerIdentity";
  public $IssuerID = "IssuerID";
  public $StoreID = "StoreID";
  public $POSID = "POSID";
  public $ServiceURL = "ServiceURL";

  /**
   * 取得TWGC 服務的建構式。
   */
  function __construct() {
      $this->TWGCAgent();
  }

  /**
   * 取得TWGC 服務的實體。
   */
  function TWGCAgent() {
  }

  /**
   * API 1: 查詢 BarCode
   *
   * 說明: 查詢 BarCode 狀態、餘額及紅利點數
   *
   * [data]
   *  *BarCode：卡號
   */
  public function BalanceInquiry($data) {
  	extract($data);

  	if (empty($POSTime)) {
  		$POSTime = date('Y/m/d H:i:s', time());
  	}

    if (empty($MbrPhone)) { $MbrPhone = ""; }

  	$BarCode_str = '';
  	if (isset($BarCode)) {
  		$BarCode_str = '<BarCode>'. $BarCode .'</BarCode>';
  	}

  	$Card_str = $BarCode_str;

  	$xml =
    '<DPReq>
      <IssuerID>'. $this->IssuerID .'</IssuerID>
      <StoreID>'. $this->StoreID .'</StoreID>
      <POSID>'. $this->POSID .'</POSID>

      <CustTrxNo>'. $CustTrxNo .'</CustTrxNo>
      <Detail>
        <Card>'. $Card_str .'</Card>
      </Detail>
      <MbrPhone>'. $MbrPhone .'</MbrPhone>
      <POSTime>'. $POSTime .'</POSTime>
    </DPReq>';

  	return $this->getResultXml(__FUNCTION__, $xml);
  }

  /**
   * API 2: 卡片記名
   *
   * 說明: 將 BarCode 綁定 UserID
   *
   * [data]
   *  *UserID：會員編號 (最多 20 字元)
   *  UserName：會員姓名
   *  UserSex：會員性別 (M/F/空字串 )
   *  Birthday：會員生日 (YYYY/MM/DD)
   *  Addr：會員地址
   *  Email：會員電子郵件
   *  TelHZone：會員市內電話區碼
   *  TelH：會員市內電話
   *  MbrPhone：手機號碼
   *  Memo：備註
   *  *BarCode：記名卡號
   */
  public function Register($data) {
  	extract($data);

  	if (empty($POSTime)) {
  		$POSTime = date('Y/m/d H:i:s', time());
  	}

    if (empty($Birthday)) {
  		$Birthday = $POSTime;
  	}

    if (empty($UserName)) { $UserName = ""; }
    if (empty($UserSex)) { $UserSex = ""; }
    if (empty($Addr)) { $Addr = ""; }
    if (empty($Email)) { $Email = ""; }
    if (empty($TelHZone)) { $TelHZone = ""; }
    if (empty($TelH)) { $TelH = ""; }
    if (empty($MbrPhone)) { $MbrPhone = ""; }
    if (empty($Memo)) { $Memo = ""; }
    if (empty($StaffID)) { $StaffID = ""; }

    $xml =
    '<DPReq>
      <IssuerID>'. $this->IssuerID .'</IssuerID>
      <StoreID>'. $this->StoreID .'</StoreID>
      <POSID>'. $this->POSID .'</POSID>

      <CustTrxNo>'. $CustTrxNo .'</CustTrxNo>
      <UserID>'. $UserID .'</UserID>
      <MbrPhone>'. $MbrPhone .'</MbrPhone>
      <BarCode>'. $BarCode .'</BarCode>
      <POSTime>'. $POSTime .'</POSTime>
      <StaffID>'. $StaffID .'</StaffID>
    </DPReq>';

    /*
  	$xml =
    '<DPReq>
      <IssuerID>'. $this->IssuerID .'</IssuerID>
      <StoreID>'. $this->StoreID .'</StoreID>
      <POSID>'. $this->POSID .'</POSID>

      <CustTrxNo>'. $CustTrxNo .'</CustTrxNo>
      <UserID>'. $UserID .'</UserID>
      <UserName>'. $UserName .'</UserName>
      <UserSex>'. $UserSex .'</UserSex>
      <Birthday>'. $Birthday .'</Birthday>
      <Addr>'. $Addr .'</Addr>
      <Email>'. $Email .'</Email>
      <TelHZone>'. $TelHZone .'</TelHZone>
      <TelH>'. $TelH .'</TelH>
      <MbrPhone>'. $MbrPhone .'</MbrPhone>
      <Memo>'. $Memo .'</Memo>
      <BarCode>'. $BarCode .'</BarCode>
      <POSTime>'. $POSTime .'</POSTime>
      <StaffID>'. $StaffID .'</StaffID>
    </DPReq>';
    */

  	return $this->getResultXml(__FUNCTION__, $xml);
  }

  /**
   * API 3: 虛擬卡開卡
   *
   * 說明: 用 EAN 去建立任意面額的 BarCode, 剛建立好的 BarCode 未綁定 UserID
   *
   * [data]
   *  *EAN：卡號前 13碼
   *  *Amount：卡片面額
   *  Timeout：未收到開卡結果的Timeout時間，0~999
   */
  public function VirtualCardActivation($data) {
    extract($data);

    if (empty($TransTime)) {
      $TransTime = date('Y/m/d H:i:s', time());
    }

    if (empty($Timeout)) { $Timeout = 100; } // 未收到開卡結果的Timeout時間，0~999

    $EAN_str = '';
    if (isset($EAN)) {
      $EAN_str = '<EAN>'. $EAN .'</EAN>';
    }
    $Amount_str = '';
    if (isset($Amount)) {
      $Amount_str = '<Amount>'. $Amount .'</Amount>';
    }

    $Card_str = $EAN_str.$Amount_str;

    $xml =
    '<DPReq>
      <IssuerID>'. $this->IssuerID .'</IssuerID>
      <StoreID>'. $this->StoreID .'</StoreID>
      <POSID>'. $this->POSID .'</POSID>

      <CustTrxNo>'. $CustTrxNo .'</CustTrxNo>
      <TransTime>'. $TransTime .'</TransTime>
      <Timeout>'. $Timeout .'</Timeout>
      <Detail>
        <Card>'. $Card_str .'</Card>
      </Detail>
    </DPReq>';

    return $this->getResultXml(__FUNCTION__, $xml);
  }

  /**
   * API 4: 退款
   *
   * 說明: 退款作業 (取消消費交易)
   *
   * [data]
   *  *CustTrxNo：原交易序號
   *  *AuthCode：原交易授權碼
   */
  public function Refund($data) {
  	extract($data);

  	if (empty($POSTime)) {
  		$POSTime = date('Y/m/d H:i:s', time());
  	}

    if (empty($MbrPhone)) { $MbrPhone = ""; }
    if (empty($StaffID)) { $StaffID = ""; }

  	$xml =
    '<DPReq>
      <IssuerID>'. $this->IssuerID .'</IssuerID>
      <StoreID>'. $this->StoreID .'</StoreID>
      <POSID>'. $this->POSID .'</POSID>

      <CustTrxNo>'. $CustTrxNo .'</CustTrxNo>
      <AuthCode>'. $AuthCode .'</AuthCode>
      <MbrPhone>'. $MbrPhone .'</MbrPhone>
      <POSTime>'. $POSTime .'</POSTime>
      <StaffID>'. $StaffID .'</StaffID>
    </DPReq>';

  	return $this->getResultXml(__FUNCTION__, $xml);
  }

  /**
   * API 5: 確認交易結果
   *
   * 說明: 確認交易結果
   *
   * [data]
   *  *CustTrxNo：原交易序號
   */
  public function CheckResult($data) {
  	extract($data);

  	if (empty($POSTime)) {
  		$POSTime = date('Y/m/d H:i:s', time());
  	}

    if (empty($MbrPhone)) { $MbrPhone = ""; }

  	$xml =
    '<DPReq>
      <IssuerID>'. $this->IssuerID .'</IssuerID>
      <StoreID>'. $this->StoreID .'</StoreID>
      <POSID>'. $this->POSID .'</POSID>

      <CustTrxNo>'. $CustTrxNo .'</CustTrxNo>
      <MbrPhone>'. $MbrPhone .'</MbrPhone>
      <POSTime>'. $POSTime .'</POSTime>
    </DPReq>';

  	return $this->getResultXml(__FUNCTION__, $xml);
  }

  /**
   * API 6: 會員綁定卡片消費
   *
   * 說明: 使用 已綁定 MemberID 的 BarCode 消費
   *
   * [data]
   *  *MemberID：會員編號 (ex. 車號)
   *  *BarCode：卡號
   *  ReqAmount：總交易金額 總交易金額 (包含非 卡片交易金額 )
   *  *Amount：卡片 交易金額 (包含使 用紅利點數金額 )
   *  ProductID：商品代號
   *  ReedemPointsForCash：使用紅利點數折抵功能，會以紅利可折抵的最大金額扣除紅利，並優先扣除 , 使用請填 “Y”，不使用則不填
   */
  public function BalanceMaintenance3($data, $redeem) {
  	extract($data);

  	if (empty($POSTime)) {
  		$POSTime = date('Y/m/d H:i:s', time());
  	}

    $RedeemPointsForCash = "";
    if ($redeem) {
      $RedeemPointsForCash = "Y"; // 紅利點數折抵
    }

    if (empty($ReqAmount)) { $ReqAmount = ""; }
    if (empty($MbrPhone)) { $MbrPhone = ""; }
    if (empty($StaffID)) { $StaffID = ""; }
    if (empty($ProductID)) { $ProductID = ""; }

  	$xml =
    '<DPReq>
      <IssuerID>'. $this->IssuerID .'</IssuerID>
      <StoreID>'. $this->StoreID .'</StoreID>
      <POSID>'. $this->POSID .'</POSID>

      <MemberID>'. $MemberID .'</MemberID>
      <BarCode>'. $BarCode .'</BarCode>
      <ReqAmount>'. $ReqAmount .'</ReqAmount>
      <Amount>'. $Amount .'</Amount>
      <CustTrxNo>'. $CustTrxNo .'</CustTrxNo>
      <MbrPhone>'. $MbrPhone .'</MbrPhone>
      <POSTime>'. $POSTime .'</POSTime>
      <StaffID>'. $StaffID .'</StaffID>
      <ProductID>'. $ProductID .'</ProductID>
      <RedeemPointsForCash>'. $RedeemPointsForCash .'</RedeemPointsForCash>
    </DPReq>';

  	return $this->getResultXml(__FUNCTION__, $xml);
  }

  /**
   * API 7: 取消卡片記名
   *
   * 說明: 取消卡片記名
   *
   * [data]
   *  *UserID:要取消記名的會員編號
   *  *MbrPhone:要取消記名的會員電話
   *  *BarCode:要取消記名的卡號
   */
  public function UnRegister($data) {
  	extract($data);

  	if (empty($POSTime)) { $POSTime = date('Y/m/d H:i:s', time()); }
    if (empty($MbrPhone)) { $MbrPhone = ""; }
    if (empty($StaffID)) { $StaffID = ""; }

    $xml =
    '<DPReq>
      <IssuerID>'. $this->IssuerID .'</IssuerID>
      <StoreID>'. $this->StoreID .'</StoreID>
      <POSID>'. $this->POSID .'</POSID>

      <CustTrxNo>'. $CustTrxNo .'</CustTrxNo>
      <UserID>'. $UserID .'</UserID>
      <MbrPhone>'. $MbrPhone .'</MbrPhone>
      <BarCode>'. $BarCode .'</BarCode>
      <POSTime>'. $POSTime .'</POSTime>
      <StaffID>'. $StaffID .'</StaffID>
    </DPReq>';

  	return $this->getResultXml(__FUNCTION__, $xml);
  }

  /**
   * API 8: 卡片儲值
   *
   * 說明:
   *  1. 實體卡: 括開銀漆後會有 PIN 碼, 輸入 PIN 即可代表該卡片
   *  2. 虛擬卡: 要先向 TWGC 取得一個一次性的密碼作為 PIN 碼, 進行儲值交易 (GetOTPin2)
   *
   * [data]
   *  *Amount:儲值金額最大共10碼數字,單位為元
   *  PromotionID:促銷活動代號 (填空字串即可)
   *  PIN:儲值碼
   *
   */
  public function Reload2($data) {
    extract($data);

    if (empty($POSTime)) { $POSTime = date('Y/m/d H:i:s', time()); }
    if (empty($PIN)) { $PIN = ""; }
    if (empty($MbrPhone)) { $MbrPhone = ""; }
    if (empty($StaffID)) { $StaffID = ""; }
    if (empty($PromotionID)) { $PromotionID = ""; }

    $xml =
    '<DPReq>
      <IssuerID>'. $this->IssuerID .'</IssuerID>
      <StoreID>'. $this->StoreID .'</StoreID>
      <POSID>'. $this->POSID .'</POSID>

      <CustTrxNo>'. $CustTrxNo .'</CustTrxNo>
      <PIN>'. $PIN .'</PIN>
      <Amount>'. $Amount .'</Amount>
      <MbrPhone>'. $MbrPhone .'</MbrPhone>
      <POSTime>'. $POSTime .'</POSTime>
      <StaffID>'. $StaffID .'</StaffID>
      <PromotionID>'. $PromotionID .'</PromotionID>
    </DPReq>';

    return $this->getResultXml(__FUNCTION__, $xml);
  }

  /**
   * API 9: 取得卡片儲值 PIN 碼
   *
   *  說明:
   *  GetOTPin2 會回傳一個圖檔 (以 base64 解碼還原為圖檔), 圖檔裡有 PIN 碼
   *
   * [data]
   *  *RequestTime:本機時間
   *  *PasswordType:固定填寫 ‘TWGCAPP’
   *  *Size:回傳圖檔大小 (L/M/S)
   */
  public function GetOTPin2($data) {
    extract($data);

    if (empty($RequestTime)) { $RequestTime = date('Y/m/d H:i:s', time()); }
    if (empty($PasswordType)) { $PasswordType = "TWGCAPP"; }
    if (empty($Size)) { $Size = "S"; }

    $xml =
    '<DPReq>
      <RequestTime>'. $RequestTime .'</RequestTime>
      <PasswordType>'. $PasswordType .'</PasswordType>
      <BarCode>'. $BarCode .'</BarCode>
      <Size>'. $Size .'</Size>
    </DPReq>';

    return $this->getResultXml(__FUNCTION__, $xml);
  }

  /**
   * API 10: 停用
   *
   *  說明: 停用 Barcode
   *
   * [data]
   *  *BarCode：要停用的卡號
   *  *Reason： 停用原因 (1.遺失, 2.損壞, 3.更換新卡而停用本卡, 4.單純停用)
   */
  public function Suspend($data) {
    extract($data);

    if (empty($PIN)) { $PIN = ""; }
    if (empty($MbrPhone)) { $MbrPhone = ""; }
    if (empty($TransTime)) { $TransTime = date('Y-m-d H:i:s', time()); }
    if (empty($Reason)) { $Reason = "4"; } // 4.單純停用

    $xml =
    '<DPReq>
      <IssuerID>'. $this->IssuerID .'</IssuerID>
      <StoreID>'. $this->StoreID .'</StoreID>
      <POSID>'. $this->POSID .'</POSID>

      <CustTrxNo>'. $CustTrxNo .'</CustTrxNo>
      <BarCode>'. $BarCode .'</BarCode>
      <PIN>'. $PIN .'</PIN>
      <MbrPhone>'. $MbrPhone .'</MbrPhone>
      <TransTime>'. $TransTime .'</TransTime>
      <Reason>'. $Reason .'</Reason>
    </DPReq>';

    return $this->getResultXml(__FUNCTION__, $xml);
  }

  /**
   * 取得 TWGC 回傳 XML
   */
  private function getResultXml($function, $xml) {
    $url = $this->ServiceURL.'/'.$function;
    $parameters =
      array(
        'issuerIdentity' => $this->issuerIdentity,
        'xml' => base64_encode($xml)
      );
    $spResult = $this->ServerPost($url, $parameters);
    // parse result
    $result_decode = base64_decode(substr(simplexml_load_string($spResult), 5));
    //echo json_encode(simplexml_load_string($result_decode));
    return simplexml_load_string($result_decode);
  }

  private function ServerPost($url, $parameters=array()) {
      $ch = curl_init();

      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_HEADER, FALSE);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
      curl_setopt($ch, CURLOPT_POST, TRUE);
      curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));
      $rs = curl_exec($ch);

      curl_close($ch);

      return $rs;
  }

}
