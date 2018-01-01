<?php             
/*
file: Qcar_model.php 停車管理系統
*/                   

class Qcar_model extends CI_Model 
{        
	function __construct()
	{
		parent::__construct(); 
		$this->load->database(); 
    }   
                     
    // 查車
	public function q_pks($lpr) 
	{                 
        $sql = "select p.pksno, p.pic_name, p.update_time, p.in_time, p.posx, p.posy, m.group_id, g.group_name, g.floors
        		from pks p, pks_group_member m, pks_groups g 
                where p.pksno = m.pksno  
                and m.group_id = g.group_id
                and g.group_type = 1
                and p.lpr = '{$lpr}'
                limit 1"; 
        $rows = $this->db->query($sql)->row_array();
                
		/*
        if (!empty($rows['pic_name']))
        {   
        	// $rows['pic_name'] = str_replace('.jpg', '', $rows['pic_name']); 
        	$rows['pic_name'] = $rows['pic_name']; 
        }   
        else	// 查無資料, 啟用模糊比對
        {                                
        	$len = strlen($lpr);
            if ($len >= 5)	// 檢查車牌號碼長度 
            {
            	$arr = explode(';', file_get_contents("http://192.168.11.253:8090/cgi-bin/parking_status.cgi?CMD=QUERY_SEAT&LPR={$lpr}"));  
            	$pksno = $arr[0];      
            }   
            else
            {
            	$pksno = 0;		// 車牌號碼長度錯誤                
            }
            
            trigger_error("電腦查詢模糊比對:[{$lpr}]:" .  print_r($arr, true));
            if ($pksno != 0)	// 模糊比對成功
            {       
        		$sql = "select p.pic_name, p.update_time, p.in_time, p.posx, p.posy, m.group_id, g.group_name, g.floors
        		from pks p, pks_group_member m, pks_groups g 
                where p.pksno = m.pksno  
                and m.group_id = g.group_id
                and g.group_type = 1
                and p.pksno = {$pksno}
                limit 1"; 
        		$rows_pks = $this->db->query($sql)->row_array(); 
                
                $rows['pksno'] = $pksno;
                // $rows['pic_name'] = str_replace('.jpg', '', $rows_pks['pic_name']);
                $rows['pic_name'] = $rows_pks['pic_name'];
                $rows['update_time'] = $rows_pks['update_time'];
				$rows['in_time'] = $rows_pks['in_time'];
				$rows['floors'] = $rows_pks['floors'];
                $rows['posx'] = $rows_pks['posx'];
                $rows['posy'] = $rows_pks['posy'];
                $rows['group_id'] = $rows_pks['group_id'];
                $rows['group_name'] = $rows_pks['group_name'];
            }
            else	// 模糊比對仍是失敗
            {
        		$rows['pksno'] = '0';		// 無該筆資料
            }
          
        }
		*/
        
        return $rows; 
    } 
    
    
    // 月租會員加入
	public function q_rents($lpr) 
	{                    
    	$rows = $this->db->select("station_no, member_no, member_name, date_format(end_date, '%Y-%m-%d') as end_date, amt , date_add(date_format(end_date, '%Y-%m-%d'), INTERVAL 1 day) as next_start, date_add(date_format(end_date, '%Y-%m-%d'), INTERVAL 1 MONTH) as next_end", false)
        	->from('members')	
            ->where('lpr', $lpr) 
            ->get()
            ->row_array(); 
        if (empty($rows['member_no']))	$rows['member_no'] = 0;		// 無此資料
        
        return $rows; 
    }  
    
    // 新增月租轉帳資料
	public function transfer_money_create($data) 
	{          
    	$this->db->insert('tx_money', $data); 
    }
	
	// 更新月租轉帳資料 (已結帳)
	public function transfer_money_done($order_no) 
	{          
		$data = array();
		$data['status'] = 1; //狀態,0:剛建立, 1:已結帳, 2:錢沒對上
		$this->db->update('tx_money', $data, array('order_no' => $order_no));
		return true;
    }
	
	// 更新發票號碼
	public function transfer_money_set_invoice($order_no, $invoice_no) 
	{          
		$data = array();
		$data['invoice_no'] = $invoice_no;
		$this->db->update('tx_money', $data, array('order_no' => $order_no));
		return true;
    }
	
	// 找不到POS機
	public function transfer_money_done_with_error_10($order_no) 
	{          
    	$data = array();
		$data['status'] = 10; //狀態,0:剛建立, 1:已結帳, 2:錢沒對上, 3:發票沒拿到, 4:手動調整, 10:找不到POS機
		$this->db->update('tx_money', $data, array('order_no' => $order_no));
		return true;
    }
	
	// 更新發票號碼失敗
	public function transfer_money_set_invoice_error_4($order_no) 
	{          
		$data = array();
		$data['status'] = 3; //狀態,0:剛建立, 1:已結帳, 2:錢沒對上, 3:發票沒拿到
		$this->db->update('tx_money', $data, array('order_no' => $order_no));
		return true;
    }
	
	// 更新月租轉帳資料 (錢沒對上)
	public function transfer_money_done_with_error_2($order_no) 
	{          
    	$data = array();
		$data['status'] = 2; //狀態,0:剛建立, 1:已結帳, 2:錢沒對上
		$this->db->update('tx_money', $data, array('order_no' => $order_no));
		return true;
    }

    // 取得月租轉帳資料	
	public function get_tx_money($order_no) 
	{          
		$result = $this->db
		  ->from('tx_money')
		  ->where(array('order_no' => $order_no))
		  ->get()
		  ->result_array();
		return $result;
    }
	
	// 歐付寶記錄
	public function create_allpay_feedback_log($data)
	{
		$this->db->insert('allpay_feedback_log', $data);
		return true;
	}
    
    // 將發票號碼加入資料庫
	public function update_invoice_no($order_no, $invoice_no) 
	{          
    	$this->db->where(array('order_no' => $order_no)) 
        	->update('tx_money', array('invoice_no' => $invoice_no)); 
    }
	
	// 新增發票記錄
	public function invoice_log_create($data) 
	{          
    	$this->db->insert('tx_invoice_log', $data); 
    }
	
	// 新增發票記錄回傳
	public function invoice_log_set_response($response_code, $invoice_no, $seqno) 
	{          
		$data = array();
		$data['response_code'] = $response_code;
		$this->db->update('tx_invoice_log', $data, array('invoice_no' => $invoice_no, 'seqno' => $seqno));
		return true;
    }
	
	// 取得POS機資訊	
	public function get_tx_pos($pos_id) 
	{          
		$result = $this->db
		  ->from('tx_pos')
		  ->where(array('pos_id' => $pos_id))
		  ->get()
		  ->result_array();
		return $result;
    }
	
	// 取得POS機資訊 by lan_ip
	public function get_tx_pos_by_lan_ip($lan_ip) 
	{          
		$result = $this->db
		  ->from('tx_pos')
		  ->where(array('lan_ip' => $lan_ip))
		  ->get()
		  ->result_array();
		return $result;
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
	
	// 取得進場資訊 (模糊比對)
	public function q_fuzzy_pks($word)
	{
		if(empty($word) || strlen($word) <= 0 || strlen($word) > 10)
		{
			return null;
		}
		
		$sql = "SELECT station_no, lpr, in_time, pic_name as pks_pic_name
				FROM pks
				WHERE {$this->getLevenshteinSQLStatement($word, 'lpr')} 
				ORDER BY lpr ASC";
		$retults = $this->db->query($sql)->result_array();
		
		if(count($retults) > 0)
		{
        	foreach ($retults as $idx => $rows) 
			{
				$pks_pic_path = '';
				if(!empty($rows['pks_pic_name']))
				{
					//$pks_pic_path = APP_URL.'pks_pics/'.str_replace('.jpg', '', $rows['pks_pic_name']);
					$pks_pic_path = SERVER_URL.'pkspic/'.$rows['pks_pic_name'];
				}
				
				$data['result'][$idx] = array
				(
					'lpr'=> $rows['lpr'],
					'pks_pic_path' => $pks_pic_path,
					'station_no' => $rows['station_no'],
					'in_time' => $rows['in_time']
				);
			}
		}
		else
		{
			// 讀取入場資料
			$sql = "SELECT cario.station_no as station_no, cario.obj_id as lpr, cario.in_time as in_time, cario.in_pic_name as pks_pic_name
					FROM cario
					WHERE {$this->getLevenshteinSQLStatement($word, 'obj_id')} 
					AND in_out = 'CI' AND finished = 0 AND err = 0 AND out_time IS NULL
					ORDER BY lpr ASC";
					// AND in_time > DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 5 DAY) 
			$retults = $this->db->query($sql)->result_array();
			
			if(count($retults) > 0)
			{
				foreach ($retults as $idx => $rows) 
				{
					$pks_pic_path = '';
					if(!empty($rows['pks_pic_name']))
					{
						$pic_name = str_replace('.jpg', '', $rows['pks_pic_name']);
						$arr = explode('-', $pic_name);
						$pks_pic_path = SERVER_URL.'carspic/'.substr($arr[7], 0, 8).'/'.$pic_name.'.jpg';
					}
					
					$data['result'][$idx] = array
					(
						'lpr'=> $rows['lpr'],
						'pks_pic_path' => $pks_pic_path,
						'station_no' => $rows['station_no'],
						'in_time' => $rows['in_time']
					);
				}
			}
		}
		return $data;
		
		/*
		foreach ($retults as $idx => $rows) 
        {
			$pks_pic_path = '';
			if(!empty($rows['pks_pic_name']))
			{
				//$pks_pic_path = APP_URL.'pks_pics/'.str_replace('.jpg', '', $rows['pks_pic_name']);
				$pks_pic_path = SERVER_URL.'pkspic/'.$rows['pks_pic_name'];
			}
			
			$data['result'][$idx] = array
            (
				'lpr'=> $rows['lpr'],
				'pks_pic_path' => $pks_pic_path,
				'station_no' => $rows['station_no'],
				'in_time' => $rows['in_time']
            );
		}
		*/
	}
	
	
	
	
	
	
	
}
