<?php             
/*
file: mitac_servicemodel.php
*/                   
class Mitac_service_model extends CI_Model 
{             
    var $vars = array(); 
    
    var $now_str;
    
	function __construct()
	{
		parent::__construct(); 
		$this->load->database();
        $this->now_str = date('Y-m-d H:i:s'); 
		
		// MITAC 連線設定 (測試環境)
		//define('MITAC_SERVICE_IP', '220.130.199.142');
		//define('MITAC_SERVICE_PORT', 49990);
		
		// MITAC 連線設定 (正式環境 - 現場呼叫)
		define('MITAC_SERVICE_IP', '192.168.10.60');
		define('MITAC_SERVICE_PORT', 49990);
    }
	
	// mitac socket
	function mitac_socket($in, $function_name = __FUNCTION__)
	{
		$in_encode = mb_convert_encoding($in, 'UTF-16LE', 'UTF-8');
		trigger_error($function_name . "..|{$in}|". json_encode($in_encode, true));
		
		$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if ($socket === false) {
			trigger_error($function_name . "..socket_create() failed: reason: " . socket_strerror(socket_last_error()));
		}

		$result = socket_connect($socket, MITAC_SERVICE_IP, MITAC_SERVICE_PORT);
		if ($result === false) {
			trigger_error($function_name . "..socket_connect() failed.\nReason: ({$result}) " . socket_strerror(socket_last_error($socket)));
			return false;	// 中斷
		}
		
		if(!socket_write($socket, $in_encode, strlen($in_encode)))
		{
			trigger_error($function_name . '..Write failed..');
		}
		
		$out = socket_read($socket, 64);
		socket_shutdown($socket);
		socket_close($socket);		
		trigger_error($function_name . "..socket output|{$out}");
		
		return $out;
	}
     
	public function init($vars)
	{                        
    	$this->vars = $vars;
    }
	
	// 詢問是否存活
	public function echo_mitac_alive()
	{
		$msg = implode(',', ['Mitac', 'Are you alive']);
		$result = $this->mitac_socket($msg, __FUNCTION__);
		return 'ok';
	}
	
	// 要求扣款 (ALTOB to MITAC)
	public function parking_fee_altob($parms)
	{
		// 轉換成對方要的格式
		$seqno = date('Ymd') . '_' . str_pad($parms['seqno'], 10, '0', STR_PAD_LEFT);
		$lpr = $parms['lpr'];
		$in_time = date('Ymd_His', strtotime($parms['in_time']));
		$out_time =	date('Ymd_His', strtotime($parms['out_time']));
		$gate_id = $parms['gate_id'];
		
		// 產生通訊內容
		$msg = implode(',', ['Mitac', 'ParkingFee_Altob', $seqno, $lpr, $in_time, $out_time, $gate_id]); //iconv("UTF-8", "ISO-8859-1", implode(',', ['Mitac', 'ParkingFee_Altob', $seqno, $lpr, $in_time, $out_time, $gate_id]));
		$result = $this->mitac_socket($msg, __FUNCTION__);
		return 'ok';
	}
	
	// 回應扣款成功 (MITAC to ALTOB)
	public function deduct_result($parms)
	{
		$seqno_arr = explode('_', $parms['seqno']);
		$in_time_arr = explode('_', $parms['in_time']);
		$out_time_arr = explode('_', $parms['out_time']);
		
		// 解出我方要的資訊
		$cario_no = intval(preg_replace( '/[^0-9]/', '', $seqno_arr[1]));
		$lpr = $parms['lpr'];
		$amt = intval(preg_replace( '/[^0-9]/', '', $parms['amt']));
		$amt_discount = intval(preg_replace( '/[^0-9]/', '', $parms['amt_discount']));
		$amt_real = intval(preg_replace( '/[^0-9]/', '', $parms['amt_real']));
		$in_time =	substr($in_time_arr[0], 0, 4). '-' . substr($in_time_arr[0], 4, 2) . '-' . substr($in_time_arr[0], 6, 2) . ' ' .
					substr($in_time_arr[1], 0, 2). ':' . substr($in_time_arr[1], 2, 2) . ':' . substr($in_time_arr[1], 4, 2);
		$pay_time =	$this->now_str;
		
		// 通訊內容
		$parms = array(
			'cario_no' => $cario_no, 
			'lpr' => $lpr, 
			'amt' => $amt, 
			'amt_discount' => $amt_discount, 
			'amt_real' => $amt_real,
			'in_time' => $in_time, 
			'pay_time' => $pay_time);
		
		$function_name = 'mitac2payed';
		
		trigger_error(__FUNCTION__ . "..call {$function_name}.." . print_r($parms, true));
		
		// 驗証碼
		$parms['ck'] = md5($parms['cario_no']. 'a' . date('dmh') . 'l' . $parms['lpr'] . 't'. $parms['amt']. 'o'. $parms['amt_discount'] . 'b'. $parms['amt_real'] . $function_name);
		
		// 呼叫
		try{
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "http://localhost/carpayment.html/{$function_name}");
            curl_setopt($ch, CURLOPT_HEADER, FALSE);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,3);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3); //timeout in seconds
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parms));
            $data = curl_exec($ch);
			
			if(curl_errno($ch))
			{
				trigger_error(__FUNCTION__ . ', curl error: '. curl_error($ch));
			}
			
            curl_close($ch);
			
			trigger_error(__FUNCTION__ . '..'. $data);

		}catch (Exception $e){
			trigger_error(__FUNCTION__ . 'error:'.$e->getMessage());
		}
		
		return 'ok';
	}
	
}
