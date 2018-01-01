<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
file: vip_parked.php   VIP車位
http://192.168.10.201/vip_parked.html/pages/1 

舊版:
file:///C:/altob/vip/javascript_mqtt/vip_parked.html
*/
       

require_once(MQ_CLASS_FILE); 

class Vip_parked extends CI_Controller
{          
    var $vars = array();      
    
	function __construct() 
	{                            
		parent::__construct();      
        
		$this->vars['date_time'] = date('Y-m-d H:i:s');	// 格式化時間(2015-10-12 14:36:21) 
		$this->vars['time_num'] = str_replace(array('-', ':', ' '), '', $this->vars['date_time']); //數字化時間(20151012143621) 
        $this->vars['date_num'] = substr($this->vars['time_num'], 0, 8);	// 數字化日期(20151012) 
		$this->vars['station_no'] = STATION_NO;	// 本站編號 
        
        /*
        // cameras or etagio直接release連線(即斷線), 但繼續處理邏輯
        $method_name = $this->router->fetch_method();
        if ($method_name == 'cameras' || $method_name == 'etagio')
        {
        	ob_end_clean();
			ignore_user_abort();
			ob_start();
			header('Connection: close');
			header('Content-Length: ' . ob_get_length());
			ob_end_flush();
			flush();
        }  
        */
            
        // ----- 程式開發階段log設定 -----
        if (@ENVIRONMENT == 'development')
        {                        
          	ini_set('display_errors', '1');
			//error_reporting(E_ALL ^ E_NOTICE); 
			error_reporting(E_ALL); 
        }  
        set_error_handler(array($this, 'error_handler'), E_ALL);	// 資料庫異動需做log   
                                        
        // mqtt subscribe
		$this->vars['mqtt'] = new phpMQTT(MQ_HOST, MQ_PORT, uniqid());  
		if(!$this->vars['mqtt']->connect()){ die ('Could not connect mqtt');  }
        
        // ----- 定義常數(路徑, cache秒數) -----       
        define('APP_VERSION', '100');		// 版本號
                                        
        define('MAX_AGE', 604800);			// cache秒數, 此定義1個月     
        define('APP_NAME', 'vip_parked');		// 應用系統名稱   
          
        define('PAGE_PATH', APP_BASE.'ci_application/views/'.APP_NAME.'/');						// path of views
        
        define('SERVER_URL', 'http://'.(isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost').'/');	// URL
        define('APP_URL', SERVER_URL.APP_NAME.'.html/');										// controller路徑 
        define('WEB_URL', SERVER_URL.APP_NAME.'/');												// 網頁路徑
        define('WEB_LIB', SERVER_URL.'/libs/');													// 網頁lib
        define('BOOTSTRAPS', WEB_LIB.'bootstrap_sb/');											// bootstrap lib  
        define('LOG_PATH', FILE_BASE.APP_NAME.'/logs/');		// log path name
        define('LOG_FILE', FILE_BASE.APP_NAME.'/logs/cario.');	// log file name
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
    
    	
	public function pages()
	{                               
        $data['vip_no'] = $this->uri->segment(3);	// vip no
        $data['mqtt_ip'] = '192.168.51.11'; 
        $this->load->view(APP_NAME.'/main_page', $data);
	}   
    
    
	public function vip_welcome()
	{                               
        $this->load->view(APP_NAME.'/vip_welcome');
	}    
                      
    
	public function parked()
	{                               
        $this->load->view(APP_NAME.'/vip_welcome');
	}
}
