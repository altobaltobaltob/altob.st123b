<?php
/*
file: Allpay_payment.php		付費系統 (歐付寶)
*/
if (!defined('BASEPATH')) exit('No direct script access allowed');
require_once(ALLPAY_FILE);

class Allpay_payment extends CI_Controller
{                 
    var $vars = array();	// 共用變數   
    
	function __construct() 
	{        
		parent::__construct();     
        // ----- 程式開發階段log設定 -----
        if (@ENVIRONMENT == 'development')
        {                        
          	ini_set('display_errors', '1');
			//error_reporting(E_ALL ^ E_NOTICE); 
			error_reporting(E_ALL); 
        }  
        set_error_handler(array($this, 'error_handler'), E_ALL);	// 資料庫異動需做log   
        
        // ----- 定義常數(路徑, cache秒數) -----       
        define('APP_VERSION', '100');		// 版本號
                                        
        define('MAX_AGE', 604800);			// cache秒數, 此定義1個月     
        define('APP_NAME', 'allpay_payment');		// 應用系統名稱   
          
        define('PAGE_PATH', APP_BASE.'ci_application/views/'.APP_NAME.'/');						// path of views
        
        define('SERVER_URL', 'http://'.(isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost').'/');	// URL
        define('APP_URL', SERVER_URL.APP_NAME.'.html/');										// controller路徑 
        define('WEB_URL', SERVER_URL.APP_NAME.'/');												// 網頁路徑
        define('WEB_LIB', SERVER_URL.'libs/');													// 網頁lib
        define('BOOTSTRAPS', WEB_LIB.'bootstrap_sb/');											// bootstrap lib  
        define('LOG_PATH', FILE_BASE.APP_NAME.'/logs/');	// log path
		
		$this->load->model('allpay_payment_model');
		$this->load->model('payment_model'); 		// 一般臨停帳單 (tx_bill)
		$this->load->model('payment_ats_model'); 	// 月租繳費機帳單 (tx_bill_ats)
		
		/*
		// ----- 正式環境 -----
		define('ALLPAY_ServiceURL', "https://payment.ecpay.com.tw/Cashier/AioCheckOut"); // 您要呼叫的服務位址 (綠界)
		//define('ALLPAY_ServiceURL', "https://payment.allpay.com.tw/Cashier/AioCheckOut"); // 您要呼叫的服務位址
		define('ALLPAY_HashKey', "tLBnwUiKlbB0e6sS"); // AllPay提供給您的Hash Key
		define('ALLPAY_HashIV', "bMhtNluToYSYoJBw"); // AllPay提供給您的Hash IV
		define('ALLPAY_MerchantID', "1148391"); // AllPay提供給您的特店編號
		// ----- 正式環境(end) -----   
		*/
		
		// ----- 測試環境 -----
		define('ALLPAY_ServiceURL', "http://payment-stage.allpay.com.tw/Cashier/AioCheckOut"); // 您要呼叫的服務位址
		define('ALLPAY_HashKey', "5294y06JbISpM5x9"); // AllPay提供給您的Hash Key
		define('ALLPAY_HashIV', "v77hoKGq4kWxNNIS"); // AllPay提供給您的Hash IV
		define('ALLPAY_MerchantID', "2000132"); // AllPay提供給您的特店編號
		// ----- 測試環境(end) -----
	}
    
    // 發生錯誤時集中在此處理
	public function error_handler($errno, $errstr, $errfile, $errline, $errcontext)
	{                                      
    	$str = date('H:i:s')."|{$errstr}|{$errfile}|{$errline}|{$errno}\n";               
    	//error_log($str, 3, $log_file . '.' . date('Ymd').'.log.txt');	// 3代表參考後面的檔名
    	error_log($str, 3, LOG_PATH.APP_NAME . '.' . date('Ymd').'.log.txt');	// 3代表參考後面的檔名
    }
	
	// 付款 (tx_bill)
	public function transfer_money_tx_bill()
	{
		$order_no = $this->uri->segment(3);	// 序號
		try{
			// 0. check tx_bill
			$data = $this->payment_model->get_tx_bill($order_no);	
			
			if (! empty($data))
			{
				$status = $data['status'];
				switch($status){
					case 100: // 狀態: 0:剛建立, 1:結帳完成, 2:錢沒對上, 3:發票沒建立, 4:手動調整, 99:訂單逾期作廢, 100:交易進行中
						$oPayment = new AllInOne();
						$oPayment->ServiceURL = ALLPAY_ServiceURL;
						$oPayment->HashKey = ALLPAY_HashKey;
						$oPayment->HashIV = ALLPAY_HashIV;
						$oPayment->MerchantID = ALLPAY_MerchantID;
						/* 基本參數 */
						$oPayment->Send['ReturnURL'] = STATION_URL.APP_NAME.'.html/tx_bill_finished/';	// 您要收到付款完成通知的伺服器端網址(server)
						$oPayment->Send['ClientBackURL'] = $data['client_back_url']; 	// 您要歐付寶返回按鈕導向的瀏覽器端網址";
						$oPayment->Send['OrderResultURL'] = $data['order_result_url']; 	// 您要收到付款完成通知的瀏覽器端網址(browser) ps. WebATM大部份銀行都回不來;
						$oPayment->Send['MerchantTradeNo'] = $data['order_no']; // 您此筆訂單交易編號
						$oPayment->Send['MerchantTradeDate'] = date('Y/m/d H:i:s', strtotime($data['tx_time'])); // 交易時間
						$oPayment->Send['TotalAmount'] = (int) $data['amt']; // 您此筆訂單的交易總金額
						$oPayment->Send['TradeDesc'] = $data['invoice_remark']; // 您該筆訂單的描述
						$oPayment->Send['ChoosePayment'] = PaymentMethod::ALL; // PaymentMethod::WebATM;
						$oPayment->Send['Remark'] = "";
						$oPayment->Send['ChooseSubPayment'] = PaymentMethodItem::None;
						$oPayment->Send['NeedExtraPaidInfo'] = ExtraPaymentInfo::No;
						$oPayment->Send['DeviceSource'] = DeviceType::Mobile; //DeviceType::PC;
						//$oPayment->Send['IgnorePayment'] = "Alipay#Tenpay"; //"<<您不要顯示的付款方式>>"; // 例(排除支付寶與財富通): Alipay#Tenpay
							 
						// 加入選購商品資料。
						array_push($oPayment->Send['Items'], 
							array(
								'Name' => "結算", 
								'Price' => (int)$data['amt'], 
								'Currency' => "元", 
								'Quantity' => (int) "1", 
								'URL' => "http://www.altob.com.tw" // 網址是做什麼用的 ?
							)
						);

						/* 產生訂單 */
						$oPayment->CheckOut();
						/* 產生訂單 Html Code 的方法 */
						$szHtml = $oPayment->CheckOutString();
						
					default:
						$sMsg = 'status != 100';
						trigger_error(APP_NAME.', '._FUNCTION_.', order_no=>' . $order_no.'<br>'.$sMsg);
						echo $sMsg;
				}
			}
			
		}catch (Exception $e){
			// 例外錯誤處理。
			$sMsg = $e->getMessage();
			trigger_error(APP_NAME.', '._FUNCTION_.', order_no=>' . $order_no.'<br>'.$sMsg);
			echo $sMsg;
		}
	}
	
	// 付款 (tx_bill_ats)
	public function transfer_money_tx_bill_ats()
	{
		$order_no = $this->uri->segment(3);	// 序號
		try{
			// 0. check tx_bill
			$data = $this->payment_ats_model->get_tx_bill($order_no);				
			
			if (! empty($data))
			{
				$status = $data['status'];
				switch($status){
					case 100: // 狀態: 0:剛建立, 1:結帳完成, 2:錢沒對上, 3:發票沒建立, 4:手動調整, 99:訂單逾期作廢, 100:交易進行中
						$oPayment = new AllInOne();
						$oPayment->ServiceURL = ALLPAY_ServiceURL;
						$oPayment->HashKey = ALLPAY_HashKey;
						$oPayment->HashIV = ALLPAY_HashIV;
						$oPayment->MerchantID = ALLPAY_MerchantID;
						/* 基本參數 */
						$oPayment->Send['ReturnURL'] = STATION_URL.APP_NAME.'.html/tx_bill_ats_finished/';	// 您要收到付款完成通知的伺服器端網址(server)
						$oPayment->Send['ClientBackURL'] = $data['client_back_url']; 	// 您要歐付寶返回按鈕導向的瀏覽器端網址";
						$oPayment->Send['OrderResultURL'] = $data['order_result_url']; 	// 您要收到付款完成通知的瀏覽器端網址(browser) ps. WebATM大部份銀行都回不來;
						$oPayment->Send['MerchantTradeNo'] = $data['order_no']; // 您此筆訂單交易編號
						$oPayment->Send['MerchantTradeDate'] = date('Y/m/d H:i:s', strtotime($data['tx_time'])); // 交易時間
						$oPayment->Send['TotalAmount'] = (int) $data['amt']; // 您此筆訂單的交易總金額
						$oPayment->Send['TradeDesc'] = $data['invoice_remark']; // 您該筆訂單的描述
						$oPayment->Send['ChoosePayment'] = PaymentMethod::WebATM; //PaymentMethod::ALL;;
						$oPayment->Send['Remark'] = "";
						$oPayment->Send['ChooseSubPayment'] = PaymentMethodItem::None;
						$oPayment->Send['NeedExtraPaidInfo'] = ExtraPaymentInfo::No;
						$oPayment->Send['DeviceSource'] = DeviceType::PC; //DeviceType::Mobile;
						//$oPayment->Send['IgnorePayment'] = "Alipay#Tenpay"; //"<<您不要顯示的付款方式>>"; // 例(排除支付寶與財富通): Alipay#Tenpay
							 
						// 加入選購商品資料。
						array_push($oPayment->Send['Items'], 
							array(
								'Name' => "結算", 
								'Price' => (int)$data['amt'], 
								'Currency' => "元", 
								'Quantity' => (int) "1", 
								'URL' => "http://www.altob.com.tw" // 網址是做什麼用的 ?
							)
						);

						/* 產生訂單 */
						$oPayment->CheckOut();
						/* 產生訂單 Html Code 的方法 */
						$szHtml = $oPayment->CheckOutString();
						
					default:
						$sMsg = 'status != 100';
						trigger_error(APP_NAME.', '._FUNCTION_.', order_no=>' . $order_no.'<br>'.$sMsg);
						echo $sMsg;
				}
			}
			
		}catch (Exception $e){
			// 例外錯誤處理。
			$sMsg = $e->getMessage();
			trigger_error(APP_NAME.', '._FUNCTION_.', order_no=>' . $order_no.'<br>'.$sMsg);
			echo $sMsg;
		}
	}
    
  	// 付款完成 (tx_bill)
  	public function tx_bill_finished()
	{
		ob_end_clean();
		ignore_user_abort();
		ob_start();
		
		try{
			$oPayment = new AllInOne();
			/* 服務參數 */   
			$oPayment->HashKey = ALLPAY_HashKey;
			$oPayment->HashIV = ALLPAY_HashIV;
			$oPayment->MerchantID = ALLPAY_MerchantID;
			/* 取得回傳參數 */
			$arFeedback = $oPayment->CheckOutFeedback();
			/* 檢核與變更訂單狀態 */
			if (sizeof($arFeedback) > 0){
				foreach ($arFeedback as $key => $value){
					switch ($key){
					/* 支付後的回傳的基本參數 */
					case "MerchantID": $szMerchantID = $value; break;
					case "MerchantTradeNo": $szMerchantTradeNo = $value; break;
					case "PaymentDate": $szPaymentDate = $value; break;
					case "PaymentType": $szPaymentType = $value; break;
					case "PaymentTypeChargeFee": $szPaymentTypeChargeFee = $value; break;
					case "RtnCode": $szRtnCode = $value; break;
					case "RtnMsg": $szRtnMsg = $value; break;
					case "SimulatePaid": $szSimulatePaid = $value; break;
					case "TradeAmt": $szTradeAmt = $value; break;
					case "TradeDate": $szTradeDate = $value; break;
					case "TradeNo": $szTradeNo = $value; break;
					default: break;
					}
				}
				// 一律記錄log。
				$data = array(
				  'merchant_id' => $szMerchantID,
				  'merchant_trade_no' => $szMerchantTradeNo,
				  'payment_date' => $szPaymentDate,
				  'payment_type' => $szPaymentType,
				  'payment_type_charge_fee' => $szPaymentTypeChargeFee,
				  'rtn_code' => $szRtnCode,
				  'rtn_msg' => $szRtnMsg,
				  'simulate_paid' => $szSimulatePaid,
				  'trade_amt' => $szTradeAmt,
				  'trade_date' => $szTradeDate,
				  'trade_no' => $szTradeNo
				);
				$this->allpay_payment_model->create_allpay_feedback_log($data);
				
				print '1|OK'; //先回傳ok
				
				header('Connection: close');
				header('Content-Length: ' . ob_get_length()); 
				ob_end_flush();
				flush();
			
				// 此筆交易為成功
				if($szRtnCode == '1')
				{
					$data = $this->payment_model->get_tx_bill($szMerchantTradeNo);	
					if (! empty($data))
					{
						$order_no = $data['order_no'];
						$amt = $data['amt'];
						$status = $data['status'];
						switch($status){
							case 100: // 狀態: 0:剛建立, 1:結帳完成, 2:錢沒對上, 3:發票沒建立, 4:手動調整, 99:訂單逾期作廢, 100:交易進行中
								// 印發票流程
								if($szTradeAmt == $amt){
									// 先記錄
									$this->payment_model->transfer_money_done($order_no);
									
									if(! empty($data["service_url"])){
										file_get_contents($data["service_url"]."/{$order_no}"); // 執行指定的結帳呼叫
										
									}else{
										// 找不到付款成功服務位址
										trigger_error(APP_NAME.', '._FUNCTION_.', order_no=>' . $order_no.'<br>'.'service_url not found..');
									}
									
								}else{
									// 錢沒對上
									$this->payment_model->transfer_money_done_with_amt_error($szMerchantTradeNo);
								}
								break;
							default:
								// 對方多傳一次時??
								trigger_error(APP_NAME.', '._FUNCTION_.', order_no=>' . $order_no.'<br>'.'tx_bill.status != 100');
						}
					}else{
						// 我們自己找不到記錄時??
						trigger_error(APP_NAME.', '._FUNCTION_.', order_no=>' . $order_no.'<br>'.'tx_bill NOT FOUND !!');
					}
				}
			
			}else{
				print '0|Fail';
			}
			
		}catch (Exception $e){
		  // 例外錯誤處理。
		  print '0|' . $e->getMessage();
		}
		
		exit();
	}
	
	// 付款完成 (tx_bill_ats)
  	public function tx_bill_ats_finished()
	{
		ob_end_clean();
		ignore_user_abort();
		ob_start();
		
		try{
			$oPayment = new AllInOne();
			/* 服務參數 */   
			$oPayment->HashKey = ALLPAY_HashKey;
			$oPayment->HashIV = ALLPAY_HashIV;
			$oPayment->MerchantID = ALLPAY_MerchantID;
			/* 取得回傳參數 */
			$arFeedback = $oPayment->CheckOutFeedback();
			/* 檢核與變更訂單狀態 */
			if (sizeof($arFeedback) > 0){
				foreach ($arFeedback as $key => $value){
					switch ($key){
					/* 支付後的回傳的基本參數 */
					case "MerchantID": $szMerchantID = $value; break;
					case "MerchantTradeNo": $szMerchantTradeNo = $value; break;
					case "PaymentDate": $szPaymentDate = $value; break;
					case "PaymentType": $szPaymentType = $value; break;
					case "PaymentTypeChargeFee": $szPaymentTypeChargeFee = $value; break;
					case "RtnCode": $szRtnCode = $value; break;
					case "RtnMsg": $szRtnMsg = $value; break;
					case "SimulatePaid": $szSimulatePaid = $value; break;
					case "TradeAmt": $szTradeAmt = $value; break;
					case "TradeDate": $szTradeDate = $value; break;
					case "TradeNo": $szTradeNo = $value; break;
					default: break;
					}
				}
				// 一律記錄log。
				$data = array(
				  'merchant_id' => $szMerchantID,
				  'merchant_trade_no' => $szMerchantTradeNo,
				  'payment_date' => $szPaymentDate,
				  'payment_type' => $szPaymentType,
				  'payment_type_charge_fee' => $szPaymentTypeChargeFee,
				  'rtn_code' => $szRtnCode,
				  'rtn_msg' => $szRtnMsg,
				  'simulate_paid' => $szSimulatePaid,
				  'trade_amt' => $szTradeAmt,
				  'trade_date' => $szTradeDate,
				  'trade_no' => $szTradeNo
				);
				$this->allpay_payment_model->create_allpay_feedback_log($data);
				
				print '1|OK'; //先回傳ok
				
				header('Connection: close');
				header('Content-Length: ' . ob_get_length()); 
				ob_end_flush();
				flush();
			
				// 此筆交易為成功
				if($szRtnCode == '1')
				{
					$data = $this->payment_ats_model->get_tx_bill($szMerchantTradeNo);	
					if (! empty($data))
					{
						$order_no = $data['order_no'];
						$amt = $data['amt'];
						$status = $data['status'];
						switch($status){
							case 100: // 狀態: 0:剛建立, 1:結帳完成, 2:錢沒對上, 3:發票沒建立, 4:手動調整, 99:訂單逾期作廢, 100:交易進行中
								// 印發票流程
								if($szTradeAmt == $amt){
									// 先記錄
									$this->payment_ats_model->transfer_money_done($order_no);
									
									if(! empty($data["service_url"])){
										file_get_contents($data["service_url"]."/{$order_no}"); // 執行指定的結帳呼叫
										
									}else{
										// 找不到付款成功服務位址
										trigger_error(APP_NAME.', '._FUNCTION_.', order_no=>' . $order_no.'<br>'.' service_url not found..');
									}
									
								}else{
									// 錢沒對上
									$this->payment_ats_model->transfer_money_done_with_amt_error($szMerchantTradeNo);
								}
								break;
							default:
								// 對方多傳一次時??
								trigger_error(APP_NAME.', '._FUNCTION_.', order_no=>' . $order_no.'<br>'.' status != 100');
						}
					}else{
						// 我們自己找不到記錄時??
						trigger_error(APP_NAME.', '._FUNCTION_.', order_no=>' . $order_no.'<br>'.' NOT FOUND !!');
					}
				}
			
			}else{
				print '0|Fail';
			}
			
		}catch (Exception $e){
		  // 例外錯誤處理。
		  print '0|' . $e->getMessage();
		}
		
		exit();
	}
	
}
