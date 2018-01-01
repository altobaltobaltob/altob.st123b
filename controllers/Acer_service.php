<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
file: Acer_service.php 	與 acer 介接相關都放這支
*/
       
// ----- 定義常數(路徑, cache秒數) -----       
define('APP_VERSION', '100');			// 版本號
define('MAX_AGE', 604800);				// cache秒數, 此定義1個月     
define('APP_NAME', 'acer_service');		// 應用系統名稱   
define('PAGE_PATH', APP_BASE.'ci_application/views/'.APP_NAME.'/');						// path of views
define('SERVER_URL', 'http://'.(isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost').'/');	// URL
define('APP_URL', SERVER_URL.APP_NAME.'.html/');										// controller路徑 
define('WEB_URL', SERVER_URL.APP_NAME.'/');												// 網頁路徑
define('WEB_LIB', SERVER_URL.'/libs/');													// 網頁lib
define('BOOTSTRAPS', WEB_LIB.'bootstrap_sb/');											// bootstrap lib  
define('LOG_PATH', FILE_BASE.APP_NAME.'/logs/');		// log path name
define('LOG_FILE', FILE_BASE.APP_NAME.'/logs/acer_service.');	// log file name

class Acer_service extends CI_Controller
{          
    var $vars = array();      
    
	function __construct() 
	{                            
		parent::__construct();      
		
		$method_name = $this->router->fetch_method();
        if (in_array($method_name, array('cmd_101', 'cmd_102')))
        {
        	ob_end_clean();
			ignore_user_abort();
			ob_start();
			header('Connection: close');
			header('Content-Length: ' . ob_get_length());
			ob_end_flush();
			flush();
        }
        
		ignore_user_abort();	// 接受client斷線, 繼續run
        
		$this->vars['date_time'] = date('Y-m-d H:i:s');	// 格式化時間(2015-10-12 14:36:21) 
		$this->vars['time_num'] = str_replace(array('-', ':', ' '), '', $this->vars['date_time']); //數字化時間(20151012143621) 
        $this->vars['date_num'] = substr($this->vars['time_num'], 0, 8);	// 數字化日期(20151012) 
		$this->vars['station_no'] = STATION_NO;	// 本站編號 
        
        // session_id(ip2long($_SERVER['REMOTE_ADDR']));	// 設定同一device為同一個session 
        session_start();   
            
        // ----- 程式開發階段log設定 -----
        if (@ENVIRONMENT == 'development')
        {                        
          	ini_set('display_errors', '1');
			//error_reporting(E_ALL ^ E_NOTICE); 
			error_reporting(E_ALL); 
        }  
        set_error_handler(array($this, 'error_handler'), E_ALL);	// 資料庫異動需做log   
                 
		$this->load->model('acer_service_model'); 
        $this->acer_service_model->init($this->vars);
		
		// 阻檔未知的 IP
		if(!in_array($_SERVER['HTTP_X_REAL_IP'], array('127.0.0.1')))
		{
			trigger_error('refused://from:'.$_SERVER['HTTP_X_REAL_IP'].'..refused..');
			exit;
		}
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
               

	// 票卡入場訊號，供 ALTOB 登記
	public function cmd_001()
	{
		$card_no = $this->input->post('card_no', true);
		$cario_no = $this->input->post('cario_no', true);
		$card_type = $this->input->post('card_type', true);
		
		trigger_error(__FUNCTION__ . ", card_no:{$card_no}, cario_no:{$cario_no}, card_type:{$card_type}");
		
		$data = $this->acer_service_model->cmd_001($card_no, $cario_no, $card_type);
		echo json_encode($data, JSON_UNESCAPED_UNICODE); 
	}
		
	// 票卡離場訊號，供 ALTOB 登記
	public function cmd_002()
	{
		$card_no = $this->input->post('card_no', true);
		$pay_time = $this->input->post('pay_time', true);
		$card_type = $this->input->post('card_type', true);
		
		trigger_error(__FUNCTION__ . ", card_no:{$card_no}, pay_time:{$pay_time}, card_type:{$card_type}");
		
		$data = $this->acer_service_model->cmd_002($card_no, $pay_time, $card_type);
		echo json_encode($data, JSON_UNESCAPED_UNICODE); 
	}
	
	// 票號離場訊號，回傳若成功觸發 ACER 開門
	public function cmd_003()
	{
		$ticket_no = $this->input->post('ticket_no', true);
		
		trigger_error(__FUNCTION__ . ", ticket_no:{$ticket_no}");
		
		$data = $this->acer_service_model->cmd_003($ticket_no);
		echo json_encode($data, JSON_UNESCAPED_UNICODE); 
	}
	
	// 車辨入場訊號，觸發 ACER 開門
	public function cmd_101()
	{
		$cario_no = $this->input->post('cario_no', true);
		$in_time = $this->input->post('in_time', true);
		$ticket_no = $this->input->post('ticket_no', true);
		$lpr = $this->input->post('lpr', true);
		$ivs_no = $this->input->post('ivs_no', true);
		
		trigger_error(__FUNCTION__ . ", cario_no:{$cario_no}, in_time:{$in_time}, ticket_no:{$ticket_no}, lpr:{$lpr}, ivs_no:{$ivs_no}");
		
		$data = $this->acer_service_model->cmd_101($cario_no, $in_time, $ticket_no, $lpr, $ivs_no);
		echo json_encode($data, JSON_UNESCAPED_UNICODE); 
	}
	
	// 車辨離場訊號，觸發 ACER 開門 (若入場編號為 0, 表示車辨失敗)
	public function cmd_102()
	{
		$cario_no = $this->input->post('cario_no', true);
		$ivs_no = $this->input->post('ivs_no', true);
		$msg_code = $this->input->post('msg_code', true);
		
		/* msg_code:
			1	離場車辨失敗
			2	離場會員鎖車
			5	會員車離場
			6	臨停車離場 (時間內離場, 已繳費)
			7	臨停車離場 (超過離場時限, 歐pa卡繳費)
			8	臨停車未付款 (時間內離場, 無繳費)
			9	臨停車未付款 (超過離場時限, 非歐pa卡會員)
			10	會員車離場 (無入場資料)
			12	臨停車未付款 (超過離場時限, 歐pa卡餘額不足)
			13	無入場記錄
			16	時段月租戶超時（月租）
		*/
		
		trigger_error(__FUNCTION__ . ", cario_no:{$cario_no}, ivs_no:{$ivs_no}, msg_code:{$msg_code}");
		
		$data = $this->acer_service_model->cmd_102($cario_no, $ivs_no, $msg_code);
		echo json_encode($data, JSON_UNESCAPED_UNICODE); 
	}
	
	
	
	
	

	
	
	// 測試 acer cmd 101
	public function test_acer_cmd_101()
	{
		// input
		$cario_no = '1234567890';				// 入場編號： 10 碼
		$in_time = '2017-04-25 15:15:15';		// 入場時間： 19碼
		$ticket_no = '123456';					// 六碼數字
		$lpr = 'ABC123';
		$ivsno = 1;								// 車道編號
		
		$data = $this->acer_service_model->cmd_101($cario_no, $in_time, $ticket_no, $lpr, $ivsno);
		echo json_encode($data, JSON_UNESCAPED_UNICODE); 
	}
	
	// 測試 acer cmd 102
	public function test_acer_cmd_102()
	{
		// input
		$cario_no = '1234567890';				// 入場編號： 10 碼
		$ivsno = 1;								// 車道編號
		
		$data = $this->acer_service_model->cmd_102($cario_no, $ivsno);
		echo json_encode($data, JSON_UNESCAPED_UNICODE); 
	}
	
}
