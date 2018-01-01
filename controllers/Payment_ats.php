<?php
/*
file: Payment_ats.php		繳費機
*/
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Payment_ats extends CI_Controller
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
        
        // ----- 定義常數(路徑, cache秒數) -----       
        define('APP_VERSION', '100');		// 版本號
                                        
        define('MAX_AGE', 604800);			// cache秒數, 此定義1個月     
        define('APP_NAME', 'payment_ats');		// 應用系統名稱   
          
        define('PAGE_PATH', APP_BASE.'ci_application/views/'.APP_NAME.'/');						// path of views
        
        define('SERVER_URL', 'http://'.(isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost').'/');	// URL
        define('APP_URL', SERVER_URL.APP_NAME.'.html/');										// controller路徑 
        define('WEB_URL', SERVER_URL.APP_NAME.'/');												// 網頁路徑
        define('WEB_LIB', SERVER_URL.'libs/');													// 網頁lib
        define('BOOTSTRAPS', WEB_LIB.'bootstrap_sb/');											// bootstrap lib  
        define('LOG_PATH', FILE_BASE.APP_NAME.'/logs/');	// log path
        
		$this->load->model('payment_ats_model'); 	// 臨停繳費
		$this->load->model('allpay_invoice_model');	// 歐付寶電子發票
		
		define('MAIN_PAGE', "main_page");
		define('RESULT_PAGE', "result_page");
		
		// ----- 行動支付, 繳費機告知已付款 -----
		define('ALTOB_ATS2PAYED', "http://localhost/carpayment.html/ats2payed");
		// ----- 行動支付, 繳費機告知已付款 (end) -----
		
		// ----- 歐付寶金流 -----
		define('ALLPAY_PAYMENT_TX_BILL_ATS', SERVER_URL."allpay_payment.html/transfer_money_tx_bill_ats"); // 歐付寶付款系統連結
		define('ALLPAY_ClientBackURL', APP_URL."client_back/"); // 您要歐付寶返回按鈕導向的瀏覽器端網址";
		define('ALLPAY_OrderResultURL', APP_URL."order_result/"); // 您要收到付款完成通知的瀏覽器端網址(browser) ps. WebATM大部份銀行都回不來;
		define('ALLPAY_AltobServiceURL', APP_URL."payment_completed_handler/"); // 付款完成後被通知的位址
		// ----- 歐付寶金流 (end) -----
	}
    
    
    // 發生錯誤時集中在此處理
	public function error_handler($errno, $errstr, $errfile, $errline, $errcontext)
	{                                      
    	$str = date('H:i:s')."|{$errstr}|{$errfile}|{$errline}|{$errno}\n";               
    	//error_log($str, 3, $log_file . '.' . date('Ymd').'.log.txt');	// 3代表參考後面的檔名
    	error_log($str, 3, LOG_PATH.APP_NAME . '.' . date('Ymd').'.log.txt');	// 3代表參考後面的檔名
    }
    
    
	// 顯示靜態網頁(html檔)
	protected function show_page($page_name, $data = null)
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
        
	// 首頁
	public function index()
	{                   
		$this->show_page(MAIN_PAGE);
	}
	
	// 查車付款 - 1.查車拿帳單
	public function payment_lpr()
	{                       
    	$payment_lpr = $this->input->post('payment_lpr', true);
        $data = $this->payment_ats_model->create_member_bill($payment_lpr);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
	}
    
    // 查車付款 - 2.確定繳交帳單
    public function transfer_money()
	{       
        $order_no = strtoupper($this->uri->segment(3)); 		// 交易序號                         
        $invoice_receiver = urldecode($this->uri->segment(4));	// 載具編號 (可有可無)
		$company_no = urldecode($this->uri->segment(5));		// 公司統編 (可有可無)
		$email_base64 = $this->uri->segment(6);					// 電子信箱
		$mobile = $this->uri->segment(7);						// 手機號碼
		
		// decode email
		if(strlen($email_base64) > 0){
			$email = base64_decode($email_base64.'='); // base64字串尾端的'='還原
		}else{
			$email = email_base64;
		}
		
		$this->payment_ats_model->  // 開始進行繳交帳單
			pay_bill($order_no, $invoice_receiver, $company_no, $email, $mobile, 
				ALLPAY_ClientBackURL, 
				ALLPAY_OrderResultURL, 
				ALLPAY_AltobServiceURL, 
				50); // 交易種類: 0:未定義, 1:現金, 40:博辰人工模組, 41:博辰自動繳費機, 50:歐付寶轉址刷卡, 51:歐付寶APP, 52:歐付寶轉址WebATM, 60:中國信託刷卡轉址
		
		// 轉址歐付寶付款系統
		echo file_get_contents(ALLPAY_PAYMENT_TX_BILL_ATS."/{$order_no}");
	}
    
  	// L.1 付款完成 (限制存取)
  	public function payment_completed_handler()
	{
		$order_no = $this->uri->segment(3); 		// 交易序號  
		
		$data = $this->payment_ats_model->get_tx_bill($order_no);	
		if (! empty($data))
		{
			$order_no = $data['order_no'];
			$station_no = $data['station_no'];
			$lpr = $data['lpr'];
			$amt = $data['amt'];
			$status = $data['status'];
			switch($status){
				case 1: // 狀態: 0:剛建立, 1:結帳完成, 2:錢沒對上, 3:發票沒建立, 4:手動調整, 99:訂單逾期作廢, 100:交易進行中, 111:產品已領取
				
					// 開立歐付寶電子發票
					$this->allpay_invoice_model->invoice_issue_for_tx_bill_ats($order_no, $amt);
				
					// 記錄為已領取
					$this->payment_ats_model->transfer_money_done_and_finished($order_no);
												
					// 繳費機告知已付款
					// http://localhost/carpayment.html/ats2payed/車牌/金額/場站編號/序號/MD5 
					// md5(車牌.金額.場站編號.序號)
					$md5 = md5($lpr.$amt.$station_no.$order_no);
					file_get_contents(ALTOB_ATS2PAYED."/{$lpr}/{$amt}/{$station_no}/{$order_no}/{$md5}");
					
				default:
					// 尚未結帳完成, 或是已領取
					trigger_error(APP_NAME.', '.__FUNCTION__.', order_no=>' . $order_no.'<br>'.' status != 1');
			}
		}
	}

	// 歐付寶返回按鈕導向的瀏覽器端網址
	public function client_back()
	{
		$this->show_page(MAIN_PAGE);
	}

	// 收到付款完成通知的瀏覽器端網址(browser) ps. WebATM大部份銀行都回不來
	public function order_result()
	{
		$this->show_page(RESULT_PAGE);
	}
	
	
	
	
	// 其它功能 1: 更改會員密碼
	public function change_pswd()
	{                       
    	$lpr = $this->input->post('lpr', true);
    	$new_pswd = $this->input->post('new_pswd', true);
        $data = $this->security_model->change_pswd($lpr, $new_pswd);
        echo json_encode($data, JSON_UNESCAPED_UNICODE); 
	}
	
	// 其它功能 2: 防盜鎖車
	public function security_action() 
	{                 
    	$lpr = $this->uri->segment(3);
    	$pswd = $this->uri->segment(4);
    	$action = $this->uri->segment(5);
        $data = $this->security_model->security_action($lpr, $pswd, $action);
        echo json_encode($data, JSON_UNESCAPED_UNICODE); 
    }
	
}
