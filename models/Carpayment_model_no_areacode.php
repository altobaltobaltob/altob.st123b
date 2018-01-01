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
	
	/*
	// MITAC 通知付款完成
	public function mitac2payed($parms) 
	{           
		$where_arr = array('obj_type' => 1, 'cario_no' => $parms['cario_no'], 'obj_id' => $parms['lpr'], 'finished' => 0, 'err' => 0);
		$result = $this->db->select("in_time, cario_no, station_no")
        		->from('cario')	
                ->where($where_arr)
                ->limit(1)
                ->get()
                ->row_array();
		
		$in_time = new DateTime($result['in_time']);
		$pay_time = new DateTime($parms['pay_time']);
			
		$data = array
			(
				'out_before_time' =>  date('Y-m-d H:i:s', strtotime("{$parms['pay_time']} + 15 minutes")),
				'pay_time' => $parms['pay_time'],
				'pay_type' => $parms['pay_type'],
				'payed' => 1
			);
						
		// 更新
		$this->db->where($where_arr)->update('cario', $data); 
			
		if (!$this->db->affected_rows())
		{
			trigger_error("付款失敗:{$parms['lpr']}|{$data['out_before_time']}");
			return 'fail';
		}
			
		trigger_error("付款後更新時間:{$parms['lpr']}|{$data['out_before_time']}");
		
		// 傳送付款更新記錄
		$sync_agent = new AltobSyncAgent();
		$sync_agent->init($result['station_no'], $result['in_time']);
		$sync_agent->cario_no = $result['cario_no'];		// 進出編號
		$sync_result = $sync_agent->sync_st_pay($parms['lpr'], $parms['pay_time'], $parms['pay_type'], 
			date('Y-m-d H:i:s', strtotime("{$parms['pay_time']} + 15 minutes")));
		trigger_error( "..sync_st_pay.." .  $sync_result);
		
		return 'ok';
	}
	*/
       
    // 通知付款完成
	public function p2payed($parms, $opay=false) 
	{           
		$result = $this->db->select("in_time, cario_no, station_no")
        		->from('cario')	
                ->where(array('obj_type' => 1, 'obj_id' => $parms['lpr'], 'finished' => 0, 'err' => 0))
                ->order_by('cario_no', 'desc') 
                ->limit(1)
                ->get()
                ->row_array();
		
		// 查不到車號才找備援碼
		if(!isset($result['in_time']) && is_numeric($parms['lpr']) && strlen($parms['lpr']) == 6)
		{
			$result = $this->db->select("in_time, cario_no, station_no")
				->from('cario')	
				->where(array('obj_type' => 1, 'ticket_no' => $parms['lpr'], 'finished' => 0, 'err' => 0))
				->order_by('cario_no', 'desc') 
				->limit(1)
				->get()
				->row_array();
				
			// 找不到記錄
			if(!isset($result['in_time']))
			{
				trigger_error(__FUNCTION__ . '..not found..' . print_r($parms, true));
				return false;
			}
			
			$in_time = new DateTime($result['in_time']);
			$pay_time = new DateTime($parms['pay_time']);
					
			// 若間隔小於 15 分鐘, 拿現在時間來當付款時間
			$parms['pay_time'] = (($pay_time->getTimestamp() - $in_time->getTimestamp()) / 60 < 15) ? $this->now_str : $parms['pay_time'];		
			
			if($opay)
			{
				// A. （備援碼）歐付寶
				$parms2 = array('seqno' => $result['cario_no'], 'amt' => $parms['amt'], 'lpr' => $parms['lpr']);
				return $this->m2payed($parms2);

			}
			else
			{
				// B. （備援碼）一般繳費機
				$data = array
					(
						'out_before_time' =>  date('Y-m-d H:i:s', strtotime("{$parms['pay_time']} + 15 minutes")),
						'pay_time' =>  $parms['pay_time'],
						'pay_type' =>  $parms['pay_type'],
						'payed' => 1
					);
							
				$this->db->where(array('cario_no' => $result['cario_no']))->update('cario', $data); 
					
				if (!$this->db->affected_rows())
				{
					trigger_error("(備援碼) 付款失敗:{$parms['lpr']}|{$data['out_before_time']}"); 
					return 'fail';	
				}
				
				trigger_error("(備援碼) 付款後更新時間:{$parms['lpr']}|{$data['out_before_time']}"); 
				return 'ok';
			}
		}
		
		// A. 歐付寶
		if($opay)
		{
			$parms2 = array('seqno' => $result['cario_no'], 'amt' => $parms['amt'], 'lpr' => $parms['lpr']);
			$result = $this->m2payed($parms2);
			
			if($result != 'ok')
				return $result;
		}
		else
		{
			// B. 一般繳費機
			$in_time = new DateTime($result['in_time']);
			$pay_time = new DateTime($parms['pay_time']);
					
			// 若間隔小於 15 分鐘, 拿現在時間來當付款時間
			$parms['pay_time'] = (($pay_time->getTimestamp() - $in_time->getTimestamp()) / 60 < 15) ? $this->now_str : $parms['pay_time'];		
			
			$data = array
					(
						'out_before_time' =>  date('Y-m-d H:i:s', strtotime("{$parms['pay_time']} + 15 minutes")),
						'pay_time' =>  $parms['pay_time'],
						'pay_type' =>  $parms['pay_type'],
						'payed' => 1
					);
						
			$this->db
				->where(array('obj_type' => 1, 'obj_id' => $parms['lpr'], 'finished' => 0, 'err' => 0)) 
				->update('cario', $data); 
			
			if (!$this->db->affected_rows())
			{
				trigger_error("付款失敗:{$parms['lpr']}|{$data['out_before_time']}");
				return 'fail';
			}
			
			trigger_error("付款後更新時間:{$parms['lpr']}|{$data['out_before_time']}");
		}
		
		// 傳送付款更新記錄
		$sync_agent = new AltobSyncAgent();
		$sync_agent->init($result['station_no'], $result['in_time']);
		$sync_agent->cario_no = $result['cario_no'];		// 進出編號
		$sync_result = $sync_agent->sync_st_pay($parms['lpr'], $parms['pay_time'], $parms['pay_type'], 
			date('Y-m-d H:i:s', strtotime("{$parms['pay_time']} + 15 minutes")));
		trigger_error( "..sync_st_pay.." .  $sync_result);
		
		return 'ok';
    }                                 
    
	
	// 繳費機告知已付款 (new 2016/07/15)
	// http://localhost/carpayment.html/ats2payed/車牌/金額/場站編號/序號/MD5 
	// md5(車牌.金額.場站編號.序號)
	public function ats2payed($parms)
	{            
    	$order_no = $parms['order_no'];
		$bill_result = $this->db->from('tx_bill_ats')
				  ->where(array('order_no' => $order_no, 'status' => 111))
				  ->limit(1)
				  ->get()
				  ->row_array();
				
		if(!empty($bill_result)){
			$member_no = $bill_result['member_no'];
			$station_no = $bill_result['station_no'];
			$next_start_time = $bill_result['next_start_time'];
			$next_end_time = $bill_result['next_end_time'];
			
			$data = array(
				'end_date' => $bill_result['next_end_time'] // TODO: 有被任何一筆序號蓋資料的可能
			);
                    
			$this->db
				->where(array('member_no' => $member_no, 'station_no' => $station_no))
				->update('members', $data); 
			if ($this->db->affected_rows())
			{
				trigger_error("繳費機更新會員資料完成,{$parms['lpr']},金額:{$parms['amt']},序號:{$parms['order_no']}");
				return 'ok';
			}     
			else
			{
				trigger_error("繳費機更新會員資料失敗,{$parms['lpr']},金額:{$parms['amt']},序號:{$parms['order_no']}");
				return 'fail';
			}
		}		  
    }
    
    
    // 行動支付, 手機告知已付款            
    // http://203.75.167.89/carpayment.html/m2payed/ABC1234/120/12112/12345/1f3870be274f6c49b3e31a0c6728957f 
    // http://203.75.167.89/carpayment.html/m2payed/車牌/金額/場站編號/序號/MD5 
    // md5(車牌.金額.場站編號.序號)
	public function m2payed($parms) 
	{           
        $data = array
            		(
                    	'out_before_time' =>  date('Y-m-d H:i:s', strtotime(" + 15 minutes")),
                    	'pay_time' => date('Y-m-d H:i:s'),
                    	'pay_type' => 4, // 歐付寶行動支付
                    	'payed' => 1		
                    );
                    
        $this->db
            ->where(array('cario_no' => $parms['seqno'])) 
        	->update('cario', $data); 
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
         
    
     
    /*
    月租繳款完成          
	http://203.75.167.89/carpayment.html/memberpayed/12345/ABC1234/120/12112/1/2016-01-31/1f3870be274f6c49b3e31a0c6728957f 
	http://203.75.167.89/carpayment.html/memberpayed/會員號碼/車牌/金額/場站編號/月繳/本期到期日/md5 
    md5(會員號碼.車牌.金額.場站編號.月繳.本期到期日)  
    
	public function memberpayed($parms)
	{           
        // update members (???)
    }  
	*/
	
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
                
        $result = $this->db->select("in_time, date_format(pay_time, '%Y/%m/%d %T') as pay_time, in_pic_name, member_no")
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
			$data['in_pic_name'] = $this->gen_in_pic_path($result['in_pic_name']);
			// s7. 繳費時間: 無繳費時間時為"2000/01/01 00:00:00"，格式為"yyyy/MM/dd HH:mm:ss"，時間為24小時制，若無紀錄秒數秒數部分可填”00”
            $data['pay_time'] = !empty($result['pay_time']) ? $result['pay_time'] : '2000/01/01 00:00:00';
        }   
        else
        {
            $data['in_time'] = '';
            $data['in_pic_name'] = '';
			$data['pay_time'] = '2000/01/01 00:00:00';
        }
        
        return $data;
	}
	
	// 取得圖檔路徑
	function gen_in_pic_path($in_pic_name)
	{	
		if(!empty($in_pic_name))
		{
			$pic_name_arr = explode('-', $in_pic_name);
			$date_num = substr($pic_name_arr[7], 0, 8);
			return "\\\\" . STATION_LOCAL_IP . "\\pics\\{$date_num}\\{$in_pic_name}";
		}
		else if(file_exists(CAR_PIC . 'lpr-404.jpg'))
		{
			return "\\\\" . STATION_LOCAL_IP . "\\pics\\lpr-404.jpg";	 // 預設圖片	
		}
		
		return '';
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
          	$data['seat_no'] = ($rows['group_id'] == 'B1' ? '-1' : '0' . substr($rows['group_id'], -1)) . '_0' . substr($rows['pksno'], -3);
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
                
        $result = $this->db->select("in_time, date_format(pay_time, '%Y/%m/%d %T') as pay_time, in_pic_name, member_no")
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
			$data['in_pic_name'] = $this->gen_in_pic_path($result['in_pic_name']);
			// s7. 繳費時間: 無繳費時間時為"2000/01/01 00:00:00"，格式為"yyyy/MM/dd HH:mm:ss"，時間為24小時制，若無紀錄秒數秒數部分可填”00”
            $data['pay_time'] = !empty($result['pay_time']) ? $result['pay_time'] : '2000/01/01 00:00:00';
        }   
        else
        {
            $data['in_time'] = '';
            $data['in_pic_name'] = '';
			$data['pay_time'] = '2000/01/01 00:00:00';
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
				$lpr = $rows['lpr'];
				$ticket_no = $rows['ticket_no'];
				
				if($lpr == 'NONE')
				{
					$tmp_data = $this->gen_query_data_type4($ticket_no);	// 備緩搜尋
				}
				else
				{
					$tmp_data = $this->gen_query_data($lpr);				// 模糊搜尋
				}
				
				if($tmp_data['in_time'] == '')
				{
					// 若查無入場時間, 直接乎略這筆
					trigger_error("查無入場時間, 直接乎略這筆[{$lpr}]:".print_r($rows, true));
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
          	$data['seat_no'] = ($rows['group_id'] == 'B1' ? '-1' : '0' . substr($rows['group_id'], -1)) . '_' . substr($rows['pksno'], -3);
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
                
        $result = $this->db->select("in_time, date_format(pay_time, '%Y/%m/%d %T') as pay_time, in_pic_name, member_no")
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
			$data['in_pic_name'] = $this->gen_in_pic_path($result['in_pic_name']);
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
