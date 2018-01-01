<?php             
/*
file: Qcar3_model.php 查車3
*/                   

class Qcar3_model extends CI_Model 
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
                
        //if (!empty($rows['pic_name']))	$rows['pic_name'] = str_replace('.jpg', '', $rows['pic_name']);
        //else $rows['pksno'] = 0;		// 如無該筆資料, 車位號碼設為0
        
        return $rows; 
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
		/*
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
						//$pks_pic_path = SERVER_URL.'carspic/'.substr($arr[7], 0, 8).'/'.$pic_name.'.jpg';
						$pks_pic_path = SERVER_URL.'carpic/'.substr($arr[7], 0, 8).'/'.$pic_name.'.jpg';
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
		*/
		return $data;
	}
	
}
