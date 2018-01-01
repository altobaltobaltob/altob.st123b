<?php
/*
file: Allpa_service.php	(歐Pa卡)
*/
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Allpa_service extends CI_Controller
{
    var $vars = array();	// 共用變數

	function __construct()
	{
		parent::__construct();

		$method_name = $this->router->fetch_method();
        if ($method_name == 'allpa_consume_handler')
        {
        	ob_end_clean();
			ignore_user_abort();
			ob_start();
			header('Connection: close');
			header('Content-Length: ' . ob_get_length());
			ob_end_flush();
			flush();
        }

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
        define('APP_NAME', 'allpa_service');		// 應用系統名稱

        define('PAGE_PATH', APP_BASE.'ci_application/views/'.APP_NAME.'/');						// path of views

        define('SERVER_URL', 'http://'.(isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost').'/');	// URL
        define('APP_URL', SERVER_URL.APP_NAME.'.html/');										// controller路徑
        define('WEB_URL', SERVER_URL.APP_NAME.'/');												// 網頁路徑
        define('WEB_LIB', SERVER_URL.'libs/');													// 網頁lib
        define('BOOTSTRAPS', WEB_LIB.'bootstrap_sb/');											// bootstrap lib
        define('LOG_PATH', FILE_BASE.APP_NAME.'/logs/');	// log path

		$this->load->model('allpa_service_model');	// 歐Pa卡
		$this->load->model('allpay_invoice_model');	// 歐付寶電子發票

		// ----- 中國信託金流 -----
		$this->load->model('ctbcbank_model');		// 中國信託金流
		define('CTBC_AuthResURL', APP_URL."return_ok/"); // 從收單行端取得授權碼後，要導回的網址，請勿填入特殊字元@、#、%、?、&等。
		// ----- 中國信託金流 (end) -----

		// ----- 頁面 -----
		define('MAIN_PAGE', "main_page");
		define('RESULT_PAGE', "result_page");
		define('ERROR_PAGE', "error_page");
		define('ADMIN_PAGE', "admin_page");
		define('ADMIN_LOGIN_PAGE', "admin_login_page");
		define('ADMIN_RESULT_PAGE', "admin_result_page");
		// ----- 頁面 -----
		
		
		
		// [START] 2016/06/08 登入
		$this->load->model('user_model'); 
		// load library
		$this->load->library(array('form_validation','session'));
		// load helpers
		$this->load->helper(array('form'));  
		// ajax code
		define('RESULT_SUCCESS', 'ok');
		define('RESULT_FORM_VALIDATION_FAIL', '-1');
		define('RESULE_FAIL', 'gg');
		// [END] 2016/06/08 登入
	}

    // 發生錯誤時集中在此處理
	public function error_handler($errno, $errstr, $errfile, $errline, $errcontext)
	{
    	$str = date('H:i:s')."|{$errstr}|{$errfile}|{$errline}|{$errno}\n";
    	//error_log($str, 3, $log_file . '.' . date('Ymd').'.log.txt');	// 3代表參考後面的檔名

		//echo "whoami: ".`whoami`;
		//echo "<br/>".$str;

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
	
	
	
	
	
	
	// [START] 2016/06/08
	
	// ADMIN.1 管理者頁面
	public function admin()
	{             
		if($this->session->userdata('logged_in'))
		{
			$session_data = $this->session->userdata('logged_in');
			$data['username'] = $session_data['username'];
			$data['type'] = $session_data['type'];
			
			if($data['type'] == 'admin')
			{
				$this->show_page('NOT_READY___', $data); // 進階管理者介面 (TODO)
			}
			else
			{
				$this->show_page(ADMIN_PAGE, $data); // 一般
			}
		}
		else
		{
			$this->show_page(ADMIN_LOGIN_PAGE);
		}
	}
	
	// ADMIN.2.a 管理者頁面登入
	public function user_login()
	{   		
		// form_validation
		$this->form_validation->set_rules('login_name', 'login_name', 'trim|required');
		$this->form_validation->set_rules('pswd', 'pswd', 'trim|required');

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
		
		$result = $this->user_model->user_login($data);
		
		if($result)
		{
			$sess_array = array();
			foreach($result as $row)
			{
				$sess_array = array
				(
					'username' => $row->login_name ,
					'type' => $row->user_type
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
	
	// ADMIN.2.b 管理者頁面登出
	public function user_logout()
	{   
		if(!$this->session->userdata('logged_in')){echo json_encode(null, JSON_UNESCAPED_UNICODE);return;} // 沒登入就回傳null
	
		$this->session->unset_userdata('logged_in');
		session_destroy();
		return RESULT_SUCCESS;
	}
	
	// ADMIN.3.a 管理者產品列表
	// http://203.75.167.89/allpa_service.html/get_allpa_admin_products
	public function get_allpa_admin_products()
	{
		if(!$this->session->userdata('logged_in')){echo json_encode(null, JSON_UNESCAPED_UNICODE);return;} // 沒登入就回傳null
		
        $data = $this->allpa_service_model->get_allpa_admin_products();
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
	}
	
	// ADMIN.3.b 產品列表 - 購買管理者產品
	public function purchase_admin_products()
	{
		if(!$this->session->userdata('logged_in')){echo json_encode(null, JSON_UNESCAPED_UNICODE);return;} // 沒登入就回傳null
		
		$product_id = $this->input->post('product_id', true);
        $data = $this->allpa_service_model->create_admin_bill($product_id);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
	}
	
	// ADMIN.3.c 管理者結帳
	public function transfer_money_admin()
	{
		if(!$this->session->userdata('logged_in')){echo json_encode(null, JSON_UNESCAPED_UNICODE);return;} // 沒登入就回傳null
		
		$lpr = strtoupper($this->uri->segment(3)); 				// 車牌號碼
        $order_no = strtoupper($this->uri->segment(4)); 		// 交易序號
        $invoice_receiver = urldecode($this->uri->segment(5));	// 載具編號 (可有可無)
		$company_no = urldecode($this->uri->segment(6));		// 載具編號 (可有可無)
		$email_base64 = $this->uri->segment(7);					// 電子信箱
		$mobile = $this->uri->segment(8);						// 手機號碼

		// decode email
		if(strlen($email_base64) > 0){
			$email = base64_decode($email_base64.'='); // base64字串尾端的'='還原
		}else{
			$email = email_base64;
		}

		$data = $this->allpa_service_model->pay_bill($lpr, $order_no, $invoice_receiver, $company_no, $email, $mobile); // 記錄訂單設定

		// 管理員結帳流程
		if (!empty($data)){
			$data = $this->allpa_service_model->get_product_bill($order_no);

			if (!empty($data)){
				$order_no = $data['order_no'];
				$lpr = $data['lpr'];
				$amt = $data['amt'];
				$status = $data['status'];
				
				switch($status){
					case 100:  // 狀態: 0:剛建立, 1:結帳完成, 2:錢沒對上, 3:發票沒建立, 4:手動調整, 99:訂單逾期作廢, 100:交易進行中, 101: 交易失敗, 111:產品已領取
						// 先記錄
						$this->allpa_service_model->transfer_money_done($order_no);

						// 開立歐付寶電子發票
						$this->allpay_invoice_model->invoice_issue_for_product_bill($order_no, $amt);
								
						// 直接開卡
						$this->allpa_service_model->activate_bill_for_new_register($order_no);

						// 交易成功
						$this->show_page(ADMIN_RESULT_PAGE);
						break;
					default:
						// 對方多傳一次時??
						trigger_error(__FUNCTION__.', order_no=>' . $order_no.'<br>'.'status != 100');
				}
			}
		}
	}
	
	// [END] 2016/06/08
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	

	// 首頁
	public function index()
	{
		$this->show_page(MAIN_PAGE);
	}
	
	// 管理
	/*
	public function admin()
	{
		$this->show_page(ADMIN_PAGE);
	}
	*/

	// A.1 查詢, 用戶歐Pa卡資訊
	// http://203.75.167.89/allpa_service.html/get_allpa_info
	public function get_allpa_info()
	{
		$user_lpr = strtoupper($this->input->post('user_lpr', true));
        $data = $this->allpa_service_model->get_allpa_info($user_lpr);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
	}

	// A.2 卡片查詢 (API)
	public function get_barcode_info()
	{
		$barcode = $this->input->post('barcode', true);
		$result = $this->allpa_service_model->get_barcode_info($barcode);
		echo json_encode($result, JSON_UNESCAPED_UNICODE);
	}

	// A.3 卡片記名 (API)
	public function card_register()
	{
		$lpr = strtoupper($this->input->post('lpr', true));
		$barcode = $this->input->post('barcode', true);
		$result = $this->allpa_service_model->card_register($lpr, $barcode);
		echo json_encode($result, JSON_UNESCAPED_UNICODE);
	}

	// B.1 啟用, 產品
	public function activate_bill()
	{
		$order_no = $this->input->post('order_no', true);
        $data = $this->allpa_service_model->activate_bill($order_no);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
	}

	// B.2 儲值
	public function allpa_reload()
	{
		$order_no = $this->input->post('order_no', true);
		$reload_pin = $this->input->post('reload_pin', true);
		$pin_check_id = $this->input->post('pin_check_id', true);
        $data = $this->allpa_service_model->allpa_reload($order_no, $reload_pin, $pin_check_id);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
	}

	// B.3 扣款
	public function allpa_pay_bill()
	{
		$order_no = $this->input->post('order_no', true);
		$data = $this->allpa_service_model->allpa_pay_bill($order_no);
		if(! $data["result_code"]){
			$data = $this->allpa_service_model->get_allpa_info($data["lpr"]);
		}
		echo json_encode($data, JSON_UNESCAPED_UNICODE);
	}

	// C.1 產品列表
	// http://203.75.167.89/allpa_service.html/get_allpa_products
	public function get_allpa_products()
	{
        $data = $this->allpa_service_model->get_allpa_products();
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
	}

	// C.2 產品列表 - 購買
	public function purchase()
	{
		$product_id = $this->input->post('product_id', true);
        $data = $this->allpa_service_model->create_bill($product_id);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
	}

	// C.3 付款
    public function transfer_money()
	{
		$lpr = strtoupper($this->uri->segment(3)); 				// 車牌號碼
        $order_no = strtoupper($this->uri->segment(4)); 		// 交易序號
        $invoice_receiver = urldecode($this->uri->segment(5));	// 載具編號 (可有可無)
		$company_no = urldecode($this->uri->segment(6));		// 載具編號 (可有可無)
		$email_base64 = $this->uri->segment(7);					// 電子信箱
		$mobile = $this->uri->segment(8);						// 手機號碼

		// decode email
		if(strlen($email_base64) > 0){
			$email = base64_decode($email_base64.'='); // base64字串尾端的'='還原
		}else{
			$email = email_base64;
		}

		$data = $this->allpa_service_model->pay_bill($lpr, $order_no, $invoice_receiver, $company_no, $email, $mobile); // 記錄訂單設定

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
				$this->allpa_service_model->transfer_money_done_with_tx_error($order_no);

			}else if(! empty($ctbc_lidm)) {
				$data = $this->allpa_service_model->get_product_bill($ctbc_lidm);

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
								$this->allpa_service_model->transfer_money_done($order_no);

								// 開立歐付寶電子發票
								$this->allpay_invoice_model->invoice_issue_for_product_bill($order_no, $amt);
								
								// 直接開卡
								$this->allpa_service_model->activate_bill_for_new_register($order_no);

								// 交易成功
								$this->show_page(RESULT_PAGE);
								return;

							}else{
								// 錢沒對上
								$this->allpa_service_model->transfer_money_done_with_amt_error($order_no);
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


	// L.1 歐Pa卡 - 開門 (限制存取)
	// http://203.75.167.89/allpa_service.html/allpa_go/1458699630/QQQ/12112/5dfe0856f3cdf67772710c3e7e805b80
	// http://203.75.167.89/allpa_service.html/allpa_go/1458714030/EEE/12112/19dd4f6692057ad897dc4e0290183a58
	// http://203.75.167.89/allpa_service.html/allpa_go/1458714030/YYY/12112/efb076ad1c1d615e6db8c718c116d2d2
	// http://203.75.167.89/allpa_service.html/allpa_go/1458714030/MMM/12112/4da21617852b4b43b86f8ac36f9db3e5
	// http://203.75.167.89/allpa_service.html/allpa_go/1458714030/BBB/12112/335f61a2a90ba3277cfe0f4cd1a07e26 // KO
	// http://203.75.167.89/allpa_service.html/allpa_go/1458897030/KKK/12112/b72332e2939a1a3152aa9d31ef945952
	// http://203.75.167.89/allpa_service.html/allpa_go/1459078230/SAYLXXX/12112/5dd8036423fa8eeb7115cd4249327e08
	public function allpa_go()
	{
		$in_time = $this->uri->segment(3); 		// 進場時間
        $lpr = $this->uri->segment(4); 			// 車牌號碼
        $station_no = $this->uri->segment(5);	// 場站編號
		$check_mac = $this->uri->segment(6);	// 驗証欄位
		
        ob_end_clean();
		ignore_user_abort();
		ob_start();
		
		$data = $this->allpa_service_model->allpa_go($in_time, $lpr, $station_no, $check_mac); // 開門
		echo json_encode($data, JSON_UNESCAPED_UNICODE);
		
		header('Connection: close');
		header('Content-Length: ' . ob_get_length());
		ob_end_flush();
		flush();

		// 呼叫: 非同步扣款流程
		if(!$data["result_code"]){
			file_get_contents(APP_URL."allpa_consume_handler/{$data["order_no"]}");
		}
	}
	
	// 遠端歐PA卡流程
	public function allpa_go_remote()
	{
		$in_time = $this->uri->segment(3); 		// 進場時間
        $lpr = $this->uri->segment(4); 			// 車牌號碼
        $station_no = $this->uri->segment(5);	// 場站編號
		$check_mac = $this->uri->segment(6);	// 驗証欄位
		
		// 驗証欄位
		if($check_mac != md5($in_time. $lpr . $station_no))
		{
			echo 'ck_error';
			exit;
		}
		
		// 先檢查本地端是否為歐PA會員
		$valid_user_ck = md5($lpr);
		$valid_user_result = $this->allpa_service_model->get_allpa_valid_user($lpr, $valid_user_ck); // check user
		if(!isset($valid_user_result['result_code']) || $valid_user_result['result_code'] != 0)
		{
			echo json_encode($valid_user_result, JSON_UNESCAPED_UNICODE);
		}
		else
		{
			$out_time = strtotime(date('Y-m-d H:i:s'));	// 結帳時間
			require_once(ALTOB_SYNC_FILE);
			$sync_agent = new AltobSyncAgent();
			$sync_agent->init($station_no, $out_time);
			$sync_agent->in_time = $in_time;			// 入場時間
			$allpa_go_result = $sync_agent->allpa_go($lpr);
			trigger_error(__FUNCTION__ . "..$lpr|$in_time|$out_time.." . print_r($allpa_go_result, true));
			echo $allpa_go_result;
		}
	}
	
	
	

	// L.2 歐Pa卡 - 非同步扣款 (限制存取)
	public function allpa_consume_handler()
	{
		$order_no = $this->uri->segment(3); // 訂單編號
		$this->allpa_service_model->allpa_pay_bill($order_no); // 扣款

		//sleep(5); // test delay
		exit();
	}

  // L.3  歐Pa卡 - 判斷有效用戶 (限制存取)
  public function get_allpa_valid_user()
  {
    $lpr = $this->uri->segment(3); 			// 車牌號碼
    $check_mac = $this->uri->segment(4);	// 驗証欄位
    
    ob_end_clean();
    ignore_user_abort();
    ob_start();

	$data = $this->allpa_service_model->get_allpa_valid_user($lpr, $check_mac); // check user
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
	
    header('Connection: close');
    header('Content-Length: ' . ob_get_length()); 
    ob_end_flush();
    flush();          
  }
  
  // only test
  public function gen_test_link()
  {
	  $in_time = strtotime("2017-11-14 16:50:00");
	  $lpr = "TEST123";
	  $station_no = "12302";
	  
	  echo "TEST: ".APP_URL."allpa_go/{$in_time}/{$lpr}/{$station_no}/".md5($in_time.$lpr.$station_no);
	  echo "\n";
	  echo "TEST: ".APP_URL."get_allpa_valid_user/{$lpr}/".md5($lpr);
	  echo "\n";
	  echo "TEST: ".APP_URL."allpa_go_remote/{$in_time}/{$lpr}/{$station_no}/".md5($in_time.$lpr.$station_no);
	  
		header('Connection: close');
		header('Content-Length: ' . ob_get_length());
		ob_end_flush();
		flush();
	
	
  }

}
