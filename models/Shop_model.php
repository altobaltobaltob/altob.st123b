<?php             
/*
file: Shop_model.php 購物
*/                   

class Shop_model extends CI_Model 
{             
	var $vars = array();

	function __construct()
	{
		parent::__construct(); 
		$this->load->database(); 
		
		// product code
		define('PRODUCT_CODE_COFFEE_SHOP', 	'coffee_shop');	// 咖啡產品包
		define('PRODUCT_CODE_COFFEE', 		'coffee');		// 咖啡
    }   
     
	public function init($vars)
	{
		$this->vars = $vars;
    } 
	
	// 取得待兌換訂單 (用戶代號)
	public function q_uuid_ready_bill($uuid)
	{
		$where_arr['uuid'] = $uuid;
		$where_arr['status'] = 1;
		
    	$result = $this->db->select('order_no, invoice_remark, product_plan, tx_time')
        		->from('product_bill')
                ->where($where_arr)->order_by("tx_time", "desc")
                ->get()
                ->result_array();
        return $result;
    }
	
	// 取得待兌換訂單 (發票)
	public function q_invoice_ready_bill($invoice_no)
	{
		$where_arr['invoice_no'] = $invoice_no;
		$where_arr['status'] = 1;
		
    	$result = $this->db->select('order_no, invoice_remark, product_plan, tx_time')
        		->from('product_bill')
                ->where($where_arr)->order_by("tx_time", "desc")
                ->get()
                ->row_array();
        return $result;
    }
	
	// 取得產品資訊
	public function q_product($product_id=0, $product_code=PRODUCT_CODE_COFFEE_SHOP)
	{
		$now = date('Y/m/d H:i:s');
		$where_arr = array('start_time <= ' => $now, 'valid_time > ' => $now);
		
		// 指定產品流水號
		if($product_id != 0)
			$where_arr['product_id'] = $product_id;
		
		// 指定產品包
		$where_arr['product_code'] = $product_code;
		
    	$data = array();
    	$result = $this->db->select('product_id, product_code, product_name, product_desc, amt, remarks, product_plan')
        		->from('products')
                ->where($where_arr)->order_by("create_time", "desc")
				->limit(1)
                ->get()
                ->row_array();
        return $result;
    }
	
	// 產生交易序號
	private function gen_trx_no()
	{
		return time().rand(10000,99999);
	}
	
	// S.1. 建立產品訂單
	public function create_product_bill($product_id, $product_code)
	{
		// 取得商品資訊
		$product_info = $this->q_product($product_id, $product_code);
		
		if(!isset($product_info['product_plan']))
		{
			return 'unknown_product';	// 中斷
		}
		
		$data = array();
		$data['order_no'] = $this->gen_trx_no();
		$data['product_id'] = $product_info["product_id"];
		$data['product_code'] = $product_info["product_code"];
		$data['product_plan'] = $product_info["product_plan"];
		$data['invoice_remark'] = $product_info["product_name"];
		$data['amt'] = $product_info["amt"];
		$data['valid_time'] = date('Y-m-d H:i:s', strtotime(" + 15 minutes")); // 15 min 內有效
		$this->db->insert('product_bill', $data);
		
		$affect_rows = $this->db->affected_rows();

		if ($affect_rows <= 0)
			return 'fail';
		
		trigger_error(__FUNCTION__ . '..' . print_r($data, true));
		return $data;
	}
	
	// S.2. 處理產品訂單
	public function proceed_product_bill($parms, $tx_type=0)
	{
		$order_no = $parms['order_no'];
		$invoice_receiver = $parms['invoice_receiver'];
		$company_no = $parms['company_no'];
		$email = $parms['email'];
		$mobile = $parms['mobile'];
		$uuid = $parms['uuid'];
		
		$product_info = $this->db->select('valid_time, product_plan')
				->from('product_bill')
				->where(array('order_no' => $order_no))
				->limit(1)
				->get()
				->row_array();
				
		if(!isset($product_info['product_plan']))
		{
			trigger_error(__FUNCTION__ . "|{$order_no}|unknown_order");
			return 'unknown_order';			// 中斷
		}
		
		if(!isset($product_info['valid_time']))
		{
			trigger_error(__FUNCTION__ . "|{$order_no}|valid_time_not_found");
			return 'valid_time_not_found';	// 中斷
		}
		
		$data = array();
		$data['tx_type'] = $tx_type;		// 交易種類: 0:未定義, 1:現金, 40:博辰人工模組, 41:博辰自動繳費機, 50:歐付寶轉址刷卡, 51:歐付寶APP, 52:歐付寶轉址WebATM, 60:中國信託刷卡轉址
		$data['uuid'] = $uuid;				// 客戶代號
		
		if(strlen($company_no) >= 8)
		{
			$data['company_no'] = $company_no;														// 電子發票：公司統編
			$data['company_receiver'] = "公司名稱";													// 電子發票：公司名稱
			$data['company_address'] = "公司地址";													// 電子發票：公司地址
		}
		
		$data['invoice_receiver'] = (strlen($invoice_receiver) >= 7) ? $invoice_receiver : '';		// 電子發票：載具編號
		$data['email'] = (strlen($email) >= 5) ? $email : '';										// 電子發票：email
		$data['mobile'] = (strlen($mobile) >= 10) ? $mobile : '';									// 電子發票：手機
		
		// 交易時間
		$tx_time = time();
		$data['tx_time'] = date('Y/m/d H:i:s', $tx_time);
		
		if(strtotime($product_info['valid_time']) < $tx_time)
		{
			$data['status'] = 99; 								//狀態: 99:訂單逾期作廢
			$this->db->update('product_bill', $data, array('order_no' => $order_no));
			trigger_error(__FUNCTION__ . "|{$order_no}| 99 gg");
			return 'gg';
		}
		
		// 完成
		$data['status'] = 100; 									// 狀態: 100:交易進行中
		$this->db->update('product_bill', $data, array('order_no' => $order_no));
		
		$affect_rows = $this->db->affected_rows();

		if ($affect_rows <= 0)
			return 'fail';

		$data['order_no'] = $order_no;
		trigger_error(__FUNCTION__ . ".." . print_r($data, true));
		return $data;
    }
	
	// S.3. 更新產品訂單
	public function reload_product_bill($order_no, $invoice_no, $product_plan)
	{
		// 更新為已結帳
		$this->db->update('product_bill', 
			array('status' => 1, 'invoice_no' => $invoice_no, 'product_plan' => $product_plan), 
			array('status' => 100,	'order_no' => $order_no)
		);
		
		$affect_rows = $this->db->affected_rows();

		if ($affect_rows <= 0)
			return 'fail';
		
		// 兌換
		return $this->redeem_product_bill($order_no);
	}
	
	// S.4. 兌換訂單商品
	public function redeem_product_bill($order_no)
	{
		$product_info = $this->db->select('invoice_no, product_plan, uuid')
				->from('product_bill')
				->where(array('order_no' => $order_no, 'status' => 1))
				->limit(1)
				->get()
				->row_array();
		
		$invoice_no = $product_info['invoice_no'];
		$product_plan = $product_info['product_plan'];
		$uuid = $product_info['uuid'];
		
		// 取得訂單內容
		$data = json_decode($product_plan, true);
		
		if(!isset($data['product_code']))
		{
			trigger_error(__FUNCTION__ . "|$order_no|not_found");
			return 'not_found';
		}
		
		$product_code = $data['product_code'];
		
		// 咖啡
		if($product_code == PRODUCT_CODE_COFFEE)
		{
			// 取得產品包資訊
			$station_no = $data['station_no'];
			$amount = $data['amount'];
			$memo = $data['memo'];
			$product = $this->q_product(0, $product_code);
			
			if(!isset($product['product_id']))
			{
				trigger_error(__FUNCTION__ . "|$order_no|$station_no, $product_code, $amount, $memo|product_not_found");
				return 'product_contain_not_found';
			}
			
			trigger_error(__FUNCTION__ . "|$order_no|$station_no, $product_code, $amount, $memo|兌換|" . print_r($product, true));
			
			// 兌換產品
			return $this->redeem_product($order_no, $invoice_no, $product, $uuid, $amount);
		}
		
		trigger_error(__FUNCTION__ . "|$order_no|undefined_product|" . print_r($data, true));
		return 'undefined_product';
	}
	
	// 兌換產品
	private function redeem_product($order_no, $invoice_no, $product, $uuid, $amount=1)
	{
		// [A.開始]
		$this->db->trans_start();
		
		// 兌換時間
		$tx_time = date('Y/m/d H:i:s');
		
		for($i = 0; $i < $amount; $i++)
		{
			$item = array();
			$item['uuid'] = $uuid;	// 用戶代號
			$item['order_no'] = $this->gen_trx_no();
			$item['tx_time'] = $tx_time;
			$item['invoice_no'] = $invoice_no;
			$item['product_id'] = $product["product_id"];
			$item['product_code'] = $product["product_code"];
			$item['product_plan'] = $product["product_plan"];
			$item['invoice_remark'] = $product["product_name"];
			$item['amt'] = $product["amt"];
			$item['status'] = 1;
			$this->db->insert('product_bill', $item);
		}
			
		// 更新為已領取
		$this->db->update('product_bill', array('status' => 111), array('status' => 1,	'order_no' => $order_no));
			
		// [C.完成]
		$this->db->trans_complete();
		if ($this->db->trans_status() === FALSE)
		{
			trigger_error(__FUNCTION__ . "..$order_no, $invoice_no..trans error..". '| last_query: ' . $this->db->last_query());
			return 'fail';	 		// 中斷
		}
		
		return 'ok';
	}
	
	// S.5. 兌換 (兌換 商品包 或 商品)
	public function redeem_order($order_no)
	{
		$product_info = $this->db->select('order_no, product_code, product_plan')
				->from('product_bill')
				->where(array('order_no' => $order_no, 'status' => 1))
				->limit(1)
				->get()
				->row_array();
		
		if(!isset($product_info['product_code']))
		{
			trigger_error(__FUNCTION__ . "|$order_no|not_found");
			return 'not_found';
		}
		
		$bill_product_code = $product_info['product_code'];
		
		// 兌換產品包
		if($bill_product_code == PRODUCT_CODE_COFFEE_SHOP)
		{
			return $this->redeem_product_bill($order_no);
		}
		// 兌換咖啡
		else if($bill_product_code == PRODUCT_CODE_COFFEE)
		{
			// [A.開始]
			$this->db->trans_start();
			
			// 兌換時間
			$tx_time = date('Y/m/d H:i:s');
			
			// 更新為已領取
			$this->db->update('product_bill', array('status' => 111, 'tx_time' => $tx_time), array('status' => 1, 'order_no' => $order_no));
				
			// [C.完成]
			$this->db->trans_complete();
			if ($this->db->trans_status() === FALSE)
			{
				trigger_error(__FUNCTION__ . "..$order_no..trans error..". '| last_query: ' . $this->db->last_query());
				return 'fail';	 		// 中斷
			}
			
			trigger_error(__FUNCTION__ . "|$order_no|已領取|" . print_r($product_info, true));
			return 'ok';
		}
		
		return 'gg';
	}
	
}
