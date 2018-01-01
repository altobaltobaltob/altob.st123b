<?php
require_once '/home/bigbang/libs/Workerman/Autoloader.php';

use Workerman\Worker;  
Worker::$logFile = '/dev/null';		// 不記錄log file 
//Worker::$pidFile = '/tmp/run/'.basename(__FILE__).'.pid';
//Worker::$logFile = __DIR__ . '/../mitac2server.log';

// 場站共用設定檔
require_once '/home/bigbang/apps/coworker/station.config.php'; 
define('APP_NAME', 'mitac');	// application name

define('WORKERMAN_DEBUG', 1);
if (WORKERMAN_DEBUG)
{
	ini_set('display_errors', '1');
	error_reporting(E_ALL); 
	set_error_handler('error_handler', E_ALL);
}

///////////////////////////////
//
// 主程式
//
///////////////////////////////

// 傳送主機資料
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true); // 啟用POST

// 建立一個Worker監聽49993埠，不使用任何應用層協定
$tcp_worker = new Worker("tcp://0.0.0.0:49993");      

// 啟動N個進程對外提供服務
$tcp_worker->count = 6;

$tcp_worker->onConnect = function($connection)
{
    echo APP_NAME . "..New Connection\n";
};

$tcp_worker->onClose = function($connection)
{
    echo APP_NAME . "..Connection closed\n";
};

$tcp_worker->onMessage = function($connection, $tcp_in)
{                 
	global $ch;      
	
	trigger_error("..tcp_in..". json_encode($tcp_in, true) .'|');
	
	$tcp_in = mb_convert_encoding($tcp_in, 'UTF-8', 'UTF-16LE');	// unicode to utf-8
	
	$explode_tcp_in = explode(',', $tcp_in);
	
	// 處理
	if(empty($explode_tcp_in) || empty($explode_tcp_in[0]))
	{
		trigger_error("..empty..". print_r($explode_tcp_in, true) .'|');
	}
	else if($explode_tcp_in[0] == 'Mitac')
	{
		trigger_error("..". $tcp_in .'..');	// Mitac 回傳
	}
	else if($explode_tcp_in[0] == 'Altob')
	{
		if($explode_tcp_in[1] == 'DeductResult' && count($explode_tcp_in) == 10)
		{
			// 回應扣款成功 (MITAC to ALTOB) 目前只有這支
			$function_name = 'deduct_result';
			$seqno = $explode_tcp_in[2];
			$lpr = $explode_tcp_in[3];
			$in_time =	$explode_tcp_in[4];
			$out_time =	$explode_tcp_in[5];
			$gate_id = $explode_tcp_in[6];
			$amt = $explode_tcp_in[7];
			$amt_discount = $explode_tcp_in[8];
			$amt_real = $explode_tcp_in[9];
			
			// 建立通訊內容
			$parms = array(
				'seqno' => $seqno, 
				'lpr' => $lpr, 
				'in_time' => $in_time, 
				'out_time' => $out_time, 
				'gate_id' => $gate_id, 
				'amt' => $amt, 
				'amt_discount' => $amt_discount,
				'amt_real' => $amt_real);
			
			// 加驗証
			$parms['ck'] = md5($parms['seqno']. 'a' . date('dmh') . 'l' . $parms['lpr'] . 't'. $parms['amt']. 'o'. $parms['amt_discount'] . 'b'. $parms['amt_real'] . $function_name);
			curl_setopt($ch, CURLOPT_URL, "http://localhost/mitac_service.html/{$function_name}/"); 
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parms));   
			$result = curl_exec($ch);
			trigger_error(".. curl {$function_name}|{$result} ..".print_r($parms, true));
		}
		else
		{
			trigger_error('..unknown cmd..' . print_r($explode_tcp_in, true));
		}
	}
	
	// 回覆
	//$connection->send('OK');
	
	// 斷開
	$connection->close();
};       

// 執行worker
Worker::runAll();

// 發生錯誤時集中在此處理
function error_handler($errno, $errstr, $errfile, $errline, $errcontext)
{         
  	$str = date('H:i:s')."|{$errstr}|{$errfile}|{$errline}|{$errno}\n";               
  	error_log($str, 3, LOG_PATH.APP_NAME . '.' . date('Ymd').'.log.txt');	// 3代表參考後面的檔名
}
