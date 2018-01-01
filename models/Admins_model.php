<?php             
/*
file: Admins_model.php
*/                   

class Admins_model extends CI_Model 
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
					MIN(CONCAT(member_tx.tx_no, member_tx.verify_state)) as tx_order,
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
					member_tx.tx_no,
					member_tx.verify_state,
					member_tx.valid_time,
					member_tx.remarks
				FROM member_tx
					LEFT JOIN members ON member_tx.member_no = members.member_no
				WHERE
					members.member_no IS NOT NULL
				GROUP BY member_tx.member_no
				ORDER BY members.lpr ASC
				";
    	$results = $this->db->query($sql)->result_array();
        return $results; 
    }
	
	// 待審核清單
	public function member_tx_check() 
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
					member_tx.remarks
        		FROM member_tx
					LEFT JOIN members ON (member_tx.member_no = members.member_no AND members.station_no = member_tx.station_no)
				WHERE member_tx.verify_state != 1
				ORDER BY member_tx.valid_time ASC
				";
        
    	$results = $this->db->query($sql)->result_array();
        return $results; 
    }
    
    // 會員查詢
	public function member_query($station_no, $q_item, $q_str) 
	{               
    	$where_station = $station_no == 0 ? '' : " station_no = {$station_no} and ";	// 如為0, 則全部塲站讀取 
                                    
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
					remarks
        		from member_tx
                where station_no = {$station_no} and member_no = {$member_no}
				order by tx_no desc";
        
    	$results = $this->db->query($sql)->result_array();
        
        return $results;
    }
	
	// 審核完成
	public function member_tx_confirmed($parms) 
	{
		$this->try_sync_batch($parms['station_no']); // 同步未同步記錄
		
		$altob_admin_submit = $this->input->post('altob_admin_submit', true);	// 取得 admin 參數
		if($altob_admin_submit !== $this->gen_admin_ck($parms['station_no']))
		{
			trigger_error(__FUNCTION__ . '..altob_admin_submit error..' . print_r($parms, true));
			return 'admin_error';	 // 中斷
		}
		
		$data_tx = array('verify_state' => $parms['verify_state'], 'valid_time' => $parms['valid_time'], 'remarks' => $parms['remarks']);
		
		// [A.開始]
        $this->db->trans_start();
        $this->db->update('member_tx', $data_tx, array('station_no' => $parms['station_no'], 'tx_no' => $parms['tx_no'])); 	// t1. 更新 member_tx.verify_state, member_tx.valid_time, member_tx.remarks
        // [B.建立同步檔]
		$sync_seqnos = $this->prepare_sync2hq('U', $parms['station_no'], 'member_tx', $parms['tx_no'], $data_tx); 			// t2. 準備同步檔 (member_tx)
		// [C.完成]
		$this->db->trans_complete();
		if ($this->db->trans_status() === FALSE)
		{
			trigger_error(__FUNCTION__ . '|'.print_r($data_tx, true) . '..trans_error..');
			return 'fail';	 		// 中斷
		}
		
		$this->worker_tx('sync_batch', array('sync_seqnos' => $sync_seqnos));
        return 'ok';
    }
     
    
    // 刪除月租會員
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
			trigger_error(__FUNCTION__ . ": {$station_no}, {$member_no}, trans_error..");
			return 'trans_error';
		}
		
		// 同步至總管理處
		$this->worker_tx('sync_batch', array('sync_seqnos' => $sync_seqnos));
        return 'ok'; 
    }
    
    // 月租會員加入
	public function member_add($data) 
	{
		$data['lpr'] = preg_replace('/\s+/', '', $data['lpr']);		// 移除空白
		
		$check_member_no = $data['member_no'];		
		$station_no = $data['station_no'];
		$tx_no = 0;
		
		$this->try_sync_batch($station_no); // 同步未同步記錄
		
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
        
    	if ($check_member_no == 0) 	// 新增一筆會員資料
        {                  
        	unset($data['member_no']); 
			$data['payed_date'] = substr($this->now_str, 0, 10);
        	$data['login_id'] = $data['lpr'];
        	$data['passwd'] = $data['lpr'];
			$action_code = 'A';
			
			// [A.開始]
			$this->db->trans_start();
			$this->db->insert('members', $data);																			// t1 新增 members
			$members_insert_id = $this->db->insert_id();
			$data_car['member_no'] = $members_insert_id;																	// t2. 新增 member_car
    		$this->db->insert('member_car', $data_car); 
			$data['member_no'] = $members_insert_id;
			$data_bill = array(
				'member_no' => $members_insert_id,			// 會員編號
				'station_no' => $station_no,				// 場站編號

				'lpr' => $data['lpr'],						// 車牌號碼
				
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
				'end_date_last' => $data['demonth_end_date']		// 上期結束日
			);
			$this->db->insert('member_bill', $data_bill);  																	// t3 新增 member_bill
			$bill_no = $this->db->insert_id(); 				// 帳單序號 
			$data_tx = array(
				'bill_no' => $bill_no,						// 帳單序號
				'member_no' => $members_insert_id,			// 會員編號
				'station_no' => $station_no,				// 場站編號
				'sync_no' => 0,								// 預設同步編號
				'lpr' => $data['lpr'],						// 車牌號碼
				
				'amt_accrued' => $data['amt_accrued'],		// 應收租金
				'amt_tot' => $data['amt_tot'],				// 實收租金
				'deposit' => $data['deposit'],				// 押金
				
				'amt' => $data['amt'],						// 本期租金
				
				// todo: 2016/12/23 只要超過季繳金額就拆開, 後續再由待開發票開立
				
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
				
				'acc_date' => $data['payed_date'],			// 入帳日(暫定)  
				
				'valid_time' => $this->default_valid_time	// 有效期限
			);
			$this->db->insert('member_tx', $data_tx);  																		// t4 新增 member_tx
			$tx_no = $this->db->insert_id(); 				// 交易序號 
			$data_tx['tx_no'] = $tx_no;
			
			// [B.建立同步檔]
			$sync_seqnos = $this->prepare_sync2hq($action_code, $station_no, 'members', $members_insert_id, $data); 		// t5 準備同步檔 (members)
			$sync_seqnos .= ',' . $this->prepare_sync2hq($action_code, $station_no, 'member_bill', $bill_no, $data_bill); 	// t6 準備同步檔 (member_bill)
			$sync_seqnos .= ',' . $this->prepare_sync2hq($action_code, $station_no, 'member_tx', $tx_no, $data_tx); 		// t7 準備同步檔 (member_tx)
			
			// [C.完成]
			$this->db->trans_complete();
			if ($this->db->trans_status() === FALSE)
			{
				trigger_error(__FUNCTION__ . '..trans_error..' . print_r($data, true));
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
				unset($data['member_attr']);
				unset($data['deposit']);
				unset($data['amt_tot']);
				unset($data['amt_accrued']);
				unset($data['fee_period1']);
				unset($data['amt1']);
				//unset($data['fee_period']);			// 例行繳期
				//unset($data['amt']);					// 例行租金
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
			}
			$action_code = 'U';
			
			// [A.開始]
			$this->db->trans_start();
        	$this->db->update('members', $data, array('station_no' => $station_no, 'member_no' => $check_member_no)); 	// t1. 更新 members
			
			// 沒有異動到車牌, 使用update, 否則重建一筆
            if ($old_lpr == $data['lpr'])																				// t2. 更新 member_car
            {
            	unset($data_car['lpr']);
            	unset($data_car['lpr_correct']);
        		$this->db->update('member_car', $data_car, array('station_no' => $station_no, 'member_no' => $check_member_no));
            } 
            else
            {
    			$this->db->delete('member_car', array('station_no' => $station_no, 'member_no' => $check_member_no));
       			$data_car['member_no'] = $check_member_no;         
    			$this->db->insert('member_car', $data_car);
            }
			
			// [B.建立同步檔]
			$sync_seqnos = $this->prepare_sync2hq($action_code, $station_no, 'members', $check_member_no, $data); 		// t3. 準備同步檔 (members)
			
			// [C.完成]
			$this->db->trans_complete();
			if ($this->db->trans_status() === FALSE)
			{
				trigger_error(__FUNCTION__ . '..trans_error..' . print_r($data, true));
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
			'tx_no' => $tx_no); 
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
	public function set_tx_invoice_no($parms)
	{
        $data = array
        (
        	'member_company_no' => $parms['member_company_no'],
        	'company_no' => $parms['company_no'],
			'invoice_track' => $parms['invoice_track'],
			'invoice_no' => $parms['invoice_no'],
			'invoice_amt' => $parms['invoice_amt'],
			'invoice_time' => $parms['invoice_time']
        );
		
		if(array_key_exists('invoice_type', $parms))
		{
			$data['invoice_type'] = $parms['invoice_type']; // 發票種類
		}
		
		// [A.開始]
		$this->db->trans_start();
        $this->db->update('member_tx', $data, array('station_no' => $parms['station_no'], 'tx_no' => $parms['tx_no']));	// t1. 更新 member_tx
		// [B.建立同步檔]
		$sync_seqnos = $this->prepare_sync2hq('U', $parms['station_no'], 'member_tx', $parms['tx_no'], $data); 			// t2. 準備同步檔
		// [C.完成]
		$this->db->trans_complete();
		if ($this->db->trans_status() === FALSE)
		{
			trigger_error(__FUNCTION__ . '|'.print_r($data, true) . '..trans_error..');
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
	
    	// 確認交易記錄
		$rows = $this->db->select('count(*) as counts')
        		->from('member_tx')
                ->where(array('tx_no' => $parms['tx_no'], 
						'station_no' => $parms['station_no'], 'member_no' => $parms['member_no'], 
						'amt' => $parms['amt'], 'amt1' => $parms['amt1']))
                ->get()
                ->row_array(); 

		if(empty($rows) || $rows['counts'] != 1)
		{
			trigger_error(__FUNCTION__ . '..tx gg..' . print_r($parms, true));
			return 'tx_error';	 	// 中斷
		}
		
		// 印發票
		$parms['invoice_amt'] = $parms['amt'] + $parms['amt1']; 					// 例行租金 + 首期租金
		
		if(array_key_exists('invoice_track', $parms) && array_key_exists('invoice_no', $parms))
		{
			$parms['invoice_time'] = date('Y-m-d H:i:s');								// 目前時間
		}
		else
		{
			$invoice_result = $this->print_invoice($parms);
			if(!empty($invoice_result) && array_key_exists('einvoice_no', $invoice_result))
			{
				$parms['invoice_track'] = $invoice_result['einvoice_track'];			// 發票字軌 
				$parms['invoice_no'] = $invoice_result['einvoice_no'];					// 發票號碼 
				$parms['invoice_time'] = date('Y-m-d H:i:s');							// 目前時間
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
	public function rents_payment($parms)
	{   
		$this->try_sync_batch($parms['station_no']); // 同步未同步記錄
	
    	//$parms['start_date'] = $parms['start_date_last']; 
		
		// 印發票
		$parms['invoice_amt'] = $parms['amt'];																	// 例行租金
		if(array_key_exists('invoice_track', $parms) && array_key_exists('invoice_no', $parms))
		{
			$parms['invoice_time'] = date('Y-m-d H:i:s');														// 目前時間
		}
		else
		{
			$invoice_result = $this->print_invoice($parms);
			if(!empty($invoice_result) && array_key_exists('einvoice_no', $invoice_result))
			{
				$parms['invoice_track'] = $invoice_result['einvoice_track'];									// 發票字軌 
				$parms['invoice_no'] = $invoice_result['einvoice_no'];											// 發票號碼 
				$parms['invoice_time'] = date('Y-m-d H:i:s');													// 目前時間
			}
			
			if(empty($parms['invoice_no']) || empty($parms['invoice_track']))
			{
				trigger_error(__FUNCTION__ . '..invoice gg..' . print_r($parms, true));
				//return 'invoice_fail';
			}
		}
		$parms['sync_no'] = 0;																					// 預設同步編號
		$parms['fee_period_last'] = $parms['fee_period'];														// 上期繳期
    	$parms['start_date'] = date('Y-m-d', strtotime("{$parms['end_date_last']} first day of next month"));	// 本期開始日：上期結束日之次月首日
		$parms['acc_date'] = date('Y-m-d');																		// 入帳日(暫定)
		$parms['valid_time'] = $this->default_valid_time;														// 有效期限
		
		// [A.開始]
		$this->db->trans_start();
		$this->db->insert('member_tx', $parms);  																						// t1. 新增 member_tx
        $tx_no = $this->db->insert_id(); // 交易序號 
		$data = $parms;
        $data['tx_no'] = $tx_no;
		$data_member = array(
			'fee_period' => $parms['fee_period'],
			'payed_date' => substr($parms['invoice_time'], 0, 10),
			'start_date' => "{$parms['start_date_last']} 00:00:00",		// 開始日：由上期繼續延續下去
			'end_date' => "{$parms['end_date']} 23:59:59"				// 結束日
		);
        $this->db->update('members', $data_member, array('station_no' => $parms['station_no'], 'member_no' => $parms['member_no']));	// t2. 更新 members
        $this->db->update('member_car', 																								// t5. 更新 member_car
			array('start_time' => "{$data_member['start_date']} 00:00:00", 'end_time' => "{$data_member['end_date']} 23:59:59"), 
			array('station_no' => $parms['station_no'], 'member_no' => $parms['member_no']));

		// [B.準備同步檔]
		$sync_seqnos = $this->prepare_sync2hq('A', $parms['station_no'], 'member_tx', $tx_no, $data); 									// t2. 準備同步檔
		$sync_seqnos .= ',' . $this->prepare_sync2hq('U', $parms['station_no'], 'members', $parms['member_no'], $data_member);			// t4. 準備同步檔
		
		// [c.完成]
		$this->db->trans_complete();
		if ($this->db->trans_status() === FALSE)
		{
			trigger_error(__FUNCTION__ . '|'.print_r($data, true) . '..trans_error..');
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
        // [B.準備同步檔]
		$sync_seqnos = $this->prepare_sync2hq('U', $parms['station_no'], 'members', $parms['member_no'], $data); 				// t2. 準備同步檔
		// [C.完成]
		$this->db->trans_complete();		
		if ($this->db->trans_status() === FALSE)
		{
			trigger_error(__FUNCTION__ . '|'.print_r($data, true) . '..trans_error..');
			return 'fail';	 		// 中斷
		}
		
		// 同步至總管理處
		$this->worker_tx('sync_batch', array('sync_seqnos' => $sync_seqnos));
        return 'ok';
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
}
