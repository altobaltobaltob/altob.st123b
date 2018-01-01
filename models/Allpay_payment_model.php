<?php             
/*
file: Allpay_payment_model.php 付費系統 (歐付寶)
*/          

class Allpay_payment_model extends CI_Model 
{        
	function __construct()
	{
		parent::__construct(); 
		$this->load->database(); 
    }
	        
	// 記錄 (歐付寶付費)
	public function create_allpay_feedback_log($data)
	{
		$this->db->insert('allpay_feedback_log', $data);
		return true;
	}
}
