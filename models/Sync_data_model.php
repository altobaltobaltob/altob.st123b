<?php             
/*
file: Sync_data_model.php 資料同步相關
*/                   

define('SYNC_DATA_LOG_TITLE', 'sync://');	// LOG (sync)

define('SYNC_PKS_GROUP_ID_CI', 'C888');		// 汽車 888
define('SYNC_PKS_GROUP_ID_MI', 'M888');		// 機車	888

define('SYNC_API_URL', 'http://61.219.172.11:60123/admins_station.html/');

class Sync_data_model extends CI_Model 
{             
	var $vars = array();

	function __construct()
	{
		parent::__construct(); 
		$this->load->database(); 
    }   
     
	public function init($vars)
	{
		$this->vars = $vars;
    } 
	
	// 送出至message queue(目前用mqtt)
	public function mq_send($topic, $msg)
	{
		$this->vars['mqtt']->publish($topic, $msg, 0);
    	trigger_error("mqtt:{$topic}|{$msg}");
		usleep(100000); // delay 0.1 sec (避免漏訊號)
    }
	
	// ------------------------------------------------
	//
	// 博辰 (START)
	//
	// ------------------------------------------------
	
	// 博辰 888 同步
	public function sync_parktron_888($parktron_result)
	{
		if(empty($parktron_result))
			return 'empty';
		
		// 解析資料
		$pks_groups_arr = array();
		
		foreach($parktron_result as $content_result_list) 
		{
			foreach ($content_result_list as $item_result_list)
			{
				$area_id = 0;
				$space_count = 0;
				$parking_count = 0;
				$blanking_count = 0;
					
				foreach ($item_result_list as $key => $value)
				{
					switch($key)
					{
						case 'areaId': $area_id = $value; break;
						case 'spaceCount': $space_count = $value; break;
						case 'parkingCount': $parking_count = $value; break;
						case 'blankingCount': $blanking_count = $value; break;
						default: trigger_error(__FUNCTION__ . "..unknown..{$key}|{$value}..");
					}
				}
					
				if($area_id > 0)
				{
					//trigger_error(__FUNCTION__ . "..$area_id, $space_count, $parking_count, $blanking_count..");
					$pks_groups_arr["P{$area_id}"] = array('tot' => $space_count, 'parked' => $parking_count, 'availables' => $blanking_count);
				}
			}
		}
		
		if(empty($pks_groups_arr))
		{
			trigger_error(__FUNCTION__ . "..empty pks_groups_arr..");
			return 'empty';	
		}
		
		// 取得場站編號
		$station_setting = $this->station_setting_query();
		$station_no_arr = explode(SYNC_DELIMITER_ST_NO, $station_setting['station_no']);
		$station_no = $station_no_arr[0];
		
		trigger_error(__FUNCTION__ . "..$station_no.." . print_r($pks_groups_arr, true));
		
		// 更新博辰資料
		$this->db->trans_start();
		foreach($pks_groups_arr as $key => $data)
		{
			$where_group_arr = array('group_id' => $key, 'station_no' => $station_no);
			$rows = $this->db->select('group_id')->from('pks_groups')
                ->where($where_group_arr)
                ->limit(1)
                ->get()  
                ->row_array(); 
			
			if(!isset($rows['group_id']))
			{
				// 不存在就新增
				$data['station_no'] = $station_no;
				$data['group_id'] = $key;
				$data['floors'] = $key;
				$data['group_name'] = '博辰 ' . $key;
				$this->db->insert('pks_groups', $data);
				trigger_error(__FUNCTION__ . '..insert pks_groups..'. print_r($data, true));
			}
			else
			{
				$data['renum'] = 0;
				$this->db->where($where_group_arr)->update('pks_groups', $data);	
			}
		}
		$this->db->trans_complete();

		return 'ok';
	}
	
	// ------------------------------------------------
	//
	// 在席系統同步 (START)
	//
	// ------------------------------------------------
	
	// 強制更新顯示
	public function force_sync_888($station_no, $group_id, $value)
	{
		$where_group_arr = array('group_id' => $group_id, 'station_no' => $station_no);
		
		$rows = $this->db->select('renum, parked, tot, availables')
        		->from('pks_groups')
                ->where($where_group_arr)	     
                ->limit(1)
                ->get()  
                ->row_array(); 
		
		// force upd
		$new_renum = 0;
		$new_ava = $value;
		$new_parked = $rows['tot'] - $new_ava;
		
		trigger_error(__FUNCTION__ . "..{$rows['renum']}|{$rows['parked']}|{$rows['availables']}|{$rows['tot']}..to..{$new_renum}|{$new_parked}|{$new_ava}|{$rows['tot']}");
		
		$this->db->where($where_group_arr)->update('pks_groups', array('renum' => $new_renum, 'parked' => $new_parked, 'availables' => $new_ava));
		
		return $this->db->affected_rows();
	}
    
	// 同步 888
	public function sync_888($parms)
	{
		$result = -888;
		
		if(!isset($parms['lpr']) || !isset($parms['etag']) || !isset($parms['io']) || 
			($parms['lpr'] == 'NONE' && $parms['etag'] == 'NONE')
		)
		{
			trigger_error(__FUNCTION__ . '..NONE..'. print_r($parms, true));
			return $result;	
		}
		
		trigger_error(__FUNCTION__ . "..{$parms['sno']}|{$parms['io']}|{$parms['lpr']}|{$parms['etag']}..");
		
		// [START] 擋相同車號進出
		$skip_or_not = false;
		$new_cars_tmp = array
		(
			'timestamp' => time(),
			'sno_io' => $parms['sno'] . $parms['io'],
			'lpr' => $parms['lpr'],
			'etag' => $parms['etag']
		);
		$cars_tmp_log_arr = $this->vars['mcache']->get(MCACHE_SYNC_888_TMP_LOG);
		if(empty($cars_tmp_log_arr))
		{
			$cars_tmp_log_arr = array();
		}
		
		if(isset($cars_tmp_log_arr[$new_cars_tmp['sno_io']]))
		{
			$last_cars_tmp = $cars_tmp_log_arr[$new_cars_tmp['sno_io']];
			
			// 判斷是否跳過 (記錄於一小時內, 相同場站進出 lpr 或 etag)
			if(	( 	($last_cars_tmp['lpr'] == $new_cars_tmp['lpr'] && $last_cars_tmp['lpr'] != 'NONE')	|| 
					($last_cars_tmp['etag'] == $new_cars_tmp['etag'] && $last_cars_tmp['etag'] != 'NONE')	)	&& $last_cars_tmp['timestamp'] > $new_cars_tmp['timestamp'] - 3600
			)
				$skip_or_not = true;
		}
		
		// 更新
		$cars_tmp_log_arr[$new_cars_tmp['sno_io']] = $new_cars_tmp;
		$this->vars['mcache']->set(MCACHE_SYNC_888_TMP_LOG, $cars_tmp_log_arr);
		trigger_error(__FUNCTION__ . '..upd ' . MCACHE_SYNC_888_TMP_LOG . " |s:{$skip_or_not}|" . print_r($cars_tmp_log_arr, true));	
			
		// 跳過
		if($skip_or_not)
		{
			trigger_error(__FUNCTION__ . '..skip..');	
			return false;
		}
		// [END] 擋相同車號進出
		
		switch($parms['io'])
        {
			// 入場
          	case 'CI':
				$result = $this->pks_availables_update(SYNC_PKS_GROUP_ID_CI, -1, false, $parms['sno']);
				break;
			case 'MI':
				$result = $this->pks_availables_update(SYNC_PKS_GROUP_ID_MI, -1, false, $parms['sno']);
				break;
			// 出場
            case 'CO':
				$result = $this->pks_availables_update(SYNC_PKS_GROUP_ID_CI, 1, false, $parms['sno']);
				break;
			case 'MO':
				$result = $this->pks_availables_update(SYNC_PKS_GROUP_ID_MI, 1, false, $parms['sno']);
				break;
		}
		
		return $result;
	}
	
	// 微調剩餘車位數
    public function pks_availables_update($group_id, $value, $is_renum=true, $station_no=STATION_NO)
	{   
		$where_group_arr = array('group_id' => $group_id, 'station_no' => $station_no);
		
		$rows = $this->db->select('renum, parked, tot')
        		->from('pks_groups')
                ->where($where_group_arr)	     
                ->limit(1)
                ->get()  
                ->row_array(); 
		
		$renum = $rows['renum'];
		$parked = $rows['parked'];
		$tot = $rows['tot'];
		
		trigger_error("更新車位數|{$group_id}|{$value}|{$is_renum}|".print_r($rows, true));
		
		if($is_renum)
		{	
			// 一般微調
			if($value == 0)
			{
				$this->db->where($where_group_arr)
						->update('pks_groups', array('renum' => 0, 'parked' => 0, 'availables' => $tot));
				trigger_error(__FUNCTION__ . '..reset everything and exit..');
				return true;	// 中斷
			}
			else if($value >= 1)
			{
				// 增加
				$renum = $renum + 1;
			}
			else
			{
				// 減少
				$renum = $renum - 1;
			}
			
			$availables = $tot - $parked + $renum;
			
			// 防止負值
			if($availables <= 0)
			{
				$availables = 0;
				$parked = $tot;
				$renum = 0;
				trigger_error(__FUNCTION__ . '..ava < 0..auto set (ava = 0, parked = tot, renum = 0)..');
			}
			else if($availables >= $tot)
			{
				$availables = $tot;
				$parked = 0;
				$renum = 0;
				trigger_error(__FUNCTION__ . '..ava > tot..auto set (ava = tot, parked = 0, renum = 0)..');
			}
					
			// 更新 db
			$this->db->where($where_group_arr)
				->update('pks_groups', array('parked' => $parked, 'availables' => $availables, 'renum' => $renum));
		}
		else
		{	
			// 進出場
			if($value == 0)
			{
				trigger_error(__FUNCTION__ . '..??? exit..');
				return true;	// 中斷
			}
			else if($value >= 1)
			{
				// 已停車位數減少, 空車位數增加
				$parked = $parked - 1;
			}
			else
			{
				// 已停車位數增加, 空車位數減少
				$parked = $parked + 1;	
			}
			
			/*
			// 防止負值
			if($parked < 0)
			{
				$parked = 0;
				$renum = 0; 			// 自動重設 renum
				trigger_error(__FUNCTION__ . '..parked < 0..set (parked = 0, renum = 0)..');
			}
			else if($parked >= $tot)
			{
				$parked = $tot;
				$renum = 0; 			// 自動重設 renum
				trigger_error(__FUNCTION__ . '..parked > tot.. = tot..set (parked = tot, renum = 0)..');
			}
			*/
			
			$availables = $tot - $parked + $renum;
			
			// 防止負值
			if($availables <= 0)
			{
				$availables = 0;
				$parked = $tot;
				$renum = 0;
				trigger_error(__FUNCTION__ . '..ava < 0..auto set (ava = 0, parked = tot, renum = 0)..');
			}
			else if($availables >= $tot)
			{
				$availables = $tot;
				$parked = 0;
				$renum = 0;
				trigger_error(__FUNCTION__ . '..ava > tot..auto set (ava = tot, parked = 0, renum = 0)..');
			}
			
			// 更新 db
			$this->db->where($where_group_arr)
				->update('pks_groups', array('parked' => $parked, 'availables' => $availables, 'renum' => $renum));
		}
		
		// 送出即時訊號
		if($group_id == SYNC_PKS_GROUP_ID_CI)
		{
			//$this->mq_send(MQ_TOPIC_ALTOB, MQ_ALTOB_888 . ",1,{$availables}" . MQ_ALTOB_888_END_TAG); // 送出 888 (汽車)
		}
		else if($group_id == SYNC_PKS_GROUP_ID_MI)
		{
			//$this->mq_send(MQ_TOPIC_ALTOB, MQ_ALTOB_888 . ",2,{$availables}" . MQ_ALTOB_888_END_TAG); // 送出 888 (機車)
		}
		else
		{
			$availables_pad = str_pad($availables, 3, '0', STR_PAD_LEFT); 			// 補零
			$this->mq_send(MQ_TOPIC_SUBLEVEL, "{$group_id},{$availables_pad}"); 	// 送出剩餘車位數給字幕機
		}
		
		return $this->db->affected_rows();
    }
	
	// ------------------------------------------------
	//
	// 在席系統同步 (END)
	//
	// ------------------------------------------------
	
	// ------------------------------------------------
	//
	// 中控接收端 (START)
	//
	// ------------------------------------------------
	
	// 同步歐pa卡 （功能: 歐pa卡同步）
	public function sync_allpa_user($info_arr=array('station_no_arr' => STATION_NO))
	{
		require_once(ALTOB_SYNC_FILE);
		$sync_agent = new AltobSyncAgent();
		$data = $sync_agent->query_allpa_users();
		$data_allpa_user_arr = json_decode($data, true);
		
		if (sizeof($data_allpa_user_arr) <= 0)
		{
			trigger_error(SYNC_DATA_LOG_TITLE . '.. allpa_user empty ..');	// 忽略完全沒會員的情況
			return 'empty';
		}
		
		$this->db->trans_start();
		// 清空
		$this->db->empty_table('allpa_user');
		// 建立 members
		$this->db->insert_batch('allpa_user', $data_allpa_user_arr);
		
		$this->db->trans_complete();
		if ($this->db->trans_status() === FALSE)
		{
			trigger_error(SYNC_DATA_LOG_TITLE . '.. sync allpa_user fail ..'. '| last_query: ' . $this->db->last_query());
			return 'fail';
		}
		
		trigger_error(SYNC_DATA_LOG_TITLE . '.. sync allpa_user completed ..');
		return 'ok';
	}
	
	// 同步場站會員 （功能: 會員同步）
	public function sync_members($info_arr=array('station_no_arr' => STATION_NO))
	{
		// 查現況
		$parms['station_no_arr'] = $info_arr['station_no_arr'];
		
		trigger_error(SYNC_DATA_LOG_TITLE . '..'. __FUNCTION__ . '..parms..' . print_r($parms, true));
		
		try{
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, SYNC_API_URL . 'member_query_all_in_one');
			curl_setopt($ch, CURLOPT_HEADER, FALSE);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,10);
			curl_setopt($ch, CURLOPT_TIMEOUT, 10); //timeout in seconds
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parms));
			$result = curl_exec($ch);
			curl_close($ch);

		}catch (Exception $e){
			trigger_error('error msg:'.$e->getMessage());
			trigger_error(SYNC_DATA_LOG_TITLE . $e->getMessage());
		}
		
		// 取得資料
		$result_arr = json_decode($result, true);
		$data_member_arr = array();
		$data_car_arr = array();
		
		if (sizeof($result_arr) <= 0)
		{
			trigger_error(SYNC_DATA_LOG_TITLE . '.. empty ..');	// 忽略完全沒會員的情況
			return 'empty';
		}
		
		/*
		// 每一目標場站, 都建立一份會員清單 20171211 upd
		if(isset($info_arr['current_station_no_arr']))
		{
			$station_no_arr = explode(SYNC_DELIMITER_ST_NO, $info_arr['current_station_no_arr']);	
			
			foreach($station_no_arr as $station_no)
			{
				foreach($result_arr as $data)
				{
					// create member
					$data_member = array
					(
						'member_no' => $data['member_no'],                    
						'station_no' => $station_no,
						'member_name' => $data['member_name'],                    
						'member_attr' => $data['member_attr'],                    
						'member_type' => $data['member_type'],    
						'contract_no' => $data['contract_no'],   
						'mobile_no' => $data['mobile_no'],   
						'lpr' => $data['lpr'],                   
						'locked' => $data['locked'],               
						'etag' => $data['etag'],                   
						'fee_period' => $data['fee_period'],           							
						'start_date' => $data['start_date'],                    
						'end_date' => $data['end_date'],
						'remarks' => $data['remarks'],
						'park_time' => $data['park_time'],
						'valid_time' => $data['valid_time']
					);
					array_push($data_member_arr, $data_member);
						
					// create member_car
					$data_car = array
					(
						'station_no' => $station_no,
						'member_no' => $data['member_no'],                    
						'lpr' => $data['lpr'],                    
						'lpr_correct' => $data['lpr'],                    
						'etag' => $data['etag'],                    
						'start_time' => $data['start_date'],                    
						'end_time' => $data['end_date']
					);
					array_push($data_car_arr, $data_car);
				}
			}
		}
		*/
		
				// 暫時版
				foreach($result_arr as $data)
				{
					// create member
					$data_member = array
					(
						'member_no' => $data['member_no'],                    
						'station_no' => $data['station_no'], 
						'member_name' => $data['member_name'],                    
						'member_attr' => $data['member_attr'],                    
						'member_type' => $data['member_type'],    
						'contract_no' => $data['contract_no'],   
						'mobile_no' => $data['mobile_no'],   
						'lpr' => $data['lpr'],                   
						'locked' => $data['locked'],               
						'etag' => $data['etag'],                   
						'fee_period' => $data['fee_period'],           							
						'start_date' => $data['start_date'],                    
						'end_date' => $data['end_date'],
						'remarks' => $data['remarks'],
						'park_time' => $data['park_time'],
						'valid_time' => $data['valid_time']
					);
					array_push($data_member_arr, $data_member);
						
					// create member_car
					$data_car = array
					(
						'station_no' => $data['station_no'], 
						'member_no' => $data['member_no'],                    
						'lpr' => $data['lpr'],                    
						'lpr_correct' => $data['lpr'],                    
						'etag' => $data['etag'],                    
						'start_time' => $data['start_date'],                    
						'end_time' => $data['end_date']
					);
					array_push($data_car_arr, $data_car);
				}
		
		//trigger_error(SYNC_DATA_LOG_TITLE . '.. test ..' . print_r($data_member_arr, true));
		
		$this->db->trans_start();
		// 清空
		$this->db->empty_table('members');
		$this->db->empty_table('member_car');
		// 建立 members
		$this->db->insert_batch('members', $data_member_arr);
		// 建立 member_car
		$this->db->insert_batch('member_car', $data_car_arr);
		
		$this->db->trans_complete();
		if ($this->db->trans_status() === FALSE)
		{
			trigger_error(SYNC_DATA_LOG_TITLE . '.. sync fail ..'. '| last_query: ' . $this->db->last_query());
			return 'fail';
		}
		
		trigger_error(SYNC_DATA_LOG_TITLE . '.. sync completed ..');
		return 'ok';
	}
	
	// 同步車牌更換 （功能: 換車牌同步）
	public function sync_switch_lpr($switch_lpr_arr)
	{
		trigger_error( __FUNCTION__ . '..' . print_r($switch_lpr_arr, true));
		
		$this->db->trans_start();
		
		foreach($switch_lpr_arr as $data)
		{
			$station_no = $data['station_no'];
			$member_no = $data['member_no'];
			$old_lpr = $data['old_lpr'];
			$new_lpr = $data['new_lpr'];
			
			
			$new_data = array('lpr' => $new_lpr, 'lpr_correct' => $new_lpr, 'member_no' => $member_no);	
			$this->db->update('etag_lpr', $new_data, array('lpr_correct' => $old_lpr));
				
			$affect_rows = $this->db->affected_rows();
			trigger_error(SYNC_DATA_LOG_TITLE . "換車牌更新 etag_lpr 共[{$affect_rows}]筆..".print_r($data, true));
			
			/*
			if($station_no == STATION_NO)
			{
				$new_data = array('lpr' => $new_lpr, 'lpr_correct' => $new_lpr, 'member_no' => $member_no);	
				$this->db->update('etag_lpr', $new_data, array('lpr_correct' => $old_lpr));
				
				$affect_rows = $this->db->affected_rows();
				trigger_error(SYNC_DATA_LOG_TITLE . "換車牌更新 etag_lpr 共[{$affect_rows}]筆..".print_r($data, true));
			}
			else
			{
				trigger_error(SYNC_DATA_LOG_TITLE . __FUNCTION__ . "..資料異常..".print_r($data, true));
			}
			*/
		}
		
		$this->db->trans_complete();
		if ($this->db->trans_status() === FALSE)
		{
			trigger_error(SYNC_DATA_LOG_TITLE . '.. sync fail ..'. '| last_query: ' . $this->db->last_query());
			return 'fail';
		}
		
		trigger_error(SYNC_DATA_LOG_TITLE . '.. sync completed ..');
		return 'ok';
	}
	
	// 同步場站費率
	public function sync_price_plan($info_arr=array('station_no_arr' => STATION_NO))
	{
		try{
			// 查另一台主機
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, SYNC_API_URL . 'price_plan_query_all_in_one');
			curl_setopt($ch, CURLOPT_HEADER, FALSE);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,5);
			curl_setopt($ch, CURLOPT_TIMEOUT, 5); //timeout in seconds
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($info_arr));
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
	
	// 取得最新未結清 （功能: 行動支付）
	public function get_last_unbalanced_cario($lpr, $station_no) 
	{                    
		$sql = "SELECT station_no, cario_no, in_time, pay_time, out_time, out_before_time, member_no
					FROM cario 
				WHERE 
					station_no = '{$station_no}' AND obj_id = '{$lpr}' AND finished = 0 AND err = 0
				ORDER BY cario_no DESC
				LIMIT 1
				";
		
		$results = $this->db->query($sql)->result_array();
		
		if(isset($results[0]))
			return $results[0]; 	
		return false;
	}
	
	// 同步車位現況 （功能: 888 同步, 在席同步）
	public function sync_pks_groups_reload($station_setting)
	{
		$info = array();
		$station_no_arr = explode(SYNC_DELIMITER_ST_NO, $station_setting['station_no']);
		$station_name_arr = explode(SYNC_DELIMITER_ST_NAME, $station_setting['station_name']);
		$station_888_arr = explode(SYNC_DELIMITER_ST_INFO, $station_setting['station_888']);
		foreach($station_no_arr as $key => $station_no)
		{
			if($station_888_arr[$key] == 1)			// 啟用
				array_push($info, array('station_no' => $station_no_arr[$key], 'station_name' => $station_name_arr[$key]));
			else if($station_888_arr[$key] == 4)	// 關閉
			{
				// 清除	888
				/*
				$clean_888_arr = array(
						'station_no' => $station_no_arr[$key], 
						'group_id in ' => "('". SYNC_PKS_GROUP_ID_CI . "', '". SYNC_PKS_GROUP_ID_MI . "')"
					);
					*/
				$this->db
					->where('station_no', $station_no_arr[$key])
					->where_in('group_id', array(SYNC_PKS_GROUP_ID_CI, SYNC_PKS_GROUP_ID_MI))
					->delete('pks_groups');
				trigger_error(__FUNCTION__ . '..clean pks_groups 888..');
			}
			else
			{
				trigger_error(__FUNCTION__ . '..unknown station_888:' . $station_888_arr[$key]);
			}
		}
		
		if(empty($info))
			return 'none';
		
		return $this->sync_pks_groups($info, true);
	}
	
	// 同步車位現況 （功能: 888 同步, 在席同步）
	public function sync_pks_groups($info_arr=array(array('station_no' => STATION_NO, 'station_name' => STATION_NAME)), $reload=false) 
	{                    
		if($reload)
		{
			// 確認應該要有的 pks_groups
			$pks_groups_arr = array();
			$pks_groups_name_arr = array();
			foreach($info_arr as $data)
			{
				$pks_key = $data['station_no'] . SYNC_DELIMITER_ST_INFO . SYNC_PKS_GROUP_ID_CI;
				array_push($pks_groups_arr, $pks_key);	// 汽車 888
				
				$pks_key = $data['station_no'] . SYNC_DELIMITER_ST_INFO . SYNC_PKS_GROUP_ID_MI;
				array_push($pks_groups_arr, $pks_key);	// 機車 888
				
				$pks_groups_name_arr[$data['station_no']] = $data['station_name']. '(888)';	// 群組名稱
			}
			
			// 過濾已存在的部份
			$sql = "SELECT station_no, group_id FROM pks_groups";
			$current_pks_group = $this->db->query($sql)->result_array(); 
			foreach($current_pks_group as $data)
			{
				$pks_key = $data['station_no'] . SYNC_DELIMITER_ST_INFO . $data['group_id'];
				$key = array_search($pks_key, $pks_groups_arr);
				if($key !== false)
					unset($pks_groups_arr[$key]);
			}
			
			// 建立缺少的部份
			if(!empty($pks_groups_arr))
			{
				// [A.開始]
				$this->db->trans_start();
				
				foreach($pks_groups_arr as $new_data)
				{
					$pks_info = explode(SYNC_DELIMITER_ST_INFO, $new_data);
					$new_pks_groups_data = array(
								'station_no' => $pks_info[0], 
								'group_id' => $pks_info[1], 
								'tot' => 100, 			// 預設車位數
								'availables' => 100,	// 預設車位數
								'floors' => 'TOT',
								'group_name' => $pks_groups_name_arr[$pks_info[0]]
							);
					$this->db->insert('pks_groups', $new_pks_groups_data);
					trigger_error(__FUNCTION__ . '..insert pks_groups..'. print_r($new_pks_groups_data, true));
				}
				
				// [C.完成]
				$this->db->trans_complete();		
				if ($this->db->trans_status() === FALSE)
				{
					trigger_error(__FUNCTION__ . '..trans_error..' . '| last_query: ' . $this->db->last_query());
					return 'fail';	 		// 中斷
				}	
			}
		}
	
		$sql = "SELECT pks_groups.station_no, 
					pks_groups.group_name as group_name, pks_groups.tot as tot, pks_groups.parked as parked, pks_groups.availables as availables, pks_groups.group_id as group_id, pks_groups.renum as renum 
				FROM pks_groups 
				ORDER BY pks_groups.group_id DESC";
        $pks_group_query_data = $this->db->query($sql)->result_array(); 
		//trigger_error(__FUNCTION__ . '..sync..' . print_r($pks_group_query_data, true));
		
		// 同步
		require_once(ALTOB_SYNC_FILE);
		$sync_agent = new AltobSyncAgent();
		$sync_agent->init(STATION_NO);	// 已帶上的資料場站編號為主
		$sync_result = $sync_agent->upd_pks_groups(json_encode($pks_group_query_data, JSON_UNESCAPED_UNICODE));
		trigger_error( SYNC_DATA_LOG_TITLE . '..'. __FUNCTION__ . "..upd_pks_groups.." .  $sync_result);
		return $sync_result;
    }
	
	// 重新載入場站設定
	public function reload_station_setting()
	{
		$port_info = (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] != 60123) ? '/' . $_SERVER['SERVER_PORT'] : '';
		$reload_url = SYNC_API_URL . 'station_setting_query' . $port_info;
		try{
			// 查現況
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $reload_url);
			curl_setopt($ch, CURLOPT_HEADER, FALSE);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,10);
			curl_setopt($ch, CURLOPT_TIMEOUT, 10); //timeout in seconds
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array()));
			$return_result = curl_exec($ch);
			curl_close($ch);
			
			trigger_error(__FUNCTION__ . "..curl {$reload_url}.." . print_r($return_result, true));

		}catch (Exception $e){
			trigger_error('error msg:'.$e->getMessage());
		}
			
		$station_setting_result = json_decode($return_result, true);
		
		if(!isset($station_setting_result['results']) || sizeof($station_setting_result['results']) <= 0)
		{
			trigger_error(__FUNCTION__ . '..fail..' . print_r($return_result, true));
			return 'fail';
		}
		
		$station_ip_str	= $station_setting_result['station_ip'];	// 場站目前對外IP
		
		if(empty($port_info))
			$station_port_str = STATION_LOCAL_PORT;
		else
			$station_port_str = substr($port_info, 1);				// 場站目前對外PORT
		
		$station_setting_arr = $station_setting_result['results'];
		$station_no_arr = array();
		$station_no_list_arr = array();
		$station_name_arr = array();
		$station_888_arr = array();
		
		$settings = array();
		foreach($station_setting_arr as $data)
		{
			$station_no = $data['station_no'];
			
			array_push($station_no_arr, $station_no);
			array_push($station_name_arr, $data['short_name']);
			array_push($station_888_arr, $data['station_888']);
			
			// 若有設定多個場站, 已多場站設定為主
			$tmp_station_no_list_arr = explode(SYNC_DELIMITER_ST_NO, $data['station_no_list']);
			
			foreach($tmp_station_no_list_arr as $key => $station_no_list_value)
			{
				if(intval($station_no_list_value) > 0)
				{	
					if(empty($station_no_list_arr))
						array_push($station_no_list_arr, $station_no_list_value);
						
					else if(!array_search($station_no_list_value, $station_no_list_arr))
						array_push($station_no_list_arr, $station_no_list_value);
				}
			}
			
			// 若無設定直接放目前場站
			if(empty($station_no_list_arr))
				array_push($station_no_list_arr, $station_no);
			
			if(!isset($settings[$station_no]))
			{
				$settings[$station_no] = array();
			}
			$settings[$station_no]['station_888'] = empty($data['station_888']) ? 1 : $data['station_888'];
			$settings[$station_no]['mqtt_ip'] = empty($data['mqtt_ip']) ? MQ_HOST : $data['mqtt_ip'];
			$settings[$station_no]['mqtt_port'] = empty($data['mqtt_port']) ? MQ_PORT : $data['mqtt_port'];
			$settings[$station_no]['local_ip'] = empty($data['local_ip']) ? STATION_LOCAL_IP : $data['local_ip'];
		}
		
		$station_no_str = implode(SYNC_DELIMITER_ST_NO, $station_no_arr);			// 取值時會用到
		$station_no_list_str = implode(SYNC_DELIMITER_ST_NO, $station_no_list_arr);	// 會員資料同步相關
		$station_name_str = implode(SYNC_DELIMITER_ST_NAME, $station_name_arr);		// 純顯示
		$station_888_str = implode(SYNC_DELIMITER_ST_INFO, $station_888_arr);		// 場站	888 設定
		
		// 設定到 mcache
		$this->vars['mcache']->set(MCACHE_STATION_NO_STR, $station_no_str);
		$this->vars['mcache']->set(MCACHE_STATION_NO_LIST_STR, $station_no_list_str);
		$this->vars['mcache']->set(MCACHE_STATION_NAME_STR, $station_name_str);
		$this->vars['mcache']->set(MCACHE_STATION_IP_STR, $station_ip_str);
		$this->vars['mcache']->set(MCACHE_STATION_PORT_STR, $station_port_str);
		$this->vars['mcache']->set(MCACHE_STATION_888_STR, $station_888_str);
		$this->vars['mcache']->set(MCACHE_STATION_SETTINGS, $settings);
		return 'ok';
	}
	
	// 取得目前場站設定
	public function station_setting_query($reload=false)
	{
		$station_no_str = $this->vars['mcache']->get(MCACHE_STATION_NO_STR);
		$station_no_list_str = $this->vars['mcache']->get(MCACHE_STATION_NO_LIST_STR);
		$station_name_str = $this->vars['mcache']->get(MCACHE_STATION_NAME_STR);
		$station_ip_str = $this->vars['mcache']->get(MCACHE_STATION_IP_STR);
		$station_port_str = $this->vars['mcache']->get(MCACHE_STATION_PORT_STR);
		$station_888_str = $this->vars['mcache']->get(MCACHE_STATION_888_STR);
		$settings = $this->vars['mcache']->get(MCACHE_STATION_SETTINGS);
	
		if(	$reload	|| 
			empty($station_no_list_str)	|| 	
			empty($station_no_str) 		|| 	empty($station_name_str)	|| 
			empty($station_ip_str) 		|| 	empty($station_port_str)			||	
			empty($station_888_str)		||
			empty($settings)
		)
		{
			$result = $this->reload_station_setting();
			
			if($result == 'ok')
			{
				$station_no_str = $this->vars['mcache']->get(MCACHE_STATION_NO_STR);
				$station_no_list_str = $this->vars['mcache']->get(MCACHE_STATION_NO_LIST_STR);
				$station_name_str = $this->vars['mcache']->get(MCACHE_STATION_NAME_STR);
				$station_ip_str = $this->vars['mcache']->get(MCACHE_STATION_IP_STR);
				$station_port_str = $this->vars['mcache']->get(MCACHE_STATION_PORT_STR);
				$station_888_str = $this->vars['mcache']->get(MCACHE_STATION_888_STR);
				$settings = $this->vars['mcache']->get(MCACHE_STATION_SETTINGS);
			}
			else
			{
				/*
				$station_setting = array();
				$station_setting['station_no'] = STATION_NO;
				$station_setting['station_name'] = STATION_NAME;
				$station_setting['station_ip'] = STATION_IP;
				return $station_setting;
				*/
				//return false;
				
				// 離線直接拿預設場站
				$station_setting = array();
				$station_setting['station_no'] = STATION_NO;
				$station_setting['station_no_list'] = STATION_NO;
				//$station_setting['station_no_list'] = STATION_NO . SYNC_DELIMITER_ST_NO . '40702';
				$station_setting['station_name'] = STATION_NAME;
				$station_setting['station_ip'] = STATION_LOCAL_IP;
				$station_setting['station_port'] = STATION_LOCAL_PORT;
				return $station_setting;
			}
		}
		
		// 第一個場站編號
		$station_no_arr = explode(SYNC_DELIMITER_ST_NO, $station_no_str);
		$first_station_no = $station_no_arr[0];
		
		$station_setting = array();
		$station_setting['station_no'] = $station_no_str;
		$station_setting['station_no_list'] = $station_no_list_str;
		$station_setting['station_name'] = $station_name_str;
		$station_setting['station_ip'] = $station_ip_str;
		$station_setting['station_port'] = $station_port_str;
		$station_setting['station_888'] = $station_888_str;
		$station_setting['settings'] = $settings;
		$station_setting['mqtt_ip']	= $settings[$first_station_no]['mqtt_ip'];		
		$station_setting['mqtt_port'] = $settings[$first_station_no]['mqtt_port'];
		return $station_setting;
	}
	
	// ------------------------------------------------
	//
	// 中控接收端 (END)
	//
	// ------------------------------------------------
	

	
	
	
	// ------------------------------------------------
	//
	// 其它 (START)
	//
	// ------------------------------------------------
	
	// 手動新增入場資料
	public function gen_carin($parms)
	{
		$in_time = date('Y-m-d H:i:s');
		
		$data = array(
					'station_no' => $parms['sno'],
					'obj_type' => 1,
					'obj_id' => $parms['lpr'],
					'etag' => '',
					'in_out' => $parms['io'],
					'finished' => 0,
					'in_time' => $in_time,
					'in_lane' => $parms['ivsno'],
					'out_before_time' => $in_time
				);
		$this->db->insert('cario', $data);
		trigger_error("新增入場資料:".print_r($parms, true));
					
		require_once(ALTOB_SYNC_FILE);
		// 傳送進場記錄
		$sync_agent = new AltobSyncAgent();
		$sync_agent->init($parms['sno'], $in_time);
		$sync_agent->cario_no = $this->db->insert_id();		// 進出編號
		$sync_result = $sync_agent->sync_st_in($parms);
		trigger_error( SYNC_DATA_LOG_TITLE . '..'. __FUNCTION__ . "..sync_st_in.." .  $sync_result);
	}
	
	// ------------------------------------------------
	//
	// 其它 (END)
	//
	// ------------------------------------------------
	
}
