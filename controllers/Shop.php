<?php
/*
file: shop.php		購物
*/
class Shop extends CC_Controller
{                 
	function __construct() 
	{        
		parent::__construct('shop');
	}
	
	// 共用首頁
	function show_main_page($data=null)
	{
		if(empty($data))
			$data = array();
		
		$data['ALTOB_SHOP_UUID'] = md5(uniqid() . time());
		$this->show_page('main_page', $data);
	}
	
	// 首頁
	public function index()
	{                   
		$this->show_main_page();
	}
	
	// 付款流程頁面 (返回)
	public function client_back()
	{
		trigger_error(__FUNCTION__ . '..'. print_r($_POST, true));
		$this->show_main_page();
	}
	
	// 付款流程頁面 (完成, 返回)
	public function order_result()
	{
		trigger_error(__FUNCTION__ . '..'. print_r($_POST, true));
		$order_no = $this->input->post('order_no', true);
		$product_plan = $this->input->post('product_plan', true);
		$invoice_no = $this->input->post('invoice_no', true);
		$ck = $this->input->post('ck', true);
		
		// 建立頁面資料
		$data = array();
		$data['invoice_no'] = $invoice_no;
		
		// 更新產品訂單
		if($ck = md5($order_no.'alt'.$product_plan.'ob'.$invoice_no))
		{
			$this->app_model()->reload_product_bill($order_no, $invoice_no, $product_plan);
		}
		
		$this->show_main_page($data);
	}
	
	// 取得用戶兌換單
	public function query_uuid_bill()
	{
		$uuid = $this->input->post('uuid', true);
		$data = $this->app_model()->q_uuid_ready_bill($uuid);
        echo json_encode($data, JSON_UNESCAPED_UNICODE); 
	}
	
	// 領取
	public function redeem_order()
	{
		$order_no = $this->input->post('order_no', true);
		echo $this->app_model()->redeem_order($order_no);
	}
	
	// 咖啡包預覽頁
    public function coffee_shop()
	{
    	$product_id = $this->uri->segment(3);	// 商品代碼
        $data = $this->app_model()->q_product($product_id);
		unset($data['product_plan']);
		$this->show_main_page($data);
	}
	
	// 付款
	public function transfer_money()
	{       
		$product_id = $this->input->post('product_id', true);
		$product_code = $this->input->post('product_code', true);
		$invoice_receiver = $this->input->post('invoice_receiver', true);
		$company_no = $this->input->post('company_no', true);
		$email = $this->input->post('email', true);
		$mobile = $this->input->post('mobile', true);
		$uuid = $this->input->post('uuid', true);
		
		// 建立訂單
		$new_bill = $this->app_model()->create_product_bill($product_id, $product_code);
		
		if(!isset($new_bill['order_no']))
		{
			echo 'bill_create_fail';
			exit;
		}
		
		$parms = array(
				'order_no' => $new_bill['order_no'],
				'invoice_receiver' => $invoice_receiver,
				'company_no' => $company_no,
				'email' => $email,
				'mobile' => $mobile,
				'uuid' => $uuid
			);
			
		// 處理產品訂單
		$proceed_bill = $this->app_model()->proceed_product_bill($parms, 50);		// 50: 歐付寶刷卡
		
		// 開始進行繳交帳單
		if(!isset($proceed_bill['status']) || $proceed_bill['status'] != 100)
		{
			echo 'bill_proceed_fail';
			exit;
		}
		
		// 串接總公司購物流程
		$proceed_bill['station_no'] = $this->get_station_no();
		$proceed_bill['product_id'] = $product_id;
		$proceed_bill['product_code'] = $product_code;
		$proceed_bill['client_back_path'] = 'shop.html/client_back';
		$proceed_bill['order_result_path'] = 'shop.html/order_result';
		trigger_error(__FUNCTION__ . '..' . print_r($proceed_bill, true));
		
		try{
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://parks.altob.com.tw/bill_service.html/proceed_bill');	// 金流
            curl_setopt($ch, CURLOPT_HEADER, FALSE);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,30);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30); //timeout in seconds
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($proceed_bill));
            $result = curl_exec($ch);
			
			if(curl_errno($ch))
			{
				trigger_error(__FUNCTION__ . ', curl error: '. curl_error($ch));
			}
			
            curl_close($ch);
			
		}catch (Exception $e){
			trigger_error(__FUNCTION__ . 'error:'.$e->getMessage());
		}
		
		echo $result;
	}
	
	/*
	// 買
	public function i_do()
	{
		$product_id = $this->uri->segment(3);	// 商品代碼
		$data = $this->app_model()->q_coffee_shop($product_id);
		
		trigger_error(__FUNCTION__ . '..' . print_r($data, true));
		
		
		echo 'ok';
	}
	
	public function test_product_plan()
	{
		$order_no = '151246880713876';
		
		$this->app_model()->redeem_product_bill($order_no);
		echo 'ok';
	}
	*/
	
}
