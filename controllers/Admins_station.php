<?php
/*
file: Admins_station.php		停車管理
*/
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once(MQ_CLASS_FILE);  

// ----- 定義常數(路徑, cache秒數) -----       
define('APP_VERSION', '100');											// 版本號
define('MAX_AGE', 604800);												// cache秒數, 此定義1個月     
define('APP_NAME', 'admins_station');									// 應用系統名稱   
define('PAGE_PATH', APP_BASE.'ci_application/views/'.APP_NAME.'/');		// path of views
define('SERVER_URL', 'http://'.$_SERVER['HTTP_HOST'].'/');				// URL   
define('WEB_SERVICE', 'http://'.$_SERVER['SERVER_NAME'].':60133/?');	// web service port:60133   
define('WEB_LIB', SERVER_URL.'libs/');									// 網頁lib
define('BOOTSTRAPS', WEB_LIB.'bootstrap_sb/');							// bootstrap lib  
define('APP_URL', SERVER_URL.APP_NAME.'.html/');						// controller路徑 
define('WEB_URL', SERVER_URL.APP_NAME.'/');								// 網頁路徑
define('LOG_PATH', FILE_BASE.APP_NAME.'/logs/');						// log path

// ----- 共用 header -----
header('Access-Control-Allow-Origin: ' . 'http://'.$_SERVER['SERVER_NAME']);	// nginx 80 轉 apache 60123

class Admins_station extends CI_Controller
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
        
        // 共用記憶體 
        $this->vars['mcache'] = new Memcache;
		$this->vars['mcache']->connect(MEMCACHE_HOST, MEMCACHE_PORT) or die ('Could not connect memcache');   
                                        
        // mqtt subscribe
		$this->vars['mqtt'] = new phpMQTT(MQ_HOST, MQ_PORT, uniqid());  
                                 
		if(!$this->vars['mqtt']->connect()){ die ('Could not connect mqtt');  }
        
		// 中控
		$this->load->model('admins_station_model'); 
        $this->admins_station_model->init($this->vars);
		
		// 臨停
		$this->load->model('carpayment_model'); 
        $this->carpayment_model->init($this->vars);
		
		// 費率
		$this->load->model('txdata_model'); 
		
		// 報表
		$this->load->model('excel_model'); 
        $this->excel_model->init($this->vars);
	}

    
    // 發生錯誤時集中在此處理
	public function error_handler($errno, $errstr, $errfile, $errline, $errcontext)
	{                                      
    	// ex: car_err://message....
    	$log_msg = explode('://', $errstr);
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
        
    	//$str = date('H:i:s')."|{$errstr}|{$errfile}|{$errline}|{$errno}\n";               
    	error_log($str, 3, $log_file . '.' . date('Ymd').'.log.txt');	// 3代表參考後面的檔名
    }
    
    
	// 顯示靜態網頁(html檔)
	protected function show_page($page_name, &$data = null)
	{           
    	$page_file = PAGE_PATH.$page_name.'.php';
        $last_modified_time = filemtime($page_file); 
          
        /*
    	// Cross-Origin Resource Sharing Header(允許跨網域連線)
		header('Access-Control-Allow-Origin: ' . SERVER_URL);
		header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
		header('Access-Control-Allow-Headers: X-Requested-With, Content-Type, Accept'); 
        */       
            
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
		// 20170505 改由遠端中控
		echo '已停用';
		exit;
		
		$this->show_page('main_page');
	}
	
    
    // 送出html code     	
	public function get_html()
	{                             
    	$data = array
        (
			'company_no' => $this->input->post('company_no', true),	// 場站統編
            'hq_company_no' => '80682490'         
        );
    	$this->load->view(APP_NAME.'/'.$this->input->post('tag_name', true), $data);
	}         
       
    // 登入帳密檢查
	public function login_verify()
	{               
    	$login_name = $this->input->post('login_name', true);
    	$login_pswd = $this->input->post('login_pswd', true);  
        
        $ok = $this->admins_station_model->login_verify($login_name, $login_pswd);
        if ($ok)	// 帳密正確
        {                      
        	$_SESSION['login_ck'] = uniqid();
            $data = array('rcode' => 'OK', 'ck' => $_SESSION['login_ck']);
        }
        else
        {   
        	$_SESSION['login_ck'] = 'NOLOGIN';
            $data = array('rcode' => 'NOLOGIN', 'ck' => $_SESSION['login_ck']);
        }                       
        
        echo json_encode($data, JSON_UNESCAPED_UNICODE);          
	}
                       
    // 設定javascript初始值
	public function js_vars()
	{       
        $data = $this->admins_station_model->get_init_vars();
		echo $data;
	} 
	
	// 費率清單
    public function price_plan_query_all()
	{                                
        $data = $this->txdata_model->get_all_valid_price_plan(STATION_NO);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
	}
    
	// 會員清單
    public function member_query_all()
	{                                
        $data = $this->admins_station_model->member_query_all();
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
	}
	
	// 待審核清單
    public function member_tx_check_query()
	{                                
        $data = $this->admins_station_model->member_tx_check_query();
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
	}
	
	// 已退款清單
    public function member_tx_refund_query()
	{                                
		$station_no = $this->input->post('station_no', true);
    	$q_item = $this->input->post('q_item', true);
    	$q_str = $this->input->post('q_str', true);
                
        $data = $this->admins_station_model->member_tx_refund_query($station_no, $q_item, $q_str);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
	}
	
	// 取得轉租資訊
    public function member_refund_transfer_data_query()
	{                                
		$station_no = $this->input->post('station_no', true);
    	$member_no = $this->input->post('member_no', true);
    	$member_refund_id = $this->input->post('member_refund_id', true);
		        
        $data = $this->admins_station_model->member_refund_transfer_data_query($station_no, $member_no, $member_refund_id);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
	}
	
	// 押金保留，結清其它金額 (退租後)
	public function member_refund_keep_deposit()
	{
		$parms = array();
    	$parms['station_no'] = $this->input->post('station_no', true);					// 場站編號
    	$parms['member_refund_id'] = $this->input->post('member_refund_id', true);		// 退租編號
		
		if(empty($parms['member_refund_id']) || empty($parms['station_no']))
		{
			echo '資料異常'; 
			exit;  
		}
		
		echo $this->admins_station_model->member_refund_keep_deposit($parms);
	}
	
	// 結清所有金額 (退租後)
	public function member_refund_dismiss_all()
	{
		$parms = array();
    	$parms['station_no'] = $this->input->post('station_no', true);					// 場站編號
    	$parms['member_refund_id'] = $this->input->post('member_refund_id', true);		// 退租編號
		
		if(empty($parms['member_refund_id']) || empty($parms['station_no']))
		{
			echo '資料異常'; 
			exit;  
		}
		
		echo $this->admins_station_model->member_refund_dismiss_all($parms);
	}
	
	// 發票折讓 (退租後)
	public function refund_invoice_allowance()
	{
		$parms = array();
    	$parms['station_no'] = $this->input->post('station_no', true);					// 場站編號
    	$parms['member_no'] = $this->input->post('member_no', true);					// 會員編號
		$parms['tx_bill_no'] = $this->input->post('tx_bill_no', true);					// 帳單編號
		$parms['refund_amt'] = $this->input->post('refund_amt', true);					// 折讓金額
		$parms['tx_no'] = $this->input->post('tx_no', true);							// 交易編號
		$parms['company_no'] = $this->input->post('company_no', true);					// 賣方統編
		
		if(empty($parms['member_no']) || empty($parms['station_no']) || empty($parms['tx_bill_no']) || empty($parms['tx_no']))
		{
			echo '資料異常'; 
			exit;  
		}
		
		// 若賣方統編未設定, 預設拿場站統編
		if(empty($parms['company_no']))
		{
			$st_info = $this->vars['mcache']->get('st_info');
			$parms['company_no'] = $st_info['company_no'];
		}
        
		echo $this->admins_station_model->refund_invoice_allowance($parms);
	}
	
	// 臨停未結確認完成
	public function cario_temp_confirmed()
	{                                
        $parms = array();
    	$parms['station_no'] = $this->input->post('station_no', true);		// 場站編號
    	$parms['cario_no'] = $this->input->post('cario_no', true);			// 進出編號
		$parms['remarks'] = $this->input->post('remarks', true);			// 備註
        echo $this->admins_station_model->cario_temp_confirmed($parms);
	}
	
	// 審核完成
	public function member_tx_confirmed()
	{                                
        $parms = array();
    	$parms['station_no'] = $this->input->post('station_no', true);		// 場站編號
    	$parms['tx_no'] = $this->input->post('tx_no', true);				// 交易編號
    	$parms['verify_state'] = $this->input->post('verify_state', true);	// 0:未審核, 1:人工審核完成
		//$parms['valid_time'] = $this->input->post('valid_time', true);	// 有效期限
		$parms['remarks'] = $this->input->post('remarks', true);			// 備註
        echo $this->admins_station_model->member_tx_confirmed($parms);
	}
	
	// 交易取消
	public function member_tx_cancel()
	{
		$parms = array();
    	$parms['station_no'] = $this->input->post('station_no', true);		// 場站編號
    	$parms['member_no'] = $this->input->post('member_no', true);		// 會員編號
		$parms['tx_no'] = $this->input->post('tx_no', true);				// 交易編號
		echo $this->admins_station_model->member_tx_cancel($parms);
	}
    
    // 會員查詢    	
	public function member_query()
	{                           
    	$station_no = $this->input->post('station_no', true);
    	$q_item = $this->input->post('q_item', true);
    	$q_str = $this->input->post('q_str', true);
                
        $data = $this->admins_station_model->member_query($station_no, $q_item, $q_str);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
	}
	
	// 交易查詢    	
	public function member_tx_query()
	{                           
    	$station_no = $this->input->post('station_no', true);
    	$member_no = $this->input->post('member_no', true);
                
        $data = $this->admins_station_model->member_tx_query($station_no, $member_no);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
	}
	
	// 發票查詢    	
	public function member_tx_bill_query()
	{                           
    	$station_no = $this->input->post('station_no', true);
    	$tx_no = $this->input->post('tx_no', true);
		$verify_state_str = $this->input->post('verify_state_str', true);
        $invoice_state_str = $this->input->post('invoice_state_str', true);
		$tx_state_str = $this->input->post('tx_state_str', true);
		$tx_bill_no = $this->input->post('tx_bill_no', true);
		$member_refund_id = $this->input->post('member_refund_id', true);
		
        $data = $this->admins_station_model->member_tx_bill_query(
			$station_no, $tx_no, $verify_state_str, $invoice_state_str, $tx_state_str, $tx_bill_no, $member_refund_id);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
	}
	
	// 退租發票查詢    	
	public function member_refund_bill_query()
	{                           
    	$station_no = $this->input->post('station_no', true);
    	$tx_no = $this->input->post('tx_no', true);
		$verify_state_str = $this->input->post('verify_state_str', true);
        $invoice_state_str = $this->input->post('invoice_state_str', true);
		$tx_state_str = $this->input->post('tx_state_str', true);
		$tx_bill_no = $this->input->post('tx_bill_no', true);
		$member_refund_id = $this->input->post('member_refund_id', true);
		
        $data = $this->admins_station_model->member_refund_bill_query(
			$station_no, $tx_no, $verify_state_str, $invoice_state_str, $tx_state_str, $tx_bill_no, $member_refund_id);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
	}
	
	// 臨停未結清單
	public function cario_temp_not_finished_query_all()
	{                           
    	$station_no = $this->input->post('station_no', true);
    	$q_item = $this->input->post('q_item', true);
    	$q_str = $this->input->post('q_str', true);
                
        $data = $this->carpayment_model->cario_temp_not_finished_query_all($station_no, $q_item, $q_str);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
	}
	
    
    // response http               
	protected function http_return($return_code, $type)
	{                                      
    	if ($type == 'text')	echo $return_code;
        else					echo json_encode($return_code, JSON_UNESCAPED_UNICODE);  
    }    
    
    
    
    // 讀取cookie內容
	protected function get_cookie($cookie_name)
	{                     
    	if (empty($_COOKIE[$cookie_name]))	return array();
    	return(json_decode($_COOKIE[$cookie_name], true));  
    }  
    
    
    // 儲存cookie內容
	protected function save_cookie($cookie_name, $cookie_info)
	{ 
    	return setcookie($cookie_name, json_encode($cookie_info, JSON_UNESCAPED_UNICODE), 0, '/');
    } 
    
    
    // 月租資料同步    	
	/*
	public function rent_sync()
	{                           
    	$station_no = $this->input->post('station_no', true);
    	$start_date = $this->input->post('start_date', true);
    	$end_date = $this->input->post('end_date', true);
                
        // $data = $this->admins_station_model->rent_sync($station_no, $start_date, $end_date);
		                                     
        // print_r($data);
	}
	*/
    
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
    
    
    // 將設定檔存入記憶體
    public function info2mem()
	{                       
        $data = $this->admins_station_model->get_info();
        foreach($data as $var => $values)
        {
        	$this->mcache->set('i_'.$var, $values);
        }
        
        echo 'OK !';
	}     
    
    
    // 將設定檔存入記憶體
    public function get_info()
	{    
    	echo $this->mcache->get('i_station_no');
	} 
    
    
    // 新增月租資料
    public function member_add()
	{          
		$start_date = $this->input->post('start_date', true);
		if(empty($start_date))
		{
			$start_date = $this->input->post('start_date_done', true);	
		}
		
		$end_date = $this->input->post('end_date', true);
		if(empty($end_date))
		{
			$end_date = $this->input->post('end_date_done', true);	
		}
		
		$demonth_start_date = $this->input->post('demonth_start_date', true);
		if(empty($demonth_start_date))
		{
			$demonth_start_date = $this->input->post('demonth_start_date_done', true);	
		}
		
		$demonth_end_date = $this->input->post('demonth_end_date', true);
		if(empty($demonth_end_date))
		{
			$demonth_end_date = $this->input->post('demonth_end_date_done', true);	
		}
		
    	$data = array
        (
			'member_no' => $this->input->post('member_no', true),                 
			'station_no' => $this->input->post('station_no', true),                 
			'lpr' => strtoupper($this->input->post('lpr', true)),                 
			'old_lpr' => strtoupper($this->input->post('old_lpr', true)),                 
			'etag' => strtoupper($this->input->post('etag', true)),                 
			'start_date' => $start_date,                 
			'end_date' => $end_date,                 
			'park_time' => $this->input->post('park_time', true),                 
			'member_name' => $this->input->post('member_name', true),         
			'member_nick_name' => $this->input->post('member_name', true),         
			'mobile_no' => $this->input->post('mobile_no', true),                
			'member_id' => $this->input->post('member_id', true),           
			'contract_no' => $this->input->post('contract_no', true), 				// 總公司 only ??			
			'member_company_no' => $this->input->post('member_company_no', true),	// 買方統編(會員統編)                          
			'company_no' => $this->input->post('company_no', true),					// 賣方統編                         
			'amt' => $this->input->post('amt', true),                          
			'tel_h' => $this->input->post('tel_h', true),                          
			'tel_o' => $this->input->post('tel_o', true),                          
			'addr' => $this->input->post('addr', true),                          
			'demonth_start_date' => $demonth_start_date,                          
			'demonth_end_date' => $demonth_end_date,  
			'member_attr' => $this->input->post('member_attr', true),                          
			'fee_period1' => $this->input->post('fee_period1', true),                          
			'fee_period' => $this->input->post('fee_period', true),                          
			'amt1' => $this->input->post('amt1', true),                                                           
			'deposit' => $this->input->post('deposit', true),                
			'amt_tot' => $this->input->post('amt_tot', true),                           
			'amt_accrued' => $this->input->post('amt_accrued', true),
			'refund_transfer_id' => $this->input->post('refund_transfer_id', true),					// 轉租來源編號
			'refund_transfer_discount' =>  $this->input->post('refund_transfer_discount', true)		// 轉租折扺金額
        );     

		if(	empty($data['station_no']) || empty($data['lpr']))
		{
			echo '資料異常'; 
			exit;  
		}
		
        trigger_error("add:".print_r($data, true));
        //if ($data['member_no'] == 0 || $data['old_lpr'] != $data['lpr'])
		if ($data['member_no'] == 0)
        {
			// 本次操作為新增車牌, 開始驗証是否能繼續
			
			if(	empty($data['start_date']) || empty($data['end_date']) || 
				empty($data['demonth_start_date']) || empty($data['demonth_end_date']))
			{
				echo '日期資料異常'; 
				exit;
			}
			
			if (!empty($data['refund_transfer_id']))
			{
				// 轉租戶 (直接進入新增流程, 新資料蓋掉舊資料)
			}
			else
			{
				// 一般戶, 再確認一次退租名單是否重複
				if ($this->admins_station_model->check_refund_lpr($data['lpr']) > 0)
				{
					echo '此車牌尚有押金未轉移，請進行轉租'; 
					exit;
				}	
				
				// 已退租且互不相欠的一般戶 (直接進入新增流程, 新資料蓋掉舊資料)
			}
        }
		else
		{
			// 本次操作為修改記錄, 開始驗証是否能繼續
			
			// 一般戶, 確認是否存在未處理的退租金額
			if ($this->admins_station_model->check_refund_member_no($data['member_no']) > 0)
			{
				echo '此會員尚有押金未轉移，請進行轉租'; 
				exit;
			}			
		}
		
		// 取得月租費率設定
		$rents_arr = $this->get_rents_arr();
		
       	echo json_encode($this->admins_station_model->member_add($data, $rents_arr), JSON_UNESCAPED_UNICODE);  
	}  
    
    // 刪除月租資料
	/*
    public function member_delete()
	{                                
        $member_no = $this->input->post('member_no', true);
        $station_no = $this->input->post('station_no', true);
		if(empty($member_no) || empty($station_no))
		{
			echo '資料異常'; 
			exit;  
		}
        echo $this->admins_station_model->member_delete($station_no, $member_no);
	} 
	*/	
    
    
    
    // 顯示圖檔(http://url/carpark.html/pics/lpr_ABY8873_O_0_0_C_20150919210022)
    public function pics()
	{                                                         
    	// ???
        readfile(CAR_PIC.$this->uri->segment(3).'/'.str_replace('/', '', $this->uri->segment(4)).'.jpg');
	}    
    
    
	
	// 說明: 手開發票
	public function hand_first_rents_payment()
	{         
		$parms = array();
		$parms['tx_no'] = $this->input->post('tx_no', true);							// 交易編號
    	$parms['station_no'] = $this->input->post('station_no', true);					// 場站編號
    	$parms['member_no'] = $this->input->post('member_no', true);					// 會員編號
    	$parms['member_company_no'] = $this->input->post('member_company_no', true);	// 買方統編
    	$parms['company_no'] = $this->input->post('company_no', true);					// 賣方統編
    	$parms['amt1'] = $this->input->post('amt1', true);								// 首期租金
		$parms['amt'] = $this->input->post('amt', true);								// 本期租金
		$parms['invoice_track'] = $this->input->post('invoice_track', true);			// * 發票字軌
		$parms['invoice_no'] = $this->input->post('invoice_no', true);					// * 發票號碼
		$parms['invoice_type'] = 1; // 手開發票
		
		$parms['invoice_amt'] = $this->input->post('invoice_amt', true);				// * 發票金額
		$parms['tx_bill_no'] = $this->input->post('tx_bill_no', true);					// 帳單編號
		
		if(empty($parms['tx_bill_no']) || empty($parms['tx_no']) || empty($parms['member_no']) || empty($parms['station_no']) )
		{
			echo '資料異常'; 
			exit;  
		}
		
		if(empty($parms['invoice_track']) || empty($parms['invoice_no']))
		{
			echo '查無發票資訊'; 
			exit;  
		}
		
		if(empty($parms['invoice_amt']))
		{
			echo '查無金額資訊'; 
			exit;  
		}
		
		// 若賣方統編未設定, 預設拿場站統編
		if(empty($parms['company_no']))
		{
			$st_info = $this->vars['mcache']->get('st_info');
			$parms['company_no'] = $st_info['company_no'];
		}
		
		// 確認是否存在退租記錄
		if ($this->admins_station_model->check_refund_member_no_exist($parms['member_no']) > 0)
		{
			echo '此會員已退租'; 
			exit;
		}
        
		echo $this->admins_station_model->first_rents_payment($parms);
	}
    
	// 說明: 1.新建立直接列印發票、 2.列印發票 (TODO: 待接上歐付寶)
	public function first_rents_payment()
	{         
		$parms = array();
		$parms['tx_no'] = $this->input->post('tx_no', true);							// 交易編號
    	$parms['station_no'] = $this->input->post('station_no', true);					// 場站編號
    	$parms['member_no'] = $this->input->post('member_no', true);					// 會員編號
		$parms['member_attr'] = $this->input->post('member_attr', true);				// 會員身份
    	$parms['member_company_no'] = $this->input->post('member_company_no', true);	// 買方統編
    	$parms['company_no'] = $this->input->post('company_no', true);					// 賣方統編
    	$parms['amt1'] = $this->input->post('amt1', true);								// 首期租金
		$parms['amt'] = $this->input->post('amt', true);								// 本期租金
		
		$parms['invoice_amt'] = $this->input->post('invoice_amt', true);				// * 發票金額
		$parms['tx_bill_no'] = $this->input->post('tx_bill_no', true);					// 帳單編號
		
		$parms['email'] = $this->input->post('email', true);							// 發票通知信箱
		$parms['mobile'] = $this->input->post('mobile', true);							// 發票通知簡訊
		
		if(empty($parms['tx_bill_no']) || empty($parms['tx_no']) || empty($parms['member_no']) || empty($parms['station_no']))
		{
			echo '資料異常'; 
			exit;  
		}
		
		if(empty($parms['invoice_amt']))
		{
			echo '查無金額資訊'; 
			exit;  
		}
		
		// 若賣方統編未設定, 預設拿場站統編
		if(empty($parms['company_no']))
		{
			$st_info = $this->vars['mcache']->get('st_info');
			$parms['company_no'] = $st_info['company_no'];
		}
		
		// 若發票通知信箱未設定
		if(empty($parms['email']))
		{
			$parms['email'] = 'altob.rd@gmail.com'; // 預設信箱
		}
		
		// 若發票通知簡訊未設定
		if(empty($parms['mobile']))
		{
			$parms['mobile'] = '';	// 預設手機
		}
        
		// 確認是否存在退租記錄
		if ($this->admins_station_model->check_refund_member_no_exist($parms['member_no']) > 0)
		{
			echo '此會員已退租'; 
			exit;
		}
		
		echo $this->admins_station_model->first_rents_payment($parms);
	} 
    
	// 說明: 繳租
	public function rents_payment()
	{         
		$parms = array();
    	$parms['station_no'] = $this->input->post('station_no', true);					// 場站編號
    	$parms['member_no'] = $this->input->post('member_no', true);					// 會員編號
		$parms['member_attr'] = $this->input->post('member_attr', true);				// 會員身份
		$parms['lpr'] = $this->input->post('lpr', true);								// 車牌號碼
    	$parms['member_company_no'] = $this->input->post('member_company_no', true);	// 買方統編
    	$parms['company_no'] = $this->input->post('company_no', true);					// 賣方統編
    	$parms['fee_period'] = $this->input->post('fee_period', true);					// 本期繳期
    	$parms['fee_period_last'] = $this->input->post('fee_period_last', true);		// 上期繳期
    	$parms['amt'] = $this->input->post('amt', true);				// 本期租金
    	$parms['amt_last'] = $this->input->post('amt_last', true);		// 上期租金
    	$parms['end_date'] = $this->input->post('end_date', true);						// 本期截止日
    	$parms['start_date_last'] = $this->input->post('start_date_last', true);		// 上期截止日   
    	$parms['end_date_last'] = $this->input->post('end_date_last', true);			// 上期截止日
		
		if(empty($parms['member_no']) || empty($parms['station_no']))
		{
			echo '資料異常'; 
			exit;  
		}
		
		if(empty($parms['end_date']))
		{
			echo '截止日異常'; 
			exit;  
		}
		
		// 若賣方統編未設定, 預設拿場站統編
		if(empty($parms['company_no']))
		{
			$st_info = $this->vars['mcache']->get('st_info');
			$parms['company_no'] = $st_info['company_no'];
		}
		
		// 確認是否存在退租記錄
		if ($this->admins_station_model->check_refund_member_no_exist($parms['member_no']) > 0)
		{
			echo '此會員已退租'; 
			exit;
		}
        
		$rents_arr = $this->get_rents_arr();					// 費率設定
		echo $this->admins_station_model->rents_payment($parms, $rents_arr);
	}    
	
	// 退租
	public function stop_rents_payment()
	{         
		$parms = array();
    	$parms['station_no'] = $this->input->post('station_no', true);					// 場站編號
    	$parms['member_no'] = $this->input->post('member_no', true);					// 會員編號
    	$parms['stop_date'] = $this->input->post('stop_date', true);					// 結束日
		$parms['tot_amt'] = $this->input->post('tot_amt', true);						// 總金額
		
		if(empty($parms['member_no']) || empty($parms['station_no']) || empty($parms['stop_date']))
		{
			echo '資料異常'; 
			exit;  
		}
		
		// 確認是否存在退租記錄
		if ($this->admins_station_model->check_refund_member_no_exist($parms['member_no']) > 0)
		{
			echo '此會員已退租'; 
			exit;
		}
        
		$rents_arr = $this->get_rents_arr();	// 費率設定
		echo $this->admins_station_model->stop_rents_payment($parms, $rents_arr);
	}    
	
	// 接續開立發票
	public function next_tx_bill()
	{         
		$parms = array();
    	$parms['station_no'] = $this->input->post('station_no', true);					// 場站編號
    	$parms['member_no'] = $this->input->post('member_no', true);					// 會員編號
		$parms['tx_bill_no'] = $this->input->post('tx_bill_no', true);					// 帳單編號
		$parms['remain_amt'] = $this->input->post('remain_amt', true);					// 剩餘金額
		$parms['tx_no'] = $this->input->post('tx_no', true);							// 交易編號
		$parms['company_no'] = $this->input->post('company_no', true);					// 賣方統編
		
		if(empty($parms['member_no']) || empty($parms['station_no']) || empty($parms['tx_bill_no']) || empty($parms['tx_no']))
		{
			echo '資料異常'; 
			exit;  
		}
		
		// 若賣方統編未設定, 預設拿場站統編
		if(empty($parms['company_no']))
		{
			$st_info = $this->vars['mcache']->get('st_info');
			$parms['company_no'] = $st_info['company_no'];
		}
		
		// 確認是否存在退租記錄
		if ($this->admins_station_model->check_refund_member_no_exist($parms['member_no']) > 0)
		{
			echo '此會員已退租'; 
			exit;
		}
        
		$rents_arr = $this->get_rents_arr();					// 費率設定
		echo $this->admins_station_model->next_tx_bill($parms, $rents_arr);
	}  
	
	// 停權或啟動
    public function suspended()
    {
		$parms = array();
    	$parms['station_no'] = $this->input->post('station_no', true);	// 會員編號
    	$parms['member_no'] = $this->input->post('member_no', true);	// 會員編號
    	$parms['suspended'] = $this->input->post('suspended', true);	// 0:啟用, 1:停權
        echo $this->admins_station_model->suspended($parms);
    }
	
	// 取得費率設定
	public function get_rents_json()
    {
		echo json_encode($this->get_rents_arr(), JSON_UNESCAPED_UNICODE);     
	}
	
	// 取得費率設定
	function get_rents_arr()
	{
		$rents_arr = array();		
		$txdata_result = $this->txdata_model->get_price_plan(STATION_NO, 1);
		//trigger_error('tx: '. print_r(json_decode($txdata_result[0]['price_plan'], true), true));
		
		if(!empty($txdata_result[0]['price_plan']))
		{
			foreach (json_decode($txdata_result[0]['price_plan'], true) as $key => $val)
			{
				$p_keys = explode('_', $key);
				if(!array_key_exists($p_keys[0], $rents_arr))
				{
					$rents_arr[$p_keys[0]] = array();
				}
				$rents_arr[$p_keys[0]][$p_keys[1]] = $val;
			}	
		}
		
		return $rents_arr;
	}
	
	// 同步場站費率
	public function sync_price_plan()
	{
		echo $this->txdata_model->sync_price_plan();
	}
	
	// 計算退租金額
	public function calculate_stop_rents_amt()
	{   
		$parms = array();
    	$parms['station_no'] = $this->input->post('station_no', true);	// 場站編號
    	$parms['member_no'] = $this->input->post('member_no', true);	// 會員編號
    	$parms['stop_date'] = $this->input->post('stop_date', true);	// 結束日
		$rents_arr = $this->get_rents_arr();							// 費率設定
		
		// 根據會員現況算出所有可退金額
		$data = $this->admins_station_model->calculate_stop_rents_amt($parms, $rents_arr);
		echo json_encode($data, JSON_UNESCAPED_UNICODE);
	}
    
	// 計算月租金額
	public function calculate_rents_amt()
	{    
		$rents_arr = $this->get_rents_arr();					// 費率設定
    	$station_no = $this->input->post('station_no', true);	// 場站編號  
    	$demonth_start_date = $this->input->post('demonth_start_date', true);	// 不足月起租日
    	$member_attr = $this->input->post('member_attr', true);	// 會員身份類別
    	$period_1 = $this->input->post('period_1', true);		// 首期繳期
    	$period_2 = $this->input->post('period_2', true);		// 例行繳期 
		
		// 當傳入不足月開始日為當月第一天時，視為一個足月 2017-02-15 updated
		if(date_parse_from_format("Y-m-d", $demonth_start_date)['day'] == 1)
		{
			$demonth_end_date = $demonth_start_date;											// 不足月結束日 （跳過不足月）
			$start_date = $demonth_start_date;													// 足月起租日 	（跳過不足月）
		}
		else
		{
			$demonth_end_date = (new DateTime($demonth_start_date))->format('Y-m-t');			// 不足月結束日	
			$start_date = date('Y-m-d', strtotime("+1 days", strtotime($demonth_end_date)));	// 足月起租日
		}
		
		// 第二版: 繳期直接算 2017-04-13 updated
		$period_month_bias = ($period_2 < 1) ? 0 : $period_2 - 1;
		$end_date = date('Y-m-t', strtotime("+{$period_month_bias} months", strtotime($start_date)));
		
		/*
		// 第一版: 根據繳期算出所有參數
		$start_date_year = date_parse_from_format("Y-m-d", $start_date)['year'];
		$start_date_month = date_parse_from_format("Y-m-d", $start_date)['month'];
		for($month = 0 ; $month <= 12 ; $month += $period_2)
		{
			$end_date = null;
			if($month >= $start_date_month)
			{
				$end_date = (new DateTime($start_date_year.'-'.$month))->format('Y-m-t');		// 足月結束日
				break;
			}
		}
		
		if(empty($end_date))
		{
			// 若有異常繳期, 拿當月最後一天為終止日
			$end_date = (new DateTime($start_date_year.'-' . $start_date_month))->format('Y-m-t');
		}
		*/
        
        if ($member_attr == 250)
        {
          	$amt1 = 0;	// 不足月 	(VIP)
          	$amt2 = 0;	// 足月		(VIP)
        } 
        else
        {            
        	// 不足月計算
			$date1 = new DateTime($demonth_start_date);
			$date2 = new DateTime($demonth_end_date);
			
			// 當傳入不足月開始日為當月第一天時，視為一個足月 2017-02-15 updated
			if(date_parse_from_format("Y-m-d", $demonth_start_date)['day'] == 1)
			{
				$demonth_days = 0;
			}
            else
			{
				$demonth_days = $date2->diff($date1)->format("%a") + 1;		// 不足月天數
			}
			
          	$demonth_amt = $rents_arr[$period_1][$member_attr];
			$amt1 = round($demonth_amt * $demonth_days / $rents_arr[$period_1][0]); 
			$amt1 = ($amt1 > $demonth_amt) ? $demonth_amt : $amt1;
			trigger_error("days:{$demonth_days}|de_amt:{$demonth_amt}|amt1:{$amt1}");
			
			// 起始日若為空, 就自動回填
			//$demonth_start_date = $date1->format('Y-m-d');
			
			// 足月計算
			$date1a = new DateTime($start_date);
			$date2a = new DateTime($end_date);
			$amonth_days = $date2a->diff($date1a)->format("%a") + 1;	// 足月天數
			
			$amonth_amt = $rents_arr[$period_2][$member_attr];
			
			// 第一版: 依天數拆分
			//$amt2 = round($amonth_amt * $amonth_days / $rents_arr[$period_2][0]); 
			//$amt2 = ($amt2 > $amonth_amt) ? $amonth_amt : $amt2;
			
			//$amonth_months = $date2a->diff($date1a)->format("%m");		// 足月月數 (gg: 測試後有問題)
			
			// 第二版: 依月數拆分 2017-02-13 updated
			//$date1a_month = $date1a->format("m");
			//$date2a_month = $date2a->format("m");
			//$amonth_months = $date2a_month - $date1a_month + 1;
			
			// 第三版: 繳期直接算 2017-04-13 updated
			$amonth_months = $period_2;
			$amt2 = round($amonth_amt * $amonth_months / $period_2); 
			$amt2 = ($amt2 > $amonth_amt) ? $amonth_amt : $amt2;
			
			trigger_error("days:{$amonth_days}|a_amt:{$amonth_amt}|amt2:{$amt2}");
        }
		
        // 回傳參數
		$amt_arr['rents_deposit'] = $rents_arr[0][0];				// 押金
		$amt_arr['demonth_start_date'] = $demonth_start_date;		// 不足月起租日
		$amt_arr['demonth_end_date'] = $demonth_end_date;			// 不足月結束日
		$amt_arr['start_date'] = $start_date;						// 足月起租日
		$amt_arr['end_date'] = $end_date;							// 足月結束日
        $amt_arr['rents_amt1'] = $amt1;								// 不足月：租金
		$amt_arr['demonth_amt'] = $demonth_amt;						// 不足月：繳期總額
		$amt_arr['demonth_days'] = $demonth_days;					// 不足月：天數
		$amt_arr['demonth_days_total'] = $rents_arr[$period_1][0];	// 不足月：總天數
        $amt_arr['rents_amt2'] = $amt2;								// 足月：租金
		$amt_arr['amonth_amt'] = $amonth_amt;						// 足月：繳期總額
		$amt_arr['amonth_days'] = $amonth_days;						// 足月：天數
		$amt_arr['amonth_days_total'] = $rents_arr[$period_2][0];	// 足月：總天數
		$amt_arr['amonth_months'] = $amonth_months;					// 足月：月數		2017-02-13 updated
		$amt_arr['amonth_months_total'] = $period_2;				// 足月：總月數		2017-02-13 updated
        echo json_encode($amt_arr, JSON_UNESCAPED_UNICODE);
	}
	
	// 設定關帳時間點
	public function set_check_point()
	{
		$parms = array();
    	$parms['station_no'] = $this->input->post('station_no', true);			// 場站編號
    	$parms['check_time'] = $this->input->post('check_point_time', true);	// 時間
		$parms['remarks'] = $this->input->post('remarks', true);				// 備註
        echo $this->admins_station_model->set_check_point($parms);
	}
	
	// 關帳查詢    	
	public function check_point_query()
	{                           
    	$parms = array();
    	$parms['station_no'] = $this->input->post('station_no', true);							// 場站編號
    	$parms['check_point_time_from'] = $this->input->post('check_point_time_from', true);	// 開始時間
		$parms['check_point_time_to'] = $this->input->post('check_point_time_to', true);		// 結束時間
		
        $data = $this->admins_station_model->check_point_query($parms);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
	}
	
	// 關帳查詢（明細）
	public function check_point_detail_query()
	{                           
    	$parms = array();
    	$parms['station_no'] = $this->input->post('station_no', true);						// 場站編號
    	$parms['check_time_no'] = $this->input->post('check_time_no', true);				// 
		$parms['check_time_last_no'] = $this->input->post('check_time_last_no', true);		// 
		
        $data = $this->admins_station_model->check_point_detail_query($parms);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
	}
	
	// 電子發票查詢    	
	public function member_invoice_query()
	{                           
    	$parms = array();
    	$parms['station_no'] = $this->input->post('station_no', true);								// 場站編號
    	$parms['member_invoice_time_from'] = $this->input->post('member_invoice_time_from', true);	// 開始時間
		$parms['member_invoice_time_to'] = $this->input->post('member_invoice_time_to', true);		// 結束時間
		
        $data = $this->admins_station_model->member_invoice_query($parms);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
	}
	
	// 電子發票作廢
	public function member_invoice_void()
	{
		$parms = array();
    	$parms['station_no'] = $this->input->post('station_no', true);								// 場站編號
		$parms['invoice_no'] = $this->input->post('invoice_no', true);								// 發票號碼
		$parms['order_no'] = $this->input->post('order_no', true);									// 訂單編號
		
		echo $this->admins_station_model->member_invoice_void($parms);
	}
	
	// 批次延時
	public function member_tx_check_list_confirm_batch()
	{
		$parms = array();
    	$parms['station_no'] = $this->input->post('station_no', true);						// 場站編號
		$parms['tx_no_str'] = $this->input->post('tx_no_str', true);						// 交易代號字串
		$parms['member_no_str'] = $this->input->post('member_no_str', true);				// 會員代號字串
		$parms['day'] = $this->input->post('day', true);									// 延期天數

		echo $this->admins_station_model->member_tx_check_list_confirm_batch($parms);
	}
	
	// 切換賣方統編
	public function switch_company_no()
	{
		$parms = array();
		$parms['station_no'] = $this->input->post('station_no', true);					// 場站編號
		$parms['tx_bill_no'] = $this->input->post('tx_bill_no', true);					// 帳單編號
		$parms['company_no'] = $this->input->post('company_no', true);					// 賣方統編
		
		echo $this->admins_station_model->switch_company_no($parms);
	}
	
	// 取得停車時段字串
	public function get_parktime_str()
	{
		$data = $this->admins_station_model->get_parktime_str();
		echo json_encode($data, JSON_UNESCAPED_UNICODE);
	}
	
	// 取得未同步資料筆數
	public function get_un_synced_count()
	{
		$data = $this->admins_station_model->get_un_synced_count(STATION_NO);
		echo json_encode($data, JSON_UNESCAPED_UNICODE);
	}
	
	public function do_sync_batch_100()
	{
		$this->admins_station_model->try_sync_batch(STATION_NO, 100);
	}
	
	// 手動 sync
	public function do_sync_batch()
	{
		$this->admins_station_model->try_sync_batch(STATION_NO, 1);
		echo STATION_NO .'..ok';
	}
	
	// 報表：匯出會員資料
	public function export_members()
	{
		$this->excel_model->export_members();
	}
	
	public function test()
	{   
		echo 'zzz';
	}
	
	public function test_check_refund_lpr()
	{                                                         
    	$lpr = $this->uri->segment(3);
		echo json_encode($this->admins_station_model->check_refund_lpr($lpr), JSON_UNESCAPED_UNICODE);  
	}
}
