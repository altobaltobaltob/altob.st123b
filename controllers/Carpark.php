<?php
/*
file: carpark.php		停車管理
*/
class Carpark extends CC_Controller
{                 
	function __construct() 
	{        
		parent::__construct('carpark');
		
		// load library
		$this->load->library(array('form_validation','session'));
		// load helpers
		$this->load->helper(array('form'));  
		// ajax code
		define('RESULT_SUCCESS', 'ok');
		define('RESULT_FORM_VALIDATION_FAIL', '-1');
		define('RESULE_FAIL', 'gg');
	}
	
	// ------------------------------------------------
	//
	// 報表
	//
	// ------------------------------------------------
	
	// 進出記錄表 (VIP)
	public function export_vip_cario_report()
	{
		// 次月算上月
		$last_day_of_previous_month = date("Y-n-j", strtotime("last day of previous month"));
		$d = date_parse_from_format("Y-m-d", $last_day_of_previous_month);
		
		$station_name = empty($this->input->post('station_name', true)) ? '場站名稱' : $this->input->post('station_name', true);
		$year = empty($this->input->post('year', true)) ? $d['year'] : $this->input->post('year', true);
		$month = empty($this->input->post('month', true)) ? $d['month'] : $this->input->post('month', true);
		$addr = empty($this->input->post('addr', true)) ? '地址' : $this->input->post('addr', true);
		$phone_no = empty($this->input->post('phone_no', true)) ? '電話' : $this->input->post('phone_no', true);
		
		$result = $this->app_model('excel')->export_cario_report($station_name . '(VIP)', $year, $month, $addr, $phone_no, 250);
		
		if(empty($result))
		{
			echo '無記錄';
			exit;
		}
	}
	
	// 進出記錄表
	public function export_cario_report()
	{
		// 次月算上月
		$last_day_of_previous_month = date("Y-n-j", strtotime("last day of previous month"));
		$d = date_parse_from_format("Y-m-d", $last_day_of_previous_month);
		
		$station_name = empty($this->input->post('station_name', true)) ? '場站名稱' : $this->input->post('station_name', true);
		$year = empty($this->input->post('year', true)) ? $d['year'] : $this->input->post('year', true);
		$month = empty($this->input->post('month', true)) ? $d['month'] : $this->input->post('month', true);
		$addr = empty($this->input->post('addr', true)) ? '地址' : $this->input->post('addr', true);
		$phone_no = empty($this->input->post('phone_no', true)) ? '電話' : $this->input->post('phone_no', true);
		
		$result = $this->app_model('excel')->export_cario_report($station_name, $year, $month, $addr, $phone_no);
		
		if(empty($result))
		{
			echo '無記錄';
			exit;
		}
	}
	
	// ------------------------------------------------
	//
	// 博辰 (START)
	//
	// ------------------------------------------------
	
	// 同步 博辰 888
	function sync_parktron_888()
	{
		try{
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'http://192.168.10.80:5477/parktron/ipms/services/areaCount/findAll');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,5);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5); //timeout in seconds
            curl_setopt($ch, CURLOPT_POSTFIELDS, '{}');
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));  
            $result = curl_exec($ch);
			
			$parktron_result = json_decode($result);
			trigger_error(PARKTRON_LOG_TITLE . '..' . __FUNCTION__ . '..' . print_r($parktron_result, true));
			$this->data_model()->sync_parktron_888($parktron_result);
			
			if(curl_errno($ch))
			{
				trigger_error(PARKTRON_LOG_TITLE . '..' . __FUNCTION__ . '..' . ', curl error: '. curl_error($ch));
			}
			
            curl_close($ch);
			
		}catch (Exception $e){
			trigger_error(PARKTRON_LOG_TITLE . '..' . __FUNCTION__ . '..' . 'error:'.$e->getMessage());
		}
	}
	
	// ------------------------------------------------
	//
	// CRM (START)
	//
	// ------------------------------------------------
	
	// [test] zzz
	public function gen_test_case()
	{
		$sno = '12302';
		$ivsno = 0;
		$io = 'CI';
		$lpr = 'TEST1109A';
		$ts = date('YmdHis');
		
		$parms = array();
		$parms['sno'] = $sno;
		$parms['ivsno'] = $ivsno;
		$parms['io'] = $io;
		$parms['lpr'] = $lpr;
		$parms['ts'] = $ts;
		
		$function_name = 'remote_lprio';
		$ck = $this->gen_cms_ck($parms, $function_name);
		echo "http://localhost/carpark.html/{$function_name}/sno/{$sno}/ivsno/{$ivsno}/io/{$io}/type/C/lpr/{$lpr}/color/NONE/sq/0/ts/{$ts}/sq2/0/etag/NONE/ant/1/ck/{$ck}";
		echo "\n\n";
		
		$function_name = 'remote_opendoor_lprio';
		$ck = $this->gen_cms_ck($parms, $function_name);
		echo "http://localhost/carpark.html/{$function_name}/sno/{$sno}/ivsno/{$ivsno}/io/{$io}/type/C/lpr/{$lpr}/color/NONE/sq/0/ts/{$ts}/sq2/0/etag/NONE/ant/1/ck/{$ck}";
		echo "\n\n";
		
		$function_name = 'remote_opendoor_anyway';
		$ck = $this->gen_cms_ck($parms, $function_name);
		echo "http://localhost/carpark.html/{$function_name}/sno/{$sno}/ivsno/{$ivsno}/io/{$io}/lpr/{$lpr}/ts/{$ts}/ck/{$ck}";
		echo "\n\n";
		exit;
	}
	
	// 產生 CK
	function gen_cms_ck($parms, $function_name)
	{
		return md5($parms['sno']. 'a' . date('dmh') . 'l' . $parms['ts'] . 't'. $parms['lpr']. 'o'. $parms['ivsno'] . 'b'. $parms['io'] . $function_name);
	}
	
	// [local] 手動車辨記錄
	public function local_lprio()
	{
		$LOG_FLAG = 'local-cms://';
		
		// 判斷 cmd
		$cmd = $this->input->post('cmd', true);
		
		// 新增車辨記錄
		if($cmd == 1)
		{
			$sno = $this->input->post('station_no', true);
			$ivsno = $this->input->post('ivsno', true);
			$io = $this->input->post('io', true);
			$ctype = $this->input->post('ctype', true);
			$lpr = $this->input->post('lpr', true);
			
			$parms = array();
			$parms['sno'] = preg_replace('/[^0-9]/', '', strtoupper(urldecode($sno)));
			$parms['ivsno'] = $ivsno;
			$parms['io'] = $ctype.$io;
			$parms['type'] = 'C';
			$parms['lpr'] = strtoupper(urldecode($lpr));
			$parms['color'] = 'NONE';
			$parms['sq'] = 0;
			$parms['ts'] = date('YmdHis');
			$parms['sq2'] = 0;
			$parms['etag'] = 'NONE';
			$parms['ant'] = 1;
			// 補充
			$parms['obj_type'] = 1;
			$parms['curr_time_str'] = date('Y-m-d H:i:s');
			$parms['pic_name'] = '';
			
			trigger_error($LOG_FLAG . __FUNCTION__ . '..' . print_r($parms, true));
			
			// 判斷 io 正確性
			if(!in_array($parms['io'], array('CI', 'CO', 'MI', 'MO')))
			{
				echo 'unknown_io';
				exit;
			}
			
			// 初始 mqtt
			$this->init_mqtt();
			$return_msg = $this->app_model('cars')->opendoor_lprio($parms);
			trigger_error(__FUNCTION__ . "|test|{$parms['lpr']}|return_msg|" . $return_msg);
			
			// 執行
			$return_msg = $this->app_model('cars')->lprio($parms);
			trigger_error(__FUNCTION__ . "|{$parms['lpr']}|return_msg|" . $return_msg);
			echo 'ok';
			exit;
		}
		
		// 修改車辨記錄
		else if($cmd == 2)
		{
			$station_no = $this->input->post('station_no', true);
			$cario_no = $this->input->post('cario_no', true);
			$old_lpr = $this->input->post('old_lpr', true);
			$new_lpr = $this->input->post('new_lpr', true);
			
			$parms = array();
			$parms['sno'] = preg_replace('/[^0-9]/', '', strtoupper(urldecode($station_no)));
			$parms['cno'] = preg_replace('/[^0-9]/', '', strtoupper(urldecode($cario_no)));
			$parms['old_lpr'] = strtoupper(urldecode($old_lpr));
			$parms['lpr'] = strtoupper(urldecode($new_lpr));
			
			trigger_error($LOG_FLAG . __FUNCTION__ . '..' . print_r($parms, true));
			
			// 判斷 lpr 正確性
			if($parms['new_lpr'] == $parms['old_lpr'])
			{
				echo 'no_changed';
				exit;
			}
			else if($parms['lpr'] == 'NONE')
			{
				echo 'lpr_none';
				exit;
			}

			// 執行
			echo $this->app_model('cars')->upd_cario($parms);
			exit;
		}
		
		// 未知
		else
		{
			echo 'unknown_cmd';
			exit;
		}
	}
	
	// [remote] 新增車辨記錄
	public function remote_lprio()
	{
		$LOG_FLAG = 'cms://';
		$parms = $this->uri->uri_to_assoc(3);
		
		// ck
		if($parms['ck'] != $this->gen_cms_ck($parms, __FUNCTION__))
		{
			echo 'ck_error';	// 中斷
			exit;
		}
		
		$parms['lpr'] = urldecode($parms['lpr']);
		$parms['obj_type'] = 1;
        $parms['curr_time_str'] = date('Y-m-d H:i:s');
        $parms['pic_name'] = '';
		
		trigger_error($LOG_FLAG . __FUNCTION__ . '..' . print_r($parms, true));
		
		// 執行
		$this->app_model('cars')->lprio($parms);
		echo 'ok';
		exit;
	}
	
	// [remote] 車辨開門
	public function remote_opendoor_lprio()
	{
		$LOG_FLAG = 'cms://';
		$parms = $this->uri->uri_to_assoc(3);
		
		// ck
		if($parms['ck'] != $this->gen_cms_ck($parms, __FUNCTION__))
		{
			echo 'ck_error';	// 中斷
			exit;
		}
		
		$parms['lpr'] = urldecode($parms['lpr']);
		
		trigger_error($LOG_FLAG . __FUNCTION__ . '..' . print_r($parms, true));
		
		// 初始 mqtt
		$this->init_mqtt();
		
		// 執行
		$this->app_model('cars')->opendoor_lprio($parms);
		echo 'ok';
		exit;
	}
	
	// [remote] 直接開門
	public function remote_opendoor_anyway()
	{
		$LOG_FLAG = 'cms://';
		$parms = $this->uri->uri_to_assoc(3);
		
		// ck
		if($parms['ck'] != $this->gen_cms_ck($parms, __FUNCTION__))
		{
			echo 'ck_error';	// 中斷
			exit;
		}
		
		$parms['lpr'] = urldecode($parms['lpr']);
		
		trigger_error($LOG_FLAG . __FUNCTION__ . '..' . print_r($parms, true));
		
		// 初始 mqtt
		$this->init_mqtt();
		
		// 判斷會員身份
		$cars_model = $this->app_model('cars');
		$rows = $cars_model->get_member($lpr);
		
		if ($rows['member_no'] == 0)
		{
			$parms['ck'] = $cars_model->gen_opendoor_ck($parms, 'temp_opendoors');	// 臨停訊號
			$cars_model->do_temp_opendoor($parms);
		}
		else
		{
			$parms['ck'] = $cars_model->gen_opendoor_ck($parms, 'member_opendoors');	// 月租訊號
			$cars_model->do_member_opendoor($parms);
		}
		
		echo 'ok';
		exit;
	}
	
	// ------------------------------------------------
	//
	// 接收端 (START)
	//
	// ------------------------------------------------
	
	// [mqtt] 接收端 
	public function mqtt_service()
	{
		$LOG_FLAG = 'mqtt://';
		$topic = $this->input->post('topic', true);
		$msg = $this->input->post('msg', true);
		$ck = $this->input->post('ck', true);
		
		if(md5($topic.'altob'.$msg) != $ck)
		{
			echo 'ck_error';
			exit;
		}
		
		trigger_error($LOG_FLAG . __FUNCTION__ . "|{$topic}|{$msg}");
		
		if($topic == 'altob.888.mqtt')
		{
			$data_model = $this->data_model();
			
			// 第一個場站編號	先不管場站
			$station_setting = $data_model->station_setting_query();
			$station_no_arr = explode(SYNC_DELIMITER_ST_NO, $station_setting['station_no']);
			$first_station_no = $station_no_arr[0];
			
			$msg_arr = explode(',', $msg);
			
			if(sizeof($msg_arr) != 4)
			{
				trigger_error($LOG_FLAG . __FUNCTION__ . "..error_size.." . print_r($msg_arr, true));
				echo 'error_size';
				exit;
			}
			
			if($msg_arr[0] != 'N888' || $msg_arr[3] != 'altob')
			{
				trigger_error($LOG_FLAG . __FUNCTION__ . "..unknown_msg.." . print_r($msg_arr, true));
				echo 'unknown_msg';
				exit;
			}
			
			$msg_arr = explode(',', $msg);
			$group_id = isset($msg_arr[1]) && $msg_arr[1] == 2 ? 'M888' : 'C888';
			$value = isset($msg_arr[2]) ? $msg_arr[2] : 0;
			$result = $data_model->force_sync_888($first_station_no, $group_id, $value);
			trigger_error($LOG_FLAG . __FUNCTION__ . "..{$first_station_no}|{$group_id}|{$value}..result..{$result}..");
		}
		
		echo 'ok';
		exit;
	}
	
	// [設定檔] 取得設定
	public function station_setting_query()
	{
		$reload = $this->input->post('reload', true);
		
		$data_model = $this->data_model();
		
		if(isset($reload) && $reload > 0)
		{
			$station_setting = $data_model->station_setting_query(true);	// 強制重新載入	
			trigger_error(__FUNCTION__ . '..station_setting: '. print_r($station_setting, true));
			
			if(!$station_setting)
			{
				echo json_encode('fail', JSON_UNESCAPED_UNICODE);
				exit;	// 中斷
			}
			
			usleep(300000); // 0.3 sec delay
			
			// 費率資料同步
			$result = $data_model->sync_price_plan(array('station_no_arr' => $station_setting['station_no']));
			trigger_error(__FUNCTION__ . '..sync_price_plan: '. $result);
			
			usleep(300000); // 0.3 sec delay
			
			// 會員資料同步
			$result = $data_model->sync_members(array('station_no_arr' => $station_setting['station_no_list'], 
				'current_station_no_arr' => $station_setting['station_no']));	// 20171211 upd
			trigger_error(__FUNCTION__ . '..sync_members: '. $result);
			
			usleep(300000); // 0.3 sec delay
			
			// 歐pa卡同步
			$result = $data_model->sync_allpa_user(array('station_no_arr' => $station_setting['station_no']));
			trigger_error(__FUNCTION__ . '..sync_allpa_user: '. $result);
			
			usleep(300000); // 0.3 sec delay
			
			// 在席資料同步
			$result = $data_model->sync_pks_groups_reload($station_setting);
			trigger_error(__FUNCTION__ . '..sync_pks_groups_reload: '. $result);
		}
		else
		{
			$station_setting = $data_model->station_setting_query(false);
			
			if(!$station_setting)
			{
				echo json_encode('fail', JSON_UNESCAPED_UNICODE);
				exit;	// 中斷
			}
		}
		echo json_encode($station_setting, JSON_UNESCAPED_UNICODE);
	}
	
	// [排程 or 強制] 同步場站資訊
	public function sync_station_data()
	{
		trigger_error(__FUNCTION__ . '..from..'. $this->my_ip() .'..network..');	// IP 會浮動？
		
		$switch_lpr_arr = array();	// 換車牌
		
		$meta = $this->input->post('meta', true);
		if(!empty($meta))
		{
			trigger_error( __FUNCTION__ . '..meta_arr..' . $meta);
			
			$meta_arr = explode('@@@', $meta);
			
			foreach($meta_arr as $raw)
			{
				if(empty($raw))
					continue;
				
				$data = json_decode($raw, true);
				
				if($data['key'] == 'switch_lpr')
				{
					array_push($switch_lpr_arr, $data['value']);
				}
			}
		}
		
		$data_model = $this->data_model();
		
		// 0. 取得場站設定
		$station_setting = $data_model->station_setting_query(false);
		trigger_error(__FUNCTION__ . '..station_setting: '. print_r($station_setting, true));
		
		$station_no_arr = array('station_no_arr' => $station_setting['station_no']);
		
		// 1. 月租系統
		$result = $data_model->sync_members($station_no_arr);
		trigger_error(__FUNCTION__ . '..sync_members: '. $result);
		
		// 2. 同步車牌更換
		if(!empty($switch_lpr_arr))
		{
			$data_model->sync_switch_lpr($switch_lpr_arr);
		}
		
		// 3. 歐pa卡同步 (TODO: 暫時放在這)
		$result = $data_model->sync_allpa_user($station_no_arr);
		trigger_error(__FUNCTION__ . '..sync_allpa_user: '. $result);
	}
	
	// [API] 取得最新未結清
	public function get_last_unbalanced_cario()
	{
		trigger_error(__FUNCTION__ . '..from..'. $this->my_ip() .'..network..');	// IP 會浮動？
		
		$lpr = $this->input->post('lpr', true);
		$station_no = $this->input->post('station_no', true);
		$c_s = $this->input->post('c_s', true);
		
		// 確認正確性
		if(empty($c_s) || $c_s != md5('altob'.$lpr.'botla'.$station_no))
		{
			trigger_error(__FUNCTION__ . "..{$lpr}|".$station_no."|{$c_s}..check fail..");
			echo 'fail';
			exit;
		}
		
        $data = $this->data_model()->get_last_unbalanced_cario($lpr, $station_no);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
	}
	
	// [API] 更新未結清 (行動支付)
	public function sync_m2payed()
	{
		trigger_error(__FUNCTION__ . '..from..'. $this->my_ip() .'..network..');	// IP 會浮動？
		
		$lpr = $this->input->post('lpr', true);
		$amt = $this->input->post('amt', true);
		$station_no = $this->input->post('station_no', true);
		$cario_no = $this->input->post('cario_no', true);
		$c_s = $this->input->post('c_s', true);
		
		// 確認正確性
		if(empty($c_s) || $c_s != md5($lpr.$amt.'altob'.$station_no.'botla'.$cario_no))
		{
			trigger_error(__FUNCTION__ . "..{$lpr}|{$amt}|{$station_no}|{$cario_no}|{$c_s}..check fail..");
			echo 'fail';
			exit;
		}
		
		// 臨停繳費
        echo $this->app_model('carpayment')->p2payed(array('seqno' => $cario_no, 'lpr' => $lpr, 'amt' => $amt), true);
		exit;
	}
	
	// 同步 （由排程呼叫）
	public function sync_minutely()
	{
		$this->sync_parktron_888();				// 同步博辰 888
		
		$this->data_model()->sync_pks_groups();	// 同步在席現況
	}
	
	/*
	// 20170816 手動新增入場資料
	public function gen_carin()
	{
		trigger_error(__FUNCTION__ . '..from..'. $this->my_ip() .'..network..');	// IP 會浮動？
		
		$lpr = $this->input->post('lpr', true);
		$station_no = $this->input->post('station_no', true);
		$c_s = $this->input->post('c_s', true);
		
		// 確認正確性
		if(empty($c_s) || $c_s != md5($lpr.'altob'.$station_no.'botla'. __FUNCTION__ ))
		{
			trigger_error(__FUNCTION__ . "..{$lpr}|{$station_no}|{$c_s}..check fail..");
			echo 'fail';
			exit;
		}
		
		$parms = array(
					'sno' => $station_no,
					'lpr' => $lpr,
					'etag' => 'NONE',
					'io' => 'CI',
					'ivsno' => 0
				);
				
		$this->carpark_model->gen_carin($parms);
        echo 'ok';
	}
	*/
	
	// ------------------------------------------------
	//
	// 接收端 (END)
	//
	// ------------------------------------------------
	
	
	
	
	// [START] 2016/06/03 登入
	
	public function index()
	{                   
		if($this->session->userdata('logged_in'))
		{
			$session_data = $this->session->userdata('logged_in');
			$data['username'] = $session_data['username'];
			$data['type'] = $session_data['type'];
			
			// 取得場站設定
			$station_setting = $this->data_model()->station_setting_query();
			if(isset($station_setting['station_no']))
			{
				$data['station_no'] = $station_setting['station_no'];	
				$data['station_name'] = $station_setting['station_name'];	
			}			
			
			if($data['type'] == 'admin')
			{
				$this->show_page('admin_page', $data); // 進階管理者介面
			}
			else
			{
				$this->show_page('main_page', $data); // 一般
			}
		}
		else
		{
			//If no session, redirect to login page
			//redirect('login', 'refresh');
			$this->show_page('login_page');
		}
	}
	
	// 登入
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
		
		$result = $this->app_model('user')->user_login($data);
		
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
	
	// 登出
	public function user_logout()
	{   
		$this->session->unset_userdata('logged_in');
		session_destroy();
		return RESULT_SUCCESS;
	}
	
	// [END] 2016/06/03 登入
	
	

    /*
    
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
	public function rent_sync()
	{                           
    	$station_no = $this->input->post('station_no', true);
    	$start_date = $this->input->post('start_date', true);
    	$end_date = $this->input->post('end_date', true);
                
        // $data = $this->carpark_model->rent_sync($station_no, $start_date, $end_date);
		                                     
        // print_r($data);
	}       
    // 重設剩餘車位數    	
	public function available_set()
	{                                           
        $data = $this->carpark_model->available_set();
        $this->http_return($data, 'json');
	}
          
    // 剩餘車位數更新    	
	public function available_update()
	{       
    	$station_no = $this->input->get_post('station_no', true);                                    
    	$data['name'] = $this->input->get_post('st_name', true);                                    
    	$data['tot_pkg'] = $this->input->get_post('tot_pkg', true); 
    	$data['ava_pkg'] = $this->input->get_post('ava_pkg', true); 
                                           
        $this->http_return($this->carpark_model->available_update($station_no, $data), 'json');
	} 
          
    // 剩餘車位數查核    	
	public function available_check()
	{       
    	$time_point = $this->input->get_post('time_point', true);    
                                           
        $this->http_return($this->carpark_model->available_check($time_point), 'json');
	}
	
	// 顯示logs (cars grep 888)
	public function show_888_logs()
	{             
        $lines = $this->uri->segment(3);	// 顯示行數
        if (empty($lines)) $lines = 1000;		// 無行數參數, 預設為1000行
		if($lines > 20000)  $lines = 20000;		// 最多 20000行
    	
		$grep_str = ' |grep 888';
		$target_str = LOG_PATH.APP_NAME . '.' . date('Ymd').'.log.txt'. $grep_str;
		
        // echo '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head><body><pre style="white-space: pre-wrap;">';
        echo '<html lang="zh-TW"><body><pre style="white-space: pre-wrap;">';      
       
		passthru('/usr/bin/tail -n ' . $lines . '  ' . $target_str);	// 利用linux指令顯示倒數幾行的logs內容 
        echo "\n----- " . $target_str . ' -----';   
        echo '</pre></body></html>';
	}
    
	
    // 新增月租資料
    public function member_add()
	{          
    	$data = array
        		(
					'member_no' => $this->input->post('member_no', true),                 
					'station_no' => $this->input->post('station_no', true),                 
					'lpr' => strtoupper($this->input->post('lpr', true)),                 
					'old_lpr' => strtoupper($this->input->post('old_lpr', true)),                 
					'etag' => strtoupper($this->input->post('etag', true)),                 
					'start_date' => $this->input->post('start_date', true),                 
					'end_date' => $this->input->post('end_date', true),                 
					'member_name' => $this->input->post('member_name', true),         
					'member_nick_name' => $this->input->post('member_name', true),         
					'mobile_no' => $this->input->post('mobile_no', true),                
					'member_id' => $this->input->post('member_id', true),                          
					'contract_no' => $this->input->post('contract_no', true),                          
					'amt' => $this->input->post('amt', true),                          
					'tel_h' => $this->input->post('tel_h', true),                          
					'tel_o' => $this->input->post('tel_o', true),                          
					'addr' => $this->input->post('addr', true)                          
                );                           
                                                          
        trigger_error("add:".print_r($data, true));
        if ($data['member_no'] == 0 || $data['old_lpr'] != $data['lpr'])
        {
    		if ($this->carpark_model->check_lpr($data['lpr']) > 0)
        	{
       			echo '車牌重複, 請查明再輸入'; 
                exit;  
        	} 
        }
        
        $this->carpark_model->member_add($data);
        echo 'ok';
	}  
	
	
    
    // 刪除月租資料
    public function member_delete()
	{                                
        $member_no = $this->input->post('member_no', true);
        $this->carpark_model->member_delete($member_no);
        echo 'ok';
	}       
     
    // 進出場事件即時顯示
    public function cario_event()
	{                    
    	set_time_limit(0);            
        while(true)
        {
          
        }
	}
	
    // 汽車開門
    public function opendoors()
	{   
        $ivsno = $this->uri->segment(3);
    	$lanes = array
        (
			0 => array ('devno' => 0, 'temp' => 0, 'member' => 1),		// 1號入口, temp:臨停, member:月租	        
//			1 => array ('devno' => 0, 'temp' => 2, 'member' => 3),		// 2號調撥入口	        
			1 => array ('devno' => 1, 'temp' => 0, 'member' => 1),		// 3號調撥出口	        
			2 => array ('devno' => 1, 'temp' => 0, 'member' => 1),		// 3號調撥出口	        
			3 => array ('devno' => 1, 'temp' => 2, 'member' => 3)		// 4號出口	
        );
                                              
        $url = 'http://admin:99999999@192.168.10.53/cgi-bin/basic_setting.cgi?ID=1&';
        $member_tag = 'member';	// member:月租會員
                     
		// 短路開柵欄
        @get_headers("{$url}OUTDEV={$lanes[$ivsno]['devno']}&OUTCH={$lanes[$ivsno][$member_tag]}&OUTSTATUS=1");  
                
		usleep(400000);		// 暫停0.4秒  
        
        // 斷路, 車過關柵欄 
		@get_headers("{$url}OUTDEV={$lanes[$ivsno]['devno']}&OUTCH={$lanes[$ivsno][$member_tag]}&OUTSTATUS=0");
	}
       
    
    
    // 調撥車道查詢
    public function reversible_lane_query()
	{     
    	$max_lane = 4;	// 共幾個車道數
        for ($idx = 0; $idx < $max_lane; ++$idx)
        {
          	$data[$idx] = file_get_contents("http://192.168.10.51:8090/cgi-bin/switcher.cgi?id={$idx}&action=9");
        }   
        // $data = array(1, 1, 0, 1);
        echo json_encode($data);                       
	}  
       
	   // 調撥車道設定
    public function reversible_lane_set()
	{     
        $lane_no = $this->input->post('lane_no', true);            
        $actions = $this->input->post('actions', true);            
        $data = file_get_contents("http://192.168.10.51:8090/cgi-bin/switcher.cgi?id={$lane_no}&action={$actions}");
        
        echo "{$lane_no}|{$actions}|{$data}";                       
	}     
    
                         
    // 在席車位檢查未有入場資料清單
    public function pks_check_list()
	{                      
        $max_rows = $this->uri->segment(3, 100);	// 一次讀取筆數, 預設為100筆
        $data = $this->carpark_model->pks_check_list($max_rows);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }       
     
                         
    // 重設在席查核
    public function reset_pks_check()
	{                      
        $data = $this->carpark_model->reset_pks_check();
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }            
    
           
    // 更正在席車號
    public function correct_pks_lpr()
	{                       
        $pksno = $this->uri->segment(3, 0);	// 車格號碼
        $lpr = $this->uri->segment(4, 'NONE');	// 車號
        
        if ($pksno == 0 || $lpr == 'NONE')
        {
        	echo json_encode(array('err' => 1, 'cario_no' => 0), JSON_UNESCAPED_UNICODE); 
        }                 
        else
        {
       		$data = $this->carpark_model->correct_pks_lpr($pksno, $lpr);
        	echo json_encode($data, JSON_UNESCAPED_UNICODE);       
        }
    }          
    
                     
    // 入場車號查核在席無資料清單
    public function carin_check_list()
	{                      
        $max_rows = $this->uri->segment(3, 20);	// 一次讀取筆數, 預設為100筆
        $data = $this->carpark_model->carin_check_list($max_rows);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    } 
           
    // 更正入場車號
    public function correct_carin_lpr()
	{                       
        $cario_no = $this->uri->segment(3, 0);	// 車格號碼
        $lpr = $this->uri->segment(4, 'NONE');	// 車號
        $in_time = urldecode($this->uri->segment(5, ''));	// 入場時間
        
       	$data = $this->carpark_model->correct_carin_lpr($cario_no, $lpr, $in_time);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);       
    } 
	
    */
    
    // 查詢月租資料
    public function member_query()
	{                                
        $data = $this->app_model()->member_query();
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
	}    
    
    // 進出場現況表
    public function cario_list()
	{                                
        $data = $this->app_model()->cario_list(); 
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
	}
      
    // 顯示圖檔(http://url/carpark.html/pics/lpr_ABY8873_O_0_0_C_20150919210022)
    public function pics()
	{                                                         
        readfile(CAR_PIC.$this->uri->segment(3).'/'.str_replace('/', '', $this->uri->segment(4)).'.jpg');
	}    
        
    // 車號入場查詢
    public function carin_lpr_query()
	{              
    	$lpr = $this->uri->segment(3);
        $data = $this->app_model()->carin_lpr_query($lpr); 
        echo json_encode($data);                       
	}  
               
    // 以時間查詢入場資訊
    public function carin_time_query()
	{              
    	$time_query = $this->input->post('time_query', true);
    	$minutes_range = $this->input->post('minutes_range', true);
        
        $data = $this->app_model()->carin_time_query($time_query, $minutes_range); 
        echo json_encode($data);                       
	}

	// 車辨失敗查詢
    public function carin_none_query()
	{              
    	$time_query = $this->input->post('time_query', true);
    	$hours_range = $this->input->post('hours_range', true);
        
        $data = $this->app_model()->carin_none_query($time_query, $hours_range); 
        echo json_encode($data);                          
	}  	
	
	// 查詢行動支付記錄
    public function tx_bill_query()
	{                                
        $data = $this->app_model()->tx_bill_query();
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
	} 
	
	// 查詢月租繳款機記錄
    public function tx_bill_ats_query()
	{                                
        $data = $this->app_model()->tx_bill_ats_query();
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
	} 
	
	// 查詢樓層在席群組
    public function pks_group_query()
	{                                
        $data = $this->app_model()->pks_group_query();
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
	} 
	
	// 微調剩餘車位數
    public function pks_availables_update()
	{                      
		$group_id = $this->uri->segment(3);		// id
        $value = $this->uri->segment(4, 0);		// value
		$station_no = $this->uri->segment(5);	// station_no
		
		// 初始 mqtt
		$this->init_mqtt();
		
        $data = $this->data_model()->pks_availables_update($group_id, $value, true, $station_no);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }

	// 進出場觸發 888 呼叫
	/*
	public function sync_888()
	{
		$io = $this->uri->segment(3);	// CI, CO, MI, MO
		
		if(empty($io))
		{
			echo 'io_not_found';
			exit;
		}
			
		$parms = array('io' => $io, 'etag' => __FUNCTION__, 'lpr' => __FUNCTION__);
		echo $this->sync_data_model->sync_888($parms);
		exit;
	}
	*/
	
	
	// 博辰測試用
	// http://localhost/carpark.html/test_8068_002/APK7310/0.199
	public function test_8068_002()
	{
		$lpr_in = $this->uri->segment(3);
		
		$seqno = '00001';
		$cmd = '002';
		$token = '000000000';
		$lpr = str_pad($lpr_in, 7, '%', STR_PAD_LEFT);
		$in_time = '2000/01/01 00:00:00';
		
		$error_str = '';
		$service_port = 8068;
		$address = empty($this->uri->segment(4)) ? "192.168.10.201" : "192.168." . $this->uri->segment(4);

		/* Create a TCP/IP socket. */
		$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if ($socket === false) {
			echo "socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n";
			$error_str .= "socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n";
		} else {
			echo "OK.\n";
		}

		echo "Attempting to connect to '{$address}' on port '{$service_port}'...";
		$result = socket_connect($socket, $address, $service_port);
		if ($result === false) {
			echo "socket_connect() failed.\nReason: ({$result}) " . socket_strerror(socket_last_error($socket)) . "\n";
			$error_str .= "socket_connect() failed: reason: " . socket_strerror(socket_last_error($socket)) . "\n";
		} else {
			echo "OK.\n";
		}
		
		$in = pack('Ca5Ca3Ca9Ca7Ca19', 0x1c, $seqno, 0x1c, $cmd, 0x1c, 
					$token, 0x1f, $lpr, 0x1f, $in_time
				);
		$out = '';
		echo "socket_write:";
		echo "{$in}<br/><br/>";
		
		if(!socket_write($socket, $in, strlen($in)))
		{
			echo('<p>Write failed</p>');
			$error_str .= "socket_write() failed: reason: " . socket_strerror(socket_last_error($socket)) . "\n";
		}
		echo "socket_write..OK..";

		echo "<br/><br/>socket_read:";
		$out = socket_read($socket, 2048) or die("Could not read server responsen");
		//while ($out = socket_read($socket, 2048)) {
		//	echo $out;
		//}
		echo "{$out}<br/><br/>";
		
		echo "Closing socket...";
		socket_close($socket);
		echo "OK.\n\n";

		trigger_error($error_str);
		exit;
	}
	
	
	// test
	public function test_cars2parktron()
	{
		$in_time = date('Y-m-d H:i:s', strtotime(" - 30 minutes"));
		$balance_time = date('Y-m-d H:i:s');
		$this->cars2parktron('TEST1234', $in_time, $balance_time, 0);
		echo 'ok';
	}
	
	// 傳送臨停資訊給博辰
    function cars2parktron($lpr, $in_time, $balance_time, $ivsno)
    {
		$lanes = array
		(
			0 => array ('ip' => '192.168.2.81'),		// 4號門 出, 中間	（傳送離場給博辰）
			1 => array ('ip' => ''),					// 4號門 出, 左		（傳送離場給博辰）
			2 => array ('ip' => ''),		// 4號門 入, 右
			3 => array ('ip' => ''),		// 4號門 入, 左
			4 => array ('ip' => ''),					// 4號門 出, 右		（傳送離場給博辰）
			5 => array ('ip' => ''),					// 3號門 出, 單		（傳送離場給博辰）
			6 => array ('ip' => ''),					// 3號門 出, 單		（傳送離場給博辰）
			7 => array ('ip' => ''),		// ??
			8 => array ('ip' => ''),		// 1號門 入, 左
			9 => array ('ip' => ''),		// 1號門 入, 右
			10 => array ('ip' => ''),		// 1號門 入, 單
			11 => array ('ip' => ''),		// 5號門 入
			12 => array ('ip' => '')					// 5號門 出			（傳送離場給博辰）
		);
		
		if(empty($lanes[$ivsno]['ip']))
		{
			trigger_error(__FUNCTION__ . "|$lpr, $in_time, $balance_time, $ivsno|skip..");
			return false;
		}

    	$param = array
			(
    		'PlateNo' => $lpr,                    // 車牌號碼
    		'EntryDateTime' => $in_time,          // 進場時間，格式為 “yyyy-MM-dd HH:mm:ss”，預設值 “2000-01-01 00:00:00”
    		'PrePaymentTime' => empty($balance_time) ? '2000-01-01 00:00:00' : $balance_time, // 最後繳費時間，格式為 “yyyy-MM-dd HH:mm:ss”，進場後還沒有任何繳費紀錄時填 “2000-01-01 00:00:00”
    		'AreaID' => '1'                       // 區域ID，同一停車場但收費費率不同時使用，例如:汽車->1，機車->2，此為例子，汽機車區域費率請依照現場狀況
    		);


		trigger_error('cars2parktron param:'.print_r($param, true). ", device ip: {$lanes[$ivsno]['ip']}");

		try{
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "http://{$lanes[$ivsno]['ip']}:10080/PlateExitCalculating/Service1.asmx/CarPaying");
			//curl_setopt($ch, CURLOPT_URL, "http://{$lanes[$ivsno]['ip']}:8080/datatrans/Service1.asmx/CarPaying");
			//curl_setopt($ch, CURLOPT_URL, 'http://www.parktron.com:8800/WebService/Service1.asmx/CarPaying'); // parktron webservice
			//curl_setopt($ch, CURLOPT_URL, 'http://192.168.0.80/Service1.asmx/CarPaying'); // parktron webservice
			curl_setopt($ch, CURLOPT_HEADER, FALSE);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,5);
			curl_setopt($ch, CURLOPT_TIMEOUT, 5); //timeout in seconds
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($param));
			$rs = curl_exec($ch);
			curl_close($ch);
			
		}catch (Exception $e){
			trigger_error('exception msg:'.$e->getMessage());
		}

		  /* Error code是由字串型態回傳，可用”:”拆出錯誤碼和訊息，可用”;”拆出多個錯誤
			錯誤碼如下
			-10000:POS路徑尚未設定                   -> 請通知我方工程師設定路徑
			-9999:車牌號碼錯誤                       -> 請查看貴公司自行送過來資料
			-9998:入場時間格式錯誤                   -> 請查看貴公司自行送過來資料
			-9997:前次繳費時間格式錯誤                -> 請查看貴公司自行送過來資料
			-9996:區域資料錯誤                       -> 請查看貴公司自行送過來資料
			-9995:資料寫入失敗，請通知客服人員處理     -> 常發生時請通知我方工程師更換儲存周邊
			0:成功
			多個錯誤碼的狀況如下
			-10000:POS路徑尚未設定;-9999:車牌號碼錯誤
		  */
		if (strpos($rs, '0:成功') !== false) {
			// do nothing
		trigger_error('cars2parktron success rs: ' . print_r($rs, true));

		} else {
			// TODO: 錯誤處理流程
			trigger_error('cars2parktron error rs: ' . print_r($rs, true));
		}
	}
	
}
