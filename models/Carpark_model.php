<?php             
/*
file: carpark_model.php 停車管理系統
*/                   

class Carpark_model extends CI_Model 
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
    
    // 月租會員加入
	public function member_add($data) 
	{                           
    	// 會員車輛基本資料檔 
        // $data['start_date'] = "{$data['start_date']} 00:00:00";
        // $data['end_date'] = "{$data['end_date']} 23:59:59";  
        $old_lpr = $data['old_lpr'];
        unset($data['old_lpr']);
    	$data_car = array
        			(
						'lpr' => $data['lpr'],                    
						'lpr_correct' => $data['lpr'],                    
						'etag' => $data['etag'],                    
						'station_no' => $data['station_no'],                    
						'start_time' => $data['start_date'],                    
						'end_time' => $data['end_date']                  
                    );    
                    
    	$check_member_no = $data['member_no'];
        unset($data['member_no']); 
        trigger_error("members:".print_r($data, true)."car:".print_r($data_car, true));
    	if ($check_member_no == 0) 	// 新增一筆會員資料
        {                     
    		$this->db->insert('members', $data);
       		$data_car['member_no'] = $this->db->insert_id();
    		$this->db->insert('member_car', $data_car); 
        }   
    	else	// update會員資料
        {                  
        	$this->db->update('members', $data, array('member_no' => $check_member_no));
            if ($old_lpr == $data['lpr'])	// 沒有異動到車牌, 使用update, 否則重建一筆
            {
            	unset($data_car['lpr']);
            	unset($data_car['lpr_correct']);
                
        		$this->db->update('member_car', $data_car, array('member_no' => $check_member_no));
            } 
            else
            {
    			$this->db->delete('member_car', array('member_no' => $check_member_no)); 
       			$data_car['member_no'] = $check_member_no;         
    			$this->db->insert('member_car', $data_car);
            }        
        }
        
        return true; 
    }  
    
         
    // 查詢車牌是否重複
	public function check_lpr($lpr)
	{                    
    	$rows = $this->db->select('count(*) as counts')
        		->from('members')
                ->where(array('lpr' => $lpr))
                ->get()
                ->row_array();    
                                
        return $rows['counts'];
    } 
    
    
    // 月租會員查詢
	public function member_query() 
	{                    
    	$results = $this->db->select('member_no, lpr, etag, member_name, mobile_no, start_date, end_date, update_time, 
					contract_no, amt, member_id, tel_h, tel_o, addr, valid_time, station_no')
        		->from('members')
                ->order_by('station_no, lpr', 'asc')	
                ->get()  
                ->result_array();  
        
        return $results; 
    }        
    
    
    // 刪除月租會員
	public function member_delete($member_no) 
	{                    
    	$this->db->delete('members', array('member_no' => $member_no));  
    	$this->db->delete('member_car', array('member_no' => $member_no));  
        
        return true; 
    }    
    
     
    // 進出場現況表
	public function cario_list() 
	{    
    	/*      
    	$data_cario = $this->db
        			->select('c.cario_no, c.in_out, in_lane, out_lane, c.in_time, c.out_time, c.minutes, c.obj_id as lpr, c.etag, c.in_pic_name, c.out_pic_name, m.member_name as owner')
        			->from('cario c')
                    ->join('members m', 'c.member_no = m.member_no', 'left') 
                    ->where(array('c.err' => 0))
                    ->order_by('c.update_time', 'desc')
                    ->limit(10)
                    ->get()
                    ->result_array();
        */ 
        $sql = '(select c.cario_no, c.in_out, in_lane, out_lane, c.in_time, c.out_time, c.minutes, c.obj_id as lpr, c.etag, c.in_pic_name, c.out_pic_name, m.member_name as owner, c.in_time as time_order 
					from cario c left join members m on c.obj_id = m.lpr 
					where c.err = 0 and c.out_time is null) 
				union 
				(select c.cario_no, c.in_out, in_lane, out_lane, c.in_time, c.out_time, c.minutes, c.obj_id as lpr, c.etag, c.in_pic_name, c.out_pic_name, m.member_name as owner, c.out_time as time_order 
					from cario c left join members m on c.obj_id = m.lpr 
					where c.err = 0 and c.out_time is not null) 
				order by time_order desc limit 10;'; 
        $data_cario = $this->db->query($sql)->result_array();
                                              
        // $lane_arr = array(0 => '入1', 1 => '入2', 3 => '出3', 3 => '出4');         
        $idx = 0;   
        foreach($data_cario as $rows)
        {                                
        	++$rows['in_lane'];
        	++$rows['out_lane'];  
                                    
            //$lane_no = $rows['in_out'] == 'CI' ? "入{$rows['in_lane']}" : "入{$rows['in_lane']} -> 出{$rows['out_lane']}";
			$lane_no = empty($rows['out_time']) ? "入{$rows['in_lane']}" : "入{$rows['in_lane']} -> 出{$rows['out_lane']}"; // 2016/08/22 有離場時間就顯示
            
	        $pic_name = str_replace('.jpg', '', empty($rows['out_pic_name']) ? $rows['in_pic_name'] : $rows['out_pic_name']);
            $arr = explode('-', $pic_name);
			$pic_path = isset($arr[7]) ? APP_URL.'pics/'.substr($arr[7], 0, 8).'/'.$pic_name : '';
              
            $data[$idx] = array
            (
              	// 'io_name' => $io_name[$rows['in_out']],
				'cario_no' => $rows['cario_no'],
				
              	'io_name' => $lane_no,
              	'lpr' => $rows['lpr'],
              	// 'etag' => $rows['etag'],
              	'etag' => $rows['etag'],
              	'owner' => $rows['owner'],
              	'io_time' => empty($rows['out_time']) ? $rows['in_time'] : "{$rows['in_time']}(入)<br>{$rows['out_time']}(出)<br>{$rows['minutes']}分(停留時間)",
              	'pic_name' => $pic_path
            );            
            ++$idx;
        }
            
        return $data; 
    }   
	
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
    
    // 車號入場查詢
	public function carin_lpr_query($word) 
	{                      
		// updated 2016/09/09 fuzzy search
    	if(empty($word) || strlen($word) < 4 || strlen($word) > 10)
		{
			return array();
		}
		$fuzzy_statement = $this->getLevenshteinSQLStatement($word, 'c.obj_id');
		trigger_error("模糊比對 {$word} where: {$fuzzy_statement}");
		
		$sql = "SELECT c.cario_no, c.in_out, in_lane, out_lane, c.in_time, c.out_time, c.minutes, c.obj_id as lpr, c.etag, c.in_pic_name, c.out_pic_name, m.member_name as owner
				FROM cario c 
				LEFT JOIN members m ON c.obj_id = m.lpr 
				WHERE {$fuzzy_statement} AND c.err = 0 AND c.obj_type = 1 
				ORDER BY c.update_time DESC
				LIMIT 50";
		$data_cario = $this->db->query($sql)->result_array();
        
		/*
    	$data_cario = $this->db
        ->select('c.cario_no, c.in_out, in_lane, out_lane, c.in_time, c.out_time, c.minutes, c.obj_id as lpr, c.etag, c.in_pic_name, c.out_pic_name, m.member_name as owner')
        ->from('cario c')
        ->join('members m', 'c.member_no = m.member_no', 'left') 
        ->where(array('c.obj_type' => 1, 'obj_id' => $lpr)) 
        ->order_by('c.update_time', 'desc')
        ->get()
        ->result_array();  
         */
		 
        $data = array();
        $idx = 0;   
        foreach($data_cario as $rows)
        {                                
        	++$rows['in_lane'];
        	++$rows['out_lane'];    
            
			$lane_no = empty($rows['out_time']) ? "入{$rows['in_lane']}" : "入{$rows['in_lane']} -> 出{$rows['out_lane']}"; 
            $io_time = empty($rows['out_time']) ? $rows['in_time'] : "{$rows['in_time']}(入)<br>{$rows['out_time']}(出)<br>{$rows['minutes']}分(停留時間)";
			
	        $pic_name = str_replace('.jpg', '', empty($rows['out_pic_name']) ? $rows['in_pic_name'] : $rows['out_pic_name']);
            $arr = explode('-', $pic_name);
            $pic_path = APP_URL.'pics/'.substr($arr[7], 0, 8).'/'.$pic_name;
            
            $data[$idx++] = array
            (
              	'io_name' => $lane_no,
              	'lpr' => $rows['lpr'],
              	'etag' => $rows['etag'],
              	'owner' => empty($rows['owner']) ? '' : $rows['owner'],
              	'io_time' => $io_time,
              	'pic_name' => $pic_path
            );            
        }  
        
        return $data; 
    } 
    
    
    
    // 車號入場查詢
	public function carin_time_query($time_query, $minutes_range) 
	{            
    	$curr_time = date('Y-m-d H:i:s');
                                  
    	$start_time = date('Y-m-d H:i:s', strtotime("{$time_query} - {$minutes_range} minutes"));
    	$end_time = date('Y-m-d H:i:s', strtotime("{$time_query} + {$minutes_range} minutes"));
        
    	$data_cario = $this->db
        ->select('c.cario_no, c.in_out, in_lane, out_lane, c.in_time, c.out_time, c.minutes, c.obj_id as lpr, c.etag, c.in_pic_name, c.out_pic_name, m.member_name as owner')
        ->from('cario c')
        ->join('members m', 'c.obj_id = m.lpr', 'left') 
        ->where(array('c.obj_type' => 1, 'c.in_time >=' => $start_time, 'c.in_time <=' => $end_time)) 
        ->order_by('c.update_time', 'desc')
        ->get()
        ->result_array();  
        
        $data = array();
        $idx = 0;   
        foreach($data_cario as $rows)
        {                                
        	++$rows['in_lane'];
        	++$rows['out_lane'];    
            
			$lane_no = empty($rows['out_time']) ? "入{$rows['in_lane']}" : "入{$rows['in_lane']} -> 出{$rows['out_lane']}"; 
            $io_time = empty($rows['out_time']) ? $rows['in_time'] : "{$rows['in_time']}(入)<br>{$rows['out_time']}(出)<br>{$rows['minutes']}分(停留時間)";                    
            
	        $pic_name = str_replace('.jpg', '', empty($rows['out_pic_name']) ? $rows['in_pic_name'] : $rows['out_pic_name']);
            $arr = explode('-', $pic_name);
            $pic_path = APP_URL.'pics/'.substr($arr[7], 0, 8).'/'.$pic_name;
            
            $data[$idx++] = array
            (
              	'io_name' => $lane_no,
              	'lpr' => $rows['lpr'],
              	'etag' => $rows['etag'],
              	'owner' => empty($rows['owner']) ? '' : $rows['owner'],
              	'io_time' => $io_time,
              	'pic_name' => $pic_path
            );            
        }
            
        return $data; 
    }    
	
	
	// 車辨失敗查詢
	public function carin_none_query($time_query, $hours_range) 
	{            
    	$curr_time = date('Y-m-d H:i:s');
		
    	$start_time = date('Y-m-d H:i:s', strtotime("{$time_query} - {$hours_range} hours"));
    	$end_time = date('Y-m-d H:i:s', strtotime("{$time_query} + {$hours_range} hours"));
        
    	$data_cario = $this->db
        ->select('c.cario_no, c.in_out, in_lane, out_lane, c.in_time, c.out_time, c.minutes, c.obj_id as lpr, c.etag, c.in_pic_name, c.out_pic_name')
        ->from('cario c')
        ->where(array(	'c.in_time >=' => $start_time, 
						'c.in_time <=' => $end_time, 
						'c.obj_id' => 'NONE', 
						'c.err' => 0, 
						'c.finished' => 0
			)) 
        ->order_by('c.in_time', 'desc')
        ->get()
        ->result_array();  
        
        $data = array();
        $idx = 0;   
        foreach($data_cario as $rows)
        {                                
        	++$rows['in_lane'];
        	++$rows['out_lane'];    
            
			$lane_no = empty($rows['out_time']) ? "入{$rows['in_lane']}" : "入{$rows['in_lane']} -> 出{$rows['out_lane']}"; 
            $io_time = empty($rows['out_time']) ? $rows['in_time'] : "{$rows['in_time']}(入)<br>{$rows['out_time']}(出)<br>{$rows['minutes']}分(停留時間)";                    
            
	        $pic_name = str_replace('.jpg', '', empty($rows['out_pic_name']) ? $rows['in_pic_name'] : $rows['out_pic_name']);
            $arr = explode('-', $pic_name);
            $pic_path = APP_URL.'pics/'.substr($arr[7], 0, 8).'/'.$pic_name;
            
            $data[$idx++] = array
            (
				'cario_no' => $rows['cario_no'],
              	'io_name' => $lane_no,
              	'lpr' => $rows['lpr'],
              	'etag' => $rows['etag'],
              	'owner' => '',
              	'io_time' => $io_time,
              	'pic_name' => $pic_path
            );            
        }
            
        return $data; 
    }    
    
    // 時間長度轉成日時分秒
	public function time2str($d1, $d2)
	{    
    $time = strtotime($d2) - strtotime($d1);
    
    $day_str = floor($time/3600/24);
    $day_str = $day_str ? $day_str .= '天 ' : '';
                                 
    $hour_str = floor($time%(24*3600)/3600);
    $hour_str = $hour_str ? $hour_str .= '小時 ' : '';      
    
    $minute_str = floor($time%3600/60);
    $minute_str = $minute_str ? $minute_str .= '分' : ''; 
     
    /*
    $second_str = $time%3600%60;
    $second_str = $second_str ? $second_str .= ' seconds ' : ''; 
    
    $n_time = floor($time/3600/24)."days".floor($time%(24*3600)/3600)."Hour".floor($time%3600/60)."Minute".($time%3600%60)."Second";
    */
    
    $n_time = $day_str . $hour_str . $minute_str;
    return $n_time;
	}           
    
        
    // 在席車位檢查未有入場資料清單
	public function pks_check_list($max_rows) 
	{          
    	$data = array();          
    	$data = $this->db
        	->select('pksno, lpr, in_time, pic_name')
        	->from('pks')
            ->where(array('status' => 'LR', 'cario_no' => 0, 'confirms' => 0, 'station_no' => STATION_NO))
            ->order_by('in_time', 'desc')
            ->limit($max_rows)
            ->get()
            ->result_array();  
        return $data; 
    }          
    
    
    // 重設在席查核
	public function reset_pks_check() 
	{                  
    	// 讀出未查核過的資料
    	$data_pks = $this->db
        	->select('pksno, lpr, in_time')
        	->from('pks')
            ->where(array('status' => 'LR', 'cario_no' => 0, 'station_no' => STATION_NO))
            ->get()
            ->result_array(); 
            
        // $tot = $this->db->num_rows();	// 總筆數         
        $tot = count($data_pks);	// 總筆數         
         
        $num_cario = 0;		// 入場資料筆數   
        foreach($data_pks as $rows)
		{                        
            $lpr = $rows['lpr'];
            if ($lpr == 'NONE')		continue; 	// 車辨失敗者不處理
                             
        	$pksno = $rows['pksno'];
            $pks_in_time = $rows['in_time'];
        	// 讀取進場時間, 如讀不到資料, 以目前時間取代(add by TZUSS 2016-02-23)  
            $rows_cario = $this->db
        				->select('cario_no, in_time')
        				->from('cario')
                		->where(array('in_out' => 'CI', 'obj_id' => $lpr, 'finished' => 0, 'err' => 0, 'station_no' => STATION_NO))
                  		->order_by('cario_no', 'desc')   
                  		->limit(1)
                		->get()
                		->row_array();     
            // if ($this->db->num_rows() == 1)		// 有入場資料 
            if (!empty($rows_cario['cario_no']))		// 有入cario_no場資料 
            {
            	$cario_no = $rows_cario['cario_no'];	// 入場序號 
                $in_time = $rows_cario['in_time'];  
                // 在席與入場資料相符, 分別在cario與pks記錄之
                $data = array
                (
                	'pksno' => $pksno,
                    'pks_time' => $pks_in_time
                );        
                $this->db->update('cario', $data, array('cario_no' => $cario_no, 'station_no' => STATION_NO));
        		$data = array
            	(                                
                    'cario_no' => $cario_no,
                    'in_time' => $in_time
            	);            
            	// 車號及照片檔名填入資料庫內	
            	$this->db->update('pks', $data, array('pksno' => $pksno, 'station_no' => STATION_NO));               
                ++$num_cario;
            }
          
        }
        return array('tot' => $tot, 'tot_correct' => $num_cario); 
    }   
    
    
    // 更正在席車號
	public function correct_pks_lpr($pksno, $lpr) 
	{                    
    
    	// 讀取進場時間, 如讀不到資料, 以目前時間取代(add by TZUSS 2016-02-23)  
        $rows_cario = $this->db
        	->select('cario_no, in_time')
        	->from('cario')
            ->where(array('in_out' => 'CI', 'obj_id' => $lpr, 'finished' => 0, 'err' => 0, 'station_no' => STATION_NO))
            ->order_by('cario_no', 'desc')   
            ->limit(1)
            ->get()
            ->row_array();
             
        if (!empty($rows_cario['cario_no']))		// 有cario_no入場資料 
        {
            	$cario_no = $rows_cario['cario_no'];	// 入場序號 
                $in_time = $rows_cario['in_time'];  
                // 在席與入場資料相符, 分別在cario與pks記錄之
                $data = array
                (
                	'pksno' => $pksno,
                    'pks_time' => $in_time
                );        
                $this->db->update('cario', $data, array('cario_no' => $cario_no, 'station_no' => STATION_NO));
        		$data = array
            	(                           
                	'confirms' => 1,     
                    'cario_no' => $cario_no,
                    'lpr' => $lpr,
                    'in_time' => $in_time
            	);            
            	// 車號及照片檔名填入資料庫內	
            	$this->db->update('pks', $data, array('pksno' => $pksno, 'station_no' => STATION_NO));
                $results = array
                (
                 	'err' => 0,       
                    'cario_no' => $cario_no
                );               
        }
        else	// 無入場資料
        {                                     
        		$data = array
                (                  
                	'confirms' => 1,
                	'lpr' => $lpr
                );     
                
            	$this->db->update('pks', $data, array('pksno' => $pksno, 'station_no' => STATION_NO));
                $results = array
                (
                 	'err' => 0,		
                    'cario_no' => 0	// 車號查無入場資料     
                );  	
        }
                        
        return $results; 
    }    
    
        
    // 入場車號查核在席無資料清單
	public function carin_check_list($max_rows) 
	{          
    	$data = array();          
    	$rows_cario = $this->db
        	->select('cario_no, obj_id as lpr, in_time, member_no, in_pic_name')
        	->from('cario')
            ->where('in_out', 'CI')
            ->where(array('pksno' => 0, 'finished' => 0, 'err' => 0, 'confirms' => 0, 'station_no' => STATION_NO, 'in_time <=' => 'date_sub(now(), interval 20 minute)'), null, false)
            ->order_by('cario_no', 'desc')   
            ->limit($max_rows)
            ->get()
            ->result_array();  
            
        $idx = 0;
        foreach($rows_cario as $rows)
        {
        	$data[$idx++] = array
            (
              	'cario_no' => $rows['cario_no'],
              	'lpr' => $rows['lpr'], 
                'in_time' => $rows['in_time'],
                'type' => $rows['member_no'] == 0 ? '' : '月租',
                'pic_name' => str_replace('-', '', substr($rows['in_time'], 0, 10)) . '/' . $rows['in_pic_name']
            ); 
        }          
        return $data; 
    }      
    
    
    
    // 更正入場車號
	public function correct_carin_lpr($cario_no, $lpr, $in_time)
	{ 
    	$rows = $this->db
        	->select('pksno, cario_no, in_time')
        	->from('pks')
            ->where(array('status' => 'LR', 'lpr' => $lpr, 'confirms' => 0, 'station_no' => STATION_NO))
            ->limit(1)
            ->get()
            ->row_array();       
        // 如果在席資料相符
        if (!empty($rows['pksno']))
        {
        	$pksno = $rows['pksno'];
            
            $data = array
            (
            	'cario_no' => $cario_no,
                'in_time' => $in_time
            ); 
            
            $this->db->update('pks', $data, array('pksno' => $pksno, 'station_no' => STATION_NO));
            
            $data_cario = array
            (                   
            	'obj_id' => $lpr,
            	'pksno' => $pksno,
                'pks_time' => $in_time,
                'confirms' => 1
            ); 	
        }        
        else	// 無在席資料
        {   
        	$data_cario = array
            (
            	'obj_id' => $lpr,
                'pksno' => 0,
                'confirms' => 1
            );
        }  
        
        $this->db->update('cario', $data_cario, array('cario_no' => $cario_no, 'station_no' => STATION_NO));
        
        return(array('pksno' => $data_cario['pksno'])); 
    }

    // 查詢行動支付記錄
	public function tx_bill_query() 
	{                    
		$sql = "SELECT 
					tx_bill.order_no as order_no, tx_bill.lpr as lpr, tx_bill.invoice_no as invoice_no, tx_bill.in_time as in_time, tx_bill.balance_time as balance_time, tx_bill.company_no as company_no, tx_bill.email as email, tx_bill.mobile as mobile, tx_bill.amt as amt, tx_bill.tx_time as tx_time, 
					allpay_feedback_log.rtn_msg as rtn_msg, allpay_feedback_log.payment_type as payment_type, 
					cario.out_before_time as out_before_time
				FROM tx_bill 
				LEFT JOIN cario ON tx_bill.cario_no = cario.cario_no
				LEFT JOIN allpay_feedback_log ON tx_bill.order_no = allpay_feedback_log.merchant_trade_no 
				WHERE tx_bill.status = 111
				ORDER BY tx_bill.tx_time DESC";
				
        return $this->db->query($sql)->result_array(); 
    } 
	
	// 查詢月租繳款機記錄
	public function tx_bill_ats_query() 
	{                    
		$sql = "SELECT 
					tx_bill_ats.order_no as order_no, tx_bill_ats.lpr as lpr, tx_bill_ats.invoice_no as invoice_no, 
					tx_bill_ats.end_time as end_time, tx_bill_ats.next_start_time as next_start_time, tx_bill_ats.next_end_time as next_end_time, 
					tx_bill_ats.company_no as company_no, tx_bill_ats.email as email, tx_bill_ats.mobile as mobile, tx_bill_ats.amt as amt, 
					tx_bill_ats.remarks as remarks, tx_bill_ats.member_name as member_name, tx_bill_ats.tx_time as tx_time, 
					allpay_feedback_log.rtn_msg as rtn_msg, allpay_feedback_log.payment_type as payment_type
				FROM tx_bill_ats 
				LEFT JOIN allpay_feedback_log ON tx_bill_ats.order_no = allpay_feedback_log.merchant_trade_no 
				WHERE tx_bill_ats.status = 111
				ORDER BY tx_bill_ats.tx_time DESC";
				
        return $this->db->query($sql)->result_array(); 
    }
		
	// 查詢樓層在席群組
	public function pks_group_query() 
	{                    
		$sql = "SELECT 
					pks_groups.station_no, pks_groups.group_name as group_name, pks_groups.tot as tot, pks_groups.parked as parked, pks_groups.availables as availables, pks_groups.group_id as group_id, pks_groups.renum as renum 
				FROM pks_groups 
				ORDER BY pks_groups.group_name * 1 DESC";
				
        return $this->db->query($sql)->result_array(); 
    } 

	// 送出至message queue(目前用mqtt)
	public function mq_send($topic, $msg)
	{
		$this->vars['mqtt']->publish($topic, $msg, 0);
    	trigger_error("mqtt:{$topic}|{$msg}");
    }
	
}
