<?php
/*
file: carpark.php		停車管理
*/
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// require_once(MQ_CLASS_FILE); 

class Parkingquery extends CI_Controller
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
		
		ignore_user_abort();	// 接受client斷線, 繼續run 
				
		$method_name = $this->router->fetch_method();
		if ($method_name == 'security_action')
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
        define('APP_NAME', 'parkingquery');		// 應用系統名稱   
          
        define('PAGE_PATH', APP_BASE.'ci_application/views/'.APP_NAME.'/');						// path of views
        
        define('SERVER_URL', 'http://'.(isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost').'/');	// URL
        define('APP_URL', SERVER_URL.APP_NAME.'.html/');										// controller路徑 
        define('WEB_URL', SERVER_URL.APP_NAME.'/');												// 網頁路徑
        define('WEB_LIB', SERVER_URL.'libs/');													// 網頁lib
        define('BOOTSTRAPS', WEB_LIB.'bootstrap_sb/');											// bootstrap lib  
        define('LOG_PATH', FILE_BASE.APP_NAME.'/logs/');	// log path
        
		$this->load->model('parkingquery_model'); 
		$this->load->model('security_model');	// 鎖車
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
		header('Cache-Control:max-age='.MAX_AGE);	// cache 1個月
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
        
        
    
    // response http               
	protected function http_return($return_code, $type)
	{                                      
    	if ($type == 'text')	echo $return_code;
        else					echo json_encode($return_code, JSON_UNESCAPED_UNICODE);  
        
    }    
    
    // 查詢各樓層剩餘車位 
	public function check_space_all() 
	{       
    	$seqno = $this->uri->segment(3);
        $data = $this->parkingquery_model->check_space_all($seqno);
        $data['result']['num'] = $seqno; 
        $data['result_code'] = 'OK'; 
        echo json_encode($data, JSON_UNESCAPED_UNICODE); 
    }
    	   
    // 查詢各樓層剩餘車位 
	public function check_space() 
	{       
    	$seqno = $this->uri->segment(3);
        $data = $this->parkingquery_model->check_space($seqno);
        $data['result']['num'] = $seqno; 
        $data['result_code'] = 'OK'; 
        echo json_encode($data, JSON_UNESCAPED_UNICODE); 
    }    
	
	// 查詢各樓層剩餘車位 (身障)
	public function check_space2() 
	{       
    	$seqno = $this->uri->segment(3);
        $data = $this->parkingquery_model->check_space($seqno, 3);
        $data['result']['num'] = $seqno; 
        $data['result_code'] = 'OK'; 
        echo json_encode($data, JSON_UNESCAPED_UNICODE); 
    }
    
	// 查詢各樓層剩餘車位 (婦友)
	public function check_space3() 
	{       
    	$seqno = $this->uri->segment(3);
        $data = $this->parkingquery_model->check_space($seqno, 4);
        $data['result']['num'] = $seqno; 
        $data['result_code'] = 'OK'; 
        echo json_encode($data, JSON_UNESCAPED_UNICODE); 
    }
     
    // 停車位置查詢
	public function check_location() 
	{       
    	$lpr = $this->uri->segment(3);
        $data = $this->parkingquery_model->check_location($lpr);
        echo json_encode($data, JSON_UNESCAPED_UNICODE); 
    }      
    
    // 空車位導引
	public function get_valid_seat() 
	{                                          
    	$pksno = $this->uri->segment(3, 0);	// 從某一個車位開始, 若無則設0 
        $data = $this->parkingquery_model->get_valid_seat($pksno);
        echo json_encode($data, JSON_UNESCAPED_UNICODE); 
    }    
	
	// 空車位導引 (身障)
	public function get_valid_seat2() 
	{                                          
    	$pksno = $this->uri->segment(3, 0);	// 從某一個車位開始, 若無則設0 
        $data = $this->parkingquery_model->get_valid_seat($pksno, 3);
        echo json_encode($data, JSON_UNESCAPED_UNICODE); 
    }
    
	// 空車位導引 (婦友)
	public function get_valid_seat3() 
	{                                          
    	$pksno = $this->uri->segment(3, 0);	// 從某一個車位開始, 若無則設0 
        $data = $this->parkingquery_model->get_valid_seat($pksno, 4);
        echo json_encode($data, JSON_UNESCAPED_UNICODE); 
    }
    
    // 防盜鎖車
    // http://xxxxxxxx/parkingquery.html/security_action/ABC1234/pswd/2
	public function security_action() 
	{                 
    	$lpr = $this->uri->segment(3);
    	$pswd = $this->uri->segment(4);
    	$action = $this->uri->segment(5);
        $data = $this->security_model->security_action($lpr, $pswd, $action);
        echo json_encode($data, JSON_UNESCAPED_UNICODE); 
    }
	
	// 查詢樓層總覽
	public function q_local_pks() 
	{       
		$seqno = $this->uri->segment(3);
		
		if(empty($seqno))
			$seqno = 'B1';
		
        $data = $this->parkingquery_model->q_local_pks($seqno);
        $data['result']['num'] = $seqno; 
		$data['result_code'] = 'OK'; 
        echo json_encode($data, JSON_UNESCAPED_UNICODE); 
    }    
	
	// [警急求救] 警急求救地圖
	public function floor_map()
	{
		$data = $this->parkingquery_model->check_space(0);
		
		if(isset($data['result']['floor']))
			$page_data['floor_info'] = json_encode($data['result']['floor'], JSON_UNESCAPED_UNICODE);
		
		$this->show_page("floor_map", $page_data);
	}
	
	// [警急求救] 警急求救地圖, 讀取緊急求救檔
	public function floor_map_read_sos()
	{
		if($this->my_ip() != '192.168.10.202')	// 限制車辨主機
		{
			trigger_error(__FUNCTION__ . '..unknown host..' . $this->my_ip());
			exit;
		}
		
		if (file_exists(SOS_MSG))
		{
			$str = file_get_contents(SOS_MSG);
			unlink(SOS_MSG);
			echo $str;
		}
		else
		{
			echo 'NONE';
		}
	}
    
    // [警急求救] 緊急求救 API
    // http://XXXXXXXXXXXXXXXX/parkingquery.html/send_sos/B2/111/123
	public function send_sos() 
	{                 
    	$floor = $this->uri->segment(3);
    	$x = $this->uri->segment(4);
    	$y = $this->uri->segment(5);
		
        file_put_contents(SOS_MSG, "{$floor},{$x},{$y}");  
		
        $data = $this->parkingquery_model->send_sos($floor, $x, $y);
        echo json_encode($data, JSON_UNESCAPED_UNICODE); 
    }
	
	// [第三方] 展示頁
	public function any_map()
	{
		$this->show_page("any_map");
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
}
