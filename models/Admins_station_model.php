<?php             
/*
file: Admins_station_model.php
*/                   

class Admins_station_model extends CI_Model 
{             
    var $vars = array();
        
	function __construct()
	{
		parent::__construct(); 
		$this->load->database(); 
        
        $this->now_str = date('Y-m-d H:i:s'); 
		$this->default_valid_time = date('Y-m-d H:i:s', strtotime("{$this->now_str} + 2 days"));	// 2016/12/15 新增有效期限 (預設為兩天)
    }   
     
	public function init($vars)
	{                        
    	$this->vars = $vars;
    }
       
    // 付款
	public function login_verify($login_name, $login_pswd) 
	{                                            
    	$rows = $this->db->select('count(*) as ok')
        			->from('staffs')	
                    ->where(array('login_name' => $login_name, 'pswd' => md5($login_pswd))) 
                    ->get()
                    ->row_array();       
                       
        return $rows['ok'];
    }   
    
    
    // 讀出各初始值至web
	public function get_init_vars() 
	{             
        $st_info = $this->vars['mcache']->get('st_info');
    	// 路徑初始值         
    	$str = 	"var APP_URL = '". APP_URL . "';\n".
        		"var WEB_LIB = '" . WEB_LIB . "';\n".
        		"var BOOTSTRAPS = '" . BOOTSTRAPS . "';\n".
        		"var WEB_SERVICE = '" . WEB_SERVICE . "';\n".
        		"var station_no ={$st_info['station_no']};\n".
        		"var company_no = {$st_info['company_no']};\n". 
        		"var xvars = new Array();\n".
        		"xvars['ck'] = 'NOLOGIN';\n";
             
    	// 讀出場站資訊 
		$str .= "var st = new Array();\n";		
		$str .= "st[" . STATION_NO . "]=\"" . STATION_NAME . "\";\n";
		/*	
    	$results = $this->db->select('station_no, short_name')
        			->from('stations')	
                    ->order_by('station_no', 'asc') 
                    ->get()
                    ->result_array();       
        $str .= "var st = new Array();\n";
        foreach($results as $rows)
        {
         	$str .= "st[{$rows['station_no']}]=\"{$rows['short_name']}\";\n";
        } 
		*/
                
        // 讀出時段表資訊      
        $str .= "var pt = new Array();\n";	// park_time
        foreach($this->vars['mcache']->get('pt') as $key => $rows)
        {
         	$str .= "pt['{$key}']={'seqno':{$rows['seqno']},'remarks':'{$rows['remarks']}'};\n";
        }  
        
        /*
        // 讀取繳期名稱
    	$rows = $this->db->select('period_name')
        			->from('info')
                    ->where('seqno', 1)	
                    ->get()
                    ->row_array(); 
        $str .= "var period_name=Array();\nperiod_name={$rows['period_name']};\n";
        */                  
        
        
        
        // 篩選會員身份及繳期資訊
        $info = $this->vars['mcache']->get('info'); 
        
        $str .= "var period_name=Array();\n";  
        foreach($info['period_name'] as $idx => $rows)
        {
         	$str .= "period_name[{$idx}]=\"{$rows}\";\n";
        } 
        
        $str .= "var mem_attr = new Array();\n";
        foreach($info['member_attr'] as $idx => $rows)
        {
         	$str .= "mem_attr[{$idx}]=\"{$rows}\";\n";
        }               
        return $str;
    }  
	
	// 取得停車時段資訊
	public function get_parktime_str()
	{
		return $this->vars['mcache']->get('pt');
	}
                   
    /*
    // 停車時段資訊
	public function park_time() 
	{             
    	$data = array();
        $idx = 0;
    	// $results = $this->db->select('time_id, seqno, park_type, week_start, week_end, daytime_start, daytime_end, remarks')
    	$results = $this->db->select('time_id, seqno, timex, remarks')
        			->from('park_time')	
                    ->order_by('seqno', 'asc') 
                    ->get()
                    ->result_array();
        foreach($results as $rows)
        {
        	$data[$idx] = array
            (
            	'time_id' => $rows['time_id'],  
            	'seqno' => $rows['time_id'],  
            	'remarks' => $rows['remarks']
            );
            ++$idx;  
        } 
        return $results;
    }     
    
    
    // 停車時段資訊單筆刪除
	public function park_time_delete($time_id) 
	{                
    	$this->db->delete('park_time', array('time_id' => $time_id));
    	
        return true;
    }  
	*/
    
    // 會員清單
	public function member_query_all() 
	{               
		$sql = "
				SELECT 
					members.member_no, 
					members.lpr, 
					members.etag, 
					members.member_name, 
					members.mobile_no, 
					members.start_date, 
					members.end_date, 
					members.fee_period, 
					members.member_attr, 
					members.suspended,
					members.contract_no, 
					members.amt,
					COALESCE(members.valid_time, members.end_date) as valid_time
				FROM members
				ORDER BY members.member_no DESC
				";
    	$results = $this->db->query($sql)->result_array();
        return $results; 
    }
	
	// 待審核清單
	public function member_tx_check_query() 
	{                    
		$sql = "
				SELECT
					members.lpr as current_lpr,
					member_tx.lpr,
					member_tx.tx_no, 
					member_tx.station_no, 
					member_tx.member_no, 
					member_tx.fee_period,
					member_tx.fee_period_last,
					member_tx.amt1,
					member_tx.amt,
					member_tx.amt_last,
					member_tx.deposit,
					date_format(member_tx.start_date,'%Y-%m-%d') as start_date, 
					date_format(member_tx.end_date,'%Y-%m-%d') as end_date,  
					date_format(member_tx.start_date_last,'%Y-%m-%d') as start_date_last, 
					date_format(member_tx.end_date_last,'%Y-%m-%d') as end_date_last,  
					member_tx.member_company_no,
					member_tx.company_no,
					member_tx.acc_date,
					member_tx.invoice_no,
					member_tx.invoice_amt,
					member_tx.invoice_track,
					member_tx.invoice_time,
					member_tx.invoice_type,
					member_tx.verify_state,
					member_tx.valid_time,
					member_tx.remarks,
					member_tx.tx_state
        		FROM member_tx
					LEFT JOIN members ON (member_tx.member_no = members.member_no AND members.station_no = member_tx.station_no)
				WHERE 
					member_tx.verify_state in (0, 99) and members.member_no is not null
					
					and member_tx.valid_time > 0 -- 20170131 added
					
				ORDER BY member_tx.valid_time ASC
				";
        
    	$results = $this->db->query($sql)->result_array();
        return $results; 
    }
	
	// 已退租交易清單
	public function member_tx_refund_query($station_no, $q_item, $q_str)
	{
		$where_station = $station_no == 0 ? '' : " member_refund.station_no = {$station_no} and ";	// 如為0, 則全部場站讀取 
                                    
    	switch($q_item)
        {
            case 'lpr': 
        		$items = "member_log.{$q_item} like ";
          		$q_str = strtoupper($q_str).'%';
                break;
            default:
        		$items = "member_log.{$q_item} like ";
          		$q_str .= '%';
                break;
        }    
		
		$sql = "
				SELECT
					member_refund.member_refund_id,
					member_refund.station_no, 
					member_refund.member_no, 
					member_log.lpr,
					member_log.member_name,
					member_log.company_no, 
					member_log.member_company_no, 
					member_refund.refund_amt, 
					member_refund.refund_deposit, 
					member_refund.refund_tot_amt, 
					member_refund.refund_time, 
					member_refund.refund_state, 
					member_refund.create_time,
					member_refund.dismiss_state
					
        		FROM member_refund
					LEFT JOIN member_log ON ( member_log.member_log_id = member_refund.member_log_id and member_log.station_no = member_refund.station_no )
					
				WHERE {$where_station} {$items} '{$q_str}'
				ORDER BY member_refund.create_time DESC
				";
				
		//trigger_error("test: {$sql}");
        
    	$results = $this->db->query($sql)->result_array();
        return $results; 
    }
	
	// 取得轉租資訊
	public function member_refund_transfer_data_query($station_no, $member_no, $member_refund_id)
	{
		$rows = $this->db->select('
						member_log.member_id,
						member_log.member_no,
						member_log.member_name,
						member_log.member_nick_name,
						member_log.member_attr,
						member_log.fee_period,
						member_log.contract_no,
						member_log.lpr,
						member_log.etag,
						member_log.member_company_no,
						member_log.mobile_no,
						member_log.tel_o,
						member_log.tel_h,
						member_log.addr,
						member_log.park_time,
						member_refund.refund_deposit,
						member_refund.refund_amt,
						member_refund.refund_state,
						member_refund.dismiss_state,
						member_refund.refund_time
						')
        		->from('member_refund')
				->join('member_log', 'member_log.member_log_id = member_refund.member_log_id', 'left')
                ->where(array(
						'member_refund.member_refund_id' => $member_refund_id,
						'member_refund.station_no' => $station_no,
						'member_refund.member_no' => $member_no
						))
                ->get()
                ->row_array(); 

		$data = array();
		if(empty($rows) || empty($rows['member_no']))
		{
			$data['result_code'] = 'NOT_FOUND';  
		}
		else
		{
			$data['result_code'] = 'OK';  
			$data['result'] = $rows;   
		}
		return $data;
	}
    
    // 會員查詢
	public function member_query($station_no, $q_item, $q_str) 
	{               
    	$where_station = $station_no == 0 ? '' : " station_no = {$station_no} and ";	// 如為0, 則全部場站讀取 
                                    
    	switch($q_item)
        {
          	case 'end_date': 
          		$items = "{$q_item} <=";
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
        
        $sql = "select
        		station_no,
        		member_no,
        		lpr, 
        		member_name, 
                mobile_no,                                    
                date_format(demonth_start_date,'%Y-%m-%d') as demonth_start_date, 
                date_format(demonth_end_date,'%Y-%m-%d') as demonth_end_date, 
                date_format(start_date,'%Y-%m-%d') as start_date, 
                date_format(end_date,'%Y-%m-%d') as end_date,  
                date_format(rent_start_date,'%Y-%m-%d') as rent_start_date, 
                contract_no, 
                coalesce(etag, '') as etag,
                fee_period1, 
                fee_period, 
                amt1,   
                amt,   
                if(member_company_no > 0, member_company_no, '') as member_company_no,
                if(company_no > 0, company_no, '') as company_no, 
                coalesce(member_attr, 1) as member_attr,
                deposit,
                park_time,
                coalesce(member_id, '') as member_id,
                coalesce(tel_o, '') as tel_o, 
                coalesce(tel_h, '') as tel_h,
                coalesce(addr, '') as addr, 
				suspended
        		from members
                where {$where_station} {$items} '{$q_str}'";
        
    	$results = $this->db->query($sql)->result_array();
        
        return $results;
    }  
	
	// 交易查詢
	public function member_tx_query($station_no, $member_no) 
	{               
        $sql = "select
					tx_no, station_no, 
					member_no, 
					lpr,
					fee_period,
					fee_period_last,
					amt1,
					amt,
					amt_last,
					deposit,
					date_format(start_date,'%Y-%m-%d') as start_date, 
					date_format(end_date,'%Y-%m-%d') as end_date,  
					date_format(start_date_last,'%Y-%m-%d') as start_date_last, 
					date_format(end_date_last,'%Y-%m-%d') as end_date_last,  
					member_company_no,
					company_no,
					acc_date,
					invoice_no,
					invoice_amt,
					invoice_track,
					invoice_time,
					invoice_type,
					verify_state,
					valid_time,
					remarks,
					tx_state
        		from member_tx
                where station_no = {$station_no} and member_no = {$member_no} 
				order by tx_no desc"; 
				
				// and tx_state = ".MEMBER_TX_TX_STATE_NONE."
        
    	$results = $this->db->query($sql)->result_array();
        
        return $results;
    }
	
	// 發票查詢
	public function member_tx_bill_query($station_no, $tx_no=0, $verify_state_str='', $invoice_state_str='', $tx_state_str='', $tx_bill_no=0) 
	{               
		$tx_no_statement = !empty($tx_no) ? " and member_tx_bill.tx_no = {$tx_no} " : "";
		$tx_bill_no_statement = !empty($tx_bill_no) ? " and member_tx_bill.tx_bill_no = {$tx_no} " : "";
		
		// 審核狀態
		$verify_state_statement = !empty($verify_state_str) ? " and member_tx.verify_state in ( {$verify_state_str} )" : "";
		
		// 發票狀態
		$invoice_statement = '';
		if($invoice_state_str == '0')										// 限定：未開立發票
		{
			$invoice_statement = " and member_tx_bill.invoice_no <= 0 ";	
		}
		if($invoice_state_str == '1')										// 限定：未開立發票, 或是 有餘額未開立 （且為 待補開發票）
		{
			$invoice_statement = " and member_tx_bill.invoice_state = 1 and ( member_tx_bill.invoice_no <= 0 OR member_tx_bill.remain_amt > 0 ) ";	
		}
		else if($invoice_state_str == '2')									// 限定：待折讓發票
		{
			$invoice_statement = " and member_tx_bill.invoice_state = 2";
		}
		else if($invoice_state_str == '100')								// 限定：未開立發票, 或是 有餘額未開立
		{
			$invoice_statement = " and ( member_tx_bill.invoice_no <= 0 OR member_tx_bill.remain_amt > 0 ) ";
		}
		
		// 交易狀態
		$tx_state_statement = ($tx_state_str != '') ? " and member_tx.tx_state in ( {$tx_state_str} )" : " and member_tx.tx_state in (0,4,44) ";
		
        $sql = "select
					member_tx.tx_no, 
					member_tx.station_no, 
					member_tx.member_no, 
					member_tx.lpr,
					member_tx.fee_period,
					member_tx.fee_period_last,
					member_tx.amt1,
					member_tx.amt,
					member_tx.amt_last,
					member_tx.deposit,
					date_format(member_tx.start_date,'%Y-%m-%d') as start_date, 
					date_format(member_tx.end_date,'%Y-%m-%d') as end_date,  
					date_format(member_tx.start_date_last,'%Y-%m-%d') as start_date_last, 
					date_format(member_tx.end_date_last,'%Y-%m-%d') as end_date_last,  
					member_tx_bill.tx_bill_no,
					member_tx_bill.member_company_no,
					member_tx_bill.company_no,
					member_tx_bill.acc_date,
					LPAD(member_tx_bill.invoice_no, 8, '0') as invoice_no,	-- 發票號碼補零到8碼
					member_tx_bill.invoice_amt,
					member_tx_bill.invoice_track,
					member_tx_bill.invoice_time,
					member_tx_bill.invoice_type,
					member_tx_bill.remain_amt,
					member_tx_bill.invoice_state, 	-- 發票狀態
					member_tx_bill.refund_amt,		-- 折讓金額
					member_tx.verify_state,
					member_tx.valid_time,
					member_tx.remarks,
					member_tx.tx_state
        		from member_tx_bill
					left join member_tx on ( member_tx.tx_no = member_tx_bill.tx_no and member_tx.station_no = member_tx_bill.station_no )
                where member_tx_bill.station_no = {$station_no} 
					{$tx_no_statement} {$tx_bill_no_statement}
					{$verify_state_statement} {$invoice_statement} {$tx_state_statement}
				order by member_tx_bill.tx_bill_no desc";
        
		//trigger_error("test: {$sql}");
		
    	$results = $this->db->query($sql)->result_array();
        
        return $results;
    }
	
	// 發票查詢
	public function member_refund_bill_query($station_no, $tx_no=0, $verify_state_str='', $invoice_state_str='', $tx_state_str='', 
		$tx_bill_no=0, $member_refund_id=0) 
	{               
		$tx_no_statement = !empty($tx_no) ? " and member_tx_bill.tx_no = {$tx_no} " : "";
		$tx_bill_no_statement = !empty($tx_bill_no) ? " and member_tx_bill.tx_bill_no = {$tx_no} " : "";
		$member_refund_id_statement = !empty($member_refund_id) ? " and member_refund.member_refund_id = {$member_refund_id} " : "";
		
		// 審核狀態
		$verify_state_statement = !empty($verify_state_str) ? " and member_tx.verify_state in ( {$verify_state_str} )" : "";
		
		// 發票狀態
		$invoice_statement = '';
		if($invoice_state_str == '0')										// 限定：未開立發票
		{
			$invoice_statement = " and member_tx_bill.invoice_no <= 0 ";	
		}
		if($invoice_state_str == '1')										// 限定：未開立發票, 或是 有餘額未開立 （且為 待補開發票）
		{
			$invoice_statement = " and member_tx_bill.invoice_state = 1 and ( member_tx_bill.invoice_no <= 0 OR member_tx_bill.remain_amt > 0 ) ";	
		}
		else if($invoice_state_str == '2')									// 限定：待折讓發票
		{
			$invoice_statement = " and member_tx_bill.invoice_state = 2";
		}
		else if($invoice_state_str == '100')								// 限定：未開立發票, 或是 有餘額未開立
		{
			$invoice_statement = " and ( member_tx_bill.invoice_no <= 0 OR member_tx_bill.remain_amt > 0 ) ";
		}
		
		// 交易狀態
		$tx_state_statement = !empty($tx_state_str) ? " and member_tx.tx_state in ( {$tx_state_str} )" : " and member_tx.tx_state = 0 ";
		
        $sql = "select
					member_tx.tx_no, 
					member_tx.station_no, 
					member_tx.member_no, 
					member_tx.lpr,
					member_tx.fee_period,
					member_tx.fee_period_last,
					member_tx.amt1,
					member_tx.amt,
					member_tx.amt_last,
					member_tx.deposit,
					date_format(member_tx.start_date,'%Y-%m-%d') as start_date, 
					date_format(member_tx.end_date,'%Y-%m-%d') as end_date,  
					date_format(member_tx.start_date_last,'%Y-%m-%d') as start_date_last, 
					date_format(member_tx.end_date_last,'%Y-%m-%d') as end_date_last,  
					member_tx_bill.tx_bill_no,
					member_tx_bill.member_company_no,
					member_tx_bill.company_no,
					member_tx_bill.acc_date,
					LPAD(member_tx_bill.invoice_no, 8, '0') as invoice_no,	-- 發票號碼補零到8碼
					member_tx_bill.invoice_amt,
					member_tx_bill.invoice_track,
					member_tx_bill.invoice_time,
					member_tx_bill.invoice_type,
					member_tx_bill.remain_amt,
					member_tx_bill.invoice_state, 	-- 發票狀態
					member_tx_bill.refund_amt,		-- 折讓金額
					member_tx.verify_state,
					member_tx.valid_time,
					member_tx.remarks,
					member_tx.tx_state
        		from member_tx_bill
					left join member_tx on ( member_tx.tx_no = member_tx_bill.tx_no and member_tx.station_no = member_tx_bill.station_no )
					left join member_refund on ( member_refund.member_no = member_tx_bill.member_no and member_refund.station_no = member_tx_bill.station_no )
                where member_tx_bill.station_no = {$station_no} 
					{$tx_no_statement} {$tx_bill_no_statement} {$member_refund_id_statement}
					{$verify_state_statement} {$invoice_statement} {$tx_state_statement}
				order by member_tx_bill.tx_bill_no desc";
        
		//trigger_error("test: {$sql}");
		
    	$results = $this->db->query($sql)->result_array();
        
        return $results;
    }
	
	// 臨停未結確認完成
	public function cario_temp_confirmed($parms) 
	{
		$this->try_sync_batch($parms['station_no']); // 同步未同步記錄
		
		trigger_error(ADMIN_LOG_TITLE.'臨停未結確認完成：' . print_r($parms, true));
		
		// 取得交易資料
		$cario_info = $this->db->select('cario_no, in_time, out_time')
        		->from('cario')
                ->where(array('station_no' => $parms['station_no'], 'cario_no' => $parms['cario_no']))
                ->get()
                ->row_array();
		
		if(empty($cario_info))
		{
			trigger_error(__FUNCTION__ . '..cario not found..' . print_r($parms, true));
			return 'tx_error_not_found';	 	// 中斷
		}
		
		$data_cario = array('confirms' => 1, 'remarks' => $parms['remarks']);
		
		// [A.開始]
        $this->db->trans_start();
        $this->db->update('cario', $data_cario, array('station_no' => $parms['station_no'], 'cario_no' => $parms['cario_no']));		// t1. 更新 cario_no.confirms, cario.remarks
		
		// [B.建立同步檔] (略)
		
		// [C.完成]
		$this->db->trans_complete();
		if ($this->db->trans_status() === FALSE)
		{
			trigger_error(__FUNCTION__ . '..trans_error..data:' . print_r($data_cario, true). '| last_query: ' . $this->db->last_query());
			return 'fail';	 		// 中斷
		}
		
        return 'ok';
    }
	
	// 審核完成
	public function member_tx_confirmed($parms) 
	{
		$this->try_sync_batch($parms['station_no']); // 同步未同步記錄
		
		trigger_error(ADMIN_LOG_TITLE.'審核完成：' . print_r($parms, true));
		
		$altob_admin_submit = $this->input->post('altob_admin_submit', true);	// 取得 admin 參數
		
		//trigger_error(__FUNCTION__ . '..admin:' . print_r($altob_admin_submit, true));
		//trigger_error(__FUNCTION__ . '..check:' . print_r($this->gen_admin_ck($parms['station_no']), true));
		
		if($altob_admin_submit !== $this->gen_admin_ck($parms['station_no']))
		{
			trigger_error(__FUNCTION__ . '..altob_admin_submit error..' . print_r($parms, true));
			return 'admin_error';	 // 中斷
		}
		
		// 取得交易資料
		$tx_info = $this->db->select('member_no, end_date')
        		->from('member_tx')
                ->where(array('station_no' => $parms['station_no'], 'tx_no' => $parms['tx_no']))
                ->get()
                ->row_array();
		
		if(empty($tx_info))
		{
			trigger_error(__FUNCTION__ . '..member_tx not found..' . print_r($parms, true));
			return 'tx_error_not_found';	 	// 中斷
		}
		
		$member_no = $tx_info['member_no'];
		
		// [ 可能的 BUG ] : 直接針對較新的交易審核過關, 會讓會員直接跳過較舊的交易有效期限
		
		$data_member = array();					// 若不為空會觸發同步 members
		$data_tx = array('verify_state' => $parms['verify_state'], 'remarks' => $parms['remarks']);
		
		// [A.開始]
        $this->db->trans_start();
        $this->db->update('member_tx', $data_tx, array('station_no' => $parms['station_no'], 'tx_no' => $parms['tx_no'])); 							// t1. 更新 member_tx.verify_state, member_tx.valid_time, member_tx.remarks
		$this->gen_member_tx_log($data_tx, $parms['station_no'], $parms['tx_no']);																	// t.log. 建立 member_tx_log
		
		if($data_tx['verify_state'] == MEMBER_TX_VERIFY_STATE_OK)
		{
			$next_valid_time = $tx_info['end_date']. ' 23:59:59';	// 交易若通過, 可取得的新有效期限
			
			// 取得會員資料
			$member_info = $this->db->select('valid_time')
        		->from('members')
                ->where(array('station_no' => $parms['station_no'], 'member_no' => $member_no))
                ->get()
                ->row_array();
		
			if(empty($member_info))
			{
				trigger_error(__FUNCTION__ . '..member not found..' . print_r($parms, true));
			}
			else
			{
				if(strtotime($member_info['valid_time']) >= strtotime($next_valid_time) )
				{
					trigger_error(__FUNCTION__ . '..member already got valid_time..' . print_r($parms, true));	
				}
				else
				{
					$data_member = array('valid_time' => $next_valid_time);
					$this->db->update('members', $data_member, array('station_no' => $parms['station_no'], 'member_no' => $member_no));	// t2. 更新 members.valid_time
					$this->gen_member_log($data_member, $parms['station_no'], $member_no);												// t.log. 建立 member_log		
				}
			}
			// 若審核完成, 更新 members 有效期限
		}
		
        // [B.建立同步檔]
		$sync_seqnos = $this->prepare_sync2hq('U', $parms['station_no'], 'member_tx', $parms['tx_no'], $data_tx); 						// t3.a. 準備同步檔 (member_tx)
		if(!empty($data_member))
		{
			$sync_seqnos .= ',' . $this->prepare_sync2hq('U', $parms['station_no'], 'members', $member_no, $data_member); 				// t3.b. 準備同步檔 (members)	
		}
		
		// [C.完成]
		$this->db->trans_complete();
		if ($this->db->trans_status() === FALSE)
		{
			trigger_error(__FUNCTION__ . '..trans_error..data:' . print_r($data_tx, true). '| last_query: ' . $this->db->last_query());
			return 'fail';	 		// 中斷
		}
		$this->worker_tx('sync_batch', array('sync_seqnos' => $sync_seqnos));
        return 'ok';
    }
     
	 
	// 切換賣方統編
	public function switch_company_no($parms) 
	{
		$this->try_sync_batch($parms['station_no']); // 同步未同步記錄
		
		trigger_error(ADMIN_LOG_TITLE.'切換賣方統編：' . print_r($parms, true));
		
		// 取得交易資料
		$tx_info = $this->db->select('company_no, invoice_no')
        		->from('member_tx_bill')
                ->where(array('station_no' => $parms['station_no'], 'tx_bill_no' => $parms['tx_bill_no']))
                ->get()
                ->row_array();
		
		if(empty($tx_info))
		{
			trigger_error(__FUNCTION__ . '..tx_error_not_found..' . print_r($parms, true));
			return 'tx_error_not_found';	 	// 中斷
		}
		
		if(!empty($tx_info['invoice_no']))
		{
			trigger_error(__FUNCTION__ . '..invoice_exist..' . print_r($parms, true));
			return 'invoice_exist';	 		// 中斷
		}
		
		$st_info = $this->vars['mcache']->get('st_info');
		$next_company_no = $st_info['company_no'];			// 場站統編
		
		if($parms['company_no'] == $next_company_no)
		{
			$next_company_no = '80682490'; 					// 總公司統編
		}
		
		if($tx_info['company_no'] == $next_company_no)
		{
			trigger_error(__FUNCTION__ . '..not_changed..' . print_r($parms, true));
			return 'not_changed';	 		// 中斷
		}
		
		$data_tx_bill = array('company_no' => $next_company_no);
		
		// [A.開始]
        $this->db->trans_start();
        $this->db->update('member_tx_bill', $data_tx_bill, array('station_no' => $parms['station_no'], 'tx_bill_no' => $parms['tx_bill_no'])); 	// t1. 更新 member_tx_bill.company_no
		
        // [B.建立同步檔]
		$sync_seqnos = $this->prepare_sync2hq('U', $parms['station_no'], 'member_tx_bill', $parms['tx_bill_no'], $data_tx_bill); 				// t2. 準備同步檔 (member_tx_bill)
		
		// [C.完成]
		$this->db->trans_complete();
		if ($this->db->trans_status() === FALSE)
		{
			trigger_error(__FUNCTION__ . '..trans_error..data:' . print_r($data_tx_bill, true). '| last_query: ' . $this->db->last_query());
			return 'fail';	 		// 中斷
		}
		$this->worker_tx('sync_batch', array('sync_seqnos' => $sync_seqnos));
        return 'ok';
    }
    
    // 刪除月租會員
	/*
	public function member_delete($station_no, $member_no) 
	{
		$this->try_sync_batch($station_no); // 同步未同步記錄
		
		// [A.開始]
		$this->db->trans_start();
    	$this->db->delete('members', array('station_no' => $station_no, 'member_no' => $member_no, 'suspended' => 0));  // t1. 刪除 members
    	$this->db->delete('member_car', array('station_no' => $station_no, 'member_no' => $member_no)); 				// t2. 刪除 member_car
		// [B.建立同步檔]
		$sync_seqnos = $this->prepare_sync2hq('D', $station_no, 'members', $member_no, array()); 						// t3. 準備同步檔 (members)
		// [C.完成]
		$this->db->trans_complete();
		if ($this->db->trans_status() === FALSE)
		{
			trigger_error(__FUNCTION__ . '..trans_error..data:' . "{$station_no}, {$member_no}". '| last_query: ' . $this->db->last_query());
			return 'trans_error';
		}
		
		// 同步至總管理處
		$this->worker_tx('sync_batch', array('sync_seqnos' => $sync_seqnos));
        return 'ok'; 
    }
	*/
    
    // 月租會員加入
	public function member_add($data, $rents_arr) 
	{
		$data['lpr'] = preg_replace('/\s+/', '', $data['lpr']);		// 移除空白
		
		$check_member_no = $data['member_no'];		
		$station_no = $data['station_no'];
		$tx_no = 0;
		
		$this->try_sync_batch($station_no); // 同步未同步記錄
		
		// 預設值 (修改會員時, 將直接填入)
		$tx_bill_no = 0;
		$invoice_amt = 0;
		$remain_amt = 0;
		$period_max_amt = 0;
		
		// 會員車輛基本資料檔 
		$start_date = (empty($data['demonth_start_date']) ? $data['start_date'] : $data['demonth_start_date']);
		$data['rent_start_date'] = $data['start_date'];  
		$data['rent_end_date'] = $data['end_date'];  
		$data['start_date'] = "{$start_date} 00:00:00";
		$data['end_date'] = "{$data['end_date']} 23:59:59";
		$old_lpr = $data['old_lpr'];
		unset($data['old_lpr']);
		$data_car = array
					(
						'lpr' => $data['lpr'],                    
						'lpr_correct' => $data['lpr'],                    
						'etag' => $data['etag'],                    
						'station_no' => $station_no,                    
						'start_time' => $data['start_date'],                    
						'end_time' => $data['end_date']
					);  
					
		// 取出會員轉租資訊
		$refund_transfer_id = empty($data['refund_transfer_id']) ? 0 : $data['refund_transfer_id'];
		$amt_discount = empty($data['refund_transfer_discount']) ? 0 : $data['refund_transfer_discount'];
		unset($data['refund_transfer_id']); 
		unset($data['refund_transfer_discount']);
        
    	if ($check_member_no == 0) 	// 新增一筆會員資料
        {                  
        	unset($data['member_no']); 
			$data['payed_date'] = substr($this->now_str, 0, 10);
        	$data['login_id'] = $data['lpr'];
        	$data['passwd'] = $data['lpr'];
			$data['valid_time']= $this->default_valid_time;				// 有效期限 	2017/01/15 added
			if($refund_transfer_id > 0)
			{
				$data['refund_transfer_id'] = $refund_transfer_id;		// 轉租來源編號	2017/02/24 added
			}
			$action_code = 'A';
			
			// [A.開始]
			$this->db->trans_start();
			/* 
				2017/03/16 統一新增流程
				
				$this->db->insert('members', $data);																			
				$members_insert_id = $this->db->insert_id();
				
				$data_car['member_no'] = $members_insert_id;																
				$this->db->insert('member_car', $data_car); 
			*/
			$members_insert_id = $this->gen_members($data);																	// t1 新增 members
			
			$data_car['member_no'] = $members_insert_id;
			$this->gen_member_car($data_car);																				// t2.a 新增 member_car
			
			$data['member_no'] = $members_insert_id;
			
			$this->gen_member_log($data);																					// t.log. 建立 member_log
			
			trigger_error(MEMBER_LOG_TITLE.'新增：' . print_r($data, true) . ", from: {$old_lpr}");
			
			$invoice_count = $this->gen_invoice_count($data['fee_period']);	// 試算發票張數
			
			$data_tx = array(
				'member_no' => $members_insert_id,			// 會員編號
				'station_no' => $station_no,				// 場站編號
				'sync_no' => 0,								// 預設同步編號
				'lpr' => $data['lpr'],						// 車牌號碼
				
				'member_attr' => $data['member_attr'],		// 會員身份
				
				'amt_accrued' => $data['amt_accrued'],		// 應收租金
				'amt_tot' => $data['amt_tot'],				// 實收租金
				'deposit' => $data['deposit'],				// 押金
				
				'amt' => $data['amt'],						// 本期租金
				'fee_period' => $data['fee_period'],		// 本期繳期
				'start_date' => $data['rent_start_date'],	// 本期開始日
				'end_date' => $data['rent_end_date'],		// 本期結束日
				
				'amt1' => $data['amt1'],							// 首期租金
				'amt_last' => $data['amt1'],						// 上期租金
				'fee_period_last' => $data['fee_period1'],			// 上期繳期
				'start_date_last' => $data['demonth_start_date'],	// 上期開始日
				'end_date_last' => $data['demonth_end_date'],		// 上期結束日
				
				'member_company_no' => $data['member_company_no'],	// 買方統編
				'company_no' => $data['company_no'],				// 賣方統編
				
				'invoice_count' => $invoice_count,					// 預計發票張數
				
				'acc_date' => $data['payed_date'],			// 入帳日(暫定)  
				
				'acc_time' => $this->now_str,				// 入帳時間 2017/02/15 added
				
				'valid_time' => $data['valid_time'],		// 有效期限
				
				'amt_discount' => $amt_discount				// 設定轉租折扺	2017/02/24 added
			);
			
			$this->db->insert('member_tx', $data_tx);  																		// t3 新增 member_tx
			$tx_no = $this->db->insert_id(); 				// 訂單序號 
			$data_tx['tx_no'] = $tx_no;
			
			$this->gen_member_tx_log($data_tx);																				// t.log. 建立 member_tx_log
			
			// 建立首期發票開立記錄 (公式 A： 首期租金 + 拆分第一期金額)
			$total_amt = $data['amt'] + $data['amt1'];
			$period_max_amt = $this->gen_invoice_count_amt($data['amt'], $invoice_count);
			$invoice_amt = $data['amt1'] + $period_max_amt;
			$remain_amt = ($total_amt - $invoice_amt > 0) ? $total_amt - $invoice_amt : 0;
			trigger_error(__FUNCTION__ . ', period_max_amt: ' . $period_max_amt . ', amt:'. $data['amt']. ', amt1:'. $data['amt1'] . ', invoice_amt:' .$invoice_amt . ', remain_amt:' . $remain_amt);
			
			/*
			// 建立首期發票開立記錄 (公式 B： 首期租金 + 最多一季金額) // 2017/01/11 更換
			$total_amt = $data['amt'] + $data['amt1'];
			$period_3_amt = $rents_arr[3][$data['member_attr']];
			$invoice_amt = ($data['amt'] > $period_3_amt) ? $period_3_amt + $data['amt1'] : $total_amt;
			$remain_amt = ($total_amt - $invoice_amt > 0) ? $total_amt - $invoice_amt : 0;
			trigger_error('period_3_amt: ' . $period_3_amt . ', amt:'. $data['amt']. ', amt1:'. $data['amt1'] . ', invoice_amt:' .$invoice_amt . ', remain_amt:' . $remain_amt);
			*/
			
			$data_tx_bill = array(
				'tx_no' => $tx_no,							// 訂單序號
				
				'member_no' => $members_insert_id,			// 會員編號
				'station_no' => $station_no,				// 場站編號
				'sync_no' => 0,								// 預設同步編號
				'lpr' => $data['lpr'],						// 車牌號碼
				
				'member_company_no' => $data['member_company_no'],	// 買方統編
				'company_no' => $data['company_no'],				// 賣方統編
				
				'acc_date' => $data['payed_date'],			// 入帳日(暫定)  
				
				'invoice_amt' => $invoice_amt,				// 本次發票金額
				'remain_amt' => $remain_amt,				// 剩餘未開立金額
				
				'invoice_count' => $invoice_count			// 預計發票張數
			);
			
			if($invoice_count > 1)
			{
				$data_tx_bill['invoice_next_date'] = $this->gen_invoice_next_date($data['rent_start_date']);	// 預計下一張發票開立日
			}
			
			$this->db->insert('member_tx_bill', $data_tx_bill);  															// t4 新增 member_tx_bill
			$tx_bill_no = $this->db->insert_id(); 				// 帳單序號 
			$data_tx_bill['tx_bill_no'] = $tx_bill_no;
			
			// [B.建立同步檔]
			$sync_seqnos = $this->prepare_sync2hq($action_code, $station_no, 'members', $members_insert_id, $data); 				// t5 準備同步檔 (members)
			
			$sync_seqnos .= ',' . $this->prepare_sync2hq($action_code, $station_no, 'member_tx', $tx_no, $data_tx); 				// t6 準備同步檔 (member_tx)
			
			$sync_seqnos .= ',' . $this->prepare_sync2hq($action_code, $station_no, 'member_tx_bill', $tx_bill_no, $data_tx_bill);	// t7 準備同步檔 (member_tx_bill)
			
			if($refund_transfer_id > 0)
			{					
				// 更新退租記錄
				$data_refund = array('dismiss_state' => MEMBER_REFUND_DISMISS_STATE_TRANSFERRED);	
				
				$this->db->update('member_refund', $data_refund, array('station_no' => $station_no, 'member_refund_id' => $refund_transfer_id));	// t8.1 更新 member_refund
				
				$this->gen_member_refund_log($data_refund, $station_no, $refund_transfer_id);										// t.log. 建立 member_refund_log
				
				$sync_seqnos .= ',' . $this->prepare_sync2hq('U', $station_no, 'member_refund', $refund_transfer_id, $data_refund);	// t8. 準備同步檔 (member_refund)	
				
				// 清除 etag 記錄
				if ($old_lpr !== $data['lpr'])
				{
					$this->db->delete('etag_lpr', array('lpr' => $old_lpr));
					trigger_error(MEMBER_LOG_TITLE."{$old_lpr} 清除 etag 記錄a");
				}
			}
			
			// [C.完成]
			$this->db->trans_complete();
			if ($this->db->trans_status() === FALSE)
			{
				trigger_error(MEMBER_LOG_TITLE.'..trans_error..last_query:' . $this->db->last_query());
				trigger_error(__FUNCTION__ . '..trans_error..data:' . print_r($data, true). ', data_tx:'. print_r($data_tx, true) .'| last_query: ' . $this->db->last_query());
				return 'trans_error';
			}
			
			// 同步至總管理處
			$this->worker_tx('sync_batch', array('sync_seqnos' => $sync_seqnos));
        }   
    	else	
        {
			$altob_admin_submit = $this->input->post('altob_admin_submit', true);	// 取得 admin 參數
			if($altob_admin_submit == $this->gen_admin_ck($station_no))
			{
				trigger_error("admin: " + $altob_admin_submit);
				//unset($data['contract_no']);			// 合約號
				//unset($data['park_time']);			// 停車時段
				unset($data['rent_start_date']);
				unset($data['rent_end_date']);
				unset($data['start_date']);
				unset($data['end_date']);
				unset($data['demonth_start_date']);
				unset($data['demonth_end_date']);
				//unset($data['member_attr']);			// 會員身份
				unset($data['deposit']);
				unset($data['amt_tot']);
				unset($data['amt_accrued']);
				unset($data['fee_period1']);
				unset($data['amt1']);
				//unset($data['fee_period']);			// 例行繳期
				//unset($data['amt']);					// 例行租金
				trigger_error(MEMBER_LOG_TITLE.'修改 (admin)：' . print_r($data, true). ", from: {$old_lpr}");
			}
			else
			{
				// 一般情況下, 時段與費率都不能在這個流程修改
				//unset($data['contract_no']);			// 合約號
				unset($data['park_time']);
				unset($data['rent_start_date']);
				unset($data['rent_end_date']);
				unset($data['start_date']);
				unset($data['end_date']);
				unset($data['demonth_start_date']);
				unset($data['demonth_end_date']);
				unset($data['member_attr']);
				unset($data['deposit']);
				unset($data['amt_tot']);
				unset($data['amt_accrued']);
				unset($data['fee_period1']);
				unset($data['amt1']);
				unset($data['fee_period']);
				unset($data['amt']);
				trigger_error(MEMBER_LOG_TITLE.'修改 (normal)：' . print_r($data, true). ", from: {$old_lpr}");
			}
			$action_code = 'U';
			
			// [A.開始]
			$this->db->trans_start();
        	$this->db->update('members', $data, array('station_no' => $station_no, 'member_no' => $check_member_no)); 	// t1. 更新 members
			
			$this->gen_member_log($data, $station_no, $check_member_no);												// t.log. 建立 member_log
			
			// 沒有異動到車牌, 使用update, 否則重建一筆
            if ($old_lpr == $data['lpr'])																				// t2.a 更新 member_car
            {
            	unset($data_car['lpr']);
            	unset($data_car['lpr_correct']);
        		$this->db->update('member_car', $data_car, array('station_no' => $station_no, 'member_no' => $check_member_no));
            } 
            else
            {
				/*
					2017/03/16 更換為統一建立流程
					
					$this->db->delete('member_car', array('station_no' => $station_no, 'member_no' => $check_member_no));
					$data_car['member_no'] = $check_member_no;         
					$this->db->insert('member_car', $data_car);
				*/
				$data_car['member_no'] = $check_member_no; 
				$this->gen_member_car($data_car);
				
				// 清除 etag 記錄
				$this->db->delete('etag_lpr', array('lpr' => $old_lpr));
				trigger_error(MEMBER_LOG_TITLE."{$old_lpr} 清除 etag 記錄b");
            }
			
			// [B.建立同步檔]
			$sync_seqnos = $this->prepare_sync2hq($action_code, $station_no, 'members', $check_member_no, $data); 		// t3. 準備同步檔 (members)
			
			// [C.完成]
			$this->db->trans_complete();
			if ($this->db->trans_status() === FALSE)
			{
				trigger_error(MEMBER_LOG_TITLE.'..trans_error..last_query:' . $this->db->last_query());
				trigger_error(__FUNCTION__ . '..trans_error..data:' . print_r($data, true). '| last_query: ' . $this->db->last_query());
				return 'trans_error';
			}
			
			// 同步至總管理處
			$this->worker_tx('sync_batch', array('sync_seqnos' => $sync_seqnos));
        }
        
        return array(
			'station_no' => $station_no, 
			'company_no' => $data['company_no'], 
			'member_no' => $data['member_no'], 
			'start_date' => $start_date, 
			'msg' => 'ok', 
			'action_code' => $action_code, 
			'tx_no' => $tx_no,
			'tx_bill_no' => $tx_bill_no,
			'invoice_amt' => $invoice_amt,
			'remain_amt' => $remain_amt,
			'period_3_amt' => $period_max_amt,
			'amt_discount' => $amt_discount
		); 
    }  
    
         
    // 查詢車牌是否重複 （查詢會員車牌）
	public function check_lpr($lpr)
	{                    
    	$rows = $this->db->select('count(*) as counts')
        		->from('members')
                ->where(array('lpr' => $lpr))
                ->get()
                ->row_array();    
                                
        return $rows['counts'];
    } 
	
	// 查詢車牌是否重複 （查詢尚未處理退租金額的退租車牌）
	public function check_refund_lpr($lpr)
	{                    
    	$rows = $this->db->select('count(*) as counts')
        		->from('member_refund')
				->join('member_log', 'member_refund.member_log_id = member_log.member_log_id', 'left')
                ->where(array('member_log.lpr' => $lpr))
				->where_in('member_refund.dismiss_state', array(
						MEMBER_REFUND_DISMISS_STATE_NONE, 					// 0:	無 (尚未處理退租金額, 視為退租中)
						MEMBER_REFUND_DISMISS_STATE_KEEP_DEPOSIT			// 10:	押金未退 (已處理退租金額, 但押金未退, 視為退租中)
						//MEMBER_REFUND_DISMISS_STATE_TRANSFERRED,			// 1:	已轉租 (金額已結清, 視為新車牌, 忽略)
						//MEMBER_REFUND_DISMISS_STATE_DONE					// 100:	已結清 (金額已結清, 視為新車牌, 忽略)
					))
                ->get()
                ->row_array();    
								
        return $rows['counts'];
    }
	
	// 查詢車牌是否重複 （查詢尚未處理退租金額的退租會員）
	public function check_refund_member_no($member_no)
	{                    
    	$rows = $this->db->select('count(*) as counts')
        		->from('member_refund')
				->join('member_log', 'member_refund.member_log_id = member_log.member_log_id', 'left')
                ->where(array('member_log.member_no' => $member_no))
				->where_in('member_refund.dismiss_state', array(
						MEMBER_REFUND_DISMISS_STATE_NONE, 					// 0:	無 (尚未處理退租金額, 視為退租中)
						MEMBER_REFUND_DISMISS_STATE_KEEP_DEPOSIT			// 10:	押金未退 (已處理退租金額, 但押金未退, 視為退租中)
						//MEMBER_REFUND_DISMISS_STATE_TRANSFERRED,			// 1:	已轉租 (金額已結清, 視為新車牌, 忽略)
						//MEMBER_REFUND_DISMISS_STATE_DONE,					// 100:	已結清 (金額已結清, 視為新車牌, 忽略)
					))
                ->get()
                ->row_array();    
								
        return $rows['counts'];
    }
	
	// 查詢車牌是否重複 （查詢已進行過退租的會員）
	public function check_refund_member_no_exist($member_no)
	{                    
    	$rows = $this->db->select('count(*) as counts')
        		->from('member_refund')
				->join('member_log', 'member_refund.member_log_id = member_log.member_log_id', 'left')
                ->where(array('member_log.member_no' => $member_no))
                ->get()
                ->row_array();    
								
        return $rows['counts'];
    }
           
	// 取得交易發票
	public function get_tx_invoice_no($tx_no)
	{
		$rows = $this->db->select('invoice_no')
        		->from('member_tx')
                ->where(array('tx_no' => $tx_no))
                ->get()
                ->row_array();    
                                
        return $rows['invoice_no'];
	}
	
	// 更新月租發票記錄
	function set_tx_invoice_no($parms)
	{
        $data = array
        (
        	'member_company_no' => $parms['member_company_no'],
        	'company_no' => $parms['company_no'],
			'invoice_track' => $parms['invoice_track'],
			'invoice_no' => $parms['invoice_no'],
			'invoice_time' => $parms['invoice_time']
        );
		
		if(array_key_exists('invoice_type', $parms))
		{
			$data['invoice_type'] = $parms['invoice_type']; // 發票種類
		}
		
		// [A.開始]
		$this->db->trans_start();
        $this->db->update('member_tx_bill', $data, array('station_no' => $parms['station_no'], 'tx_bill_no' => $parms['tx_bill_no']));	// t1. 更新 member_tx_bill
		
		trigger_error(TX_LOG_TITLE.'更新發票記錄：' . print_r($data, true). ', tx_bill_no: '. $tx_bill_no);
		
		// [B.建立同步檔]
		$sync_seqnos = $this->prepare_sync2hq('U', $parms['station_no'], 'member_tx_bill', $parms['tx_bill_no'], $data); 				// t2. 準備同步檔 (member_tx_bill)
		
		// 判斷是否為補開發票
		$sql = "select member_refund.member_refund_id, member_refund.member_no, member_refund.member_log_id
        		from member_tx_bill
					LEFT JOIN member_refund ON ( member_refund.member_no = member_tx_bill.member_no and member_refund.station_no = member_tx_bill.station_no )
                WHERE
					member_tx_bill.station_no = {$parms['station_no']} and
					member_tx_bill.tx_bill_no = {$parms['tx_bill_no']} and
					member_tx_bill.invoice_count = 1 and
					member_tx_bill.remain_amt = 0 and
					member_tx_bill.invoice_state = ".MEMBER_TX_BILL_INVOICE_STATE_MORE
					;
        
    	$rows = $this->db->query($sql)->result_array();
		
		//trigger_error('test xxx：'.$sql. ', member_refund_id: ' . $rows[0]['member_refund_id']);
		
		if(!empty($rows[0]) && array_key_exists('member_refund_id', $rows[0]))
		{
			$data_refund = array('refund_state' => MEMBER_REFUND_STATE_DONE);	// 完結
			$this->db->update('member_refund', $data_refund, 				
					array('station_no' => $parms['station_no'], 'member_refund_id' => $rows[0]['member_refund_id'])); 								// t3. 更新 member_refund.refund_state
		
			$this->gen_member_refund_log($data_refund, $parms['station_no'], $rows[0]['member_refund_id']);											// t.log. 建立 member_refund_log
		
			$sync_seqnos .= ',' . $this->prepare_sync2hq('U', $parms['station_no'], 'member_refund', $rows[0]['member_refund_id'], $data_refund); 	// t4. 準備同步檔 (member_refund)	
		}
		
		// [C.完成]
		$this->db->trans_complete();
		if ($this->db->trans_status() === FALSE)
		{
			trigger_error(TX_LOG_TITLE.'..trans_error..last_query:' . $this->db->last_query());
			trigger_error(__FUNCTION__ . '..trans_error..data:' . print_r($data, true). '| last_query: ' . $this->db->last_query());
			return 'fail';	 		// 中斷
		}
		
		// 同步至總管理處
		$this->worker_tx('sync_batch', array('sync_seqnos' => $sync_seqnos));
        return 'ok';
	}
	
	// 首期月租付款交易
	public function first_rents_payment($parms)
	{   
		$this->try_sync_batch($parms['station_no']); // 同步未同步記錄
		
		trigger_error(TX_LOG_TITLE.'列印發票：' . print_r($parms, true));
	
    	// 確認交易記錄
		$rows = $this->db->select('count(*) as counts, lpr')
        		->from('member_tx_bill')
                ->where(array(
						'tx_bill_no' => $parms['tx_bill_no'], 'tx_no' => $parms['tx_no'], 
						'station_no' => $parms['station_no'], 'member_no' => $parms['member_no'],
						'invoice_amt' => $parms['invoice_amt'], 'invoice_no' => ''
						))
                ->get()
                ->row_array(); 

		if(empty($rows) || $rows['counts'] != 1)
		{
			trigger_error(__FUNCTION__ . '..member_tx_bill not found..' . print_r($parms, true) . $this->db->last_query());
			return 'tx_error';	 	// 中斷
		}
		
		// 印發票
		if(array_key_exists('invoice_track', $parms) && array_key_exists('invoice_no', $parms))
		{
			$parms['invoice_time'] = $this->now_str;								// 目前時間
		}
		else
		{
			$invoice_parms = array(
					'station_no' => $parms['station_no'],
					'tx_bill_no' => $parms['tx_bill_no'],
					'amt' => $parms['invoice_amt'],
					'member_company_no' => $parms['member_company_no'],
					'company_no' => $parms['company_no'],
					'email' => $parms['email'],
					'mobile' => $parms['mobile'],
					'lpr' => $rows['lpr']
				);
			
			$invoice_result = $this->print_invoice($invoice_parms);
			if(!empty($invoice_result) && array_key_exists('einvoice_no', $invoice_result))
			{
				$parms['invoice_track'] = $invoice_result['einvoice_track'];		// 發票字軌 
				$parms['invoice_no'] = $invoice_result['einvoice_no'];				// 發票號碼 
				$parms['invoice_time'] = $this->now_str;							// 目前時間
			}
		}
		
		if(empty($parms['invoice_no']) || empty($parms['invoice_track']))
		{
			trigger_error(__FUNCTION__ . '..invoice gg..' . print_r($parms, true));
			return 'invoice_fail';	 	// 中斷
		}
		
		// 更新月租發票記錄
		echo $this->set_tx_invoice_no($parms);
    } 
    
    // 新增月租付款交易
	public function rents_payment($parms, $rents_arr)
	{   
		$this->try_sync_batch($parms['station_no']); // 同步未同步記錄
		
		trigger_error(TX_LOG_TITLE.'繳租：' . print_r($parms, true). print_r($rents_arr, true));
		
		$invoice_count = $this->gen_invoice_count($parms['fee_period']);	// 試算發票張數
		
		// 先不印發票
		$parms['sync_no'] = 0;																					// 預設同步編號
		$parms['fee_period_last'] = $parms['fee_period'];														// 上期繳期
    	$parms['start_date'] = date('Y-m-d', strtotime("{$parms['end_date_last']} first day of next month"));	// 本期開始日：上期結束日之次月首日
		$parms['acc_date'] = date('Y-m-d');																		// 入帳日(暫定)
		$parms['valid_time'] = $this->default_valid_time;														// 有效期限
		$parms['invoice_count'] = $invoice_count;																// 預計發票張數
		$parms['acc_time'] =  $this->now_str;																	// 入帳時間 2017/02/15 added
		
		// [A.開始]
		$this->db->trans_start();
		$data_tx = $parms;
		$this->db->insert('member_tx', $data_tx);  																						// t1 新增 member_tx
        $tx_no = $this->db->insert_id(); // 交易序號 
        $data_tx['tx_no'] = $tx_no;
		
		$this->gen_member_tx_log($data_tx);																								// t.log. 建立 member_tx_log
		
		// 接續發票開立記錄 (公式： 拆分第一期金額)
		$invoice_amt = $this->gen_invoice_count_amt($parms['amt'], $invoice_count);
		$remain_amt = $parms['amt'] - $invoice_amt;
		trigger_error(__FUNCTION__ . ', amt:'. $parms['amt']. ', invoice_amt:' .$invoice_amt . ', remain_amt:' . $remain_amt);
		
		/*
		// 接續發票開立記錄 (公式： 最多一季金額) // 2017/01/11 更換
		$period_3_amt = ($rents_arr[3][$parms['member_attr']] > 2000) ? $rents_arr[3][$parms['member_attr']] : 2000; // 至少 $2000
		$invoice_amt = ($parms['amt'] > $period_3_amt) ? $period_3_amt : $parms['amt'];
		$remain_amt = $parms['amt'] - $invoice_amt;
		trigger_error('period_3_amt: ' . $period_3_amt . ', amt:'. $parms['amt']. ', invoice_amt:' .$invoice_amt . ', remain_amt:' . $remain_amt);
		*/
		
		$data_tx_bill = array(
				'tx_no' => $tx_no,							// 交易編號
				
				'member_no' => $parms['member_no'],			// 會員編號
				'station_no' => $parms['station_no'],		// 場站編號
				'sync_no' => 0,								// 預設同步編號
				
				'lpr' => $parms['lpr'],						// 車牌號碼
				
				'member_company_no' => $parms['member_company_no'],	// 買方統編
				'company_no' => $parms['company_no'],				// 賣方統編
				
				'acc_date' => $parms['acc_date'],			// 入帳日(延用)  
				
				'invoice_amt' => $invoice_amt,				// 本次發票金額
				'remain_amt' => $remain_amt,				// 剩餘未開立金額
				
				'invoice_count' => $invoice_count			// 預計發票張數
			);
			
		if($invoice_count > 1)
		{	
			$data_tx_bill['invoice_next_date'] = $this->gen_invoice_next_date($parms['start_date']);	// 預計下一張發票開立日
		}
			
		$this->db->insert('member_tx_bill', $data_tx_bill);  																			// t2 新增 member_tx_bill
		$tx_bill_no = $this->db->insert_id(); 				// 帳單序號 
		$data_tx_bill['tx_bill_no'] = $tx_bill_no;
		
		$data_member = array(
			'fee_period' => $parms['fee_period'],
			'payed_date' => $parms['acc_date'],							// 付款日
			'start_date' => "{$parms['start_date_last']} 00:00:00",		// 開始日：由上期繼續延續下去
			'end_date' => "{$parms['end_date']} 23:59:59",				// 結束日
			'valid_time' => $this->default_valid_time					// 有效期限 (更新寬限期)	// 2017/02/15 updated
		);
        $this->db->update('members', $data_member, array('station_no' => $parms['station_no'], 'member_no' => $parms['member_no']));	// t3. 更新 members
		
		$this->gen_member_log($data_member, $parms['station_no'], $parms['member_no']);													// t.log. 建立 member_log
		
        $this->db->update('member_car', 																								// t4. 更新 member_car
			array('start_time' => "{$data_member['start_date']} 00:00:00", 'end_time' => "{$data_member['end_date']} 23:59:59"), 
			array('station_no' => $parms['station_no'], 'member_no' => $parms['member_no']));
		
		// [B.準備同步檔]
		$sync_seqnos = $this->prepare_sync2hq('A', $parms['station_no'], 'member_tx', $tx_no, $data_tx); 								// t5. 準備同步檔 (member_tx)
		$sync_seqnos .= ',' . $this->prepare_sync2hq('A', $parms['station_no'], 'member_tx_bill', $tx_bill_no, $data_tx_bill);			// t6. 準備同步檔 (member_tx_bill)
		$sync_seqnos .= ',' . $this->prepare_sync2hq('U', $parms['station_no'], 'members', $parms['member_no'], $data_member);			// t7. 準備同步檔 (members)
		
		// [c.完成]
		$this->db->trans_complete();
		if ($this->db->trans_status() === FALSE)
		{
			trigger_error(__FUNCTION__ . '..trans_error..data:' . print_r($data_tx, true). '| last_query: ' . $this->db->last_query());
			return 'fail';	 		// 中斷
		}
        
        // 同步至總管理處
		$this->worker_tx('sync_batch', array('sync_seqnos' => $sync_seqnos));    
        return 'ok';
    } 

	// 退租
	public function stop_rents_payment($parms, $rents_arr)
	{
		$this->try_sync_batch($parms['station_no']); // 同步未同步記錄
		
		trigger_error(MEMBER_LOG_TITLE.'開始退租流程：' . print_r($parms, true). print_r($rents_arr, true));
		
		// 1. 取得退租資訊
		$result = $this->calculate_stop_rents_amt($parms, $rents_arr);
		
		// 2. 確認審核狀態
		if(!$result['verify_state'])
		{
			$altob_admin_submit = $this->input->post('altob_admin_submit', true);	// 取得 admin 參數
			if($altob_admin_submit == $this->gen_admin_ck($parms['station_no']))
			{
				$parms['tot_amt'] = $result['return_amt'] + $result['return_deposit'];
				trigger_error(ADMIN_LOG_TITLE.'強制退租：' . print_r($parms, true). print_r($rents_arr, true));
				trigger_error("force refund by admin: " + $altob_admin_submit + " | set tot_amt: " + $parms['tot_amt']);
			}
			else
			{
				trigger_error(__FUNCTION__ . '..verify_state_error..' . print_r($parms, true). print_r($result, true));
				return 'verify_state_error';	 	// 尚未審核完成	
			}
		}
		
		// 3. 確認金額正確性
		$tot_amt = $result['return_amt'] + $result['return_deposit'];
		if($tot_amt != $parms['tot_amt'])
		{
			trigger_error(__FUNCTION__ . '..tot_amt_error..' . print_r($parms, true). print_r($result, true));
			return 'tot_amt_error';	 	// 金額有誤
		}
		
		// 4. 確認會員資料
		$data_member = $this->db->select('*')->from('members')
                ->where(array('station_no' => $parms['station_no'], 'member_no' => $parms['member_no']))
                ->get()
                ->row_array(); 

		if(empty($data_member))
		{
			trigger_error(__FUNCTION__ . '..member_not_found..' . print_r($parms, true));
			return 'member_not_found';	 	// 中斷
		}
		
		// 5. 退租
		
		// TODO: 審核流程？
		$result['return_state'] = ($result['return_state'] == MEMBER_REFUND_STATE_NONE) ? MEMBER_REFUND_STATE_DONE : $result['return_state']; // 自動完成
		
		// [A.開始]
		$this->db->trans_start();
		
		/*
			2017/03/16: 改成不刪除記錄, 但變更時間區間
			
			$this->db->delete('members', array('station_no' => $parms['station_no'], 'member_no' => $parms['member_no']));  	// t1. 刪除 members
		*/
		$data_member_end_date = array('end_date' => $parms['stop_date']. ' 23:59:59');
		$this->db->update('members', $data_member_end_date, array('station_no' => $parms['station_no'], 'member_no' => $parms['member_no']));	// t1.a 更新 members
		
		$data_member['end_date'] = $data_member_end_date['end_date'];
		$member_log_id = $this->gen_member_log($data_member, $parms['station_no'], $parms['member_no']);								// t.log. 建立 member_log
		
		$data_refund = array(
			'member_no' => $parms['member_no'],
			'station_no' => $parms['station_no'],
			'member_log_id' => $member_log_id,
			'refund_amt' => $result['return_amt'],
			'refund_deposit' => $result['return_deposit'],
			'refund_tot_amt' => $tot_amt,
			'refund_state' => $result['return_state'],
			'refund_time' => $data_member_end_date['end_date']
		);
		$this->db->insert('member_refund', $data_refund);																	// t1.b 建立 member_refund
		$refund_id = $this->db->insert_id();
		$data_refund['member_refund_id'] = $refund_id;
		
		trigger_error(__FUNCTION__ . '..data_refund..' . print_r($data_refund, true));
		
		$this->gen_member_refund_log($data_refund, $parms['station_no']);													// t.log. 建立 member_refund_log
		
		/*
			2017/03/16: 改成不刪除記錄, 但變更時間區間
			
			$this->db->delete('member_car', array('station_no' => $parms['station_no'], 'member_no' => $parms['member_no']));
		*/
		$this->db->update('member_car', 																					// t2. 更新 member_car
			array('end_time' => "{$data_refund['refund_time']}"), 
			array('station_no' => $parms['station_no'], 'member_no' => $parms['member_no']));
		
		// [B.建立同步檔]
		
		/*
			2017/03/16: 改成不刪除記錄, 但變更時間區間
			
			$sync_seqnos = $this->prepare_sync2hq('D', $parms['station_no'], 'members', $parms['member_no'], array()); 				// t3. 	準備同步檔 (members)
		*/
		$sync_seqnos = $this->prepare_sync2hq('U', $parms['station_no'], 'members', $parms['member_no'], $data_member_end_date);	// t3.a 準備同步檔 (members)
		$sync_seqnos .= ',' . $this->prepare_sync2hq('A', $parms['station_no'], 'member_refund', $refund_id, $data_refund);			// t3.b 準備同步檔 (member_refund)
		
		foreach($result['results'] as $key => $val)
        {	
			$tx_no = $key;
			$data_tx = array('tx_state' => MEMBER_TX_TX_STATE_STOP);	// 已退租
			$this->db->update('member_tx', $data_tx, 				
				array('station_no' => $parms['station_no'], 'member_no' => $parms['member_no'], 'tx_no' => $tx_no)); 		// t4. 更新 member_tx.tx_state

			$this->gen_member_tx_log($data_tx, $parms['station_no'], $tx_no);												// t.log. 建立 member_tx_log
					
			// [B.建立同步檔]
			$sync_seqnos .= ',' . $this->prepare_sync2hq('U', $parms['station_no'], 'member_tx', $tx_no, $data_tx); 		// t5. 準備同步檔 (member_tx)
		}
		
		foreach($result['results_i'] as $key => $val)
        {
			$tx_bill_no = $key;
			$data_tx_bill = array('invoice_state' => MEMBER_TX_BILL_INVOICE_STATE_ALLOWANCE, 'refund_amt' => $val['refund_amt']);	// 待折讓
			$this->db->update('member_tx_bill', $data_tx_bill, 				
				array('station_no' => $parms['station_no'], 'member_no' => $parms['member_no'], 'tx_bill_no' => $tx_bill_no)); 		// t6. 更新 member_tx_bill.invoice_state
				
			// [B.建立同步檔]
			$sync_seqnos .= ',' . $this->prepare_sync2hq('U', $parms['station_no'], 'member_tx_bill', $tx_bill_no, $data_tx_bill); 	// t7. 準備同步檔 (member_tx_bill)
		}
		
		if($result['return_state'] == MEMBER_REFUND_STATE_MORE_INVOICE)
		{
			// 補開
			$invoice_tot_amt = -$tot_amt;
			$invoice_count = $this->gen_invoice_count($data_member['fee_period']);	// 試算發票張數
			$invoice_amt = $this->gen_invoice_count_amt($invoice_tot_amt, $invoice_count);
			$remain_amt = $invoice_tot_amt - $invoice_amt;
			trigger_error(__FUNCTION__ . ', invoice_tot_amt:'. $invoice_tot_amt. ', invoice_amt:' .$invoice_amt . ', remain_amt:' . $remain_amt);
			
			$parms['sync_no'] = 0;																					// 預設同步編號
			$parms['acc_date'] = date('Y-m-d');																		// 入帳日(暫定)
			$parms['invoice_count'] = $invoice_count;																// 預計發票張數
			
			$data_tx = array(
				'member_no' => $parms['member_no'],					// 會員編號
				'station_no' => $parms['station_no'],				// 場站編號
				'sync_no' => 0,										// 預設同步編號
				
				'lpr' => $data_member['lpr'],						// 車牌號碼

				'end_date' => $data_refund['refund_time'],			// 結束日
				
				'member_company_no' => $data_member['member_company_no'],	// 買方統編
				'company_no' => $data_member['company_no'],					// 賣方統編
				'acc_date' => $parms['acc_date'],					// 入帳日(暫定) 
				
				'amt' => $invoice_tot_amt,							// 本次發票金額
				'invoice_count' => $invoice_count,					// 預計發票張數
				
				'tx_state' => MEMBER_TX_TX_STATE_STOP				// 已退租
			);
			$this->db->insert('member_tx', $data_tx);  																		// t8. 新增 member_tx
			$tx_no = $this->db->insert_id(); 						// 訂單序號 
			$data_tx['tx_no'] = $tx_no;
			
			$this->gen_member_tx_log($data_tx);																				// t.log. 建立 member_tx_log
			
			$data_tx_bill = array(
					'tx_no' => $tx_no,							// 交易編號
					
					'member_no' => $parms['member_no'],			// 會員編號
					'station_no' => $parms['station_no'],		// 場站編號
					'sync_no' => 0,								// 預設同步編號
					
					'lpr' => $data_member['lpr'],				// 車牌號碼
					
					'member_company_no' => $data_member['member_company_no'],	// 買方統編
					'company_no' => $data_member['company_no'],					// 賣方統編
					'acc_date' => $parms['acc_date'],			// 入帳日(延用)  
					
					'invoice_amt' => $invoice_amt,				// 本次發票金額
					'remain_amt' => $remain_amt,				// 剩餘未開立金額
					
					'invoice_count' => $invoice_count,			// 預計發票張數
					
					'invoice_state' => MEMBER_TX_BILL_INVOICE_STATE_MORE	// 待補開
				);
				
			if($invoice_count > 1)
			{	
				$data_tx_bill['invoice_next_date'] = $this->gen_invoice_next_date($parms['acc_date']);	// 預計下一張發票開立日
			}
				
			$this->db->insert('member_tx_bill', $data_tx_bill);  																			// t9. 新增 member_tx_bill
			$tx_bill_no = $this->db->insert_id(); 				// 帳單序號 
			$data_tx_bill['tx_bill_no'] = $tx_bill_no;
			
			$sync_seqnos .= ',' . $this->prepare_sync2hq('A', $parms['station_no'], 'member_tx', $tx_no, $data_tx); 						// t10. 準備同步檔 (member_tx)
			$sync_seqnos .= ',' . $this->prepare_sync2hq('A', $parms['station_no'], 'member_tx_bill', $tx_bill_no, $data_tx_bill);			// t11. 準備同步檔 (member_tx_bill)
		}
		
		// [c.完成]
		$this->db->trans_complete();
		if ($this->db->trans_status() === FALSE)
		{
			trigger_error(__FUNCTION__ . '..trans_error..data:' . print_r($parms, true). print_r($result, true). '| last_query: ' . $this->db->last_query());
			return 'fail';	 		// 中斷
		}
        
        // 同步至總管理處
		$this->worker_tx('sync_batch', array('sync_seqnos' => $sync_seqnos));    
        return 'ok';
	}
	
	// 計算退租金額
	public function calculate_stop_rents_amt($parms, $rents_arr)
	{
		$result = array();
		$result['results'] = array();			// 交易
		$result['verify_state'] = true;			// 審核
		$result['results_i'] = array();			// 發票
		
		// 取得所有已開立發票記錄
		$result_tx = array();		// 發票記錄 A (tx_no)
		$result_tx_bill = array();	// 發票記錄 B (tx_bill_no)
		$sql = "select
					member_tx_bill.tx_no, member_tx_bill.tx_bill_no, 
					member_tx_bill.invoice_amt, member_tx_bill.invoice_no, member_tx_bill.invoice_track, member_tx_bill.invoice_time
				from member_tx_bill
				left join member_tx on (member_tx.tx_no = member_tx_bill.tx_no and member_tx.station_no = member_tx_bill.station_no)
				where 
					member_tx_bill.station_no = {$parms['station_no']} and 
					member_tx_bill.member_no = {$parms['member_no']} and 
					member_tx_bill.invoice_no != 0 and
					member_tx.tx_state not in (". MEMBER_TX_TX_STATE_CANCEL .", ". MEMBER_TX_TX_STATE_STOP .")
				order by member_tx_bill.invoice_time asc";
			
		$invoice_results = $this->db->query($sql)->result_array();
			
		if(!empty($invoice_results))
		{
			foreach($invoice_results as $rows)
			{
				$tx_no = $rows['tx_no'];
				if(!array_key_exists($tx_no, $result_tx))
				{
					$result_tx[$tx_no] = array();
				}
				$result_tx[$tx_no][$rows['tx_bill_no']] = $rows;	// 發票記錄 A (tx_no)
				//array_push($result_tx[$tx_no], $rows);			// 發票記錄 A (tx_no)
				
				$result_tx_bill[$rows['tx_bill_no']] = $rows;		// 發票記錄 B (tx_bill_no)
			}
		}
		
		// TEST
		//$result['test_rents_arr'] = json_encode($rents_arr, true);
		//$result['results_A'] = $result_tx;
		//$result['results_B'] = $result_tx_bill;
		
		// 取得所有交易記錄
		$sql = "select
					tx_no, start_date, end_date, start_date_last, end_date_last, 
					member_attr, fee_period, fee_period_last, amt, amt1, amt_last, deposit, amt_tot, verify_state, remarks  
        		from member_tx
                where 
					member_tx.station_no = {$parms['station_no']} and 
					member_tx.member_no = {$parms['member_no']} and
					member_tx.tx_state not in (". MEMBER_TX_TX_STATE_CANCEL .", ". MEMBER_TX_TX_STATE_STOP .")
				order by tx_no asc";
        
    	$member_tx_results = $this->db->query($sql)->result_array();
		
		if(!empty($member_tx_results))
		{
			// A. 取得待退金額與相關依據
			$total_amt = 0;
			$return_amt = 0;
			$return_deposit = 0;
			$last_end_date = $parms['stop_date'];		
			$stop_date_value = strtotime($parms['stop_date']);
			$stop_date_time = new DateTime($parms['stop_date']);
			foreach($member_tx_results as $rows)
			{
				$last_end_date = $rows['end_date'];		// 暫存最後一筆結束時間
				
				// 更新審核狀態
				if($rows['verify_state'] != MEMBER_TX_VERIFY_STATE_OK)
				{
					$result['verify_state'] = false; 	// 任何一筆未審核就 gg
				}
				
				$start_date = $rows['start_date'];		// 本期開始日
				$end_date = $rows['end_date'];			// 本期結束日
				$member_attr = $rows['member_attr'];	// 本期身份
				$fee_period = $rows['fee_period'];		// 本期繳期
				$deposit = $rows['deposit'];			// 首期押金
				$amt1 = $rows['amt1'];					// 首期租金
				$amt = $rows['amt'];					// 本期租金
				
				$stop_used_days_last = 0;				// f.1. 首期已使用天數
				$stop_rents_period_amt_last = $amt1;	// f.2. 首期繳期金額
				$stop_rents_used_amt_last = 0;			// f.3. 首期已消耗金額
				$stop_rents_period = $fee_period;		// a.0. 本期試算繳期
				$stop_used_days = 0;					// a.1. 本期已使用天數
				$stop_rents_period_amt = $amt;			// a.2. 本期繳期金額
				$stop_rents_used_amt = 0;				// a.3. 本期已消耗金額
					
				// F. 首期金額
				if($amt1 > 0)
				{
					$start_date_last = $rows['start_date_last'];	// 上期開始日
					$end_date_last = $rows['end_date_last'];		// 上期結束日
					$fee_period_last = $rows['fee_period_last'];	// 上期繳期
					
					$start_date_last_time = new DateTime($start_date_last);
					if($start_date_last_time > $stop_date_time)
					{
						// F.1. 首期金額, 沒用過, 完全退費 （預設值）
					}
					else
					{
						// 首期開始日早於退租日
						$stop_used_days_last = $stop_date_time->diff($start_date_last_time)->format("%a") + 1;											// f.1. 首期已使用天數	
						$stop_rents_period_amt_last = $amt1;																							// f.2. 首期繳期金額
						if(strtotime($end_date_last) < $stop_date_value)
						{
							// F.2. 首期金額, 完全用盡
							$stop_rents_used_amt_last = $stop_rents_period_amt_last;																	// f.3. 首期已消耗金額
						}
						else
						{
							// F.3. 首期金額, 尚未用盡, 臨停金額換算
							$in_time = $start_date_last.' 00:00:00';
							$balance_time = $parms['stop_date']. ' 23:59:59';
							$stop_rents_used_amt_last = $this->get_bill($in_time, $balance_time, $parms['station_no']);
						}
					}
				}
				
				// A. 本期金額
				if(strtotime($start_date) > $stop_date_value)
				{
					// A.1. 本期金額, 沒用過, 完全退費 （預設值）
				}
				else if(strtotime($end_date) <= $stop_date_value)
				{
					// A.3. 本期金額, 完全用盡
					$stop_used_days = $rents_arr[$stop_rents_period][0];		// a.1. 本期已使用天數
					$stop_rents_period_amt = $amt;								// a.2. 本期繳期金額
					$stop_rents_used_amt = $stop_rents_period_amt;				// a.3. 本期已消耗金額
				}
				else
				{
					// A.2. 本期金額, 用過一部份
					$start_date_time = new DateTime($start_date);
					$stop_used_days = $stop_date_time->diff($start_date_time)->format("%a") + 1;					// a.1. 本期已使用天數
					if($fee_period > 1)
					{	
						// A.2.a. 使用上一階繳期
						for(; $fee_period >= 1 ; $fee_period--)
						{
							if(array_key_exists($fee_period, $rents_arr) && $stop_used_days >= $rents_arr[$fee_period][0])
							{
								$stop_rents_period = $fee_period;																				// a.0. 本期試算繳期
								$stop_rents_period_amt = $rents_arr[$stop_rents_period][$member_attr];											// a.2. 本期繳期金額 (根據上一階繳期)
								$stop_rents_used_amt = round($stop_rents_period_amt * $stop_used_days / $rents_arr[$stop_rents_period][0]);		// a.3. 本期已消耗金額 (根據上一階繳期)
								break;
							}
						}
					}
					
					// A.2.b. 查無適用繳期, 改用臨停金額換算
					if($stop_rents_used_amt == 0)
					{
						$stop_rents_period = 0;																		// a.0. 本期試算繳期 (臨停)
						$in_time = $start_date.' 00:00:00';
						$balance_time = $parms['stop_date']. ' 23:59:59';
						$stop_rents_period_amt = $this->get_bill($in_time, $balance_time, $parms['station_no']);	// a.2. 本期繳期金額 (臨停)
						$stop_rents_used_amt = $stop_rents_period_amt;												// a.3. 本期已消耗金額 (臨停)
					}
				}

				$data = $rows;
				$data['stop_rents_tot_amt'] = $amt + $amt1;															// y. 交易總金額
				$data['stop_rents_return_amt'] = $amt + $amt1 - $stop_rents_used_amt - $stop_rents_used_amt_last;	// z. 可退還金額
				$data['stop_used_days_last'] = $stop_used_days_last;									// f.1. 首期已使用天數
				$data['stop_rents_period_amt_last'] = $stop_rents_period_amt_last;						// f.2. 首期繳期金額
				$data['stop_rents_used_amt_last'] = $stop_rents_used_amt_last;							// f.3. 首期已消耗金額
				$data['stop_rents_period'] = $stop_rents_period;										// a.0. 本期試算繳期
				$data['stop_used_days'] = $stop_used_days;												// a.1. 本期已使用天數
				$data['stop_rents_period_amt'] = $stop_rents_period_amt;								// a.2. 本期繳期金額
				$data['stop_rents_used_amt'] = $stop_rents_used_amt;									// a.3. 本期已消耗金額
				
				// i.1.發票
				$data['stop_rents_invoices'] = array_key_exists($data['tx_no'], $result_tx) ? $result_tx[$data['tx_no']] : array();
				
				// R.1. 明細
				$result['results'][$data['tx_no']] = $data;
				
				$total_amt += $data['stop_rents_tot_amt'];			
				$return_amt += $data['stop_rents_return_amt'];
				$return_deposit += $data['deposit'];
			}
			
			// B. 無待退金額, 補繳臨停金額
			if($return_amt == 0)
			{
				$in_time = date('Y-m-d', strtotime("+1 days", strtotime($last_end_date))). ' 00:00:00'; // 最後一天開始算臨停
				$balance_time = $parms['stop_date']. ' 23:59:59';
				$return_amt = - $this->get_bill($in_time, $balance_time, $parms['station_no']);
			}
			
			$result['total_amt'] = $total_amt;					// R.2. 累計交易總金額
			$result['return_amt'] = $return_amt;				// R.3. 累計可退還金額
			
			// 2017/04/06 若無首期押金記錄, 由會員身份查詢押金
			if($return_deposit == 0)
			{
				$member_info = $this->db->select('deposit')
					->from('members')
					->where(array('member_no' => $parms['member_no']))
					->get()
					->row_array();    
				
				if(!empty($member_info['deposit']))
				{
					trigger_error(__FUNCTION__ . '..get return_deposit 1..' . print_r($parms, true) . ' deposit: ' . $member_info['deposit']);
					$return_deposit = $member_info['deposit'];	
				}
			}
			
			$result['return_deposit'] = $return_deposit;		// R.4. 總押金
			
			// C. 發票, 折讓或補印
			$result['return_state'] = MEMBER_REFUND_STATE_NONE;			// 預設 0
			$result['return_tot_amt'] = $return_amt + $return_deposit;	// 總金額
			if($result['return_amt'] > 0)
			{
				$tmp_amt = $result['return_amt'];	// 僅折讓非押金的部份
				
				// C.1. 需退還金額 (折讓發票)
				foreach($result_tx_bill as $tx_bill_no => $tx_invoice)
				{
					$refund_amt = 0;
					
					if($tmp_amt > $tx_invoice['invoice_amt'])
					{
						// 尚有待折讓金額
						$refund_amt = $tx_invoice['invoice_amt'];
						$tmp_amt -= $tx_invoice['invoice_amt'];
					}
					else
					{
						// 已無待折讓金額
						$refund_amt = $tmp_amt;
						$tmp_amt = 0;
					}
					//trigger_error("{$tx_bill_no} ： {$tmp_amt}, {$refund_amt}");
					$result['results'][$tx_invoice['tx_no']]['stop_rents_invoices'][$tx_bill_no]['refund_amt'] = $refund_amt;
					
					// 記錄發票折讓資訊
					$result['results_i'][$tx_bill_no] = $result['results'][$tx_invoice['tx_no']]['stop_rents_invoices'][$tx_bill_no];	
					
					$result['return_state'] = MEMBER_REFUND_STATE_LESS_INVOICE; // 只要有就設為需要折讓
				}
			}
			else if($result['return_tot_amt'] < 0)
			{
				// C.2. 需補繳金額 (補印發票)
				$result['return_state'] = MEMBER_REFUND_STATE_MORE_INVOICE;
			}
			
			
		}
		else
		{
			// Z. 異常
			$result['return_state'] = MEMBER_REFUND_STATE_NONE;	// 預設 0
			$result['total_amt'] = 0;					// R.2. 累計交易總金額
			$result['return_amt'] = 0;					// R.3. 累計可退還金額
			//$result['return_deposit'] = 0;				// R.4. 總押金
			
			// 2017/04/06 若無首期押金記錄, 由會員身份查詢押金
			$member_info = $this->db->select('deposit')
					->from('members')
					->where(array('member_no' => $parms['member_no']))
					->get()
					->row_array();    
				
			if(!empty($member_info['deposit']))
			{
				trigger_error(__FUNCTION__ . '..gen return_deposit 2..' . print_r($parms, true) . ' deposit: ' . $member_info['deposit']);
				$result['return_deposit'] = $member_info['deposit'];	// R.4. 總押金
				$result['return_tot_amt'] = $result['return_deposit'];	// 總金額
			}
		}
		
		return $result;
	}
	
	// 押金保留，結清其它金額 (退租後)
	public function member_refund_keep_deposit($parms)
	{
		trigger_error(TX_LOG_TITLE.'押金保留，結清其它金額：' . print_r($parms, true));
		
		return $this->do_member_refund_dismiss($parms, MEMBER_REFUND_DISMISS_STATE_KEEP_DEPOSIT);
	}
	
	// 結清所有金額 (退租後)
	public function member_refund_dismiss_all($parms)
	{
		trigger_error(TX_LOG_TITLE.'結清所有金額：' . print_r($parms, true));
		
		return $this->do_member_refund_dismiss($parms, MEMBER_REFUND_DISMISS_STATE_DONE);
	}
	
	// [共用] 退租結清共用操作 (退租後)
	function do_member_refund_dismiss($parms, $dismiss_state)
	{
		$this->try_sync_batch($parms['station_no']); // 同步未同步記錄
		
		$data = array('dismiss_state' => $dismiss_state);
		
		// [A.開始]
        $this->db->trans_start();
        $this->db->update('member_refund', $data, array('station_no' => $parms['station_no'], 'member_refund_id' => $parms['member_refund_id']));	// t1. 更新 member_refund
		
		$this->gen_member_refund_log($data, $parms['station_no'], $parms['member_refund_id']);														// t.log. 建立 member_refund_log
		
        // [B.準備同步檔]
		$sync_seqnos = $this->prepare_sync2hq('U', $parms['station_no'], 'member_refund', $parms['member_refund_id'], $data); 						// t2. 準備同步檔
		
		// [C.完成]
		$this->db->trans_complete();		
		if ($this->db->trans_status() === FALSE)
		{
			trigger_error(__FUNCTION__ . '..trans_error..data:' . print_r($data, true). '| last_query: ' . $this->db->last_query());
			return 'fail';	 		// 中斷
		}
		
		// 同步至總管理處
		$this->worker_tx('sync_batch', array('sync_seqnos' => $sync_seqnos));
        return 'ok';
	}
	
	// 發票折讓 (退租後)
	public function refund_invoice_allowance($parms)
	{
		$this->try_sync_batch($parms['station_no']); // 同步未同步記錄
		
		trigger_error(TX_LOG_TITLE.'發票折讓：' . print_r($parms, true));
		
		// 確認 member_tx_bill
		$sql = "select
					member_tx_bill.tx_bill_no,
					member_tx_bill.invoice_track, 
					member_tx_bill.invoice_no, 
					member_tx_bill.lpr, 
					member_tx_bill.member_company_no, 
					member_tx_bill.company_no, 
					member_tx_bill.acc_date, 
					member_tx_bill.refund_amt as amt,
					member_tx_bill.invoice_count,
					member_tx_bill.invoice_next_date,
					member_refund.member_refund_id
        		from member_tx_bill
					LEFT JOIN member_refund ON ( member_refund.member_no = member_tx_bill.member_no and member_refund.station_no = member_tx_bill.station_no )
                WHERE
					member_tx_bill.station_no = {$parms['station_no']} and
					member_tx_bill.member_no = {$parms['member_no']} and
					member_tx_bill.invoice_state = ".MEMBER_TX_BILL_INVOICE_STATE_ALLOWANCE
					;
        
    	$results = $this->db->query($sql)->result_array();
		//trigger_error('test: '. $sql);
		
		$refund_info = array();
		$count = 0;
		foreach($results as $rows)
        {
			if($rows['tx_bill_no'] == $parms['tx_bill_no'])
			{
				$refund_info = $rows;
			}
			$count++;
		}
				
		if(empty($refund_info))
		{
			trigger_error(__FUNCTION__ . '..member_tx_bill not found..' . print_r($parms, true));
			return 'tx_error_not_found';	 	// 中斷
		}
		
		if($refund_info['invoice_no'] <= 0)
		{
			trigger_error(__FUNCTION__ . '..member_tx_bill not ready..' . print_r($parms, true));
			return 'tx_error_not_ready';	 	// 中斷（本期的還沒開立）
		}
		
		// 電子發票折讓介接
		$allowance_parms = array(
					'station_no' => $parms['station_no'],
					'invoice_no' => $refund_info['invoice_track'].str_pad($refund_info['invoice_no'], 8, '0', STR_PAD_LEFT),
					'allowance_amt' => $refund_info['amt']
				);
		$allowance_result = $this->allowance_invoice($allowance_parms);
		
		if($allowance_result['result_code'] == 'OK')
		{
			// [A.開始]
			$this->db->trans_start();
			
			$data_tx_bill = array('invoice_state' => MEMBER_TX_BILL_INVOICE_STATE_ALLOWANCE_DONE);		// 折讓完成
			$this->db->update('member_tx_bill', $data_tx_bill, 				
				array('station_no' => $parms['station_no'], 'member_no' => $parms['member_no'], 'tx_bill_no' => $parms['tx_bill_no'])); 					// t1. 更新 member_tx_bill.invoice_state
					
			// [B.建立同步檔]
			$sync_seqnos = $this->prepare_sync2hq('U', $parms['station_no'], 'member_tx_bill', $parms['tx_bill_no'], $data_tx_bill); 						// t2. 準備同步檔 (member_tx_bill)
			
			if($count <= 1)
			{
				$data_refund = array('refund_state' => MEMBER_REFUND_STATE_DONE);						// 完結退租
				$this->db->update('member_refund', $data_refund, 				
					array('station_no' => $parms['station_no'], 'member_refund_id' => $refund_info['member_refund_id'])); 									// t3. 更新 member_refund.refund_state

				$this->gen_member_refund_log($data_refund, $parms['station_no'], $refund_info['member_refund_id']);											// t.log. 建立 member_refund_log
					
				$sync_seqnos .= ',' . $this->prepare_sync2hq('U', $parms['station_no'], 'member_refund', $refund_info['member_refund_id'], $data_refund); 	// t4. 準備同步檔 (member_refund)
			}
			
			// [c.完成]
			$this->db->trans_complete();
			if ($this->db->trans_status() === FALSE)
			{
				trigger_error(__FUNCTION__ . '..trans_error..data:' . print_r($data_tx_bill, true). '| last_query: ' . $this->db->last_query());
				return 'fail';	 		// 中斷
			}

			// 同步至總管理處
			$this->worker_tx('sync_batch', array('sync_seqnos' => $sync_seqnos));    
			return 'ok';
		}
		else if(!empty($allowance_result['result_msg']))
		{
			return $allowance_result['result_msg'];	// 折讓發票失敗
		}
		else
		{
			return '未知的錯誤';
		}
	}
	
	// 交易取消
	public function member_tx_cancel($parms)
	{
		$this->try_sync_batch($parms['station_no']); // 同步未同步記錄
		
		trigger_error(TX_LOG_TITLE.'交易取消：' . print_r($parms, true));
		
		// A. 確認交易記錄
		$sql = "select
					tx_no, start_date, end_date, start_date_last, end_date_last, fee_period, verify_state, deposit
        		from member_tx
                where station_no = {$parms['station_no']} and member_no = {$parms['member_no']} and tx_state = ".MEMBER_TX_TX_STATE_NONE."
				order by tx_no DESC limit 2";
        
    	$member_tx_results = $this->db->query($sql)->result_array();
		
		if(empty($member_tx_results))
		{
			trigger_error(__FUNCTION__ . '..tx_error_not_found..' . print_r($parms, true));
			return 'tx_error_not_found';	 			// 查無記錄
		}
		
		$member_last_tx	= $member_tx_results[0]; 		// 最後一筆交易
		$member_restore_tx	= $member_tx_results[1];	// 倒數一筆交易
		
		if($member_last_tx['tx_no'] != $parms['tx_no'])
		{
			trigger_error(__FUNCTION__ . '..tx_error_not_last..' . print_r($parms, true) . print_r($member_last_tx, true));
			return 'tx_error_not_last';	 					// 拒絕 (只能由最後一筆記錄開始取消)
		}
		
		$data_member = $this->db->select('*')->from('members')
					->where(array('station_no' => $parms['station_no'], 'member_no' => $parms['member_no']))
					->get()
					->row_array(); 

		if(empty($data_member))
		{
			trigger_error(__FUNCTION__ . '..member_not_found..' . print_r($parms, true));
			return 'member_not_found';	 	// 中斷
		}
		
		// [A.開始]
		$this->db->trans_start();
		
		$sync_seqnos = "";
		
		if(!empty($member_restore_tx))
		{
			// A. 一般取消流程
			$data_member = array(
				'fee_period' => $member_restore_tx['fee_period'],
				'payed_date' => $member_restore_tx['acc_date'],						// 付款日
				'start_date' => "{$member_restore_tx['start_date_last']} 00:00:00",	// 開始日
				'end_date' => "{$member_restore_tx['end_date']} 23:59:59"			// 結束日
			);
			$this->db->update('members', $data_member, array('station_no' => $parms['station_no'], 'member_no' => $parms['member_no']));	// t.1a. 更新 members
			$this->gen_member_log($data_member, $parms['station_no'], $parms['member_no']);													// t.log. 建立 member_log
			$this->db->update('member_car', 																								// t.2a. 更新 member_car
				array('start_time' => "{$data_member['start_date']} 00:00:00", 'end_time' => "{$data_member['end_date']} 23:59:59"), 
				array('station_no' => $parms['station_no'], 'member_no' => $parms['member_no']));
				
			// [B.建立同步檔]
			$sync_seqnos = $this->prepare_sync2hq('U', $parms['station_no'], 'members', $parms['member_no'], $data_member);					// t.3a. 準備同步檔 (members)

			$data_tx = array('tx_state' => MEMBER_TX_TX_STATE_CANCEL);	// 交易取消
			
			$this->db->update('member_tx', $data_tx, 				
					array('station_no' => $parms['station_no'], 'member_no' => $parms['member_no'], 'tx_no' => $parms['tx_no'])); 	// t4. 更新 member_tx.tx_state
			
			$this->gen_member_tx_log($data_tx, $parms['station_no'], $parms['tx_no']);												// t.log. 建立 member_tx_log
			
			$sync_seqnos .= ',' . $this->prepare_sync2hq('U', $parms['station_no'], 'member_tx', $parms['tx_no'], $data_tx); 		// t.5. 準備同步檔 (member_tx)
		}
		else
		{
			// 僅有一筆交易，且交易尚未審核
			if($member_last_tx['verify_state'] == MEMBER_TX_VERIFY_STATE_NONE)
			{
				$data_tx = array('tx_state' => MEMBER_TX_TX_STATE_CANCEL);	// 交易取消
				
				$this->db->update('member_tx', $data_tx, 				
						array('station_no' => $parms['station_no'], 'member_no' => $parms['member_no'], 'tx_no' => $parms['tx_no'])); 	// t4. 更新 member_tx.tx_state
				
				$this->gen_member_tx_log($data_tx, $parms['station_no'], $parms['tx_no']);												// t.log. 建立 member_tx_log
				
				$sync_seqnos = $this->prepare_sync2hq('U', $parms['station_no'], 'member_tx', $parms['tx_no'], $data_tx); 				// t.5. 準備同步檔 (member_tx)
				
				// 若此交易有壓金，判定為取消新增會員，進入刪除會員流程 2017/04/16 added 
				if($member_last_tx['deposit'] > 0)
				{
					trigger_error(TX_LOG_TITLE.' DELETE members, member_car：' . print_r($parms, true));
					
					// B. 第一筆交易, 金額尚未審核, 直接進行刪除流程
					$this->db->delete('members', array('station_no' => $parms['station_no'], 'member_no' => $parms['member_no']));  	// t.1b. 刪除 members
					$this->gen_member_log($data_member, $parms['station_no'], $parms['member_no']);										// t.log. 建立 member_log
					$this->db->delete('member_car', array('station_no' => $parms['station_no'], 'member_no' => $parms['member_no']));	// t.2b. 刪除 member_car
					
					// [B.建立同步檔]
					$sync_seqnos.= ',' .$this->prepare_sync2hq('D', $parms['station_no'], 'members', $parms['member_no'], array()); 	// t.3b. 準備同步檔 (members)		
				}
				
				// 若此交易為轉租，判定為取消會員轉租，還原原退租記錄 （TODO: 預設都是全額押金轉租，如果有變化就要改成還原上一個狀態）
				if($data_member['refund_transfer_id'] > 0)
				{	
					trigger_error(TX_LOG_TITLE.' RESTORE member_refund' . print_r($data_member, true));
					
					$data_refund = array('dismiss_state' => MEMBER_REFUND_DISMISS_STATE_KEEP_DEPOSIT);	
					$station_no = $data_member['station_no'];
					$refund_transfer_id = $data_member['refund_transfer_id'];
					
					$this->db->update('member_refund', $data_refund, array('station_no' => $station_no, 'member_refund_id' => $refund_transfer_id));	// t8.1 更新 member_refund
					
					$this->gen_member_refund_log($data_refund, $station_no, $refund_transfer_id);										// t.log. 建立 member_refund_log
					
					$sync_seqnos .= ',' . $this->prepare_sync2hq('U', $station_no, 'member_refund', $refund_transfer_id, $data_refund);	// t8. 準備同步檔 (member_refund)	
				}
				
			}
			else
			{
				$this->db->trans_complete();
				trigger_error(__FUNCTION__ . '..tx_error_refuse..' . print_r($parms, true));
				return 'tx_error_refuse';	 	// 中斷
			}
		}
		
		// [c.完成]
		$this->db->trans_complete();
		if ($this->db->trans_status() === FALSE)
		{
			trigger_error(__FUNCTION__ . '..trans_error..data:' . print_r($parms, true). '| last_query: ' . $this->db->last_query());
			return 'fail';	 		// 中斷
		}
        
        // 同步至總管理處
		$this->worker_tx('sync_batch', array('sync_seqnos' => $sync_seqnos));    
        return 'ok';
	}
	
	// 接續開立發票
	public function next_tx_bill($parms, $rents_arr)
	{   
		$this->try_sync_batch($parms['station_no']); // 同步未同步記錄
		
		trigger_error(TX_LOG_TITLE.'接續開立發票：' . print_r($parms, true). print_r($rents_arr, true));
		
		// 確認上一筆 member_tx_bill
		$rows = $this->db->select('
						member_tx_bill.tx_bill_no,
						member_tx_bill.invoice_no, 
						member_tx_bill.lpr, 
						member_tx_bill.member_company_no, 
						member_tx_bill.company_no, 
						member_tx_bill.acc_date, 
						member_tx_bill.remain_amt as amt,
						COALESCE(members.member_attr, 1) as member_attr,
						member_tx_bill.invoice_count,
						member_tx_bill.invoice_next_date,
						member_tx_bill.invoice_state
						')
        		->from('member_tx_bill')
				->join('members', 'members.member_no = member_tx_bill.member_no', 'left')
                ->where(array(
						'member_tx_bill.tx_bill_no' => $parms['tx_bill_no'], 'member_tx_bill.tx_no' => $parms['tx_no'], 
						'member_tx_bill.station_no' => $parms['station_no'], 'member_tx_bill.member_no' => $parms['member_no'],
						'member_tx_bill.remain_amt' => $parms['remain_amt']
						))
                ->get()
                ->row_array(); 

		if(empty($rows))
		{
			trigger_error(__FUNCTION__ . '..member_tx_bill not found..' . print_r($parms, true));
			return 'tx_error_not_found';	 	// 中斷
		}
		
		if($rows['invoice_no'] <= 0)
		{
			trigger_error(__FUNCTION__ . '..member_tx_bill not ready..' . print_r($parms, true));
			return 'tx_error_not_ready';	 	// 中斷（本期的還沒開立）
		}
		
		if($rows['invoice_count'] <= 1 || empty($rows['invoice_next_date']))
		{
			trigger_error(__FUNCTION__ . '..member_tx_bill no next..' . print_r($parms, true));
			return 'tx_error_next';	 			// 中斷（無下一張資訊）
		}
		
		if(strtotime($rows['invoice_next_date']) > time())
		{
			trigger_error(__FUNCTION__ . '..member_tx_bill not yet..' . "next_date:{$rows['invoice_next_date']}" . print_r($parms, true));
			return $rows['invoice_next_date'];	// 中斷（時間還沒到）
		}
		
		$next_invoice_count = ($rows['invoice_count'] > 1) ? $rows['invoice_count'] - 1 : 1;
		
		// 接續發票開立記錄 (公式： 拆分第一期金額)
		$invoice_amt = $this->gen_invoice_count_amt($rows['amt'], $next_invoice_count);
		$remain_amt = $rows['amt'] - $invoice_amt;
		trigger_error(__FUNCTION__ . ', amt:'. $rows['amt']. ', invoice_amt:' .$invoice_amt . ', remain_amt:' . $remain_amt);
		
		/*
		// 接續發票開立記錄 (公式 B： 最多一季金額)
		$period_3_amt = ($rents_arr[3][$rows['member_attr']] > 2000) ? $rents_arr[3][$rows['member_attr']] : 2000; // 至少 $2000
		$invoice_amt = ($rows['amt'] > $period_3_amt) ? $period_3_amt : $rows['amt'];
		$remain_amt = $rows['amt'] - $invoice_amt;
		trigger_error('period_3_amt: ' . $period_3_amt . ', amt:'. $rows['amt']. ', invoice_amt:' .$invoice_amt . ', remain_amt:' . $remain_amt);
		*/
		
		// [A.開始]
		$this->db->trans_start();
		$data_tx_bill = array(
				'tx_no' => $parms['tx_no'],					// 交易編號
				
				'member_no' => $parms['member_no'],			// 會員編號
				'station_no' => $parms['station_no'],		// 場站編號
				'sync_no' => 0,								// 預設同步編號
				
				'lpr' => $rows['lpr'],						// 車牌號碼
				
				'member_company_no' => $rows['member_company_no'],	// 買方統編
				'company_no' => $rows['company_no'],				// 賣方統編
				
				'acc_date' => $rows['acc_date'],			// 入帳日(延用)  
				
				'invoice_amt' => $invoice_amt,				// 本次發票金額
				'remain_amt' => $remain_amt,				// 剩餘未開立金額
				
				'invoice_count' => $next_invoice_count,		// 預計發票張數
				
				'invoice_state' => $rows['invoice_state']	// 發票狀態
			);
			
		if($next_invoice_count > 1 && !empty($rows['invoice_next_date']))
		{	
			$data_tx_bill['invoice_next_date'] = $this->gen_invoice_next_date($rows['invoice_next_date']);	// 預計下一張發票開立日
		}
			
		$this->db->insert('member_tx_bill', $data_tx_bill);  									// t1 新增 member_tx_bill
		$tx_bill_no = $this->db->insert_id(); 				// 帳單序號 
		$data_tx_bill['tx_bill_no'] = $tx_bill_no;
		
		$tx_bill_no_reset = $rows['tx_bill_no'];
		$data_tx_bill_reset = array('remain_amt' => 0, 'invoice_count' => 1);
		$this->db->update('member_tx_bill', $data_tx_bill_reset, 
			array('station_no' => $parms['station_no'], 'tx_bill_no' => $tx_bill_no_reset));	// t1.a. 更新 member_tx_bill.remain_amt 為 0

		// [B.準備同步檔]
		$sync_seqnos = $this->prepare_sync2hq('A', $parms['station_no'], 'member_tx_bill', $tx_bill_no, $data_tx_bill);							// t2 準備同步檔 (member_tx_bill)
		$sync_seqnos .= ',' . $this->prepare_sync2hq('U', $parms['station_no'], 'member_tx_bill', $tx_bill_no_reset, $data_tx_bill_reset); 		// t2.a 準備同步檔 (member_tx_bill)
		
		// [c.完成]
		$this->db->trans_complete();
		if ($this->db->trans_status() === FALSE)
		{
			trigger_error(__FUNCTION__ . '..trans_error..data:' . print_r($data_tx_bill, true). '| last_query: ' . $this->db->last_query());
			return 'fail';	 		// 中斷
		}
        
        // 同步至總管理處
		$this->worker_tx('sync_batch', array('sync_seqnos' => $sync_seqnos));    
        return 'ok';
    } 
	
	// 停權或啟動
    public function suspended($parms)
    {
		$this->try_sync_batch($parms['station_no']); // 同步未同步記錄
		
		trigger_error(ADMIN_LOG_TITLE.'停權或啟動：' . print_r($parms, true));
		
		$altob_admin_submit = $this->input->post('altob_admin_submit', true);	// 取得 admin 參數
		if($altob_admin_submit !== $this->gen_admin_ck($parms['station_no']))
		{
			trigger_error(__FUNCTION__ . '..altob_admin_submit error..' . print_r($parms, true));
			return 'admin_error';	 // 中斷
		}
		
		$data = array('suspended' => $parms['suspended']);
		
		// [A.開始]
        $this->db->trans_start();
        $this->db->update('members', $data, array('station_no' => $parms['station_no'], 'member_no' => $parms['member_no']));	// t1. 更新 member.suspended
		
		$this->gen_member_log($data, $parms['station_no'], $parms['member_no']);												// t.log. 建立 member_log
		
        // [B.準備同步檔]
		$sync_seqnos = $this->prepare_sync2hq('U', $parms['station_no'], 'members', $parms['member_no'], $data); 				// t2. 準備同步檔
		// [C.完成]
		$this->db->trans_complete();		
		if ($this->db->trans_status() === FALSE)
		{
			trigger_error(__FUNCTION__ . '..trans_error..data:' . print_r($data, true). '| last_query: ' . $this->db->last_query());
			return 'fail';	 		// 中斷
		}
		
		// 同步至總管理處
		$this->worker_tx('sync_batch', array('sync_seqnos' => $sync_seqnos));
        return 'ok';
    }
	
	// 批次延時
    public function member_tx_check_list_confirm_batch($parms)
    {
		$this->try_sync_batch($parms['station_no']); // 同步未同步記錄
		
		trigger_error(ADMIN_LOG_TITLE.'批次延時：' . print_r($parms, true));
		
		$altob_admin_submit = $this->input->post('altob_admin_submit', true);	// 取得 admin 參數
		if($altob_admin_submit !== $this->gen_admin_ck($parms['station_no']))
		{
			trigger_error(__FUNCTION__ . '..altob_admin_submit error..' . print_r($parms, true));
			return 'admin_error';	 // 中斷
		}
		
		// 建立延時資訊
		if($parms['day'] > 0)
		{
			$new_valid_time = date('Y-m-d 23:59:59', strtotime("{$this->now_str} + {$parms['day']} days"));	
		}
		else
		{
			$new_valid_time = date('Y-m-d 23:59:59', strtotime("{$this->now_str} + 1 days"));
		}
		
		trigger_error(__FUNCTION__ . '..start..' . print_r($parms, true) . ', new_valid_time: '. $new_valid_time);
		
		// [A.開始]
		$this->db->trans_start();
		$sync_seqnos = '';
		
		// 更新有效期限: 交易記錄
		$tx_no_array = explode(',', $parms['tx_no_str']);
		foreach($tx_no_array as $idx => $tx_no)
		{
			$data_tx = array('valid_time' => $new_valid_time);
			$this->db->update('member_tx', $data_tx, array('station_no' => $parms['station_no'], 'tx_no' => $tx_no));	// t1. 更新 member_tx.valid_time
			$this->gen_member_tx_log($data_tx, $parms['station_no'], $tx_no);											// t.log. 建立 member_tx_log
			
			if($idx > 0)
			{
				$sync_seqnos .= ',';
			}
			
			$sync_seqnos .= $this->prepare_sync2hq('U', $parms['station_no'], 'member_tx', $tx_no, $data_tx); 			// t2. 準備同步檔 (member_tx)
		}
		
		// 更新有效期限: 會員
		$member_no_array = explode(',', $parms['member_no_str']);
		foreach($member_no_array as $idx => $member_no)
		{
			// 取得會員資料
			$member_info = $this->db->select('member_no, valid_time')
        		->from('members')
                ->where(array('station_no' => $parms['station_no'], 'member_no' => $member_no))
                ->get()
                ->row_array();
		
			if(empty($member_info))
			{
				trigger_error(__FUNCTION__ . '..member not found..' . print_r($parms, true));
			}
			else
			{
				if(strtotime($member_info['valid_time']) >= strtotime($new_valid_time) )
				{
					trigger_error(__FUNCTION__ . '..member already got valid_time..' . print_r($member_info, true));	
				}
				else
				{
					$data_member = array('valid_time' => $new_valid_time);
					$this->db->update('members', $data_member, array('station_no' => $parms['station_no'], 'member_no' => $member_no));	// t3. 更新 members.valid_time
					$this->gen_member_log($data_member, $parms['station_no'], $member_no);												// t.log. 建立 member_log		
					
					$sync_seqnos .= ',' . $this->prepare_sync2hq('U', $parms['station_no'], 'members', $member_no, $data_member); 		// t4. 準備同步檔 (members)
				}
			}
		}

		// [C.完成]
		$this->db->trans_complete();		
		if ($this->db->trans_status() === FALSE)
		{
			trigger_error(__FUNCTION__ . '..trans_error..data:' . print_r($data, true). '| last_query: ' . $this->db->last_query());
			return 'fail';	 		// 中斷
		}
		
		// 同步至總管理處
		$this->worker_tx('sync_batch', array('sync_seqnos' => $sync_seqnos));
        return 'ok';
    }
	
	// 設定關帳時間點
    public function set_check_point($parms)
    {
		$this->try_sync_batch($parms['station_no']); // 同步未同步記錄
		
		trigger_error(TX_LOG_TITLE.'設定關帳時間點：' . print_r($parms, true));
		
		// 取得上一關帳時間點
		$check_time_last_result = $this->db->select('check_time as check_time_last, check_time_no as check_time_last_no')
        			->from('check_points')	
                    ->where(array('station_no' => $parms['station_no'])) 
					->order_by("check_time", "desc")
                    ->get()
                    ->row_array();   
		
		$check_time_last = !empty($check_time_last_result['check_time_last']) ? $check_time_last_result['check_time_last'] : '2017-01-01 00:00:00';
		$check_time_last_no = !empty($check_time_last_result['check_time_last_no']) ? $check_time_last_result['check_time_last_no'] : 0;
		
		// 取得本次關帳時間對應交易編號
		$member_tx_last_result = $this->db->select('tx_no as check_time_no')
        			->from('member_tx')	
                    ->where(array(
						'acc_time <= ' => $parms['check_time'], 
						'tx_no > ' => $check_time_last_no, 
						'station_no' => $parms['station_no'], 
						'tx_state' => MEMBER_TX_TX_STATE_NONE
						))
					->order_by("tx_no", "desc")
                    ->get()
                    ->row_array(); 
					
		$check_time_no = !empty($member_tx_last_result['check_time_no']) ? $member_tx_last_result['check_time_no'] : 0;
		
		// 取得上一關帳時間點至本次時間點，金額加總等資訊
		$member_tx_result = $this->db->select('SUM(member_tx.amt) as amt, SUM(member_tx.amt1) as amt1, SUM(member_tx.deposit) as deposit')
        			->from('member_tx')	
                    ->where(array(
						'tx_no <= ' => $check_time_no, 
						'tx_no > ' => $check_time_last_no, 
						'station_no' => $parms['station_no'], 
						'tx_state' => MEMBER_TX_TX_STATE_NONE
						))
                    ->get()
                    ->row_array(); 

		$check_amt = $member_tx_result['amt'] + $member_tx_result['amt1'];
		$check_deposit = $member_tx_result['deposit'];
		
		// 確認金額是否異常
		if(empty($check_amt) && empty($check_deposit))
		{
			trigger_error(__FUNCTION__ . '..error_amt..parms:' . print_r($parms, true). '| last_query: ' . $this->db->last_query());
			return 'error_amt';	 		// 中斷
		}
		
		trigger_error(__FUNCTION__ . ', check_amt:'. $check_amt. ', check_deposit:' .$check_deposit . 
			', check_time:' . $parms['check_time'] . ', check_time_last:' . $check_time_last .
			', check_time_no:' . $check_time_no . ', check_time_last_no:' . $check_time_last_no
			);
		
		$data_check_point = array(
				'station_no' => $parms['station_no'],
				'check_time' => $parms['check_time'],
				'check_time_no' => $check_time_no,
				'check_time_last' => $check_time_last,
				'check_time_last_no' => $check_time_last_no,
				'check_type' => 1, 						// 手動關帳
				'check_amt' => $check_amt,				// 總金額
				'check_deposit' => $check_deposit,		// 總押金
				'remarks' => $parms['remarks']
			);
		
		// [A.開始]
        $this->db->trans_start();
        $this->db->insert('check_points', $data_check_point);  																					// t1. 新增 check_points
		$check_no = $this->db->insert_id(); 
		$data_check_point['check_no'] = $check_no;
		
        // [B.準備同步檔]
		$sync_seqnos = $this->prepare_sync2hq('A', $parms['station_no'], 'check_points', $data_check_point['check_no'], $data_check_point); 	// t2. 準備同步檔
		// [C.完成]
		$this->db->trans_complete();		
		if ($this->db->trans_status() === FALSE)
		{
			trigger_error(__FUNCTION__ . '..trans_error..data_check_point:' . print_r($data_check_point, true). '| last_query: ' . $this->db->last_query());
			return 'fail';	 		// 中斷
		}
		
		// 同步至總管理處
		$this->worker_tx('sync_batch', array('sync_seqnos' => $sync_seqnos));
        return 'ok';
    }
	
	// 關帳查詢
	public function check_point_query($parms) 
	{               
		$station_no = $parms['station_no'];
		$check_point_time_from = $parms['check_point_time_from'];
		$check_point_time_to = $parms['check_point_time_to'];
        
        $sql = "select
					check_no, station_no, check_time, check_time_no, check_time_last, check_time_last_no, 
					check_amt, check_deposit, check_type, remarks, create_time
        		from check_points
                where 
					station_no = {$station_no} and 
					check_time >= '{$check_point_time_from}' and
					check_time <= '{$check_point_time_to}'
				order by check_no desc
				";
        
    	$results = $this->db->query($sql)->result_array();
        
        return $results;
    }
	
	// 關帳查詢（明細）
	public function check_point_detail_query($parms)
	{           
		$station_no = $parms['station_no'];
		$check_time_no = $parms['check_time_no'];
		$check_time_last_no = $parms['check_time_last_no'];
	
		$sql = "
				SELECT
					members.lpr as current_lpr,
					member_tx.lpr,
					member_tx.tx_no, 
					member_tx.station_no, 
					member_tx.member_no, 
					member_tx.fee_period,
					member_tx.fee_period_last,
					member_tx.amt1,
					member_tx.amt,
					member_tx.amt_last,
					member_tx.deposit,
					date_format(member_tx.start_date,'%Y-%m-%d') as start_date, 
					date_format(member_tx.end_date,'%Y-%m-%d') as end_date,  
					date_format(member_tx.start_date_last,'%Y-%m-%d') as start_date_last, 
					date_format(member_tx.end_date_last,'%Y-%m-%d') as end_date_last,  
					member_tx.member_company_no,
					member_tx.company_no,
					member_tx.acc_date,
					member_tx.invoice_no,
					member_tx.invoice_amt,
					member_tx.invoice_track,
					member_tx.invoice_time,
					member_tx.invoice_type,
					member_tx.verify_state,
					member_tx.valid_time,
					member_tx.remarks,
					member_tx.tx_state
        		FROM member_tx
					LEFT JOIN members ON (member_tx.member_no = members.member_no AND members.station_no = member_tx.station_no)
				WHERE 
					member_tx.station_no = {$station_no} and 
					member_tx.tx_no <= {$check_time_no} and
					member_tx.tx_no > {$check_time_last_no}
				ORDER BY member_tx.valid_time ASC
				";
				
    	$results = $this->db->query($sql)->result_array();
        return $results; 
    }
	
	// 電子發票查詢
	public function member_invoice_query($parms) 
	{               
		$station_no = $parms['station_no'];
		$member_invoice_time_from = $parms['member_invoice_time_from'];
		$member_invoice_time_to = $parms['member_invoice_time_to'];
        
        $sql = "select
					order_no, amt, station_no, tx_time, tx_type, email, mobile, invoice_no, invoice_remark, status, lpr
        		from tx_bill_ats
                where 
					station_no = {$station_no} and 
					tx_time >= '{$member_invoice_time_from}' and
					tx_time <= '{$member_invoice_time_to}' and
					invoice_no is not NULL
				order by tx_time desc
				";
        
    	$results = $this->db->query($sql)->result_array();
        
        return $results;
    }
	
	// 電子發票作廢
	public function member_invoice_void($parms) 
	{
		$this->try_sync_batch($parms['station_no']);	// 同步未同步記錄
		
		trigger_error(TX_LOG_TITLE.'電子發票作廢：' . print_r($parms, true));
		
		$void_result = $this->void_invoice($parms);	// 作廢發票
		
		if($void_result['result_code'] == 'OK')
		{
			$tx_bill_no = $void_result['tx_bill_no'];
			
			$data = array
			(
				'member_company_no' => 0,
				'invoice_track' => '',
				'invoice_no' => 0,
				'invoice_time' => NULL,
				'invoice_type' => 0
			);
			
			// [A.開始]
			$this->db->trans_start();
			$this->db->update('member_tx_bill', $data, array('station_no' => $parms['station_no'], 'tx_bill_no' => $tx_bill_no));	// t1. 更新 member_tx_bill
			
			trigger_error(TX_LOG_TITLE.'更新發票記錄：' . print_r($data, true) . ', tx_bill_no: '. $tx_bill_no);
			
			// [B.建立同步檔]
			$sync_seqnos = $this->prepare_sync2hq('U', $parms['station_no'], 'member_tx_bill', $tx_bill_no, $data); 		// t2. 準備同步檔 (member_tx_bill)
			
			// [C.完成]
			$this->db->trans_complete();
			if ($this->db->trans_status() === FALSE)
			{
				trigger_error(TX_LOG_TITLE.'..trans_error..last_query:' . $this->db->last_query());
				trigger_error(__FUNCTION__ . '..trans_error..data:' . print_r($data, true). '| last_query: ' . $this->db->last_query());
				return 'fail';	 		// 中斷
			}
			
			// 同步至總管理處
			$this->worker_tx('sync_batch', array('sync_seqnos' => $sync_seqnos));
			return 'ok';	
		}
		else if(!empty($void_result['result_msg']))
		{
			return $void_result['result_msg'];	// 作廢發票失敗
		}
		else
		{
			return '未知的錯誤';
		}
	}
	
	// 取得未同步資料筆數
	public function get_un_synced_count($station_no)
	{
		$sql = "select COUNT(*) as count from syncs where synced = 0 and erred = 0 and station_no = {$station_no}";
    	$result = $this->db->query($sql)->result_array();
		
		if(!empty($result[0]))
			return $result[0]; 
		else
			return 0;
	}
	
	// 同步未同步記錄
	public function try_sync_batch($station_no, $limit=5)
	{
		$sql = "select st_sync_no
				from syncs
                where synced = 0 and erred = 0 and station_no = {$station_no}
				order by st_sync_no ASC 
				limit {$limit}";
    	$results = $this->db->query($sql)->result_array();
		
		if(empty($results)) return false; // do nothing
		
		$sync_seqnos = '';
		foreach($results as $rows)
        {
			$sync_seqnos .= ',' . $rows['st_sync_no'];
        }
		$sync_seqnos = ltrim($sync_seqnos, ',');
		trigger_error(__FUNCTION__ . '|' . $sync_seqnos);
		
		// 同步至總管理處
		$this->worker_tx('sync_batch', array('sync_seqnos' => $sync_seqnos));
	}
	
	// 取得 : 預計下一張發票開立日
	function gen_invoice_next_date($this_date)
	{
		return date('Y-m-d', strtotime("+3 months", strtotime($this_date)));
	}
	
	// 取得 : 發票拆分金額
	function gen_invoice_count_amt($amt, $count)
	{
		return round($amt / $count);	// 四捨五入
	}
	
	// 取得 : 預計發票張數
	function gen_invoice_count($fee_period)
	{
		return ceil($fee_period / 3);
	}
	
	// 產生會員記錄
	function gen_members($data)
	{
		$this->db->delete('members', array('station_no' => $data['station_no'], 'lpr' => $data['lpr']));	
		trigger_error(__FUNCTION__ . ', remove members by lpr: ' . $data['lpr']);
		
		$this->db->insert('members', $data);
		trigger_error(__FUNCTION__ . ', new members: ' . print_r($data, true));
		
		return $this->db->insert_id();
	}
	
	// 產生會員車記錄
	function gen_member_car($data)
	{
		$this->db->delete('member_car', array('station_no' => $data['station_no'], 'lpr' => $data['lpr']));	
		trigger_error(__FUNCTION__ . ', remove member_car by lpr: ' . $data['lpr']);
		
		$this->db->delete('member_car', array('station_no' => $data['station_no'], 'member_no' => $data['member_no']));	
		trigger_error(__FUNCTION__ . ', remove member_car by member_no: ' . $data['member_no']);
		
		$this->db->insert('member_car', $data); 
		trigger_error(__FUNCTION__ . ', new member_car: ' . print_r($data, true));
		
		return $this->db->insert_id();
	}
	
	// 產生會員檔記錄
	function gen_member_log($data, $station_no=0, $member_no=0)
	{
		$data_log = $data;
		
		if(!empty($station_no))
			$data_log['station_no'] = $station_no;
		
		if(!empty($member_no))
			$data_log['member_no'] = $member_no;
		
		$this->db->insert('member_log', $data_log);		
		return $this->db->insert_id();
	}
	
	// 產生交易檔記錄
	function gen_member_tx_log($data, $station_no=0, $tx_no=0)
	{
		$data_log = $data;
		
		if(!empty($station_no))
			$data_log['station_no'] = $station_no;
		
		if(!empty($tx_no))
			$data_log['tx_no'] = $tx_no;
		
		$this->db->insert('member_tx_log', $data_log);		
		return $this->db->insert_id();
	}
	
	// 產生退租檔記錄
	function gen_member_refund_log($data, $station_no=0, $member_refund_id=0)
	{
		$data_log = $data;
		
		if(!empty($station_no))
			$data_log['station_no'] = $station_no;
		
		if(!empty($member_refund_id))
			$data_log['member_refund_id'] = $member_refund_id;
		
		$this->db->insert('member_refund_log', $data_log);		
		return $this->db->insert_id();
	}

	// 同步至總公司
	function prepare_sync2hq($act, $station_no, $st_tname, $st_seqno, $data)
	{
		$data_syncs = array
		(
			'station_no' => $station_no,
			'synced' => 0,	// 尚未同步 
			'erred' => 0,
			'act' => $act,	// A:新增, U:修改, D:刪除
			'hq_tname' => 'hq_'.$st_tname,
			'st_tname' => $st_tname,	// 場站資料表
			'st_seqno' => $st_seqno,	// 場站交易序號
			'sync_data' => json_encode($data, JSON_UNESCAPED_UNICODE)
		);
		$this->db->insert('syncs', $data_syncs);
		return $this->db->insert_id();
	}	
    
    // curl送收資料
	function worker_tx($cmd, $data)
	{   
		try
		{
			$ch = curl_init(); 
			$curl_options = array
			(
				CURLOPT_URL => "http://localhost:60133/?cmd={$cmd}",
				CURLOPT_HEADER => 0, 
				CURLOPT_RETURNTRANSFER => 1,        // 返回值不顯示, 只做變數用 
				CURLOPT_POST => 1,
				CURLOPT_POSTFIELDS => $data
			);        
			curl_setopt_array($ch, $curl_options);
			curl_exec($ch);
			curl_close($ch);
		}
		catch (Exception $e)
		{
			trigger_error("{$cmd} error: ".$e->getMessage());
        }
    } 
	
	// 管理員參數
	function gen_admin_ck($station_no)
	{
		return md5(date("m \a\l\t\o\b d").$station_no.date("i \z\z\z H"));
	}
	
	// 試算臨停費用
	function get_bill($in_time, $balance_time, $station_no)
	{
		require_once(ALTOB_BILL_FILE); // 臨停費率
		$oPayment = new AltobPayment();
		$oPayment->ServiceURL = ALTOB_PAYMENT_TXDATA_URL;
		
		$bill = $oPayment->getBill($in_time, $balance_time, $station_no);
		$price = $bill[BillResultKey::price];
		trigger_error(__FUNCTION__ . "|{$station_no}|{$in_time}|{$balance_time}|price:{$price}");
		
		return $price;
	}
	
	
	// 印發票
	function print_invoice($parms)
	{
		try{
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'http://localhost/allpay_invoice.html/create_member_tx_bill_invoice');
            curl_setopt($ch, CURLOPT_HEADER, FALSE);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,30);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30); //timeout in seconds
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parms));
            $data = curl_exec($ch);
			
			if(curl_errno($ch))
			{
				trigger_error(__FUNCTION__ . ', curl error: '. curl_error($ch));
			}
			
            curl_close($ch);
			
			if(!empty($data))
			{
    			$data_decode = json_decode($data, true);
				
				if($data_decode['result_code'] == 'OK')
				{
					$result = array();
					$result['einvoice_track'] = substr($data_decode['invoice_no'], 0, 2);		// 發票字軌 
					$result['einvoice_no'] = substr($data_decode['invoice_no'], 2, 8);			// 發票號碼			
					return $result;
				}
    		}

		}catch (Exception $e){
			trigger_error(__FUNCTION__ . 'error:'.$e->getMessage());
		}
		
		$result = array();
		$result['einvoice_track'] = '';			// 發票字軌 
		$result['einvoice_no'] = '';			// 發票號碼	
		return $result;
	}
	
	// 作廢發票
	function void_invoice($parms)
	{
		try{
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'http://localhost/allpay_invoice.html/void_member_tx_bill_invoice');
            curl_setopt($ch, CURLOPT_HEADER, FALSE);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,30);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30); //timeout in seconds
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parms));
            $data = curl_exec($ch);
			
			if(curl_errno($ch))
			{
				trigger_error(__FUNCTION__ . ', curl error: '. curl_error($ch));
			}
			
            curl_close($ch);
			
			if(!empty($data))
			{
    			$data_decode = json_decode($data, true);
				
				return $data_decode;
    		}

		}catch (Exception $e){
			trigger_error(__FUNCTION__ . 'error:'.$e->getMessage());
		}
		
		return 0;
	}
	
	// 折讓發票
	function allowance_invoice($parms)
	{
		try{
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'http://localhost/allpay_invoice.html/allowance_member_tx_bill_invoice');
            curl_setopt($ch, CURLOPT_HEADER, FALSE);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,30);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30); //timeout in seconds
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parms));
            $data = curl_exec($ch);
			
			if(curl_errno($ch))
			{
				trigger_error(__FUNCTION__ . ', curl error: '. curl_error($ch));
			}
			
            curl_close($ch);
			
			if(!empty($data))
			{
    			$data_decode = json_decode($data, true);
				
				return $data_decode;
    		}

		}catch (Exception $e){
			trigger_error(__FUNCTION__ . 'error:'.$e->getMessage());
		}
		
		return 0;
	}
	
	/*
	// 印發票
	public function print_invoice($parms)
	{
		$result = array();
		try
		{
			// 印發票
			$ch = curl_init(); 
			$curl_options = array
			(
				CURLOPT_URL => "http://localhost:60134/",
				CURLOPT_HEADER => 0, 
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_CONNECTTIMEOUT => 2,
				CURLOPT_TIMEOUT => 2,
				CURLOPT_POST => 1,
				CURLOPT_POSTFIELDS => array(
						'cmd'			=> 'printInvoice',
						'company_no'	=>	$parms['company_no'],
						'vCUS_COMP_CODE'=>	$parms['member_company_no'],
						'vAmount'		=>	$parms['invoice_amt'],
						'vPLU_MEMO'		=>	'parking:50:3',
						'vTAIL_MESSAGE'	=>	'Rental'
					)
			);        
			curl_setopt_array($ch, $curl_options);
			$ch_response = curl_exec($ch);
			
			trigger_error(__FUNCTION__ . '|' . print_r($ch_response, true));
			
			curl_close($ch);
			
			$result = json_decode($ch_response, true);
		}
		catch (Exception $e)
		{
			trigger_error(__FUNCTION__ .$e->getMessage());
        }
		
		//測試用
		//$result['einvoice_track'] = 'AB';			// 發票字軌 
		//$result['einvoice_no'] = '12345678';		// 發票號碼			
		
		return $result;
	}
	*/
	
	
	
	
	
	
}
