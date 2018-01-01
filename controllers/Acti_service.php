<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
file: Acti_service.php 	與 acti 介接相關都放這支
*/
       
// ----- 定義常數(路徑, cache秒數) -----       
define('APP_VERSION', '100');			// 版本號
define('MAX_AGE', 604800);				// cache秒數, 此定義1個月     
define('APP_NAME', 'acti_service');		// 應用系統名稱   
define('PAGE_PATH', APP_BASE.'ci_application/views/'.APP_NAME.'/');						// path of views
define('SERVER_URL', 'http://'.(isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost').'/');	// URL
define('APP_URL', SERVER_URL.APP_NAME.'.html/');										// controller路徑 
define('WEB_URL', SERVER_URL.APP_NAME.'/');												// 網頁路徑
define('WEB_LIB', SERVER_URL.'/libs/');													// 網頁lib
define('BOOTSTRAPS', WEB_LIB.'bootstrap_sb/');											// bootstrap lib  
define('LOG_PATH', FILE_BASE.APP_NAME.'/logs/');		// log path name
define('LOG_FILE', FILE_BASE.APP_NAME.'/logs/acti_service.');	// log file name

class Acti_service extends CI_Controller
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
		
		// 阻檔未知的 IP
		$from_ip = $this->my_ip();
		if(!in_array($from_ip, array(
			'127.0.0.1', 
			'192.168.10.130',
			'192.168.10.131',
			'192.168.10.132',
			'192.168.10.133',
			'192.168.10.134',
			'192.168.10.135',
			'192.168.10.136',
			'192.168.10.137',
			'192.168.10.138',
			'192.168.10.139'
		)))
		{
			trigger_error('refused://from:'.$from_ip.'..refused..'.print_r($_REQUEST, true));
			exit;
		}
		
		$method_name = $this->router->fetch_method();
        if (in_array($method_name, array('sos')))
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
	}
     
	// 取得 IP
	function my_ip()
	{
		if (getenv('HTTP_X_FORWARDED_FOR')) 
		{
			$ip = getenv('HTTP_X_FORWARDED_FOR');
		}
		elseif (getenv('HTTP_X_REAL_IP')) 
		{
			$ip = getenv('HTTP_X_REAL_IP');
		}
		else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}

		return $ip;
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
	
	// [區網] 由設備端呼叫
	public function sos()
	{
		$msg = isset($_REQUEST['Message']) ? $_REQUEST['Message'] : '';
		trigger_error(__FUNCTION__ . "..Message..{$msg}..". print_r($_REQUEST, true));
		
		$msg_arr = explode('-', $msg);
		$station_no = isset($msg_arr['0']) ? $msg_arr['0'] : 0;
		$machine_no = isset($msg_arr['1']) ? $msg_arr['1'] : 0;
		//$station_no = $this->uri->segment(3);
		//$machine_no = $this->uri->segment(4);
		
		if(!isset($station_no) || !isset($machine_no))
		{
			trigger_error(__FUNCTION__ . '..unknown msg..');
			exit;
		}
		
		require_once(ALTOB_SYNC_FILE) ;
		
		// 傳送 SOS
		$sync_agent = new AltobSyncAgent();
		$sync_agent->init($station_no, date('Y-m-d H:i:s'));
		$sync_result = $sync_agent->sync_st_sos($machine_no);
		trigger_error( "..sync_st_sos.." .  $sync_result);
	}
	
}
