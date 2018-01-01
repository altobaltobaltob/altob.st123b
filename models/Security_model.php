<?php             
/*
file: Security_model.php 停車管理系統 (鎖車)
*/                   

class Security_model extends CI_Model 
{             
    
	function __construct()
	{
		parent::__construct(); 
		$this->load->database(); 
    }   
	
	// 更改會員密碼
	public function change_pswd($lpr, $new_pswd) 
	{          
		$data = array('passwd' => $new_pswd);
		$this->db->update('members', $data, array('lpr' => $lpr));
		return 'ok';
    }
    
    // 防盜鎖車
    // http://203.75.167.89/parkingquery.html/security_action/ABC1234/pswd/2
	public function security_action($lpr, $pswd, $action)
	{                      
    	$data = array();    
        /*
    	$rows = $this->db->select('member_no, passwd, locked')
        		->from('members')
                ->where(array('lpr' => $lpr, 'passwd' => $pswd))	     
                ->limit(1)
                ->get()  
                ->row_array(); 
        trigger_error('防盜鎖車:'.$this->db->last_query());
                                                        
        // 無資料或密碼錯誤
        if (empty($rows['member_no']))
        {
          	$data['result_code'] = 'FAIL';
            return($data);
        } 
        */
          
    	$rows = $this->db->select('member_no, passwd, locked, lpr')
        		->from('members')
                ->where(array('lpr' => $lpr))	     
                ->limit(1)
                ->get()  
                ->row_array(); 
        trigger_error('防盜鎖車:'.$this->db->last_query());
                                                        
        // 無資料或密碼錯誤
        if (empty($rows['member_no']) || md5($rows['passwd']) != $pswd)
        {
			// 密碼未設定且輸入密碼為車牌號碼
			if(empty($rows['passwd']) && md5($rows['lpr']) == $pswd){
				// do nothing
			}else{
				$data['result_code'] = 'FAIL';
				return($data);	
			}
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
