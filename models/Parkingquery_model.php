<?php             
/*
file: Parkingquery_model.php 停車管理系統(提供資策會使用)
*/                   

class Parkingquery_model extends CI_Model 
{             
    
	function __construct()
	{
		parent::__construct(); 
		$this->load->database(); 
    }   
     
	public function init($vars)
	{
		// do nothing
    } 
    
    // 取得所有在席資訊
	public function q_local_pks($group_id)
	{
		$sql = "SELECT
					SUBSTR(pks.pksno, -3) as l_no, 
					if(pks.lpr <> '', 1, 0) as s
				FROM pks
				LEFT JOIN pks_group_member ON (pks.pksno = pks_group_member.pksno AND pks.station_no = pks_group_member.station_no)
				WHERE pks_group_member.group_id = '{$group_id}'
				";
		$retults = $this->db->query($sql)->result_array();

		foreach ($retults as $idx => $rows)
        {
			$key = $rows['l_no'];
			unset($rows['l_no']);
			
			$data['result'][$key] = $rows;
		}

		return $data;
	}
	
	// 查詢各樓層剩餘車位 (不分類)
	public function check_space_all($seqno) 
	{           
    	$data = array();         
    	$results = $this->db->select('group_id, availables, tot')
        		->from('pks_groups')
                ->get()  
                ->result_array();  
                         
        foreach($results as $idx => $rows)
        {
          	$data['result']['floor'][$idx] = array
            (
            	'floor_name' => $rows['group_id'], 
            	'valid_count' => $rows['availables'], 
            	'total_count' => $rows['tot'] 
            );
        }
        return $data; 
    }   
    
    // 查詢各樓層剩餘車位
	public function check_space($seqno, $group_type=1) 
	{           
    	$data = array();  
		
    	$results = $this->db->select('group_id, availables, tot')
        		->from('pks_groups')
                ->where('group_type', $group_type)
				->order_by('cast(group_name as unsigned)', 'desc')
                ->get()  
                ->result_array();  
			
		//$sql = "SELECT group_id, availables, tot FROM `pks_groups` WHERE pks_groups.group_type = $group_type order by cast(group_name as unsigned) desc"; 
		//$results = $this->db->query($sql)->row_array(); 
		
        foreach($results as $idx => $rows)
        {
          	$data['result']['floor'][$idx] = array
            (
            	'floor_name' => $rows['group_id'], 
            	'valid_count' => $rows['availables'], 
            	'total_count' => $rows['tot'] 
            );
        }
        return $data; 
    }   
    
    // 停車位置查詢
	public function check_location($lpr) 
	{                 
    	$lpr = strtoupper($lpr);	// 一律轉大寫
    	$data = array();         
    	$rows = $this->db->select('pksno, pic_name, in_time')
        		->from('pks')
                ->where('lpr', $lpr)	
        		->limit(1)
                ->get()  
                ->row_array();  
        if (!empty($rows['pksno']))
        {
        	$data['result']['num'] = $lpr;
        	$data['result']['location_no'] = "{$rows['pksno']}";     
			$data['result']['pic_name'] = $rows['pic_name'];
			$data['result']['in_time'] = $rows['in_time'];
        	$data['result_code'] = 'OK';  
        }    
        else	// 查無資料, 啟用模糊比對
        {     
			/*
			// 讀取最近一筆入場資料
        	$rows_cario = $this->db
							->select('cario_no, in_time')
        					->from('cario')
                			->where(array('in_out' => 'CI', 'obj_id' => $lpr, 'finished' => 0, 'err' => 0, 'out_time IS NULL' => null))
                  			->order_by('cario_no', 'desc')
                  			->limit(1)
                			->get()
                			->row_array();
							
			// 有入場記錄, 直接猜在頂樓
			if (!empty($rows_cario['cario_no']))
            {
				$data['result']['num'] = $lpr;
				$data['result']['location_no'] = "7000";     
				$data['result']['in_time'] = $rows_cario['in_time'];
				$data['result_code'] = 'OK';  
			}
			else
			{
				$data['result']['num'] = $lpr;
				$data['result']['location_no'] = '0';
				$data['result_code'] = 'FAIL';
			}
			*/
			
			$data['result']['num'] = $lpr;
			$data['result']['location_no'] = '0';
			$data['result_code'] = 'FAIL';
        }      
        return $data; 
    }          
	
	// 空車位導引
	public function get_valid_seat($pksno, $group_type=1)
	{           
    	$data = array();   
        $this->db->trans_start(); 
		
		$sql = '';
        if ($pksno > 0)	// 限制從某一個車位開始指派車位
        {   
			// 取得指定車格座標
			$sql_xy = "	SELECT pks.posx, pks.posy, LEFT(pks.pksno, 2) as pksno_idx
						FROM pks
						WHERE pks.pksno = {$pksno}
						";
			
			$rows_xy = $this->db->query($sql_xy)->row_array(); 
			if(!empty($rows_xy['posx']) && !empty($rows_xy['posy']))
			{
				// 找最近
				$sql = "
						select pks.pksno, pks.posx, pks.posy, pks_group_member.group_id, 
							( 
								ABS(cast(pks.pksno as signed) - {$pksno}) +
								ABS(cast(pks.posx as signed) - {$rows_xy['posx']}) + 
								ABS(cast(pks.posy as signed) - {$rows_xy['posy']}) +
								ABS(LEFT(pks.pksno, 2) - {$rows_xy['pksno_idx']}) * 1000
							) AS v
							from pks 
							left join pks_group_member on (pks_group_member.pksno = pks.pksno)
							left join pks_groups on (pks_groups.group_id = pks_group_member.group_id)
						where 
							pks.status = 'VA' and prioritys != 0 and (pks.book_time is null or pks.book_time <= now()) 
							and pks_groups.group_type = {$group_type}
						order by v asc limit 1 for update;
						";
			}
        }
        
		// 依順序
		if(empty($sql))
			$sql = "SELECT pks.pksno 
						FROM pks 
						LEFT JOIN pks_group_member ON (pks_group_member.pksno = pks.pksno)
						LEFT JOIN pks_groups ON (pks_groups.group_id = pks_group_member.group_id)
						WHERE pks.status = 'VA' 
							AND pks.prioritys != 0 
							AND (pks.book_time IS NULL OR pks.book_time <= now()) 
							AND pks_groups.group_type = {$group_type}
						ORDER BY pks.prioritys ASC LIMIT 1 FOR UPDATE;"; 
						
		trigger_error(__FUNCTION__ . "..sql: {$sql}..");
        
        $rows = $this->db->query($sql)->row_array(); 
        if (!empty($rows['pksno']))
        {
        	$data['result']['location_no'] = "{$rows['pksno']}";
        	$data['result_code'] = 'OK';  
            $sql = "update pks set book_time = addtime(now(), '00:10:00') where pksno = {$rows['pksno']};";
            $this->db->query($sql);
			
			trigger_error(__FUNCTION__ . "[{$pksno}]:" .  print_r($data, true).  print_r($rows, true));
        }      
        else   
        {
        	$data['result']['location_no'] = '0';
        	$data['result_code'] = 'FAIL';
			trigger_error(__FUNCTION__ . "[{$pksno}]:" .  print_r($data, true));
        }      
        $this->db->trans_complete(); 
        return $data; 
    } 
    
    
    // 緊急求救
    // http://xxxxxxxxxx/parkingquery.html/send_sos/B2/111/123
	public function send_sos($floor, $x, $y)
	{           
    	$data = array
        (
        	'result' => array('send_from' => array('floor' => $floor, 'x' => $x, 'y' => $y)),
            'result_code' => 'OK'
        );  
        return $data; 
    }   
    
    
    // 防盜鎖車
    // http://xxxxxxxxxx/parkingquery.html/security_action/ABC1234/pswd/2
	public function security_action($lpr, $pswd, $action)
	{                      
    	$data = array();    
        
    	$rows = $this->db->select('member_no, passwd, locked')
        		->from('members')
                ->where(array('lpr' => $lpr))	     
                ->limit(1)
                ->get()  
                ->row_array(); 
        trigger_error('防盜鎖車:'.$this->db->last_query());
                                                        
        // 無資料或密碼錯誤
        if (empty($rows['member_no']) || md5($rows['passwd']) != $pswd)
        {
          	$data['result_code'] = 'FAIL';
            return($data);
        }
        
        $data['result_code'] = 'OK';
    	// 查詢防盜狀態                 
    	if ($action == 2)
        {      
        	$data['result']['action'] = 'CHECK_SECURITY';
        	$data['result'][0]['num'] = $lpr;
        	$data['result'][0]['result'] = $rows['locked'] ? 'ON' : 'OFF';
            return $data;
        }     
                
        $this->db
        	->where('member_no', $rows['member_no'])
        	->update('members', array('locked' => $action)); 
        
    	$data['result']['action'] = $action == 1 ? 'ON' : 'OFF';  
        return $data; 
    } 
}
