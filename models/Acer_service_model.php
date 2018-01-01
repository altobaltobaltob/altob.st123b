<?php             
/*
file: acer_servicemodel.php
*/                   
class Acer_service_model extends CI_Model 
{             
    var $vars = array(); 
    
    var $now_str;
    
	function __construct()
	{
		parent::__construct(); 
		$this->load->database();
        $this->now_str = date('Y-m-d H:i:s'); 
		
		// ACER 連線設定 (測試環境)
		//define('ACER_SERVICE_IP', '220.130.199.142');
		//define('ACER_SERVICE_PORT', 60833);
		
		// ACER 連線設定 (測試環境 - 現場呼叫)
		//define('ACER_SERVICE_IP', '220.130.199.142');
		//define('ACER_SERVICE_PORT', 8033);
		
		// ACER 連線設定 (正式環境 - 現場呼叫)
		define('ACER_SERVICE_IP', '192.168.10.221');
		define('ACER_SERVICE_PORT', 8033);
		
		// 結果代碼
		define('ALTOB_RESULT_CODE_SUCCESS', 'OK');	// 成功
		define('ALTOB_RESULT_CODE_FAIL', 	'GG');	// 失敗

		// 錯誤碼
		define('ALTOB_ERROR_CODE_NONE', 						'0000');	// 預設值 （成功帶這個）
		define('ALTOB_ERROR_CODE_UNKNOWN_INPUT', 				'1001');	// 未知的 輸入
		define('ALTOB_ERROR_CODE_UNKNOWN_CMD', 					'1002');	// 未知的 CMD
		define('ALTOB_ERROR_CODE_NOT_FOUND', 					'1003');	// 查無記錄
		define('ALTOB_ERROR_CODE_ERROR',	 					'1004');	// 交易失敗
		define('ALTOB_ERROR_CODE_UNDEFINED', 					'9999');	// 未定義的錯誤
		define('ALTOB_ERROR_CODE_ACER_RESULT_FAIL', 			'2001');	// ACER 回傳處理錯誤
    }   
	
	// acer socket
	function acer_socket($in)
	{
		trigger_error(__FUNCTION__ . "..socket input|{$in}");
		
		$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if ($socket === false) {
			trigger_error(__FUNCTION__ . "..socket_create() failed: reason: " . socket_strerror(socket_last_error()));
		}

		$result = socket_connect($socket, ACER_SERVICE_IP, ACER_SERVICE_PORT);
		if ($result === false) {
			trigger_error(__FUNCTION__ . "..socket_connect() failed.\nReason: ({$result}) " . socket_strerror(socket_last_error($socket)));
			return false;	// 中斷
		}
		
		if(!socket_write($socket, $in, strlen($in)))
		{
			trigger_error(__FUNCTION__ . '..Write failed..');
		}
		
		$out = socket_read($socket, 64);
		socket_shutdown($socket);
		socket_close($socket);		
		trigger_error(__FUNCTION__ . "..socket output|{$out}");
		
		return $out;
	}
     
	public function init($vars)
	{                        
    	$this->vars = $vars;
    }
	
	// 票卡入場訊號，供 ALTOB 登記
	//（傳入：卡號、入場編號、是否月租卡； 回傳：結果代碼、入場編號、錯誤碼）
	public function cmd_001($card_no, $cario_no, $card_type)
	{                        
		// 票號查詢最近一筆入場資料
		$rows_cario = $this->db
							->select('cario_no, lpr, in_time, pay_time, out_before_time')
        					->from('cario')
							->where(array(
									'in_out' => 'CI', 'err' => 0, 'finished' => 0, 
									'cario_no' => $cario_no, 
									'in_time > DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 DAY)' => null))
                  			->limit(1)
                			->get()
                			->row_array();

		trigger_error(__FUNCTION__ . '..cario..' . print_r($rows_cario, true));
		
		$cario_no = 0;							// 進出碼
		$error_code = ALTOB_ERROR_CODE_NONE;	// 錯誤碼
		
		if (!empty($rows_cario['cario_no']))
		{
			$cario_no = $rows_cario['cario_no'];
			
			// 更新入場資訊
			$this->db->where(array('cario_no' => $cario_no))->update('cario', array('remarks' => "{$card_no}")); 
			if (!$this->db->affected_rows())
			{
				trigger_error(__FUNCTION__ . '..fail..' . $this->db->last_query());
				$error_code = ALTOB_ERROR_CODE_ERROR;
			}
		}
		else
		{
			// 查無入場記錄
			$error_code = ALTOB_ERROR_CODE_NOT_FOUND;
		}
		
		$data = array();
		$data['result_code'] = ALTOB_RESULT_CODE_SUCCESS;
		$data['result']['cario_no'] = $cario_no;
		$data['result']['error_code'] = $error_code;
		return $data;
    }
	
	// 票卡離場訊號，供 ALTOB 登記
	//（傳入：卡號、繳費時間、是否月租卡； 回傳：結果代碼、入場編號、錯誤碼）
	public function cmd_002($card_no, $pay_time, $card_type)
	{                   
		// 卡號查詢最近一筆入場資料
		$rows_cario = $this->db
							->select('cario_no, lpr, in_time, pay_time, out_before_time')
        					->from('cario')
							->where(array(
									'in_out' => 'CI', 'err' => 0, 'finished' => 0, 
									'remarks' => $card_no, 
									'in_time > DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 5 DAY)' => null))
							->order_by('cario_no', 'desc')
                  			->limit(1)
                			->get()
                			->row_array();

		trigger_error(__FUNCTION__ . '..cario..' . print_r($rows_cario, true));
		
		$cario_no = 0;							// 進出碼
		$error_code = ALTOB_ERROR_CODE_NONE;	// 錯誤碼
		
		if (!empty($rows_cario['cario_no']))
		{
			$cario_no = $rows_cario['cario_no'];
			
			// 暫不處理
		}
		else
		{
			// 查無入場記錄
			$error_code = ALTOB_ERROR_CODE_NOT_FOUND;
		}
		
		$data = array();
		$data['result_code'] = ALTOB_RESULT_CODE_SUCCESS;
		$data['result']['cario_no'] = $cario_no;
		$data['result']['error_code'] = $error_code;
		return $data;
    }
	
	// 票號離場訊號，回傳若成功觸發 ACER 開門
	// （傳入：6 碼數字； 回傳：結果代碼、入場編號、錯誤碼、離場代碼）
	public function cmd_003($ticket_no)
	{                   
		// 票號查詢最近一筆入場資料 （只能查 5天 內）
		$rows_cario = $this->db
							->select('cario_no, payed, in_time, pay_time, out_before_time')
        					->from('cario')
							->where(array(
									'ticket_no' => $ticket_no, 'err' => 0, 
									'in_time > DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 5 DAY)' => null)
								)
                  			->order_by('cario_no', 'desc')
                  			->limit(1)
                			->get()
                			->row_array();

		trigger_error(__FUNCTION__ . '..cario..' . print_r($rows_cario, true));
		
		$cario_no = 0;							// 進出碼
		$error_code = ALTOB_ERROR_CODE_NONE;	// 錯誤碼
		$msg_code = 0;							// 離場碼
		
		if (!empty($rows_cario['cario_no']))
		{
			$cario_no = $rows_cario['cario_no'];
			
			if(strtotime($rows_cario['out_before_time']) >= time())
			{
				if ($rows_cario['payed'])
				{
					// CO.B.1 臨停車已付款
					$msg_code = 6;
				}
				else
				{
					// CO.B.2 臨停車未付款
					$msg_code = 8;
				}
			}
			else
			{
				// CO.C.1 其它付款方式
				$msg_code = 9;
			}
		}
		else
		{
			// CO.Z.Z 無入場資料
			$cario_no = 0;
			$msg_code = 13;
		}
		
		$data = array();
		$data['result_code'] = ALTOB_RESULT_CODE_SUCCESS;
		$data['result']['cario_no'] = $cario_no;
		$data['result']['error_code'] = $error_code;
		$data['result']['msg_code'] = $msg_code;
		return $data;
    }
	
	// 呼叫 acer （cmd: 101)
	// （傳入：入場編號、進場時間、 6 碼數字、車牌號碼、出入口編號； 回傳：結果代碼、入場編號、錯誤碼）
	public function cmd_101($cario_no, $in_time, $ticket_no, $lpr, $ivs_no)
	{                
		$seq = '00001';
		$cmd = '101';
		$cario_no_pad = str_pad($cario_no, 10, '0', STR_PAD_LEFT); 		// 入場編號： 10 碼 (左邊補 0)
		$lpr_pad = str_pad($lpr, 10, '*', STR_PAD_LEFT); 				// 車牌號碼： 10 碼 (左邊補 *)
		$ivs_no_pad = str_pad($ivs_no, 2, '0', STR_PAD_LEFT); 			// 車道編號： 2 碼 (左邊補 0)
	
		// 建立封包		
		$packformat = "a10Ca19Ca6Ca10Ca2";
		$data = pack($packformat,
					// Data：內容除分隔符號為0x1F，其他全為ASCII碼0x20 ~ 0x7F內
					$cario_no_pad, 0x1f, $in_time, 0x1f, $ticket_no, 0x1f, $lpr_pad, 0x1f, $ivs_no_pad
				);
		$data_len = strlen($data);
		$socket_len = $data_len + 16;

		$in = pack("Ca2Ca5Ca3C{$packformat}CCC",
					0x02,												// STX：封包起始碼(0x02)
					$socket_len, 0x1c, 									// 封包長度：從STX到ETX的位元數
					$seq, 0x1c, 										// 封包流水號：5碼ASCII數字(不足5碼時左補”0”補滿5碼)
					$cmd, 0x1c,  										// CmdID：命令ID

					// Data：內容除分隔符號為0x1F，其他全為ASCII碼0x20 ~ 0x7F內
					$cario_no_pad, 0x1f, $in_time, 0x1f, $ticket_no, 0x1f, $lpr_pad, 0x1f, $ivs_no_pad,

					0x1c,
					0x80, 												// CRC：封包檢查碼
					0x03												// ETX：封包結束碼(0x03)
					);
		
		// 連線
		$out = $this->acer_socket($in);
		
		if(!empty($out))
		{
			list($front, $seq, $cmd, $data) = explode(chr(28), $out);
			list($result_code, $cario_no, $error_code) = explode(chr(31), $data);
			trigger_error(__FUNCTION__ . "..socket return explode|{$front}, {$seq}, {$cmd}, {$result_code}, {$cario_no}, {$error_code}");
		
			if(!empty($result_code) && ALTOB_RESULT_CODE_SUCCESS == $result_code)
			{
				$data = array();
				$data['result_code'] = ALTOB_RESULT_CODE_SUCCESS;
				$data['result']['cario_no'] = $cario_no;
				return $data;
			}	
		}
		
		$data = array();
		$data['result_code'] = ALTOB_RESULT_CODE_FAIL;
		$data['result']['error_code'] = ALTOB_ERROR_CODE_ACER_RESULT_FAIL;
		return $data;
    }
	
	// 呼叫 acer （cmd: 102)
	// （傳入：入場編號、出入口編號、離場代碼； 回傳：結果代碼、入場編號、錯誤碼）
	public function cmd_102($cario_no, $ivs_no, $msg_code)
	{                
		$seq = '00001';
		$cmd = '102';
		
		$cario_no_pad = str_pad($cario_no, 10, '0', STR_PAD_LEFT); 		// 入場編號： 10 碼 (左邊補 0)
		$ivs_no_pad = str_pad($ivs_no, 2, '0', STR_PAD_LEFT); 			// 車道編號： 2 碼 (左邊補 0)
		$msg_code_pad = str_pad($msg_code, 5, '0', STR_PAD_LEFT); 		// 離場代碼： 5 碼 (左邊補 0)
	
		// 建立封包		
		$packformat = "a10Ca2Ca5";
		$data = pack($packformat,
					// Data：內容除分隔符號為0x1F，其他全為ASCII碼0x20 ~ 0x7F內
					$cario_no_pad, 0x1f, $ivs_no_pad, 0x1f, $msg_code_pad
				);
		$data_len = strlen($data);
		$socket_len = $data_len + 16;

		$in = pack("Ca2Ca5Ca3C{$packformat}CCC",
					0x02,												// STX：封包起始碼(0x02)
					$socket_len, 0x1c, 									// 封包長度：從STX到ETX的位元數
					$seq, 0x1c, 										// 封包流水號：5碼ASCII數字(不足5碼時左補”0”補滿5碼)
					$cmd, 0x1c,  										// CmdID：命令ID

					// Data：內容除分隔符號為0x1F，其他全為ASCII碼0x20 ~ 0x7F內
					$cario_no_pad, 0x1f, $ivs_no_pad, 0x1f, $msg_code_pad,

					0x1c,
					0x80, 												// CRC：封包檢查碼
					0x03												// ETX：封包結束碼(0x03)
					);
		
		// 連線
		$out = $this->acer_socket($in);
		
		if(!empty($out))
		{
			list($front, $seq, $cmd, $data) = explode(chr(28), $out);
			list($result_code, $cario_no, $error_code) = explode(chr(31), $data);
			trigger_error(__FUNCTION__ . "..socket return explode|{$front}, {$seq}, {$cmd}, {$result_code}, {$cario_no}, {$error_code}");
		
			if(!empty($result_code) && ALTOB_RESULT_CODE_SUCCESS == $result_code)
			{
				$data = array();
				$data['result_code'] = ALTOB_RESULT_CODE_SUCCESS;
				$data['result']['cario_no'] = $cario_no;
				return $data;
			}	
		}
		
		$data = array();
		$data['result_code'] = ALTOB_RESULT_CODE_FAIL;
		$data['result']['error_code'] = ALTOB_ERROR_CODE_ACER_RESULT_FAIL;
		return $data;
    }
	
	
	
}
