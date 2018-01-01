<?php
/*
file: Ctbcbank.php	中國信託介接
*/
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Ctbcbank extends CI_Controller
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
        define('APP_NAME', 'ctbcbank');		// 應用系統名稱   
          
        define('PAGE_PATH', APP_BASE.'ci_application/views/'.APP_NAME.'/');						// path of views
        
        define('SERVER_URL', 'http://'.(isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost').'/');	// URL
        define('APP_URL', SERVER_URL.APP_NAME.'.html/');										// controller路徑 
        define('WEB_URL', SERVER_URL.APP_NAME.'/');												// 網頁路徑
        define('WEB_LIB', SERVER_URL.'libs/');													// 網頁lib
        define('BOOTSTRAPS', WEB_LIB.'bootstrap_sb/');											// bootstrap lib  
        define('LOG_PATH', FILE_BASE.APP_NAME.'/logs/');	// log path
        
		//$this->load->model('twgc_model'); 
        // $this->parkingquery_model->init($this->vars);
		
		$this->load->model('payment_model'); // 帳單
		$this->load->model('ctbcbank_model');// 中國信託金流
		$this->load->model('allpay_invoice_model');	// 歐付寶電子發票
		
		define('CTBC_AuthResURL', APP_URL."return_ok/"); // 從收單行端取得授權碼後，要導回的網址，請勿填入特殊字元@、#、%、?、&等。	
		
		define('MAIN_PAGE', "main_page");
		define('RESULT_PAGE', "result_page");
		define('ERROR_PAGE', "error_page");
		
		// ----- 回傳訊息 -----
		define('RESULT_CODE_OK', "OK");
		define('RESULT_CODE_UNKNOWN_ERROR', "-99");
		define('RESULT_MSG_UNKNOWN_ERROR', "發生未預期錯誤");
		// ----- 回傳訊息 (END) -----
	}
    
    // 發生錯誤時集中在此處理
	public function error_handler($errno, $errstr, $errfile, $errline, $errcontext)
	{                
    	$str = date('H:i:s')."|{$errstr}|{$errfile}|{$errline}|{$errno}\n";               
    	//error_log($str, 3, $log_file . '.' . date('Ymd').'.log.txt');	// 3代表參考後面的檔名
		
		//echo $str;
		
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
    
    // 首頁
	public function index()
	{                   
		$this->show_page(MAIN_PAGE); // http://203.75.167.89/ctbcbank.html
	}
	
	// 查車付款 - 1.查車拿帳單
	public function payment_lpr()
	{                       
    	$payment_lpr = $this->input->post('payment_lpr', true);
        $data = $this->payment_model->create_cario_bill($payment_lpr);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
	}
	
	
	// 查車付款 - 2.確定繳交帳單
    public function transfer_money()
	{       
        $order_no = strtoupper($this->uri->segment(3)); 		// 交易序號                         
        $invoice_receiver = urldecode($this->uri->segment(4));	// 載具編號 (可有可無)
		$company_no = urldecode($this->uri->segment(5));		// 載具編號 (可有可無)
		$email_base64 = $this->uri->segment(6);					// 電子信箱
		$mobile = $this->uri->segment(7);						// 手機號碼
		
		// decode email
		if(strlen($email_base64) > 0){
			$email = base64_decode($email_base64.'='); // base64字串尾端的'='還原
		}else{
			$email = email_base64;
		}
		
		$data = $this->payment_model->pay_bill($order_no, $invoice_receiver, $company_no, $email, $mobile); // 繳交帳單
		
		if (!empty($data)){
			$this->ctbcbank_model->transfer_money_ctbc($data, CTBC_AuthResURL); // 中國信託
		}
	}
	
	// C.4 收單行端取得授權碼後，要導回的網址 (call by CTBC)
	public function return_ok()
	{
		/**
		=======ALL_REQUEST======
		URLResEnc: AF2100C51A9E844A1E820B953EA512BE49286403253FF9373474F6AE4A5877B084DD4D9B20F03AF0F34DB3D42A9C583938FE5DC1B60C53384864D9581EE997F92552A2F516B04F2FB2FE3E5C10D61CE84C5B63B87379F1048E16AC430AE94989724D8B0087734F73BF40CE904A05D555F526E5A93462C42931A0A7EC1E6AFF8BC39E641A8F5EE8882BD5B02F838BC217A87533AB9FE98CE1DD558B3CC345FC77D06C1783067F75DF94C58B55A826CD33B32355439BF3C9F17A4596138942B4457B66EB8FA09749C246D5B91799FB3942EC90138033272D89861B8698398FF4F3010628C0D11C7D88FB7A5F85CC59717D9F8D5CADBA5A1231E0396AE344B1DA139BA84F9D477E53A73A376BFCC92B52619E3B396BB28ABE7C4CEE3D3ED2CA1BEEF466F2D645C5CBF1C678C5747BA2A048F2F0FC2B86CAE379DE7A7F144D07C31A
		merID: 10063
		----------------------------END
		*/
		// 取得回傳資訊
		foreach ($_REQUEST as $key => $value) {
			switch ($key){
				case "URLResEnc": $resenc = $value; break;
				case "merID": $merid = $value; break;
			}
		}

		if(! empty($resenc)){
			$return_data = $this->ctbcbank_model->ctbcbank_return_handler($resenc, $merid);
			$ctbc_lidm = $return_data['lidm'];
			$ctbc_authamt = $return_data['authamt'];
			$ctbc_status = $return_data['status'];

			if($ctbc_status != 0){
				// 金流處理失敗
				trigger_error(__FUNCTION__.', ctbc_status GG ..lidm=>' . $ctbc_lidm);

			}else if(! empty($ctbc_lidm)) {
				$data = $this->payment_model->get_tx_bill($ctbc_lidm);

				if (! empty($data)) {
					$order_no = $data['order_no'];
					$lpr = $data['lpr'];
					$amt = $data['amt'];
					$status = $data['status'];
					switch($status){
						case 100:  // 狀態: 0:剛建立, 1:結帳完成, 2:錢沒對上, 3:發票沒建立, 4:手動調整, 99:訂單逾期作廢, 100:交易進行中, 101: 交易失敗, 111:產品已領取
							// 印發票流程
							if($ctbc_authamt == $amt){
								// 先記錄
								$this->payment_model->transfer_money_done($order_no);

								// 開立歐付寶電子發票
								//$this->allpay_invoice_model->invoice_issue_for_product_bill($order_no, $amt);

								// 交易成功
								$this->show_page(RESULT_PAGE);
								return;

							}else{
								// 錢沒對上
								$this->payment_model->transfer_money_done_with_error_2($order_no);
							}
							break;
						default:
							// 對方多傳一次時??
							trigger_error(__FUNCTION__.', order_no=>' . $order_no.'<br>'.'status != 100');
					}
				}else{
					// 我們自己找不到記錄時??
					trigger_error(__FUNCTION__.', order_no=>' . $order_no.'<br>'.' NOT FOUND !!');
				}

			}else{
				// 回傳沒有資料 lidm
				trigger_error(__FUNCTION__.', ERROR ..lidm=>' . $ctbc_lidm);
			}

		}else{
			// 回傳沒有資料 resenc
			trigger_error(__FUNCTION__.', ERROR ..resenc=>' . $resenc);
		}

		// 交易失敗
		$this->show_page(ERROR_PAGE);
	}
    
}
