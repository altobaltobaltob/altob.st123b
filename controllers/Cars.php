<?php
/*
file: cars.php	車輛進出場處理
*/
class Cars extends CC_Controller
{          
	function __construct() 
	{                            
		parent::__construct('cars');      
		
		//ignore_user_abort();	// 接受client斷線, 繼續run 
		
		if(in_array($this->router->fetch_method(), array(
			'ipcam', 'ipcam_meta', 
			'check_lpr_etag', 
			'opendoor',
			'temp_opendoors', 'member_opendoors'
		)))
		{
			ob_end_clean();
			ignore_user_abort();
			ob_start();
			
			echo 'ok';
			
			header('Connection: close');
			header('Content-Length: ' . ob_get_length());
			ob_end_flush();
			flush();
		}
		else if(in_array($this->router->fetch_method(), array('post_ipcam')))
		{
			ob_end_clean();
			ignore_user_abort();
			ob_start();
			
			echo 'OK';	// 痾
			
			header('Connection: close');
			header('Content-Length: ' . ob_get_length());
			ob_end_flush();
			flush();
		}
	}
	
	// ------------------------------------
	// 廠商對接 START
	// ------------------------------------
	
	// [API]
	public function get_opendoor()
	{
		// 初始 mqtt
		$this->init_mqtt(true);
		
		// 執行
		$parms = $this->uri->uri_to_assoc(3);
		$parms['lpr'] = urldecode($parms['lpr']); // 中文車牌
		
		$return_msg = $this->app_model()->opendoor_lprio($parms);
		trigger_error(__FUNCTION__ . "|{$parms['sno']}|{$parms['io']}|{$parms['lpr']}|return_msg|" . $return_msg);
		
		echo $return_msg;
		exit;
	}
	
	// [API]
	public function post_ipcam()
	{
		// 執行	
    	$parms = $this->uri->uri_to_assoc(3);
		$parms['lpr'] = urldecode($parms['lpr']); // 中文車牌
		
		// 同步並送出一次出入口 888
		//$this->data_model()->sync_888($parms);
                                                                  
        $pic_folder = CAR_PIC.$this->vars['date_num'].'/';		// 今日資料夾名(yyyymmdd)
        if (!file_exists($pic_folder))	mkdir($pic_folder);		// 如果資料夾不存在, 建立日期資料夾
        
        $config['upload_path'] = $pic_folder;           
        $config['allowed_types'] = 'jpg';                 
        $config['file_name'] = "lpr-{$parms['lpr']}-{$parms['io']}-{$parms['ivsno']}-{$parms['sq']}-C-1-{$this->vars['time_num']}.jpg"; 
		
		trigger_error(__FUNCTION__ . '..' . print_r($_FILES, true));
		
		if (!isset($_FILES['cars'])) 
		{
			$status = 'error';		// 顯示上傳錯誤
			trigger_error('[ERROR] cars not found: ' . print_r($_FILES, true));
		}
		else
		{
			$this->load->library('upload', $config);
        
			if(!$this->upload->do_upload('cars')){         
				$status = 'error';		// 顯示上傳錯誤
				trigger_error($this->upload->display_errors());
			} 
			else
			{
				// 若無錯誤，則上傳檔案
				$file = $this->upload->data('cars');
				$status = 'ok';
			}
		}
        
        $parms['obj_type'] = 1;	// 車牌類  
        $parms['curr_time_str'] = $this->vars['date_time'];	// 現在時間, 例2015-09-21 15:36:47  
        $parms['pic_name'] = $config['file_name'];	// 圖片檔名 
        
        $return_msg = $this->app_model()->lprio($parms);
		trigger_error(__FUNCTION__ . "|{$parms['sno']}|{$parms['io']}|{$parms['lpr']}|return_msg|" . $return_msg);
	}
	
	// ------------------------------------
	// 廠商對接 END
	// ------------------------------------
	
	/*
		出入口
		
		說明: 與ipcam相同判斷邏輯, 但不做任何資料更改
    */
	public function opendoor()
	{
		// 初始 mqtt
		$this->init_mqtt();
		
		// 執行
		$parms = $this->uri->uri_to_assoc(3);
		$parms['lpr'] = urldecode($parms['lpr']); // 中文車牌
		
		$return_msg = $this->app_model()->opendoor_lprio($parms);
		trigger_error(__FUNCTION__ . "|{$parms['sno']}|{$parms['io']}|{$parms['lpr']}|return_msg|" . $return_msg);
	}
    
    // IVS -> 車號, 影像 
    /*
    	鼎高IVS傳送車號及影像檔
http://192.168.10.201/cars.html/ipcam/sno/12119/ivsno/0/io/O/type/C/lpr/4750YC/color/NULL/sq/0/ts/1441051995/sq2/0/etag/ABCD123456789/ant/1
		sno:       場站編號(光興國小:12119)
		ivsno:     ivs編號, 每一支都是獨立編號(序號)
		io:        i:進場, o:出場
		type:       C:汽車, H:重機, M:機車
		lpr:  ABC-1234(車號)
		color:     red(紅色), 若無請用NULL(4個字)
		sq: 序號(參考用) 
        sq2:		暫不用
        etag:		eTag ID
        ant:		eTag

		http設定說明:
		method: POST
		上傳圖檔名英數字, 副檔名為gif/jpg/png均可
		上傳圖檔欄位名稱為cars
    */
    public function ipcam()
	{          
		// 執行	
    	$parms = $this->uri->uri_to_assoc(3);
		$parms['lpr'] = urldecode($parms['lpr']); // 中文車牌
		
		// 同步並送出一次出入口 888
		$this->data_model()->sync_888($parms);
                                                                  
        $pic_folder = CAR_PIC.$this->vars['date_num'].'/';		// 今日資料夾名(yyyymmdd)
        if (!file_exists($pic_folder))	mkdir($pic_folder);		// 如果資料夾不存在, 建立日期資料夾
        
        $config['upload_path'] = $pic_folder;
        // $config['allowed_types'] = 'gif|jpg|png';                 
        $config['allowed_types'] = '*';                 
        // ex. lpr_1625AB_I_1_152_C_1_2015080526.jpg -> car_交易序號_進出_順序_車號_時間.jpg
        $config['file_name'] = "lpr-{$parms['lpr']}-{$parms['io']}-{$parms['ivsno']}-{$parms['sq']}-{$parms['type']}-{$parms['sq2']}-{$this->vars['time_num']}.jpg"; 
		
		if (!isset($_FILES['cars'])) 
		{
			$status = 'error';		// 顯示上傳錯誤
			trigger_error('[ERROR] cars not found: ' . print_r($_FILES, true));
		}
		else
		{
			$this->load->library('upload', $config);
        
			if(!$this->upload->do_upload('cars')){         
				$status = 'error';		// 顯示上傳錯誤
				trigger_error($this->upload->display_errors());
			} 
			else
			{
				// 若無錯誤，則上傳檔案
				$file = $this->upload->data('cars');
				$status = 'ok';
			}
		}
        
        $parms['obj_type'] = 1;	// 車牌類  
        $parms['curr_time_str'] = $this->vars['date_time'];	// 現在時間, 例2015-09-21 15:36:47  
        $parms['pic_name'] = $config['file_name'];	// 圖片檔名 
        
        $return_msg = $this->app_model()->lprio($parms);	// 測試eTag
		trigger_error(__FUNCTION__ . "|{$parms['sno']}|{$parms['io']}|{$parms['lpr']}|return_msg|" . $return_msg);
	}  

	/*
		出入口
		
		說明: 特殊方式進出註記 (ex. 悠遊卡)
    */
	public function ipcam_meta()
	{                             
		$parms = $this->uri->uri_to_assoc(3);
		$parms['lpr'] = urldecode($parms['lpr']); // 中文車牌
		
		// 執行	
		$this->app_model()->ipcam_meta($parms);
	}
    
    // 用車牌與eTag, 檢查資料庫
    public function check_lpr_etag()
	{                                  
    	$lpr = $this->uri->segment(3);
    	$etag = $this->uri->segment(4);  
        
		// 執行	
        $this->app_model()->check_lpr_etag($lpr, $etag);	
        exit;
    }     
	
	// 開門 (臨停)
    public function temp_opendoors()
	{                                  
		$parms['ivsno'] = $this->uri->segment(3);
    	$parms['lpr'] = $this->uri->segment(4);  
		$parms['ck'] = $this->uri->segment(5);  
	
		// 初始 mqtt
		$this->init_mqtt(true);
		
		// 執行	
		$result = $this->app_model()->do_temp_opendoor($parms);
		trigger_error(__FUNCTION__ . "..{$result}.." . print_r($parms, true));
		exit;
	}
	
	// 開門 (臨停)
    public function member_opendoors()
	{                                  
    	$parms['ivsno'] = $this->uri->segment(3);
    	$parms['lpr'] = $this->uri->segment(4);  
		$parms['ck'] = $this->uri->segment(5);  
		
		// 初始 mqtt
		$this->init_mqtt(true);
		
		// 執行	
		$result = $this->app_model()->do_member_opendoor($parms);
		trigger_error(__FUNCTION__ . "..{$result}.." . print_r($parms, true));
		exit;
	}
	
}
