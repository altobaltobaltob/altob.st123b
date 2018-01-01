<?php
/*
file: vip.php		交通局VIP管理系統
*/
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once(MQ_CLASS_FILE); 

session_start(); //we need to call PHP's session object to access it through CI

class Vip extends CI_Controller
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
        
		/**
        // 共用記憶體 
        $this->vars['mcache'] = new Memcache;
		$this->vars['mcache']->connect(MEMCACHE_HOST, MEMCACHE_POST) or die ('Could not connect memcache'); 
                                        
        // mqtt subscribe
		$this->vars['mqtt'] = new phpMQTT(MQ_HOST, MQ_POST, 'cario');  
                                 
		if(!$this->vars['mqtt']->connect()){ die ('Could not connect mqtt');  }
		**/
		
        // ----- 定義常數(路徑, cache秒數) -----       
        define('APP_VERSION', '100');		// 版本號
                                        
        define('MAX_AGE', 604800);			// cache秒數, 此定義1個月     
        define('APP_NAME', 'vip');		// 應用系統名稱   
          
        define('PAGE_PATH', APP_BASE.'ci_application/views/'.APP_NAME.'/');						// path of views
        
        define('SERVER_URL', 'http://'.(isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost').'/');	// URL
        define('APP_URL', SERVER_URL.APP_NAME.'.html/');										// controller路徑 
        define('WEB_URL', SERVER_URL.APP_NAME.'/');												// 網頁路徑
        define('WEB_LIB', SERVER_URL.'libs/');													// 網頁lib
        define('BOOTSTRAPS', WEB_LIB.'bootstrap_sb/');											// bootstrap lib  
        define('LOG_PATH', FILE_BASE.APP_NAME.'/logs/');	// log path
		
        
		$this->load->model('vip_model'); 
        $this->vip_model->init($this->vars);
		
		// load library
		$this->load->library(array('form_validation','session'));
		// load helpers
		$this->load->helper(array('form'));  
		// ajax code
		define('RESULT_SUCCESS', 'ok');
		define('RESULT_FORM_VALIDATION_FAIL', '-1');
		define('RESULE_FAIL', 'gg');
	}
       
    
    
    // 發生錯誤時集中在此處理
	public function error_handler($errno, $errstr, $errfile, $errline, $errcontext)
	{                                      
    	// ex: car_err://message....
    	//$log_msg = explode('://', $errstr);
        /*
        if (count($log_msg) > 1)
        {
            $log_file = LOG_PATH.$log_msg[0];    
        	$str = date('H:i:s')."|{$log_msg[1]}|{$errfile}|{$errline}|{$errno}\n"; 
        } 
        else
        {   
        	$log_file = LOG_PATH.APP_NAME;
    		$str = date('H:i:s')."|{$errstr}|{$errfile}|{$errline}|{$errno}\n";
        }
        */   
        
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
		if($this->session->userdata('logged_in'))
		{
			$session_data = $this->session->userdata('logged_in');
			$data['username'] = $session_data['username'];
			$data['type'] = $session_data['type'];
			
			if($data['type'] == 'ma')
			{
				$this->show_page('admin_page', $data); // 進階管理者介面
			}
			else
			{
				$this->show_page('main_page', $data); // 一般管理者介面
			}
		}
		else
		{
			//If no session, redirect to login page
			//redirect('login', 'refresh');
			$this->show_page('login_page');
		}
	}
	         
	// 登入
	public function user_login()
	{   		
		// form_validation
		$this->form_validation->set_rules('login_name', 'login_name', 'trim|required|xss_clean');
		$this->form_validation->set_rules('pswd', 'pswd', 'trim|required|xss_clean');

		if($this->form_validation->run() == FALSE)
		{
			return RESULT_FORM_VALIDATION_FAIL;
		}
		
		// go model
		$data = array
				(
					'login_name' => $this->input->post('login_name', true),                 
					'pswd' => $this->input->post('pswd', true)
				);                           
		
		$result = $this->vip_model->user_login($data);
		
		if($result)
		{
			$sess_array = array();
			foreach($result as $row)
			{
				$sess_array = array
				(
					'username' => $row->login_name ,
					'type' => $row->type
				);
				$this->session->set_userdata('logged_in', $sess_array);
			}
			echo RESULT_SUCCESS;
		}
		else
		{
			return RESULE_FAIL;
		}
	}
	
	// 登出
	public function user_logout()
	{   
		$this->session->unset_userdata('logged_in');
		session_destroy();
		return RESULT_SUCCESS;
	}
    
    // 新增與修改
    public function member_add()
	{
		// form_validation (required)
		$this->form_validation->set_rules('member_no', 'member_no', 'trim|required|xss_clean');
		$this->form_validation->set_rules('station_no', 'station_no', 'trim|required|xss_clean');
		$this->form_validation->set_rules('lpr', 'lpr', 'trim|required|xss_clean|alpha_numeric');
		$this->form_validation->set_rules('start_date', 'start_date', 'trim|required|xss_clean');
		$this->form_validation->set_rules('end_date', 'end_date', 'trim|required|xss_clean');
		$this->form_validation->set_rules('member_name', 'member_name', 'trim|required|xss_clean');
		$this->form_validation->set_rules('mobile_no', 'mobile_no', 'trim|required|xss_clean');
		// form_validation (basic)
		$this->form_validation->set_rules('remarks', 'remarks', 'trim|xss_clean');
		
		if($this->form_validation->run() == FALSE)
		{
			return RESULT_FORM_VALIDATION_FAIL;
		}
	
		// go model
    	$data = array
        		(
					'member_no' => $this->input->post('member_no', true),                 
					'station_no' => $this->input->post('station_no', true),                 
					'lpr' => strtoupper($this->input->post('lpr', true)),                 
					'start_date' => $this->input->post('start_date', true),                 
					'end_date' => $this->input->post('end_date', true),                 
					'member_name' => $this->input->post('member_name', true),         
					'member_nick_name' => $this->input->post('member_name', true),         
					'mobile_no' => $this->input->post('mobile_no', true), 
					'remarks' => $this->input->post('remarks', true)
                );                           
         
        $this->vip_model->vip_add($data);
        echo RESULT_SUCCESS;
	}  
    
    
    // 查詢
    public function member_query()
	{                                
        $data = $this->vip_model->vip_query();
		
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
	}    
    
    
    // 刪除
    public function member_delete()
	{                                
		// form_validation
		$this->form_validation->set_rules('member_no', 'member_no', 'trim|required|xss_clean');
		
		if($this->form_validation->run() == FALSE)
		{
			return RESULE_FAIL;
		}
	
		// go model
        $member_no = $this->input->post('member_no', true);
        $this->vip_model->member_delete($member_no);
        echo RESULT_SUCCESS;
	}       
	
	// 管理者新增與修改
    public function user_add()
	{
		// 判斷target_name分流insert or update
		$this->form_validation->set_rules('target_name', 'target_name', 'trim|xss_clean');
		// form_validation (basic)
		$this->form_validation->set_rules('type', 'type', 'trim|required|xss_clean');
		$this->form_validation->set_rules('user_name', 'user_name', 'trim|xss_clean');
		$this->form_validation->set_rules('email', 'email', 'trim|xss_clean');
		$this->form_validation->set_rules('mobile_no', 'mobile_no', 'trim|xss_clean');
		$this->form_validation->set_rules('tel', 'tel', 'trim|xss_clean');
		$this->form_validation->set_rules('car_plate', 'car_plate', 'trim|xss_clean');
		
		if($this->form_validation->run() == FALSE)
		{
			return RESULT_FORM_VALIDATION_FAIL;
		}
		
		$target_name = $this->input->post('target_name', true);
		
		if($target_name == '')
		{
			// insert 流程
			
			// form_validation (required)
			$this->form_validation->set_rules('login_name', 'login_name', 'trim|required|xss_clean');
			$this->form_validation->set_rules('pswd', 'pswd', 'trim|required|xss_clean');
			
			if($this->form_validation->run() == FALSE)
			{
				return RESULT_FORM_VALIDATION_FAIL;
			}
		
			// go model
			$data = array
					(
						'type' => $this->input->post('type', true),
						'login_name' => $this->input->post('login_name', true),                 
						'pswd' => MD5($this->input->post('pswd', true)),                 
						'user_name' => $this->input->post('user_name', true),
						'email' => $this->input->post('email', true),
						'mobile_no' => $this->input->post('mobile_no', true),         
						'tel' => $this->input->post('tel', true), 
						'car_plate' => strtoupper($this->input->post('car_plate', true))
					);                          
			 
			$this->vip_model->user_insert($data);
			echo RESULT_SUCCESS;
		}
		else
		{
			// update 流程
			
			// go model
			$data = array
					(                
						'type' => $this->input->post('type', true),
						'user_name' => $this->input->post('user_name', true),
						'email' => $this->input->post('email', true),
						'mobile_no' => $this->input->post('mobile_no', true),         
						'tel' => $this->input->post('tel', true), 
						'car_plate' => strtoupper($this->input->post('car_plate', true))
					);                          
			 
			$this->vip_model->user_update($data, $target_name);
			echo RESULT_SUCCESS;
		}
		
		
	}
    
    
    // 管理者查詢
    public function user_query()
	{                                
        $data = $this->vip_model->user_query();
		
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
	}    
    
    
    // 管理者刪除
    public function user_delete()
	{                                
		// form_validation
		$this->form_validation->set_rules('login_name', 'login_name', 'trim|required|xss_clean');
		
		if($this->form_validation->run() == FALSE)
		{
			return RESULE_FAIL;
		}
	
		// go model
        $login_name = $this->input->post('login_name', true);
        $this->vip_model->user_delete($login_name);
        echo RESULT_SUCCESS;
	}
    
}
