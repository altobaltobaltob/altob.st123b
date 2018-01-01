<?php
require_once '/home/bigbang/libs/Workerman/Autoloader.php';

use Workerman\Worker;  
Worker::$logFile = '/dev/null';		// 不記錄log file 
//Worker::$pidFile = '/tmp/run/'.basename(__FILE__).'.pid';
//Worker::$logFile = __DIR__ . '/../acer2server.log';

// 場站共用設定檔
require_once '/home/bigbang/apps/coworker/station.config.php'; 
define('APP_NAME', 'acer');	// application name

define('WORKERMAN_DEBUG', 1);
if (WORKERMAN_DEBUG)
{
	ini_set('display_errors', '1');
	error_reporting(E_ALL); 
	set_error_handler('error_handler', E_ALL);
}   

///////////////////////////////
//
// 回傳代碼定義
//
///////////////////////////////

// 結果代碼
define('ALTOB_RESULT_CODE_SUCCESS', 'OK');	// 成功
define('ALTOB_RESULT_CODE_FAIL', 	'GG');	// 失敗

// 錯誤碼
define('ALTOB_ERROR_CODE_NONE', 						'0000');	// 預設值 （成功帶這個）
define('ALTOB_ERROR_CODE_UNKNOWN_INPUT', 				'1001');	// 未知的 輸入
define('ALTOB_ERROR_CODE_UNKNOWN_CMD', 					'1002');	// 未知的 CMD
define('ALTOB_ERROR_CODE_UNDEFINED', 					'9999');	// 未定義的錯誤
define('ALTOB_ERROR_CODE_ACER_RESULT_FAIL', 			'2001');	// ACER 回傳處理錯誤


///////////////////////////////
//
// 主程式
//
///////////////////////////////

// 傳送主機資料
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true); // 啟用POST

// 建立一個Worker監聽8033埠，不使用任何應用層協定
$tcp_worker = new Worker("tcp://0.0.0.0:8033");      

// 啟動N個進程對外提供服務
$tcp_worker->count = 4;

$tcp_worker->onConnect = function($connection)
{
    echo "New Connection\n";
};

$tcp_worker->onClose = function($connection)
{
    echo "Connection closed\n";
};

// 當用戶端發來數據(主程式)
$tcp_worker->onMessage = function($connection, $tcp_in)
{                       
	global $ch;
	
	$explode_tcp_in = explode(chr(28), $tcp_in);	// 0x1C tcp欄位分隔 
	$send_data = null;
	
	if(empty($explode_tcp_in) || count($explode_tcp_in) != 5)
	{
		trigger_error(".. unknown tcp_in|". print_r($explode_tcp_in, true) .'|');
		$send_data = gen_error_result(ALTOB_ERROR_CODE_UNKNOWN_INPUT);
	}
	else
	{
		list(, $seq, $cmd, $data, ) = $explode_tcp_in;
		
		$seq = 1;
		
		switch($cmd)
		{
			// 票卡入場訊號，供 ALTOB 登記
			case '001':
				//（傳入：卡號、入場編號、是否月租卡； 回傳：成功、入場編號）
				list($card_no, $cario_no, $card_type) = explode(chr(31), $data);		// 0x1F data欄位分隔
				trigger_error("cmd:{$cmd}, card_no:{$card_no}, cario_no:{$cario_no}, card_type:{$card_type}");
				
				// 呼叫 cmd_001
				$data = array('card_no' => $card_no, 'cario_no' => $cario_no, 'card_type' => $card_type);
				curl_setopt($ch, CURLOPT_URL, 'http://localhost/acer_service.html/cmd_001/'); 
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));   
				$jdata = curl_exec($ch);      
				$result = json_decode($jdata, true);
				
				trigger_error("{$cmd}|{$card_no}|{$cario_no}|{$card_type}|result|" . print_r($result, true));
				
				$send_data = gen_cario_result($seq, $cmd, 
					$result['result_code'], 
					$result['result']['cario_no'], 
					$result['result']['error_code']);	
				break;
			
			// 票卡離場訊號，供 ALTOB 登記
			case '002': 
				//（傳入：卡號、繳費時間、是否月租卡； 回傳：成功、入場編號）
				list($card_no, $pay_time, $card_type) = explode(chr(31), $data);			// 0x1F data欄位分隔
				trigger_error("cmd:{$cmd}, card_no:{$card_no}, pay_time:{$pay_time}, card_type:{$card_type}");
				
				// 呼叫 cmd_002
				$data = array('card_no' => $card_no, 'pay_time' => $pay_time, 'card_type' => $card_type);
				curl_setopt($ch, CURLOPT_URL, 'http://localhost/acer_service.html/cmd_002/'); 
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));   
				$jdata = curl_exec($ch);      
				$result = json_decode($jdata, true);
				
				trigger_error("{$cmd}|{$card_no}|{$pay_time}|{$card_type}|result|" . print_r($result, true));
				
				$send_data = gen_cario_result($seq, $cmd, 
					$result['result_code'], 
					$result['result']['cario_no'], 
					$result['result']['error_code']);	
				break;
				  
			// 票號離場訊號，回傳若成功觸發 ACER 開門
			case '003':
				// （傳入：6 碼數字； 回傳：入場編號、成功與否代號）
				list($ticket_no) = explode(chr(31), $data);								// 0x1F data欄位分隔
				trigger_error("cmd:{$cmd}, ticket_no:{$ticket_no}");
				
				// 呼叫 cmd_003
				$data = array('ticket_no' => $ticket_no);
				curl_setopt($ch, CURLOPT_URL, 'http://localhost/acer_service.html/cmd_003/'); 
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));   
				$jdata = curl_exec($ch);      
				$result = json_decode($jdata, true);
				
				trigger_error("{$cmd}|{$ticket_no}|result|" . print_r($result, true));
				
				$send_data = gen_cario_result($seq, $cmd, 
					$result['result_code'], 
					$result['result']['cario_no'], 
					$result['result']['error_code'],
					$result['result']['msg_code']);	
				break;
			
			default:
				trigger_error(".. unknown cmd | {$seq}, {$cmd}, {$data}|");
				$send_data = gen_error_result(ALTOB_ERROR_CODE_UNKNOWN_CMD);
				break;
		}   	
	}
	
	// 未定義的錯誤
	if(empty($send_data))
	{
		$send_data = gen_error_result(ALTOB_ERROR_CODE_UNDEFINED);	
	}
	
	$connection->close($send_data);
};       

// 執行worker
Worker::runAll();

// 產生錯誤回傳
function gen_error_result($error_code)
{
	return gen_cario_result(0, 0, ALTOB_RESULT_CODE_FAIL, 0, $error_code);
}

// 產生 cario_no 回傳
function gen_cario_result($seq, $cmd, $result_code, $cario_no, $error_code, $msg_code=0)
{
	$send_data = '';
	
	$seq_pad = str_pad($seq, 5, '0', STR_PAD_LEFT); 				// 序號： 		5 碼 	(左邊補 0)
	$cmd_pad = str_pad($cmd, 3, '0', STR_PAD_LEFT); 				// 指令：		3 碼 	(左邊補 0)
	$cario_no_pad = str_pad($cario_no, 10, '0', STR_PAD_LEFT); 		// 入場編號：	10 碼 	(左邊補 0)
	$error_code_pad = str_pad($error_code, 4, '0', STR_PAD_LEFT); 	// 錯誤碼： 	4 碼 	(左邊補 0)
	$msg_code_pad = str_pad($msg_code, 5, '0', STR_PAD_LEFT); 		// 離場代碼： 	5 碼 	(左邊補 0)
	
	trigger_error(__FUNCTION__ . "..{$seq_pad}|{$cmd_pad}|{$cario_no_pad}|{$error_code_pad}|{$msg_code_pad}..");
	
	if(empty($msg_code))
	{
		$packformat = "a2Ca10Ca4";
		$data = pack($packformat, 
					// Data：內容除分隔符號為0x1F，其他全為ASCII碼0x20 ~ 0x7F內
					$result_code, 0x1f, $cario_no_pad, 0x1f, $error_code_pad
				); 
		$data_len = strlen($data);    
		$socket_len = $data_len + 16;
		
		//trigger_error(".. TEST socket_len | {$socket_len}, {$data_len}|". intval($socket_len / 0x0100). '_'. intval($socket_len % 0x0100));
			
		$send_data = pack("Ca2Ca5Ca3C{$packformat}CCC",
					0x02,												// STX：封包起始碼(0x02)
					$socket_len, 0x1c, 									// 封包長度：從STX到ETX的位元數
					$seq_pad, 0x1c, 									// 封包流水號：5碼ASCII數字(不足5碼時左補”0”補滿5碼)
					$cmd_pad, 0x1c,  									// CmdID：命令ID
					
					// Data：內容除分隔符號為0x1F，其他全為ASCII碼0x20 ~ 0x7F內
					$result_code, 0x1f, $cario_no_pad, 0x1f, $error_code_pad, 
					
					0x1c,
					0x80, 												// CRC：封包檢查碼
					0x03												// ETX：封包結束碼(0x03)
					);
	}
	else
	{
		$packformat = "a2Ca10Ca4Ca5";
		$data = pack($packformat, 
					// Data：內容除分隔符號為0x1F，其他全為ASCII碼0x20 ~ 0x7F內
					$result_code, 0x1f, $cario_no_pad, 0x1f, $error_code_pad, 0x1f, $msg_code_pad
				); 
		$data_len = strlen($data);    
		$socket_len = $data_len + 16;
		
		//trigger_error(".. TEST socket_len | {$socket_len}, {$data_len}|". intval($socket_len / 0x0100). '_'. intval($socket_len % 0x0100));
			
		$send_data = pack("Ca2Ca5Ca3C{$packformat}CCC",
					0x02,												// STX：封包起始碼(0x02)
					$socket_len, 0x1c, 									// 封包長度：從STX到ETX的位元數
					$seq_pad, 0x1c, 									// 封包流水號：5碼ASCII數字(不足5碼時左補”0”補滿5碼)
					$cmd_pad, 0x1c,  									// CmdID：命令ID
					
					// Data：內容除分隔符號為0x1F，其他全為ASCII碼0x20 ~ 0x7F內
					$result_code, 0x1f, $cario_no_pad, 0x1f, $error_code_pad, 0x1f, $msg_code_pad, 
					
					0x1c,
					0x80, 												// CRC：封包檢查碼
					0x03												// ETX：封包結束碼(0x03)
					);	
	}
	
	return $send_data;
}

// 發生錯誤時集中在此處理
function error_handler($errno, $errstr, $errfile, $errline, $errcontext)
{         
  	$str = date('H:i:s')."|{$errstr}|{$errfile}|{$errline}|{$errno}\n";               
  	error_log($str, 3, LOG_PATH.APP_NAME . '.' . date('Ymd').'.log.txt');	// 3代表參考後面的檔名
}
