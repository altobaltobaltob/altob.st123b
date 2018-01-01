<?php             
/*
file: Txdata_model.php 	交易資訊系統
*/                   

class Txdata_model extends CI_Model 
{             
     
	var $mcache;    
	var $mqtt;      
    
	function __construct()
	{
		parent::__construct(); 
		$this->load->database(); 
		
		$this->now_str = date('Y-m-d H:i:s'); 
    }   
     
	public function init($vars)
	{
		$this->mcache = $vars['mcache'];
        $this->mqtt = $vars['mqtt'];
    } 
	
    // 取得所有場站有效費率
	public function get_all_valid_price_plan($station_no)
	{           
    	$result = $this->db->select('tx_price_plan_id as txid, tx_type, station_no, remarks, price_plan, start_time, valid_time, create_time')
				->from('tx_price_plan')
                ->where(array(
					'start_time <= ' => $this->now_str, 
					'valid_time > ' => $this->now_str, 
					'station_no' => $station_no
					))	
                ->get()  
                ->result_array();
        return $result; 
    }
    
    // 取得場站費率設定
    // http://203.75.167.89/txdata.html/get_price_plan/12112/0
	public function get_price_plan($station_no, $tx_type)
	{                 
    	$result = $this->db->select('price_plan')
        		->from('tx_price_plan')
                ->where(array(
					'start_time <= ' => $this->now_str, 
					'valid_time > ' => $this->now_str, 
					'station_no' => $station_no, 
					'tx_type' => $tx_type
					))	
                ->get()  
                ->result_array();
        return $result; 
    }
	
	// 取得特殊日期設定
    // http://203.75.167.89/txdata.html/get_date_plan/12345678/23456789
	public function get_date_plan($inTime, $balanceTime) 
	{           
		$inDateTimestamp = strtotime(date("Y-m-d", $inTime));
		$balanceDateTimestamp = strtotime(date("Y-m-d", $balanceTime));
	
    	$result = $this->db->select('p_type, p_date')
        		->from('tx_date_plan')
                ->where("p_date BETWEEN FROM_UNIXTIME({$inDateTimestamp}) AND FROM_UNIXTIME({$balanceDateTimestamp})")
				//->where("p_date BETWEEN '{$inDate}' AND '{$outDate}'")
                ->get()  
                ->result_array();
        return $result; 
    }
	
	// 同步場站費率
	public function sync_price_plan()
	{
		try{
			$param = array('station_no' => STATION_NO);
			// 查另一台主機
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 'http://61.219.172.11:60123/admins.html/price_plan_query');
			curl_setopt($ch, CURLOPT_HEADER, FALSE);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,5);
			curl_setopt($ch, CURLOPT_TIMEOUT, 5); //timeout in seconds
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($param));
			$data = curl_exec($ch);
			curl_close($ch);

		}catch (Exception $e){
			trigger_error('error msg:'.$e->getMessage());
		}
		
		$decode_result = json_decode($data, true);
		
		if (sizeof($decode_result) <= 0) return "empty";
		
		$this->db->trans_start();
		foreach ($decode_result as $key => $value)
		{
			$station_no = $value["station_no"];
			$tx_price_plan_id = $value["txid"];
			$tx_type = $value["tx_type"];
			
			$price_plan_data = array
			(
				'station_no' => $station_no,
				'tx_type' => $tx_type,
				'remarks' => $value['remarks'],
				'price_plan' => $value['price_plan'],
				'start_time' => $value['start_time'],
				'valid_time' => $value['valid_time']
			);
			
			// 刪除
			$this->db->delete('tx_price_plan', array('station_no' => $station_no, 'tx_type' => $tx_type));
			
			// 新增
			$this->db->insert('tx_price_plan', $price_plan_data);
		}
		$this->db->trans_complete();
		
		return "ok";
	}
}
