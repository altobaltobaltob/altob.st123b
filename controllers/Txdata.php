<?php
/*
file: Txdata.php	交易資訊系統
*/
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Txdata extends CI_Controller
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
           
        /*
        // 共用記憶體 
        $this->vars['mcache'] = new Memcache;
		$this->vars['mcache']->connect(MEMCACHE_HOST, MEMCACHE_PORT) or die ('Could not connect memcache');   
                                        
        // mqtt subscribe
		$this->vars['mqtt'] = new phpMQTT(MQ_HOST, MQ_PORT, uniqid());  
                                 
		if(!$this->vars['mqtt']->connect()){ die ('Could not connect mqtt');  }
        */
        
        // ----- 定義常數(路徑, cache秒數) -----       
        define('APP_VERSION', '100');		// 版本號
                                        
        define('MAX_AGE', 604800);			// cache秒數, 此定義1個月     
        define('APP_NAME', 'txdata');		// 應用系統名稱   
          
        define('PAGE_PATH', APP_BASE.'ci_application/views/'.APP_NAME.'/');						// path of views
        
        define('SERVER_URL', 'http://'.(isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost').'/');	// URL
        define('APP_URL', SERVER_URL.APP_NAME.'.html/');										// controller路徑 
        define('WEB_URL', SERVER_URL.APP_NAME.'/');												// 網頁路徑
        define('WEB_LIB', SERVER_URL.'libs/');													// 網頁lib
        define('BOOTSTRAPS', WEB_LIB.'bootstrap_sb/');											// bootstrap lib  
        define('LOG_PATH', FILE_BASE.APP_NAME.'/logs/');	// log path
        
		$this->load->model('txdata_model'); 
        // $this->parkingquery_model->init($this->vars);
	}
    
    // 發生錯誤時集中在此處理
	public function error_handler($errno, $errstr, $errfile, $errline, $errcontext)
	{                
    	$str = date('H:i:s')."|{$errstr}|{$errfile}|{$errline}|{$errno}\n";               
    	//error_log($str, 3, $log_file . '.' . date('Ymd').'.log.txt');	// 3代表參考後面的檔名
    	error_log($str, 3, LOG_PATH.APP_NAME . '.' . date('Ymd').'.log.txt');	// 3代表參考後面的檔名
    }
    
    
	// 顯示靜態網頁(html檔)
	protected function show_page($page_name, &$data = null)
	{           
    	$page_file = PAGE_PATH.$page_name.'.php';
        $last_modified_time = filemtime($page_file);         
            
    	// 若檔案修改時間沒有異動, 或版本無異動, 通知瀏覽器使用cache, 不再下傳網頁
		// header('Cache-Control:max-age='.MAX_AGE);	// cache 1個月
    	header('Last-Modified: '.gmdate('D, d M Y H:i:s', $last_modified_time).' GMT');
        header('Etag: '. APP_VERSION);
		header('Cache-Control: public'); 
        
        if (!empty($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] == APP_VERSION && @strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $last_modified_time)
    	{                  
        	header('HTTP/1.1 304 Not Modified');
    	}
        else
        {                                           
        	$this->load->view(APP_NAME.'/'.$page_name, $data);
        }    
	} 
    
    // 取得場站費率設定
    // http://203.75.167.89/txdata.html/get_price_plan/12112/0
	public function get_price_plan() 
	{       
    	$station_no = $this->uri->segment(3);
		$tx_type =  $this->uri->segment(4);
        $data = $this->txdata_model->get_price_plan($station_no, $tx_type);
        echo json_encode($data, JSON_UNESCAPED_UNICODE); 
    }
	
	// 取得特殊日期設定
    // http://203.75.167.89/txdata.html/get_date_plan/12345678/23456789
	public function get_date_plan() 
	{       
    	$inTime = $this->uri->segment(3);
		$balanceTime =  $this->uri->segment(4);
        $data = $this->txdata_model->get_date_plan($inTime, $balanceTime);
        echo json_encode($data, JSON_UNESCAPED_UNICODE); 
    }
    
}
