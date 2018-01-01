<?php             
/*
file: User_model.php 管理登入系統
*/                   

class User_model extends CI_Model 
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

	// 登入
	public function user_login($data)
	{
		$login_name = $data['login_name'];
		$pswd = $data['pswd'];
		
		$this -> db -> select('login_name, user_type, status');
		$this -> db -> from('users');
		$this -> db -> where('login_name', $login_name);
		$this -> db -> where('pswd', MD5($pswd));
		$this -> db -> where('status', 1); // '狀態, 1:正常, 2:暫時停權, 3:永久停權'
		$this -> db -> limit(1);
		$query = $this -> db -> get();
		
		if($query -> num_rows() == 1)
		{
		   return $query->result();
		}
		else
		{
			return false;
		}
	}
     
    // 新增
	public function user_insert($data) 
	{                           
		$data['status'] = 1; // '狀態, 1:正常, 2:暫時停權, 3:永久停權'
    	$this->db->insert('users', $data);
		
        return true; 
    }  
	
	// 修改
	public function user_update($data, $target_name) 
	{                           
        $this->db->update('users', $data, array('login_name' => $target_name));         
        
        return true; 
    }  
	
    // 查詢
	public function user_query() 
	{                    
    	$results = $this->db->select('login_name, user_name, status, modify_time, user_type')
        		->from('users')
				->where(array('user_type' => 'user')) // 'admin:最高管理者, user:用戶'
                ->order_by('modify_time', 'desc')	
                ->get()  
                ->result_array();  
        
        return $results; 
    }        
    
    // 刪除
	public function user_delete($login_name) 
	{                    
    	$this->db->delete('users', array('login_name' => $login_name));
		
        return true; 
    }  
}
