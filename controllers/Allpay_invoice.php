<?php
/*
file: Allpay_invoice.php		歐付寶電子發票
*/
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// ----- 定義常數(路徑, cache秒數) -----       
define('APP_VERSION', '100');											// 版本號
define('MAX_AGE', 604800);												// cache秒數, 此定義1個月     
define('APP_NAME', 'allpay_invoice');									// 應用系統名稱   
define('PAGE_PATH', APP_BASE.'ci_application/views/'.APP_NAME.'/');		// path of views
define('SERVER_URL', 'http://'.$_SERVER['HTTP_HOST'].'/');				// URL   
define('WEB_SERVICE', 'http://'.$_SERVER['SERVER_NAME'].':60133/?');	// web service port:60133   
define('WEB_LIB', SERVER_URL.'libs/');									// 網頁lib
define('BOOTSTRAPS', WEB_LIB.'bootstrap_sb/');							// bootstrap lib  
define('APP_URL', SERVER_URL.APP_NAME.'.html/');						// controller路徑 
define('WEB_URL', SERVER_URL.APP_NAME.'/');								// 網頁路徑
define('LOG_PATH', FILE_BASE.APP_NAME.'/logs/');						// log path

class Allpay_invoice extends CI_Controller
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
		
		// 費率
		$this->load->model('allpay_invoice_model'); 
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
        
    	$str = date('H:i:s')."|{$errstr}|{$errfile}|{$errline}|{$errno}\n";               
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
		$this->show_page('main_page');
	}
	
	/*
	// step 1: 建立待開記錄
	public function test_step1()
	{
		$station_no = 54321;
		$tx_bill_no = 12;
		$amt = 1600;
		$data = $this->allpay_invoice_model->test_step1_init_bill($station_no, $tx_bill_no, $amt);
		echo json_encode($data, JSON_UNESCAPED_UNICODE);
	}
	
	// step 2: 開立發票參數
	public function test_step2()
	{
		$order_no = '14906858560000000012';
		$company_no = 0;
		$email = 'saylxxx@gmail.com';
		$mobile = '0953034986';
		$invoice_receiver = 0;
		$data = $this->allpay_invoice_model->test_step2_submit_purchase($order_no, $company_no, $email, $mobile, $invoice_receiver);
		echo json_encode($data, JSON_UNESCAPED_UNICODE);
	}
	
	// step 1: 建立待開記錄與參數
	public function test_step1_step2_submit()
	{
		$station_no = 54321;
		$tx_bill_no = 12;
		$amt = 1600;
		$company_no = 0;
		$email = 'saylxxx@gmail.com';
		$mobile = '0953034986';
		$invoice_receiver = 0;
		$data = $this->allpay_invoice_model->test_step1_step2_submit(
			$station_no, $tx_bill_no, $amt, 
			$company_no, $email, $mobile, $invoice_receiver);
		echo json_encode($data, JSON_UNESCAPED_UNICODE);
	}
	
	// step 3: 開立發票
	public function test_step3()
	{
		$order_no = '14906866680000000012';
		$amt = 1600;
		$data = $this->allpay_invoice_model->invoice_issue_for_tx_bill_ats($order_no, $amt);
		echo json_encode($data, JSON_UNESCAPED_UNICODE);
	}
	
	// 測試：M.1 建立月租系統發票待開記錄
	public function test_create_member_tx_bill_record()
	{
		$station_no = 54321;
		$tx_bill_no = 12;
		$amt = 1600;
		$company_no = 0;
		$email = 'saylxxx@gmail.com';
		$mobile = '0953034986';
		$data = $this->allpay_invoice_model->create_member_tx_bill_record($station_no, $tx_bill_no, $amt, $company_no, $email, $mobile);
		echo json_encode($data, JSON_UNESCAPED_UNICODE);
		
		// 回傳, 要記下 order_no
		// {"invoice_remark":"停車費用帳單","order_no":"14906884740000000012","station_no":54321,"amt":1600,"email":"saylxxx@gmail.com","mobile":"0953034986","status":1,"tx_time":"2017\/03\/28 16:07:54","tx_type":100}
	}
	
	// 測試：M.2 開立月租系統待開發票
	public function test_create_member_tx_bill_invoice()
	{
		$order_no = '14906884740000000012';
		$amt = 1600;
		$data = $this->allpay_invoice_model->create_member_tx_bill_invoice($order_no, $amt);
		echo json_encode($data, JSON_UNESCAPED_UNICODE);
		
		// 回傳
		// {"invoice_no":"TM00012802","result_code":"OK","result_msg":"成功"}
	}
	
	// 測試：作廢發票
	public function test_invoice_void()
	{
		$invoice_no = 'TM00012665';
		$reason_str = 'test';
		$data = $this->allpay_invoice_model->invoice_void($invoice_no, $reason_str);
		echo json_encode($data, JSON_UNESCAPED_UNICODE);
	}
	
	// 測試：作廢月租系統發票
	public function test_void_member_tx_bill_invoice()
	{
		$station_no = "54321";
		$order_no = "14906958880000000025";
		$invoice_no = "TM00012805";
		$data = $this->allpay_invoice_model->void_member_tx_bill_invoice($station_no, $order_no, $invoice_no);
		echo json_encode($data, JSON_UNESCAPED_UNICODE);
	}
	
	// 測試：開立折讓
	public function test_invoice_allowance()
	{
		$invoice_no = 'TM00012665';
		$allowance_amt = 1600;
		$data = $this->allpay_invoice_model->invoice_allowance($invoice_no, $allowance_amt);
		echo json_encode($data, JSON_UNESCAPED_UNICODE);
	}
	
	*/
	
	public function test_curl()
	{
		$station_no = 54321;
		$tx_bill_no = 25;
		$amt = 2640;
		$company_no = 0;
		$email = 'saylxxx@gmail.com';
		$mobile = '0953034986';
		
		$data = $this->allpay_invoice_model->test_curl($station_no, $tx_bill_no, $amt, $company_no, $email, $mobile);
		echo json_encode($data, JSON_UNESCAPED_UNICODE);
	}
	
	// 開立月租系統發票
	public function create_member_tx_bill_invoice()
	{
		$station_no = $this->input->post('station_no', true);	// 場站編號
		$tx_bill_no = $this->input->post('tx_bill_no', true);	// 會員交易帳單代號
		$amt = $this->input->post('amt', true);					// 金額
		$member_company_no = $this->input->post('member_company_no', true);	// 買方統編
		$company_no = $this->input->post('company_no', true);				// 賣方統編
		$email = $this->input->post('email', true);				// 通知信箱
		$mobile = $this->input->post('mobile', true);			// 通知簡訊
		$lpr = $this->input->post('lpr', true);			// 車牌號碼
		$data = $this->allpay_invoice_model->create_member_tx_bill_invoice($station_no, $tx_bill_no, $amt, $member_company_no, $company_no, $email, $mobile, $lpr);
		echo json_encode($data, JSON_UNESCAPED_UNICODE);
	}
	
	// 作廢月租系統發票
	public function void_member_tx_bill_invoice()
	{
		$station_no = $this->input->post('station_no', true);	// 場站編號
		$order_no = $this->input->post('order_no', true);		// 訂單編號
		$invoice_no = $this->input->post('invoice_no', true);	// 發票號碼
		$data = $this->allpay_invoice_model->void_member_tx_bill_invoice($station_no, $order_no, $invoice_no);
		echo json_encode($data, JSON_UNESCAPED_UNICODE);
	}
	
	// 折讓月租系統發票
	public function allowance_member_tx_bill_invoice()
	{
		$station_no = $this->input->post('station_no', true);		// 場站編號
		$invoice_no = $this->input->post('invoice_no', true);		// 發票號碼
		$allowance_amt = $this->input->post('allowance_amt', true);	// 折讓金額
		$data = $this->allpay_invoice_model->allowance_member_tx_bill_invoice($station_no, $invoice_no, $allowance_amt);
		echo json_encode($data, JSON_UNESCAPED_UNICODE);
	}
	
	
	// 測試：作廢折讓
	public function test_allowance_void()
	{
		$invoice_no = 'TM00012665';
		$allowance_no = '2017032814257398';
		$reason_str = 'test';
		$data = $this->allpay_invoice_model->allowance_void($invoice_no, $allowance_no, $reason_str);
		echo json_encode($data, JSON_UNESCAPED_UNICODE);
	}
}
