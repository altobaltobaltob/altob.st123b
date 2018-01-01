<?php
/*
file: qcar3.php		查車系統3
*/
if (!defined('BASEPATH')) exit('No direct script access allowed');

        // ----- 定義常數(路徑, cache秒數) -----       
        define('APP_VERSION', '100');		// 版本號
                                        
        define('MAX_AGE', 604800);			// cache秒數, 此定義1個月     
        define('APP_NAME', 'qcar3');		// 應用系統名稱   
          
        define('PAGE_PATH', APP_BASE.'ci_application/views/'.APP_NAME.'/');						// path of views

        define('SERVER_URL', 'http://'.(isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost').'/');	// URL
        define('APP_URL', SERVER_URL.APP_NAME.'.html/');										// controller路徑
        define('WEB_URL', SERVER_URL.APP_NAME.'/');												// 網頁路徑
        define('WEB_LIB', SERVER_URL.'libs/');													// 網頁lib
        define('BOOTSTRAPS', WEB_LIB.'bootstrap_sb/');											// bootstrap lib
        define('LOG_PATH', FILE_BASE.APP_NAME.'/logs/');	// log path

class Qcar3 extends CI_Controller
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
        
		$this->load->model('qcar3_model'); 
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
        
        	
	public function index()
	{                   
		$this->show_page('main_page');				// 1122x630
	}
    
    // 顯示logs
	public function show_logs()
	{             
        $lines = $this->uri->segment(3);	// 顯示行數
        if (empty($lines)) $lines = 40;		// 無行數參數, 預設為40行
    	
        // echo '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head><body><pre style="white-space: pre-wrap;">';
        echo '<html lang="zh-TW"><body><pre style="white-space: pre-wrap;">';
		passthru('/usr/bin/tail -n ' . $lines . '  ' . LOG_FILE);		// 利用linux指令顯示倒數幾行的logs內容 
        echo "\n----- " . LOG_FILE . ' -----';   
        echo '</pre></body></html>';
	}    
    
    // 車位查詢結果頁
    public function show_result()
	{
    	$lpr = $this->uri->segment(3);	// 車牌號碼
        $data = $this->qcar3_model->q_pks($lpr);
		$data['lpr'] = strtoupper($lpr);
		$this->show_page('result_page', $data);		// 1280x1080
	}
	
	// 車位查詢結果頁 (2)
    public function show_result2()
	{
    	$lpr = $this->uri->segment(3);	// 車牌號碼
        $data = $this->qcar3_model->q_pks($lpr);
		$data['lpr'] = strtoupper($lpr);
		$this->show_page('result_page2', $data);	// 2560x1440
	}

    // 車位查詢
    public function q_pks()
	{
    	$lpr = $this->input->post('lpr', true);
        $data = $this->qcar3_model->q_pks($lpr);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
	}

	// 取得進場資訊 (模糊比對)
	public function q_fuzzy_pks()
	{
		$input = $this->input->post('fuzzy_input', true);
		$data = $this->qcar3_model->q_fuzzy_pks($input);
		echo json_encode($data, JSON_UNESCAPED_UNICODE);
	}  
    
}
