<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
file: Carpayment.php 	停車計費

http://192.168.10.201/carpayment.html/query_in/		(查詢入場時間)
http://192.168.10.201/carpayment.html/p2payed/

*/
       

        // ----- 定義常數(路徑, cache秒數) -----       
        define('APP_VERSION', '100');		// 版本號
                                        
        define('MAX_AGE', 604800);			// cache秒數, 此定義1個月     
        define('APP_NAME', 'carpayment');		// 應用系統名稱   
          
        define('PAGE_PATH', APP_BASE.'ci_application/views/'.APP_NAME.'/');						// path of views
        
        define('SERVER_URL', 'http://'.(isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost').'/');	// URL
        define('APP_URL', SERVER_URL.APP_NAME.'.html/');										// controller路徑 
        define('WEB_URL', SERVER_URL.APP_NAME.'/');												// 網頁路徑
        define('WEB_LIB', SERVER_URL.'/libs/');													// 網頁lib
        define('BOOTSTRAPS', WEB_LIB.'bootstrap_sb/');											// bootstrap lib  
        define('LOG_PATH', FILE_BASE.APP_NAME.'/logs/');		// log path name
        define('LOG_FILE', FILE_BASE.APP_NAME.'/logs/carpayment.');	// log file name

class Carpayment extends CI_Controller
{          
    var $vars = array();      
    
	function __construct() 
	{                            
    	// $this->time_start = microtime(true);  
		parent::__construct();      
        
		ignore_user_abort();	// 接受client斷線, 繼續run
        
		$this->vars['date_time'] = date('Y-m-d H:i:s');	// 格式化時間(2015-10-12 14:36:21) 
		$this->vars['time_num'] = str_replace(array('-', ':', ' '), '', $this->vars['date_time']); //數字化時間(20151012143621) 
        $this->vars['date_num'] = substr($this->vars['time_num'], 0, 8);	// 數字化日期(20151012) 
		//$this->vars['station_no'] = STATION_NO;	// 本站編號 
        
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
                 
        /*
        // 共用記憶體 
        $this->vars['mcache'] = new Memcache;
		$this->vars['mcache']->pconnect(MEMCACHE_HOST, MEMCACHE_POST) or die ('Could not connect memcache');   
                                        
        // mqtt subscribe
		$this->vars['mqtt'] = new phpMQTT(MQ_HOST, MQ_PORT, uniqid());  
		if(!$this->vars['mqtt']->connect()){ die ('Could not connect mqtt');  }
        
        */
		$this->load->model('carpayment_model'); 
        $this->carpayment_model->init($this->vars);
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
	
	
	
                 
                                                
    // http://localhost/carpayment.html/p2payed/ (post method)
    // 可用$this->input->is_cli_request()判斷是否在cli之下執行
    // 博辰aps已付款
    public function p2payed()
	{            
    	$parms['ticket_no'] = $this->input->post('ticket_no', true);
    	$parms['lpr'] = trim($this->input->post('lpr', true));
    	$parms['in_time'] = $this->input->post('in_time', true);
    	$parms['pay_time'] = $this->input->post('pay_time', true);
    	$parms['pay_type'] = $this->input->post('pay_type', true);
        trigger_error('博辰付款參數:' . print_r($parms, true));
        
        $this->carpayment_model->p2payed($parms);
    }  
	
	// MITAC 付款
	public function mitac2payed()
	{            
    	$cario_no = $this->input->post('cario_no', true);	// 通訊序號
		$lpr = $this->input->post('lpr', true);				// 車牌
		$amt = $this->input->post('amt', true);						// 金額
		$amt_discount =	$this->input->post('amt_discount', true);	// 折扣
		$amt_real = $this->input->post('amt_real', true);			// 實收
		$in_time = $this->input->post('in_time', true);		// 入場時間
		$pay_time = $this->input->post('pay_time', true);	// 付款時間
		$ck = $this->input->post('ck', true);				// 驗証碼
		
		// 通訊內容
		$parms = array(
			'cario_no' => $cario_no, 
			'lpr' => $lpr, 
			'amt' => $amt, 
			'amt_discount' => $amt_discount, 
			'amt_real' => $amt_real,
			'in_time' => $in_time, 
			'pay_time' => $pay_time);
			
		if($ck != md5($parms['cario_no']. 'a' . date('dmh') . 'l' . $parms['lpr'] . 't'. $parms['amt']. 'o'. $parms['amt_discount'] . 'b'. $parms['amt_real'] . __FUNCTION__))
		{
			trigger_error(__FUNCTION__ . '..ck_error..' . print_r($parms, true));
			exit; // 中斷
		}
		
		$parms['pay_type'] = 93; // MITAC 專用
		trigger_error('MITAC 付款:' . print_r($parms, true));
		
		// 註記已離場
		$this->carpayment_model->p2payed($parms, false, true);
    }
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
      
    // 行動支付, 手機告知已付款            
    // http://203.75.167.89/carpayment.html/m2payed/ABC1234/120/12112/12345/1f3870be274f6c49b3e31a0c6728957f 
    // http://203.75.167.89/carpayment.html/m2payed/車牌/金額/場站編號/序號/MD5 
    // md5(車牌.金額.場站編號.序號)
    public function m2payed()
	{            
    	$parms['lpr'] =  $lines = $this->uri->segment(3);
    	$parms['amt'] =  $lines = $this->uri->segment(4);
    	$parms['station_no'] =  $lines = $this->uri->segment(5);
    	$parms['seqno'] =  $lines = $this->uri->segment(6);
    	$md5 =  $this->uri->segment(7); 
		trigger_error(__FUNCTION__ . '..' . print_r($parms, true));
        echo $this->carpayment_model->m2payed($parms); 
    }
    
                         
    // 查詢入場時間
    public function query_in()
	{            
    	$lpr = $this->input->post('lpr', true);
                                                            
        $data = $this->carpayment_model->query_in($lpr);
        echo json_encode($data);
    }

    // 查詢入場時間 (fuzzy)
    public function query_in_fuzzy()
	{            
    	$lpr = $this->input->post('lpr', true);
                                                            
        $data = $this->carpayment_model->query_in_fuzzy($lpr);
        echo json_encode($data);
    }	
         
                    
    // 行動設備查詢入場時間   
    // http://203.75.167.89/carpayment.html/m2query_in/ABC1234/12112/1f3870be274f6c49b3e31a0c6728957f 
    // http://203.75.167.89/carpayment.html/m2query_in/車牌/場站編號/MD5  
    // 回傳0: 失敗, 成功: 12345,60(第一欄位非0數字代表成功, 第二欄位為金額), 此值在付款時必需傳回, 否則視為非法
    public function m2query_in()
	{                                 
    
    	$parms['lpr'] =  $lines = $this->uri->segment(3);
    	$parms['station_no'] =  $lines = $this->uri->segment(4);
    	$md5 =  $this->uri->segment(5); 
                                                            
        // 驗證md5
        if (md5($parms['lpr'].$parms['station_no']) === $md5)
        {	                                               
        	$data = $this->carpayment_model->m2query_in($parms);
        }
        else
        {                       
        	$data = 0;
        }   
        
        $_SESSEION['seqno'] = $data;
        echo $data;
    }     
	
	// 測試：回傳 seat_no
	// http://192.168.0.199/carpayment.html/test_seat_no/B2/123
	public function test_seat_no()
	{
		$rows['group_id'] =  $this->uri->segment(3);
		$rows['pksno'] =  $this->uri->segment(4);
		
		//echo substr($rows['group_id'], 0, 1);
		echo (substr($rows['group_id'], 0, 1) == 'B' ? '-' : '0') . substr($rows['group_id'], 1, 1) . '_' . substr($rows['pksno'], -3);
	}
}
