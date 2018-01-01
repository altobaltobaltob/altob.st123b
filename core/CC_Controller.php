<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
	ALTOB base controller
*/

class CC_Controller extends CI_Controller
{          
    public $vars;      
    
	function __construct($app_name) 
	{                            
		parent::__construct();
		
		define('APP_NAME', $app_name);												// 應用系統名稱
		define('LOG_PATH', FILE_BASE.APP_NAME.'/logs/');							// log path name
		
        // ----- 程式開發階段log設定 -----
        if (@ENVIRONMENT == 'development')
        {                        
          	ini_set('display_errors', '1');
			//error_reporting(E_ALL ^ E_NOTICE); 
			error_reporting(E_ALL); 
        }
        set_error_handler(array($this, 'error_handler'), E_ALL);	// 資料庫異動需做log 
		
        $method_name = $this->router->fetch_method();
		$request_assoc = $this->uri->uri_to_assoc(3);
		trigger_error(__FUNCTION__ . '://..' . $method_name. '..uri_to_assoc..' . print_r($request_assoc, true));
		
		// ----- 常數 -----       
		define('APP_VERSION', '100');												// 版本號                                        
		define('MAX_AGE', 604800);													// cache秒數, 此定義1個月     
		define('SERVER_URL', 'http://'.(isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost') . ($_SERVER['SERVER_PORT'] != 60123 ? ':' . $_SERVER['SERVER_PORT'] : '') .'/');	// URL for 同外網IP, 不同PORT
		define('WEB_LIB', SERVER_URL.'/libs/');										// 網頁lib
		define('BOOTSTRAPS', WEB_LIB.'bootstrap_sb/');								// bootstrap lib  
		define('WEB_URL', SERVER_URL.APP_NAME.'/');									// 網頁路徑
		define('APP_URL', SERVER_URL.APP_NAME.'.html/');							// controller路徑 
		define('PAGE_PATH', APP_BASE.'ci_application/views/'.APP_NAME.'/');			// path of views
		
		$this->vars = array();
		$this->vars['date_time'] = date('Y-m-d H:i:s');												// 格式化時間(2015-10-12 14:36:21) 
		$this->vars['time_num'] = str_replace(array('-', ':', ' '), '', $this->vars['date_time']); 	// 數字化時間(20151012143621) 
        $this->vars['date_num'] = substr($this->vars['time_num'], 0, 8);							// 數字化日期(20151012) 
		
		// 共用記憶體 
        $this->vars['mcache'] = new Memcache;
		if(!$this->vars['mcache']->pconnect(MEMCACHE_HOST, MEMCACHE_PORT)){ trigger_error('..Could not connect mcache..'); }
	}
    
    // 發生錯誤時集中在此處理
	public function error_handler($errno, $errstr, $errfile, $errline, $errcontext)
	{           
    	$log_msg = explode('://', $errstr);
        if (count($log_msg) > 1 && substr($log_msg[0], -4) != 'http')
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
	
	// 顯示靜態網頁(html檔)
	public function show_page($page_name, &$data = null)
	{           
    	$page_file = PAGE_PATH.$page_name.'.php';
        $last_modified_time = filemtime($page_file);         
            
    	// 若檔案修改時間沒有異動, 或版本無異動, 通知瀏覽器使用cache, 不再下傳網頁
		// header('Cache-Control:max-age='.MAX_AGE);	// cache 1個月
    	header('Last-Modified: '.gmdate('D, d M Y H:i:s', $last_modified_time).' GMT');
        header('Etag: '. APP_VERSION);
		header('Cache-Control: public'); 
		
		// 20170921
		header("cache-Control: no-store, no-cache, must-revalidate");
		header("cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
		header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");		
        
        if (!empty($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] == APP_VERSION && @strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $last_modified_time)
    	{                  
        	header('HTTP/1.1 304 Not Modified');
    	}
        else
        {                                           
        	$this->load->view(APP_NAME.'/'.$page_name, $data);
        }    
	}
    
    // 顯示logs
	public function show_logs()
	{             
        $lines = $this->uri->segment(3);	// 顯示行數
        if (empty($lines)) $lines = 140;		// 無行數參數, 預設為40行
    	
        // echo '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head><body><pre style="white-space: pre-wrap;">';
        echo '<html lang="zh-TW"><body><pre style="white-space: pre-wrap;">';      
       
		passthru('/usr/bin/tail -n ' . $lines . '  ' . LOG_PATH . APP_NAME . '.' . date('Ymd').'.log.txt');	// 利用linux指令顯示倒數幾行的logs內容 
        echo "\n----- " . LOG_PATH . APP_NAME . '.' . date('Ymd').'.log.txt' . ' -----';   
        echo '</pre></body></html>';
	}
	
	// 送出html code     	
	public function get_html()
	{                             
    	$this->load->view(APP_NAME.'/'.$this->input->post('tag_name', true), array());
	}
	
	// 驗証 IP
	public function is_ip_valid()
	{
		$client_ip = $this->my_ip();
		if(!in_array($client_ip, array('127.0.0.1', '61.219.172.11', '61.219.172.82')))
		{
			trigger_error('..block..from:'.$client_ip.'..unknown network..');
			return false;
		}
		return true;
	}
	
	// 取得 IP
	public function my_ip()
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
	
	// 啟動 MQTT
	public function init_mqtt($offline=false)
	{
		require_once(MQ_CLASS_FILE); 
		if($offline)
		{
			$this->vars['mqtt'] = new phpMQTT(MQ_HOST, MQ_PORT, uniqid());
			$this->vars['mqtt']->connect();
			return false;
		}
		
		$station_setting = $this->data_model()->station_setting_query();
		$mqtt_ip = isset($station_setting['mqtt_ip']) ? $station_setting['mqtt_ip'] : MQ_HOST;
		$mqtt_port = isset($station_setting['mqtt_port']) ? $station_setting['mqtt_port'] : MQ_PORT;
		$this->vars['mqtt'] = new phpMQTT($mqtt_ip, $mqtt_port, uniqid());
		$this->vars['mqtt']->connect();
	}
	
	// 資料同步模組
	public function data_model()
	{
		return $this->app_model('sync_data');
	}
	
	// 啟動模組
	public function app_model($app_name='')
	{
		$model_name = !empty($app_name) ? $app_name . '_model' : APP_NAME . '_model';
		$this->load->model($model_name); 
        $this->$model_name->init($this->vars);
		return $this->$model_name;
	}
	
	// 取得場站編號
	public function get_station_no()
	{
		$station_setting = $this->data_model()->station_setting_query();
		$station_no_arr = explode(SYNC_DELIMITER_ST_NO, $station_setting['station_no']);
		return $station_no_arr[0];
	}
}
