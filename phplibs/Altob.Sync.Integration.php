<?php

/**
 * Altob 資料同步
 *
 * @version 1.0
 * @author mip
 */
class AltobSyncAgent
{
	const SYNC_PKS_URL = 'http://61.219.172.12/parkings/cars.html/';			// 在席記錄
	const SYNC_CARS_URL = 'http://61.219.172.12/parkings/cars.html/';			// 進出記錄
	const SYNC_ST_URL = 'http://61.219.172.11:60123/admins_station.html/';		// 場站記錄
	const API_URL = 'http://parks.altob.com.tw:60123/parkingquery.html/';		// 場站 API
	const ALLPA_URL = 'http://61.219.172.11:60123/allpa_service.html/';			// 歐pa卡
	
	public $io = '';
	public $etag = '';
	public $pic_name = '';
	public $ivsno = 0;
	public $member_no = 0;
	public $finished = 0;
	public $in_time = '';
	public $cario_no = 0;

	private $post_parms;

	/**
	 * 初始化
	 */
	public function init($station_no, $time='')
	{
		$this->post_parms = array();
		$this->post_parms['station_no'] = $station_no;
		$this->post_parms['io_time'] = $time;
	}

	// 檢查基本欄位
	function check_init_parms()
	{
		if(empty($this->post_parms['station_no']))
			return 'station_no not set';		// 場站編號

		if(empty($this->post_parms['io_time']))
			return 'io_time not set';			// 進出時間 (若為繳費帶進場時間)

		return false;
	}

	// ===============================================
	// st_io
	// ===============================================

	// 傳送進場記錄
	public function sync_st_in($parms)
	{
		return 'pause';
		
		$error_parms_msg = $this->check_init_parms();
		if(!empty($error_parms_msg)) { return $error_parms_msg; }

		if(empty($this->cario_no))
			return 'cario_no not found';

		if(empty($parms['lpr']))
			return 'lpr not found';

		if(empty($parms['io']))
			return 'io not found';

		$this->post_parms['cario_no'] = $this->cario_no;	// 需設定
		$this->post_parms['member_no'] = $this->member_no;	// 預設為 0

		$this->post_parms['lpr'] = $parms['lpr'];
		$this->post_parms['io'] = $parms['io'];
		$this->post_parms['etag'] = $parms['etag'] == 'NONE' ? '' : $parms['etag'];
		$this->post_parms['pic_name'] = empty($parms['pic_name']) ? '' : $parms['pic_name'];
		$this->post_parms['ivsno'] = $parms['ivsno'];
		$this->post_parms['out_before_time'] = $this->post_parms['io_time'];

		// 初始化網路服務物件。
		$oService = new AltobSyncService();
		$oService->ServiceURL = AltobSyncAgent::SYNC_CARS_URL;
		$oService->ServiceCMD = 'st_in';

		// 傳遞參數至遠端。
		return $oService->ServerPost($this->post_parms);
	}

	// 傳送離場記錄
	public function sync_st_out($parms)
	{
		return 'pause';
		
		$error_parms_msg = $this->check_init_parms();
		if(!empty($error_parms_msg)) { return $error_parms_msg; }

		// 有入場記錄時
		if($this->cario_no > 0)
		{
			$this->post_parms['cario_no'] = $this->cario_no;

			if(empty($this->in_time))
				return 'in_time not found';					// 需設定

			$this->post_parms['in_time'] = $this->in_time;
			$this->post_parms['minutes'] = floor((strtotime($this->post_parms['io_time']) - strtotime($this->in_time)) / 60); // 停車時數 (分鐘)
		}

		if(empty($parms['lpr']))
			return 'lpr not found';

		if(empty($parms['io']))
			return 'io not found';

		$this->post_parms['member_no'] = $this->member_no;	// 預設為 0
		$this->post_parms['finished'] = $this->finished;	// 預設為 0

		$this->post_parms['lpr'] = $parms['lpr'];
		$this->post_parms['io'] = $parms['io'];
		$this->post_parms['etag'] = $parms['etag'] == 'NONE' ? '' : $parms['etag'];
		$this->post_parms['pic_name'] = empty($parms['pic_name']) ? '' : $parms['pic_name'];
		$this->post_parms['ivsno'] = $parms['ivsno'];

		// 初始化網路服務物件。
		$oService = new AltobSyncService();
		$oService->ServiceURL = AltobSyncAgent::SYNC_CARS_URL;
		$oService->ServiceCMD = 'st_out';

		// 傳遞參數至遠端。
		return $oService->ServerPost($this->post_parms);
	}

	// 傳送付費更新記錄
	public function sync_st_pay($lpr, $pay_time, $pay_type=0, $out_before_time='', $finished=false)
	{
		return 'pause';
		
		$error_parms_msg = $this->check_init_parms();
		if(!empty($error_parms_msg)) { return $error_parms_msg; }

		if(empty($this->cario_no))
			return 'cario_no not found';

		if(empty($lpr))
			return 'lpr not found';

		if(empty($pay_time))
			return 'pay_time not found';

		$this->post_parms['cario_no'] = $this->cario_no;	// 需設定

		$this->post_parms['lpr'] = $lpr;
		$this->post_parms['pay_time'] = $pay_time;
		$this->post_parms['pay_type'] = $pay_type;
		$this->post_parms['out_before_time'] = empty($out_before_time) ? date('Y-m-d H:i:s', strtotime("{$pay_time} + 15 minutes")) : $out_before_time;
		
		// 是否註記完結
		if($finished)
			$this->post_parms['finished'] = 1;
		
		// 初始化網路服務物件。
		$oService = new AltobSyncService();
		$oService->ServiceURL = AltobSyncAgent::SYNC_CARS_URL;
		$oService->ServiceCMD = 'st_pay';

		// 傳遞參數至遠端。
		return $oService->ServerPost($this->post_parms);
	}
	
	// 傳送進出更新記錄
	public function sync_st_io_meta($meta)
	{
		return 'pause';
		
		$error_parms_msg = $this->check_init_parms();
		if(!empty($error_parms_msg)) { return $error_parms_msg; }
		
		if(empty($meta))
			return 'meta not found';

		if(empty($this->cario_no))
			return 'cario_no not found';
		
		$this->post_parms['meta'] = $meta;	// 需設定
		$this->post_parms['cario_no'] = $this->cario_no;	// 需設定
		
		// 初始化網路服務物件。
		$oService = new AltobSyncService();
		$oService->ServiceURL = AltobSyncAgent::SYNC_CARS_URL;
		$oService->ServiceCMD = 'st_io_meta';

		// 傳遞參數至遠端。
		return $oService->ServerPost($this->post_parms);
	}
	
	// ===============================================
	// SOS
	// ===============================================
	
	// 傳送 SOS
	public function sync_st_sos($machine_no)
	{
		$error_parms_msg = $this->check_init_parms();
		if(!empty($error_parms_msg)) { return $error_parms_msg; }

		if(empty($machine_no))
			return 'machine_no not found';

		$this->post_parms['machine_no'] = $machine_no;

		// 初始化網路服務物件。
		$oService = new AltobSyncService();
		$oService->ServiceURL = AltobSyncAgent::SYNC_CARS_URL;
		$oService->ServiceCMD = 'st_sos';

		// 傳遞參數至遠端。
		return $oService->ServerPost($this->post_parms);
	}

	// ===============================================
	// members
	// ===============================================

	// 取得場站會員
	public function get_st_members()
	{
		if(empty($this->post_parms['station_no']))
			return 'station_no not set';		// 場站編號

		// 初始化網路服務物件。
		$oService = new AltobSyncService();
		$oService->ServiceURL = AltobSyncAgent::SYNC_ST_URL;
		$oService->ServiceCMD = 'member_query_all';

		// 傳遞參數至遠端。
		return $oService->ServerPost($this->post_parms);
	}
	
	// 同步會員鎖車 (0: 解鎖, 1: 上鎖, 2: 查詢)
	public function sync_security_action($lpr, $pswd, $action)
	{
		if(empty($this->post_parms['station_no']))
			return 'station_no not set';		// 場站編號

		$station_no = $this->post_parms['station_no'];
		$ck = md5($lpr.'i'.$pswd.'iii'.$action);
		
		// 初始化網路服務物件。
		$oService = new AltobSyncService();
		$oService->ServiceURL = AltobSyncAgent::API_URL;
		$oService->ServiceCMD = __FUNCTION__ . "/{$lpr}/{$pswd}/{$action}/{$ck}/{$station_no}";

		// 傳遞參數至遠端。
		return $oService->ServerPost($this->post_parms);
	}
	
	// 同步會員改密碼
	public function sync_change_pwd($lpr, $pswd, $new_pwd)
	{
		if(empty($this->post_parms['station_no']))
			return 'station_no not set';		// 場站編號

		$station_no = $this->post_parms['station_no'];
		$ck = md5($lpr.'i'.$pswd.'iii'.$new_pwd);
		
		// 初始化網路服務物件。
		$oService = new AltobSyncService();
		$oService->ServiceURL = AltobSyncAgent::API_URL;
		$oService->ServiceCMD = __FUNCTION__ . "/{$lpr}/{$pswd}/{$new_pwd}/{$ck}/{$station_no}";

		// 傳遞參數至遠端。
		return $oService->ServerPost($this->post_parms);
	}

	// ===============================================
	// pks
	// ===============================================

	// 在席
	public function upd_pks_groups($data)
	{
		if(empty($this->post_parms['station_no']))
			return 'station_no not set';		// 場站編號

		$this->post_parms['data'] = $data;

		// 初始化網路服務物件。
		$oService = new AltobSyncService();
		$oService->ServiceURL = AltobSyncAgent::SYNC_PKS_URL;
		$oService->ServiceCMD = __FUNCTION__;

		// 傳遞參數至遠端。
		return $oService->ServerPost($this->post_parms);
	}
	
	// ===============================================
	// 歐Pa卡
	// ===============================================
	
	// 歐Pa卡 - 開門
	public function allpa_go($lpr)
	{
		$error_parms_msg = $this->check_init_parms();
			if(!empty($error_parms_msg)) { return $error_parms_msg; }
		
		if(empty($this->in_time))
			return 'in_time not found';			// 需設定
		
		if(empty($lpr))
			return 'lpr not found';				// 需設定
		
		$this->post_parms['in_time'] = $this->in_time;
		$this->post_parms['lpr'] = $lpr;
		$this->post_parms['ck'] = md5(
				$this->post_parms['lpr']. 'a' . date('dmh') . 'lt' . 
				$this->post_parms['in_time'] . 'o' .							// timestamp
				$this->post_parms['station_no'] . 'b' . __FUNCTION__ . 
				$this->post_parms['io_time']									// timestamp
			);
		
		// 初始化網路服務物件。
		$oService = new AltobSyncService();
		$oService->ServiceURL = AltobSyncAgent::ALLPA_URL;
		$oService->ServiceCMD = __FUNCTION__;

		// 傳遞參數至遠端。
		return $oService->ServerPost($this->post_parms);
	}
	
	// 歐Pa卡 - 取得場站會員
	public function query_allpa_users()
	{
		$this->post_parms['ck'] = md5('a' . date('dmh') . 'lt' . __FUNCTION__ . 'ob');
		
		// 初始化網路服務物件。
		$oService = new AltobSyncService();
		$oService->ServiceURL = AltobSyncAgent::ALLPA_URL;
		$oService->ServiceCMD = __FUNCTION__;

		// 傳遞參數至遠端。
		return $oService->ServerPost($this->post_parms);
	}

}

/**
 * 呼叫網路服務的類別。
 */
class AltobSyncService {

    /**
     * 網路服務類別呼叫的位址。
     */
    public $ServiceURL = 'ServiceURL';
	public $ServiceCMD = 'ServiceCMD';

    /**
     * 網路服務類別的建構式。
     */
    function __construct() {$this->AltobSyncService();}

    /**
     * 網路服務類別的實體。
     */
    function AltobSyncService() {}

    /**
     * 提供伺服器端呼叫遠端伺服器 Web API 的方法。
     */
    function ServerPost($parameters) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->ServiceURL . $this->ServiceCMD);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,5);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5); //timeout in seconds
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));
        $rs = curl_exec($ch);
        curl_close($ch);

        return $rs;
    }

}
