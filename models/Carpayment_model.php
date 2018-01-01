<?php             
/*
file: carpayment_model.php
*/                   
require_once(ALTOB_SYNC_FILE) ;

class Carpayment_model extends CI_Model 
{             
    var $vars = array(); 
    
    var $io_name = array('I' => '車入', 'O' => '車出', 'MI' => '機入', 'MO' => '機出', 'FI' => '樓入', 'FO' => '樓出');  
    var $now_str;
    
	function __construct()
	{
		parent::__construct(); 
		$this->load->database();
        $this->now_str = date('Y-m-d H:i:s'); 
    }   
     
	public function init($vars)
	{                        
    	$this->vars = $vars;
    } 
	
	////////////////////////////////////////
	//
	// 付款後, 付款資訊建檔
	//
	////////////////////////////////////////
	
	// [付款完成] 付款後續流程
	function payed_finished($cario_no, $lpr, $etag, $in_time)
	{
		$LOG_TAG = 'set_payed://';
		$LOG_TAG_FATAL = 'set_payed_fatal://';
		trigger_error(__FUNCTION__ . "|$cario_no 付款完成|$lpr, $etag, $in_time|");
		
		$trim_finished_cario_no_arr = array();	// 需處理的進場索引
		
		$in_time_value = strtotime($in_time);
		$in_time_1 = date('Y-m-d H:i:s', $in_time_value + 1);	// +1 sec
		$in_time_2 = date('Y-m-d H:i:s', $in_time_value - 1);	// -1 sec
		$in_time_3 = date('Y-m-d H:i:s', $in_time_value + 2);	// +2 sec
		$in_time_4 = date('Y-m-d H:i:s', $in_time_value - 2);	// -2 sec
		
		// 挑出已付款入場記錄, 入場時間附近 2 秒內, 但尚未結清之入場記錄
		$sql = "SELECT cario_no, obj_id as lpr, etag, in_time
				FROM cario
				WHERE in_time in ('$in_time', '$in_time_1', '$in_time_2', '$in_time_3', '$in_time_4') 
					AND cario_no != $cario_no 
					AND finished = 0 
					AND payed = 0 
					AND err = 0
				";
		$in_time_retults = $this->db->query($sql)->result_array();
		
		if(!empty($in_time_retults) && count($in_time_retults) > 0)
		{
			$data = array();
			foreach ($in_time_retults as $idx => $rows) 
			{
				$result_cario_no = $rows['cario_no'];
				$result_lpr = $rows['lpr'];
				$result_etag = $rows['etag'];
				$result_in_time = $rows['in_time'];
				
				if(empty($result_lpr) || $result_lpr == 'NONE')
				{
					if(strlen($result_etag) > 20 && $result_etag == $etag)
					{
						trigger_error($LOG_TAG . "$cario_no, $lpr, $etag, $in_time|無車牌|ETAG吻合|$result_cario_no, $result_lpr, $result_etag, $result_in_time|待註銷|skip");
						//array_push($trim_finished_cario_no_arr, $result_cario_no);
					}
				}
				else
				{
					$levenshtein_value = levenshtein($result_lpr, $lpr);
					if(	$levenshtein_value == 0 || $levenshtein_value == 1)
					{
						trigger_error($LOG_TAG . "$cario_no, $lpr, $etag, $in_time|車牌 差0-1碼|$result_cario_no, $result_lpr, $result_etag, $result_in_time|待註銷");
						array_push($trim_finished_cario_no_arr, $result_cario_no);
					}
					else if($levenshtein_value == 2)
					{
						if(strlen($result_etag) > 20 && $result_etag == $etag)
						{
							trigger_error($LOG_TAG . "$cario_no, $lpr, $etag, $in_time|車牌 差2碼|ETAG吻合|$result_cario_no, $result_lpr, $result_etag, $result_in_time|待註銷|skip");
							//array_push($trim_finished_cario_no_arr, $result_cario_no);
						}
					}
					else
					{
						if(strlen($result_etag) > 20 && $result_etag == $etag)
						{
							trigger_error($LOG_TAG . "$cario_no, $lpr, $etag, $in_time|車牌不合|ETAG吻合|$result_cario_no, $result_lpr, $result_etag, $result_in_time|??");
							trigger_error($LOG_TAG_FATAL . "$cario_no, $lpr, $etag, $in_time|車牌不合|ETAG吻合|$result_cario_no, $result_lpr, $result_etag, $result_in_time|??");
						}
					}
				}
			}
		}
		
		// 執行註銷
		if(!empty($trim_finished_cario_no_arr))
		{
			$this->db->where_in('cario_no', $trim_finished_cario_no_arr)->update('cario', array('err' => 2))->limit(5);
			
			if (!$this->db->affected_rows())
			{
				trigger_error($LOG_TAG . "註銷失敗|" . $this->db->last_query());
				return 'fail';
			}
			
			trigger_error($LOG_TAG . "註銷成功|" . $this->db->last_query());
			trigger_error(__FUNCTION__ . '..trim_finished_cario_no_arr..' . print_r($trim_finished_cario_no_arr, true));
		}
		
		return 'ok';
	}
       
    // 通知付款完成
	public function p2payed($parms, $opay=false, $finished=false) 
	{           
		$result = $this->db->select("in_time, cario_no, station_no, etag")
        		->from('cario')	
                ->where(array('obj_type' => 1, 'obj_id' => $parms['lpr'], 'finished' => 0, 'err' => 0))
                ->order_by('cario_no', 'desc') 
                ->limit(1)
                ->get()
                ->row_array();
		
		// 找不到記錄
		if(!isset($result['cario_no']))
		{
			trigger_error(__FUNCTION__ . '..not found..' . print_r($parms, true));
			return false;
		}
		
		if($opay)
		{
			$pay_time = $this->now_str;
			$out_before_time = date('Y-m-d H:i:s', strtotime(" + 15 minutes"));
			$pay_type = 4;
			
			$data = array
            		(
                    	'out_before_time' => $out_before_time,
                    	'pay_time' => $pay_time,
                    	'pay_type' => 4, // 歐付寶行動支付
                    	'payed' => 1		
                    );
			
			// 是否註記完結
			if($finished)
				$data['finished'] = 1;
			
			$this->db->where(array('cario_no' => $result['cario_no']))->update('cario', $data); 
			
			if (!$this->db->affected_rows())
			{
				trigger_error("歐付寶行動支付失敗,{$parms['lpr']}金額:{$parms['amt']},序號:{$parms['seqno']}");
				return 'fail';
			}
			
			trigger_error("歐付寶行動支付成功,{$parms['lpr']}金額:{$parms['amt']},序號:{$parms['seqno']}");
		}
		else
		{
			// 若間隔小於 15 分鐘, 拿現在時間來當付款時間
			$pay_time = ((strtotime($parms['pay_time']) - strtotime($result['in_time'])) / 60 < 15) ? $this->now_str : $parms['pay_time'];
		
			// 限時離場時間
			$out_before_time = date('Y-m-d H:i:s', strtotime("{$pay_time} + 15 minutes"));
		
			// 付款方式
			$pay_type = $parms['pay_type'];
		
			// B. 一般繳費機
			$data = array
					(
						'out_before_time' =>  $out_before_time,
						'pay_time' =>  $pay_time,
						'pay_type' =>  $pay_type,
						'payed' => 1
					);

			// 是否註記完結
			if($finished)
				$data['finished'] = 1;
					
			$this->db->where(array('cario_no' => $result['cario_no']))->update('cario', $data); 
			
			if (!$this->db->affected_rows())
			{
				trigger_error("付款失敗:{$parms['lpr']}|{$data['out_before_time']}");
				return 'fail';
			}
			
			trigger_error("付款後更新時間:{$parms['lpr']}|{$data['out_before_time']}|". print_r($data, true));
		}
		
		// 付款後續流程
		$this->payed_finished($result['cario_no'], $parms['lpr'], $result['etag'], $result['in_time']);
		
		// 傳送付款更新記錄
		$sync_agent = new AltobSyncAgent();
		$sync_agent->init($result['station_no'], $result['in_time']);
		$sync_agent->cario_no = $result['cario_no'];		// 進出編號
		$sync_result = $sync_agent->sync_st_pay($parms['lpr'], $pay_time, $pay_type, $out_before_time, $finished);
		trigger_error( "..sync_st_pay.." .  $sync_result);
		return 'ok';
    }                                 
    
    
    // 行動支付, 手機告知已付款            
    // http://203.75.167.89/carpayment.html/m2payed/ABC1234/120/12112/12345/1f3870be274f6c49b3e31a0c6728957f 
    // http://203.75.167.89/carpayment.html/m2payed/車牌/金額/場站編號/序號/MD5 
    // md5(車牌.金額.場站編號.序號)
	public function m2payed($parms, $finished=false) 
	{           
        $data = array
            		(
                    	'out_before_time' =>  date('Y-m-d H:i:s', strtotime(" + 15 minutes")),
                    	'pay_time' => date('Y-m-d H:i:s'),
                    	'pay_type' => 4, // 歐付寶行動支付
                    	'payed' => 1		
                    );
			
		// 是否註記完結
		if($finished)
			$data['finished'] = 1;
		
        $this->db->where(array('cario_no' => $parms['seqno']))->update('cario', $data); 
		
        if ($this->db->affected_rows())
        {
          	trigger_error("歐付寶行動支付成功,{$parms['lpr']}金額:{$parms['amt']},序號:{$parms['seqno']}");
            return 'ok';
        }     
        else
        {
          	trigger_error("歐付寶行動支付失敗,{$parms['lpr']}金額:{$parms['amt']},序號:{$parms['seqno']}");
            return 'fail';
        }
    }    



	////////////////////////////////////////
	//
	// 付款前, 入場資訊查找
	//
	////////////////////////////////////////

	
    
	// 模糊比對
	function getLevenshteinSQLStatement($word, $target)
	{
		$words = array();
		
		if(strlen($word) >= 5)
		{
			for ($i = 0; $i < strlen($word); $i++) {
				// insertions
				$words[] = substr($word, 0, $i) . '_' . substr($word, $i);
				// deletions
				$words[] = substr($word, 0, $i) . substr($word, $i + 1);
				// substitutions
				//$words[] = substr($word, 0, $i) . '_' . substr($word, $i + 1);
			}
		}
		else
		{
			for ($i = 0; $i < strlen($word); $i++) {
				// insertions
				$words[] = substr($word, 0, $i) . '_' . substr($word, $i);
			}
		}
		
		// last insertion
		$words[] = $word . '_';
		//return $words;
		
		$fuzzy_statement = ' (';
		foreach ($words as $idx => $word) 
        {
			$fuzzy_statement .= " {$target} LIKE '%{$word}%' OR ";
		}
		$last_or_pos = strrpos($fuzzy_statement, 'OR');
		if($last_or_pos !== false)
		{
			$fuzzy_statement = substr_replace($fuzzy_statement, ')', $last_or_pos, strlen('OR'));
		}
		
		return $fuzzy_statement;
	}
	
	// 取得進場資訊 (模糊比對)
	function q_fuzzy_pks($word)
	{
		if(empty($word) || strlen($word) < 4 || strlen($word) > 10)
		{
			return null;
		}
		/*
		// 備援數字使用
		else if(is_numeric($word) && strlen($word) == 6)
		{
			trigger_error(__FUNCTION__ . '..備援查詢: ' . $word);
			
			$sql = "SELECT obj_id as lpr, ticket_no
					FROM cario
					WHERE finished = 0 AND err = 0
						AND out_before_time > DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 5 DAY)
						AND ticket_no = {$word}
					ORDER BY out_before_time DESC";
			$retults = $this->db->query($sql)->result_array();
			return $retults;
		}
		*/
		$fuzzy_statement = $this->getLevenshteinSQLStatement($word, 'obj_id');
		//trigger_error("模糊比對 {$word} where: {$fuzzy_statement}");
		
		$sql = "SELECT obj_id as lpr, ticket_no
				FROM cario
				WHERE {$fuzzy_statement} AND finished = 0 AND err = 0
				AND out_before_time > DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 5 DAY)
				GROUP BY obj_id 
				ORDER BY out_before_time DESC";
		$retults = $this->db->query($sql)->result_array();
		return $retults;
	}
	
	// 建立博辰查詢入場時間資料 (by ticket_no)
	function gen_query_data_type4($ticket_no)
	{
		$data = array();
		
		// s2. 完整車牌號碼: 右邊補空格補滿7碼
		$data['lpr'] = $ticket_no;
        
		// s3. 塔號_車格號碼: 該車牌相關車輛所停車的停車塔號或樓層號碼，
		// 地下室部分為負值例如B1為-1，平面停車場為1，二樓為2 (左邊補 ‘0’ 補滿2碼)，
		// 停車格號4碼(左邊補 ‘0’ 補滿4碼)，
		// 樓層和停車格號中間以 ‘_’ 分隔，例如：”01_0101”
        $data['seat_no'] = 'XX_XXXX';
        $data['ticket'] = 0;
        $data['start_date'] = '2000/01/01';
        $data['end_date'] = '2000/01/01';
        $data['start_time'] = '00:00';
        $data['end_time'] = '00:00';
                
        $result = $this->db->select("in_time, date_format(pay_time, '%Y/%m/%d %T') as pay_time, in_pic_name, member_no, in_lane, in_out, station_no")
        		->from('cario')	
                ->where(array('obj_type' => 1, 'ticket_no' => $ticket_no, 'finished' => 0, 'err' => 0))
                ->order_by('cario_no', 'desc') 
                ->limit(1)
                ->get()
                ->row_array();
            
        if (!empty($result['in_time']))
        {
			// s5. 入場時間: 格式為"yyyy/MM/dd HH:mm:ss"，時間為24小時制，若無紀錄秒數秒數部分可填”00”
            $data['in_time'] = $result['in_time'];
			// s6. 入場車牌圖片路徑: 貴公司的絕對路徑，我方使用網路芳鄰或FTP下載
			$data['in_pic_name'] = $this->gen_in_pic_path($result);
			// s7. 繳費時間: 無繳費時間時為"2000/01/01 00:00:00"，格式為"yyyy/MM/dd HH:mm:ss"，時間為24小時制，若無紀錄秒數秒數部分可填”00”
            $data['pay_time'] = !empty($result['pay_time']) ? $result['pay_time'] : '2000/01/01 00:00:00';
			// s12. 停車位置區域代碼: 從 1 開始
			$data['area_code'] = $this->gen_area_code($result);
        }   
        else
        {
            $data['in_time'] = '';
            $data['in_pic_name'] = '';
			$data['pay_time'] = '2000/01/01 00:00:00';
			$data['area_code'] = 1;
        }
        
        return $data;
	}
	
	// 取得圖檔路徑
	function gen_in_pic_path($cario)
	{	
		// 北車西上特例
		$station_local_ip = ($cario['station_no'] == 12304)? '192.168.10.203' : STATION_LOCAL_IP;
	
		if(!empty($cario['in_pic_name']))
		{
			$pic_name_arr = explode('-', $cario['in_pic_name']);
			$date_num = substr($pic_name_arr[7], 0, 8);
			return "\\\\" . $station_local_ip . "\\pics\\{$date_num}\\{$cario['in_pic_name']}";
		}
		else if(file_exists(CAR_PIC . 'lpr-404.jpg'))
		{
			return "\\\\" . $station_local_ip . "\\pics\\lpr-404.jpg";	 // 預設圖片	
		}
		
		return '';
	}
	
	// 產生區域代碼 (判斷 in_out, in_lane, station_no)
	function gen_area_code($cario)
	{
		// 1: 北車西上 (一般車)
		// 2: 北車西上 (機車)
		// 3: 北車西下 (一般車)
		// 4: 北車西下 (計程車)
		
		// 北車西下
		if($cario['station_no'] == 12303)
		{
			if($cario['in_lane'] == 0)
			{
				return 4;	// 4: 北車西下 (計程車)
			}
			else
			{
				return 3;	// 3: 北車西下 (一般車)
			}
		}
		
		// 北車西上
		else if($cario['station_no'] == 12304)
		{
			if(substr($cario['in_out'], 0, 1) === 'C')
			{
				return 1;	// 1: 北車西上 (一般車)
			}
			else
			{
				return 2;	// 2: 北車西上 (機車)
			}
		}
		
		return 1;			// 預設值
	}
	
	// 建立博辰查詢入場時間資料
	function gen_query_data($lpr)
	{
		$data = array();
		
		// s2. 完整車牌號碼: 右邊補空格補滿7碼
		$data['lpr'] = $lpr; //str_pad($lpr, 7, ' ', STR_PAD_RIGHT);
        
		// s3. 塔號_車格號碼: 該車牌相關車輛所停車的停車塔號或樓層號碼，
		// 地下室部分為負值例如B1為-1，平面停車場為1，二樓為2 (左邊補 ‘0’ 補滿2碼)，
		// 停車格號4碼(左邊補 ‘0’ 補滿4碼)，
		// 樓層和停車格號中間以 ‘_’ 分隔，例如：”01_0101”
        $sql = "select p.pksno, m.group_id
        		from pks p, pks_group_member m, pks_groups g 
                where p.pksno = m.pksno  
                and m.group_id = g.group_id
                and g.group_type = 1
                and p.lpr = '{$lpr}'
                limit 1"; 
        $rows = $this->db->query($sql)->row_array();
        if (!empty($rows['pksno']))
        {
          	//$data['seat_no'] = ($rows['group_id'] == 'B1' ? '-1' : '0' . substr($rows['group_id'], -1)) . '_0' . substr($rows['pksno'], -3);
			
			$group_floor_type = preg_replace( '/[^A-Z]/', '', $rows['group_id']);
			$group_floor_num = preg_replace( '/[^1-9]/', '', $rows['group_id']);
			if($group_floor_type == 'B')
			{
				$data['seat_no'] = '-' . $group_floor_num . '_0' . substr($rows['pksno'], -3);
			}
			else
			{
				$group_floor_num = str_pad($group_floor_num, 2, '0', STR_PAD_LEFT);
				$data['seat_no'] = $group_floor_num . '_0' . substr($rows['pksno'], -3);
			}
			
        } 
        else
        {
			$data['seat_no'] = 'XX_XXXX';   // '-1_0028';
        }
                     
        // 查詢是否月租會員                
        $result = $this->db->select("date_format(start_date, '%Y/%m/%d') as start_date, date_format(end_date,'%Y/%m/%d') as end_date")
        		->from('members')	
                ->where(array(
						'lpr' => $lpr, 
						'start_date <' => $this->vars['date_time'],
						'end_date >=' => $this->vars['date_time'])
						, false)
                ->limit(1)
                ->get()
                ->row_array();      
        if (!empty($result['start_date']))	// 月租會員
        {
        	$data['ticket'] = 1;						// s4. 是否為月票: 0:非月票, 1:月票						
          	$data['start_date'] = $result['start_date'];// s8.	有效起始時間: 非月票時為"2000/01/01", 格式為"yyyy/MM/dd"
          	$data['end_date'] = $result['end_date'];	// s9.	有效截止日期: 非月票時為"2000/01/01", 格式為"yyyy/MM/dd"
          	$data['start_time'] = '00:00';				// s10. 使用起始時段: 非月票時為"00:00", 格式為"HH:mm"
          	$data['end_time'] = '23:59';				// s11. 使用結束時段: 非月票時為"00:00", 格式為"HH:mm"
        }       
        else	// 臨停車
        {   
        	$data['ticket'] = 0;
          	$data['start_date'] = '2000/01/01';
          	$data['end_date'] = '2000/01/01';
          	$data['start_time'] = '00:00';
          	$data['end_time'] = '00:00';
        }
                
        $result = $this->db->select("in_time, date_format(pay_time, '%Y/%m/%d %T') as pay_time, in_pic_name, member_no, in_lane, in_out, station_no")
        		->from('cario')	
                ->where(array('obj_type' => 1, 'obj_id' => $lpr, 'finished' => 0, 'err' => 0))
                ->order_by('cario_no', 'desc') 
                ->limit(1)
                ->get()
                ->row_array();
            
        if (!empty($result['in_time']))
        {
			// s5. 入場時間: 格式為"yyyy/MM/dd HH:mm:ss"，時間為24小時制，若無紀錄秒數秒數部分可填”00”
            $data['in_time'] = $result['in_time'];
			// s6. 入場車牌圖片路徑: 貴公司的絕對路徑，我方使用網路芳鄰或FTP下載
			$data['in_pic_name'] = $this->gen_in_pic_path($result);
			// s7. 繳費時間: 無繳費時間時為"2000/01/01 00:00:00"，格式為"yyyy/MM/dd HH:mm:ss"，時間為24小時制，若無紀錄秒數秒數部分可填”00”
            $data['pay_time'] = !empty($result['pay_time']) ? $result['pay_time'] : '2000/01/01 00:00:00';
			// s12. 停車位置區域代碼: 從 1 開始
			$data['area_code'] = $this->gen_area_code($result);
        }   
        else
        {
            $data['in_time'] = '';
            $data['in_pic_name'] = '';
			$data['pay_time'] = '2000/01/01 00:00:00';
			$data['area_code'] = 1;
        }
        
        return $data;
	}
    
    // 博辰查詢入場時間 (fuzzy)
	public function query_in_fuzzy($lpr) 
	{          
		$fuzzy_result = $this->q_fuzzy_pks($lpr);
		
		if(!empty($fuzzy_result) && count($fuzzy_result) > 0)
		{
			$data = array();
			// s2 ~ s11 的資料會因模糊比對筆數增加或減少而增減
			foreach ($fuzzy_result as $idx => $rows) 
			{
				$result_lpr = $rows['lpr'];
				$ticket_no = $rows['ticket_no'];
				
				if($result_lpr == 'NONE')
				{
					$tmp_data = $this->gen_query_data_type4($ticket_no);	// 備緩搜尋
				}
				else
				{
					$tmp_data = $this->gen_query_data($result_lpr);			// 模糊搜尋
				}
				
				if($tmp_data['in_time'] == '')
				{
					// 若查無入場時間, 直接乎略這筆
					trigger_error("查無入場時間, 直接乎略這筆[{$result_lpr}]:".print_r($rows, true));
				}
				else
				{
					$data['results'][$idx] = $tmp_data;	
				}
				
			}
			$data['count'] = count($fuzzy_result);
		}
		else
		{
			$data_0 = array();
			$data_0['lpr'] = str_pad($lpr, 7, ' ', STR_PAD_RIGHT);
			$data_0['seat_no'] = 'XX_XXXX';
			$data_0['ticket'] = 0;
          	$data_0['start_date'] = '2000/01/01';
          	$data_0['end_date'] = '2000/01/01';
          	$data_0['start_time'] = '00:00';
          	$data_0['end_time'] = '00:00';
			$data_0['in_time'] = '';
            $data_0['pay_time'] = '2000/01/01 00:00:00';
            $data_0['in_pic_name'] = '';
			$data_0['area_code'] = 1;
			
			$data = array();
			$data['results'][0] = $data_0;
			$data['count'] = 0;
		}
		
		trigger_error("fuzzy aps查詢入場時間[{$lpr}]:".print_r($data, true));
		
		return $data;
    }

    // 博辰查詢入場時間
    public function query_in($lpr) 
	{     
        $data = array();
        
        // 讀取樓層數, group_type = 2為樓層
        $sql = "select p.pksno, m.group_id
        		from pks p, pks_group_member m, pks_groups g 
                where p.pksno = m.pksno  
                and m.group_id = g.group_id
                and g.group_type = 1
                and p.lpr = '{$lpr}'
                limit 1"; 
        $rows = $this->db->query($sql)->row_array();
        if (!empty($rows['pksno']))
        {
          	//$data['seat_no'] = ($rows['group_id'] == 'B1' ? '-1' : '0' . substr($rows['group_id'], -1)) . '_' . substr($rows['pksno'], -3);
			
			$group_floor_type = preg_replace( '/[^A-Z]/', '', $rows['group_id']);
			$group_floor_num = preg_replace( '/[^1-9]/', '', $rows['group_id']);
			if($group_floor_type == 'B')
			{
				$data['seat_no'] = '-' . $group_floor_num . '_0' . substr($rows['pksno'], -3);
			}
			else
			{
				$group_floor_num = str_pad($group_floor_num, 2, '0', STR_PAD_LEFT);
				$data['seat_no'] = $group_floor_num . '_0' . substr($rows['pksno'], -3);
			}
			
        } 
        else
        {
          	$data['seat_no'] = 'XX_XXXX';
        }
                     
        // 查詢是否月租會員                
        $result = $this->db->select("date_format(start_date, '%Y/%m/%d') as start_date, date_format(end_date,'%Y/%m/%d') as end_date")
        		->from('members')	
                ->where(array('lpr' => $lpr, 'end_date >=' => $this->vars['date_time']), false)
                ->limit(1)
                ->get()
                ->row_array();      
        if (!empty($result['start_date']))	// 月租會員
        {
        	$data['ticket'] = 1;
          	$data['start_date'] = $result['start_date'];
          	$data['end_date'] = $result['end_date'];
          	$data['start_time'] = '00:00';
          	$data['end_time'] = '23:59';
        }       
        else	// 臨停車
        {   
        	$data['ticket'] = 0;
          	$data['start_date'] = '2000/01/01';
          	$data['end_date'] = '2000/01/01';
          	$data['start_time'] = '00:00';
          	$data['end_time'] = '00:00';
        }
                
        $result = $this->db->select("in_time, date_format(pay_time, '%Y/%m/%d %T') as pay_time, in_pic_name, member_no, station_no")
        		->from('cario')	
                ->where(array('obj_type' => 1, 'obj_id' => $lpr, 'finished' => 0, 'err' => 0))
                ->order_by('cario_no', 'desc') 
                ->limit(1)
                ->get()
                ->row_array();  
            
        if (!empty($result['in_time']))
        {
        	trigger_error("aps查詢入場時間|{$lpr}|{$result['in_time']}|{$result['in_pic_name']}"); 
            $data['in_time'] = $result['in_time'];
            $data['pay_time'] = !empty($result['pay_time']) ? $result['pay_time'] : '2000/01/01 00:00:00';
			$data['in_pic_name'] = $this->gen_in_pic_path($result);
            $data['records'] = 1; 
        }   
        else
        {
            $data['in_time'] = '';
            $data['pay_time'] = '2000/01/01 00:00:00';
            $data['in_pic_name'] = '';
            $data['records'] = 0; 
        }
        
        trigger_error("aps查詢入場時間[{$lpr}]:".print_r($data, true)); 
        // return array('in_time' => '', 'in_pic_name' => '', 'records' => 0, 'ticket' => 0, 'seat_no' => 'XX_XXXX');
        return $data;
    }   
    
    
    // 行動設備查詢入場時間   
    // http://203.75.167.89/carpayment.html/m2query_in/ABC1234/12112/1f3870be274f6c49b3e31a0c6728957f 
    // http://203.75.167.89/carpayment.html/m2query_in/車牌/場站編號/MD5  
    // 回傳0: 失敗, 成功: 12345,60(第一欄位非0數字代表成功, 第二欄位為金額), 此值在付款時必需傳回, 否則視為非法
    public function m2query_in($parms) 
	{   
        $result = $this->db->select('cario_no, out_before_time')
        		->from('cario')	
                ->where(array('obj_type' => 1, 'obj_id' => $parms['lpr'], 'station_no' => $parms['station_no'], 'finished' => 0, 'err' => 0))
                ->order_by('cario_no', 'desc') 
                ->limit(1)
                ->get()
                ->row_array();  
            
        if (!empty($result['cario_no']))
        {
        	trigger_error("行動設備查詢入場時間成功|{$lpr}|{$result['cario_no']}|{$result['in_time']}"); 
          	// call計費模組
            $amt = 10;
        }   
        else
        {
            $result['cario_no'] = 0;
            $amt = 0;   
        	trigger_error('行動設備查詢入場時間失敗'.print_r($parms, true));
        }
        
        return "{$result['cario_no']},{$amt}";
    }  
	
	
	// 臨停未結清單
	public function cario_temp_not_finished_query_all($station_no, $q_item, $q_str) 
	{   
    	$where_station = $station_no == 0 ? '' : " station_no = {$station_no} and ";	// 如為0, 則全部場站讀取 
									
    	switch($q_item)
        {
          	case 'in_time': 
          		$items = "{$q_item} >=";
            	$q_str .= ' 23:59:59';
                break;
            case 'lpr': 
        		$items = "{$q_item} like ";
          		$q_str = strtoupper($q_str).'%';
                break;
            default:
        		$items = "{$q_item} like ";
          		$q_str .= '%';
                break;
        }              
        
        $sql = "
				SELECT
					cario_no,
					station_no,
					obj_id as lpr, 
					in_time,
					out_before_time,
					pay_time
        		FROM cario
                WHERE 
					{$where_station} {$items} '{$q_str}'
					and obj_type = 1 and finished = 0 and err = 0 and confirms = 0
					and member_no = 0 
					and out_time is null
				ORDER BY cario.cario_no asc
				";
		
		//trigger_error(__FUNCTION__ . "test sql: {$sql}");
		
    	$results = $this->db->query($sql)->result_array();
		
        return $results;
    }
	
}
