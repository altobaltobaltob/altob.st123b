<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
file: Mitac_service.php 	與 mitac 介接相關都放這支
*/
       
// ----- 定義常數(路徑, cache秒數) -----       
define('APP_VERSION', '100');			// 版本號
define('MAX_AGE', 604800);				// cache秒數, 此定義1個月     
define('APP_NAME', 'mitac_service');		// 應用系統名稱   
define('PAGE_PATH', APP_BASE.'ci_application/views/'.APP_NAME.'/');						// path of views
define('SERVER_URL', 'http://'.(isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost').'/');	// URL
define('APP_URL', SERVER_URL.APP_NAME.'.html/');										// controller路徑 
define('WEB_URL', SERVER_URL.APP_NAME.'/');												// 網頁路徑
define('WEB_LIB', SERVER_URL.'/libs/');													// 網頁lib
define('BOOTSTRAPS', WEB_LIB.'bootstrap_sb/');											// bootstrap lib  
define('LOG_PATH', FILE_BASE.APP_NAME.'/logs/');		// log path name
define('LOG_FILE', FILE_BASE.APP_NAME.'/logs/mitac_service.');	// log file name

class Mitac_service extends CI_Controller
{          
    var $vars = array();      
    
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

		ignore_user_abort();	// 接受client斷線, 繼續run
		
		$method_name = $this->router->fetch_method();
		
		$request_assoc = $this->uri->uri_to_assoc(3);
		trigger_error(__FUNCTION__ . '..' . $method_name. '..request start..' . print_r($request_assoc, true));
		
        if (in_array($method_name, array('parking_fee_altob', 'deduct_result')))
        {
        	ob_end_clean();
			ignore_user_abort();
			ob_start();
			
			echo 'ok';
			
			header('Connection: close');
			header('Content-Length: ' . ob_get_length());
			ob_end_flush();
			flush();
        }
        
		$this->vars['date_time'] = date('Y-m-d H:i:s');	// 格式化時間(2015-10-12 14:36:21) 
		$this->vars['time_num'] = str_replace(array('-', ':', ' '), '', $this->vars['date_time']); //數字化時間(20151012143621) 
        $this->vars['date_num'] = substr($this->vars['time_num'], 0, 8);	// 數字化日期(20151012) 
		$this->vars['station_no'] = STATION_NO;	// 本站編號 
        
        // session_id(ip2long($_SERVER['REMOTE_ADDR']));	// 設定同一device為同一個session 
        //session_start();   
		
		// 阻檔未知的 IP
		if(!in_array($_SERVER['HTTP_X_REAL_IP'], array('127.0.0.1')))
		{
			trigger_error('refused://from:'.$_SERVER['HTTP_X_REAL_IP'].'..refused..');
			exit;
		}
		
		// MITAC 模組
		$this->load->model('mitac_service_model'); 
        $this->mitac_service_model->init($this->vars);
	}
     
    
    // 發生錯誤時集中在此處理
	public function error_handler($errno, $errstr, $errfile, $errline, $errcontext)
	{           
    	$log_msg = explode('://', $errstr);
        if (count($log_msg) > 1)
        {
            $log_file = $log_msg[0];    
        	$str = date('H:i:s')."|{$log_msg[1]}|{$errfile}|{$errline}|{$errno}\n"; 
        } 
        else
        {   
        	$log_file = APP_NAME;
    		$str = date('H:i:s')."|{$errstr}|{$errfile}|{$errline}|{$errno}\n";
        }              
          
        error_log($str, 3, LOG_PATH.$log_file . '.' . date('Ymd').'.log.txt');	// 3代表參考後面的檔名  
    }
    
    
    // 顯示logs
	public function show_logs()
	{             
        $lines = $this->uri->segment(3);	// 顯示行數
        if (empty($lines)) $lines = 100;		// 無行數參數, 預設為40行
    	
        // echo '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head><body><pre style="white-space: pre-wrap;">';
        echo '<html lang="zh-TW"><body><pre style="white-space: pre-wrap;">';      
       
		passthru('/usr/bin/tail -n ' . $lines . '  ' . LOG_PATH.APP_NAME . '.' . date('Ymd').'.log.txt');	// 利用linux指令顯示倒數幾行的logs內容 
        echo "\n----- " . LOG_PATH.APP_NAME . '.' . date('Ymd').'.log.txt' . ' -----';   
        echo '</pre></body></html>';
	}    
	
	// [排程] 詢問是否存活
	public function echo_mitac_alive()
	{
		echo $this->mitac_service_model->echo_mitac_alive();
		exit;
	}
	
	// 要求扣款 (ALTOB to MITAC)
	public function parking_fee_altob()
	{
		$seqno = $this->input->post('seqno', true);			// 通訊序號
		$lpr = $this->input->post('lpr', true);				// 車牌
		$in_time = $this->input->post('in_time', true);		// 進場時間
		$out_time =	$this->input->post('out_time', true);	// 出場時間
		$gate_id = $this->input->post('gate_id', true);		// 驗票機編號
		$ck = $this->input->post('ck', true);				// 驗証碼
		
		// 通訊內容
		$parms = array(
			'seqno' => $seqno, 
			'lpr' => $lpr, 
			'in_time' => $in_time, 
			'out_time' => $out_time, 
			'gate_id' => $gate_id);
			
		if($ck != md5($parms['seqno']. 'a' . date('dmh') . 'l' . $parms['lpr'] . 't'. $parms['in_time']. 'o'. $parms['out_time'] . 'b'. $parms['gate_id'] . __FUNCTION__))
		{
			trigger_error(__FUNCTION__ . '..ck_error..' . print_r($parms, true));
			exit; // 中斷
		}
		
		trigger_error(__FUNCTION__ . '..' . print_r($parms, true));
		$this->mitac_service_model->parking_fee_altob($parms);
		exit;
	}

	// 回應扣款成功 (MITAC to ALTOB)
	public function deduct_result()
	{
		$seqno = $this->input->post('seqno', true);
		$lpr = $this->input->post('lpr', true);
		$in_time = $this->input->post('in_time', true);
		$out_time =	$this->input->post('out_time', true);
		$gate_id = $this->input->post('gate_id', true);
		$amt = $this->input->post('amt', true);
		$amt_discount = $this->input->post('amt_discount', true);
		$amt_real = $this->input->post('amt_real', true);
		$ck = $this->input->post('ck', true);
		
		// 通訊內容
		$parms = array(
			'seqno' => $seqno, 
			'lpr' => $lpr, 
			'in_time' => $in_time, 
			'out_time' => $out_time, 
			'gate_id' => $gate_id, 
			'amt' => $amt, 
			'amt_discount' => $amt_discount,
			'amt_real' => $amt_real);
			
		if($ck != md5($parms['seqno']. 'a' . date('dmh') . 'l' . $parms['lpr'] . 't'. $parms['amt']. 'o'. $parms['amt_discount'] . 'b'. $parms['amt_real'] . __FUNCTION__))
		{
			trigger_error(__FUNCTION__ . '..ck_error..' . print_r($parms, true));
			exit;	// 中斷
		}
		
		trigger_error(__FUNCTION__ . '..' . print_r($parms, true));
		$this->mitac_service_model->deduct_result($parms);
		exit;
	}
	
	// http://localhost/mitac_service.html/test_parking_fee_altob
	public function test_parking_fee_altob()
	{
		$function_name = 'parking_fee_altob';
		$seqno = '20161004_101010';
		$lpr = 'AA1234';
		$in_time =	'2017-11-11 16:40:02';
		$out_time =	'2017-11-11 16:58:36';
		$gate_id = 1;		
		
		// 通訊內容
		$parms = array(
			'seqno' => $seqno, 
			'lpr' => $lpr, 
			'in_time' => $in_time, 
			'out_time' => $out_time, 
			'gate_id' => $gate_id);

		// 驗証碼
		$parms['ck'] = md5($parms['seqno']. 'a' . date('dmh') . 'l' . $parms['lpr'] . 't'. $parms['in_time']. 'o'. $parms['out_time'] . 'b'. $parms['gate_id'] . $function_name);
		
		// 測試呼叫
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "http://localhost/mitac_service.html/{$function_name}");
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parms));
		$rs = curl_exec($ch);
		curl_close($ch);
		
		echo $rs;
		exit;
	}
	
	// http://localhost/mitac_service.html/test_49993/
	public function test_49993()
	{
		$seqno = '201711111_027771';
		$lpr = 'TEST1111B';
		$in_time =	'20171111_190048';
		$out_time =	'20171111_190048';
		$gate_id = 0;
		$amt = 66;
		$amt_discount = 10;
		$amt_real = 56;
		$msg = implode(',', ['Altob', 'DeductResult', $seqno, $lpr, $in_time, $out_time, $gate_id, $amt, $amt_discount, $amt_real]);
		
		$error_str = '';
		$service_port = 49993;
		$address = "192.168.10.201";

		/* Create a TCP/IP socket. */
		$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if ($socket === false) {
			echo "socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n";
			$error_str .= "socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n";
		} else {
			echo "OK.\n";
		}

		echo "Attempting to connect to '{$address}' on port '{$service_port}'...";
		$result = socket_connect($socket, $address, $service_port);
		if ($result === false) {
			echo "socket_connect() failed.\nReason: ({$result}) " . socket_strerror(socket_last_error($socket)) . "\n";
			$error_str .= "socket_connect() failed: reason: " . socket_strerror(socket_last_error($socket)) . "\n";
		} else {
			echo "OK.\n";
		}
		
		/*
		$in = pack('Ca5Ca3Ca9Ca7Ca19', 0x1c, $seqno, 0x1c, $cmd, 0x1c, 
					$token, 0x1f, $lpr, 0x1f, $in_time
				);
		*/
		$in = $msg;
		$out = '';
		echo "socket_write:";
		echo "{$in}<br/><br/>";
		
		if(!socket_write($socket, $in, strlen($in)))
		{
			echo('<p>Write failed</p>');
			$error_str .= "socket_write() failed: reason: " . socket_strerror(socket_last_error($socket)) . "\n";
		}
		echo "socket_write..OK..";

		echo "<br/><br/>socket_read:";
		$out = socket_read($socket, 2048) or die("Could not read server responsen");
		//while ($out = socket_read($socket, 2048)) {
		//	echo $out;
		//}
		echo "{$out}<br/><br/>";
		
		echo "Closing socket...";
		socket_close($socket);
		echo "OK.\n\n";

		trigger_error($error_str);
		exit;
	}
	
}
