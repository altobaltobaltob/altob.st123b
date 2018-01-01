<?php             
/*
file: Landb_model.php 撈區網資料庫
*/                   

class Landb_model extends CI_Model 
{             
	function __construct()
	{
		parent::__construct(); 
        // 資料庫連線
        //$dsn_old_db = 'mysqli://root:HISHARP@192.168.0.2:3306/parking';
		//$this->dsn_old_db = $this->load->database($dsn_old_db, true);
        
        $this->now_str = date('Y-m-d H:i:s'); 
    }

    // test
	public function test($lpr) 
	{                  
		$result	= $lpr.' @ '. $this->now_str.' @ ';
		
		$sql = "SELECT ParkingNum as pksno, LPR as lpr
				FROM table_carpark where lpr = 'xxx'";

		$dsn_old_db = $this->load->database('old_db', true);
		
		/*
		$config['hostname'] = '192.168.0.2';
		$config['port']     = "3306";  
		$config['username'] = 'root';
		$config['password'] = 'HISHARP';
		$config['database'] = 'parking';
		$config['dbdriver'] = 'mysqli';
		$config['dbprefix'] = '';
		$config['pconnect'] = FALSE;
		$config['db_debug'] = TRUE;
		$config['cache_on'] = FALSE;
		$config['cachedir'] = '';
		$config['char_set'] = 'utf8';
		$config['dbcollat'] = 'utf8_general_ci';
		$dsn_old_db = $this->load->database($config, true);
		*/
		
		$retults = $dsn_old_db->query($sql)->result_array();
		
		//$retults = $this->dsn_old_db->query($sql)->result_array();
		$seat_no = '';
		if(!empty($retults[0]))
		{
			$seat_no = '-'.substr($retults[0]['pksno'], 0, 1).'_'.substr($retults[0]['pksno'], 1);
		}
		
    	return 
			'result >>>>>' . $result . '@' . '<br/>'. 
			$sql . '<br/>'.
			json_encode($retults, true).'<br/>'.
			$seat_no;
    }  
}
