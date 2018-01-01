<?php
/*
file: pks.php 	車位在席模組    

IVS -> 車號, 影像 
鼎高IVS傳送車號及影像檔   
http://203.75.167.89/pks.html/cameras/sno/12112/ivsno/3/pksno/2016/io/KI/type/C/lpr/ABC1234/color/red/sq/5236        
http://203.75.167.89/pks.html/cameras/sno/12119/ivsno/3/pksno/195/io/KO/type/C/lpr/NONE/color/red/sq/5236
sno:	場站編號(新北市圖書館:12118)
ivsno:	ivs編號, 每一支都是獨立編號(序號)
pksno:	車位編號
io:		KI:進車格, KO:出車格, KL:車牌
type:	C:汽車, H:重機, M:機車
lpr:	ABC1234(車號), 無:NONE
color:	red(紅色), 若無請用NONE(4個字)
sq:		序號(查詢時參考用)

http設定說明:
method: POST
上傳圖檔名英數字, 副檔名為gif/jpg/png均可
上傳圖檔欄位名稱為cars
*/
class Pks extends CC_Controller
{          
	function __construct() 
	{                            
		parent::__construct('pks');          
        
		ignore_user_abort();	// 接受client斷線, 繼續run
               
        if ($this->router->fetch_method() == 'cameras')
        {
        	ob_end_clean();
			ignore_user_abort();
			ob_start();
			header('Connection: close');
			header('Content-Length: ' . ob_get_length());
			ob_end_flush();
			flush();
        }
	}
    
	public function parked()
	{                               
		$data['group_id'] = $this->uri->segment(3);  
		$data['init_value'] = $this->uri->segment(4);  
        // $data['client_id'] = uniqid();
        // $data['mqtt_ip'] = '192.168.10.201';
        // $data['port_no'] = 8000;
        $this->load->view(APP_NAME.'/parked', $data);
	}
	
	// 樓層平面圖
    // http://203.75.167.89/parkingquery.html/floor_map
	public function floor_map()
	{    
    	/*
    	header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
		header('Access-Control-Allow-Headers: X-Requested-With, Content-Type, Accept');
        */
		$this->load->view("parkingquery/floor_map");
	}        
    
    // IVS -> 車號, 影像 
    /*
	IVS -> 車號, 影像 
	鼎高IVS傳送車號及影像檔   
	http://203.75.167.89/pks.html/cameras/sno/12119/ivsno/3/pksno/102/io/KI/type/C/lpr/ABC1234/color/red/sq/5236
	sno:	場站編號(新北市圖書館:12118)
	ivsno:	ivs編號, 每一支都是獨立編號(序號)
	pksno:	車位編號
	io:		KI:進車格, KO:出車格, KL:車牌辨識
	type:	C:汽車, H:重機, M:機車
	lpr:	ABC1234(車號)
	color:	red(紅色), 若無請用NONE(4個字)
	sq:		序號(查詢時參考用)

	http設定說明:
	method: POST
	上傳圖檔名英數字, 副檔名為gif/jpg/png均可
	上傳圖檔欄位名稱為cars
    */
    public function cameras()
	{                             
    	$parms = $this->uri->uri_to_assoc(3);
		
		// 調整 pksno 為 pks 格式
		if (strpos($parms['pksno'], 'B') !== false)
			$parms['pksno'] = '9' . intval(preg_replace('/[^0-9\-]/', '', $parms['pksno']));	// 地下 B
		else
			$parms['pksno'] = intval(preg_replace('/[^0-9\-]/', '', $parms['pksno']));
		
        trigger_error('在席參數傳入:'.print_r($parms, true));  
        
		// 初始 mqtt
		$this->init_mqtt();
		
		// 執行
        $this->app_model()->pksio($parms);	// 車輛進出車格資料庫處理 
        exit;          
	}
         
    // 重新計算
    // http://203.75.167.89/pks.html/reculc/
    public function reculc()
	{ 
    	$this->app_model()->reculc();  
    }
	
	// 取得所有車位狀態資訊
    // http://203.75.167.89/pks.html/query_station_status/12112
	public function query_station_status() 
	{   
		$station_no = $this->uri->segment(3);      
        $data = $this->app_model()->query_station_status($station_no);
        echo json_encode($data, JSON_UNESCAPED_UNICODE); 
    }
	
	// 取得車位資訊
    // http://203.75.167.89/pks.html/query_station_pks/12112/2021
	public function query_station_pks(){
		$station_no = $this->uri->segment(3);      
		$pksno = $this->uri->segment(4);      
		$data = $this->app_model()->query_station_pks($station_no, $pksno);
		echo json_encode($data, JSON_UNESCAPED_UNICODE); 
	}
	
	// 車位狀態資訊圖
    // http://203.75.167.89/pks.html/status_map
	public function status_map()
	{    
		$this->show_page("status_map");
	}  
	
}
