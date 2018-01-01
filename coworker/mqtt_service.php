<?php 
// file: mqtt_service.php	接收 MQTT 轉發
require_once('/home/bigbang/libs/phplibs/phpMQTT.php');  
require_once '/home/bigbang/apps/coworker/station.config.php'; 

define('APP_NAME', 'mqtt_service');    // application name

// 發生錯誤時集中在此處理
function error_handler($errno, $errstr, $errfile, $errline, $errcontext)
{   
	//$str = date('H:i:s')."|{$errstr}|{$errfile}|{$errline}|{$errno}\n";               
	$str = date('H:i:s')."|{$errstr}\n";               
	
	echo $str;
	error_log($str, 3, LOG_PATH.APP_NAME . '.' . date('Ymd').'.log.txt');   // 3代表參考後面的檔名
}

set_error_handler('error_handler', E_ALL);

trigger_error('..start..');
           
// 共用記憶體 
$mcache = new Memcache;
$mcache->pconnect('localhost', 11211) or die ('Could not connect memcache'); 

// 取得 memcache settings
$retry_count = 0;
while(!$mcache->get('altob_station_settings'))
{
	trigger_error("altob_station_settings..not_found..{$retry_count}");
	
	if($retry_count > 5)
		die ('Could not init altob_station_settings');
	$retry_count++;
	
	// call & retry
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POST, true); // 啟用POST
	curl_setopt($ch, CURLOPT_URL, 'http://localhost/carpark.html/station_setting_query/'); 
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('reload' => 1)));   
	$result = curl_exec($ch);      
	trigger_error("..retry..curl:{$result}..");
	
	sleep(5);
}
$settings = $mcache->get('altob_station_settings');
trigger_error("memcache['altob_station_settings'] = " . print_r($settings, true));

// 取得第一個場站編號
$station_no_str = $mcache->get('station_no_str');
$station_no_arr = explode(',', $station_no_str);
$first_station_no = $station_no_arr[0];
trigger_error("station_no: {$first_station_no}");

// 取得 mqtt 設定
$mqtt_ip = isset($settings[$first_station_no]['mqtt_ip']) ? $settings[$first_station_no]['mqtt_ip']:'localhost';
$mqtt_port = isset($settings[$first_station_no]['mqtt_port']) ? $settings[$first_station_no]['mqtt_port']:1883;
trigger_error("mqtt: {$mqtt_ip}:{$mqtt_port}");
								   
// mqtt subscribe
$mqtt = new phpMQTT($mqtt_ip, $mqtt_port, uniqid());  
if(!$mqtt->connect()){ die ('Could not connect mqtt');  }

// 場站資料庫資訊
$topics['#'] = array('qos'=> 0, 'function'=>'procmsg');
$mqtt->subscribe($topics, 0);
trigger_error("..mqtt subscribe..".print_r($topics, true));

while($mqtt->proc()){ }
$mqtt->close();

function procmsg($topic, $msg)
{     
	trigger_error("..{$topic}|{$msg}..");

	$data = array('topic' => $topic, 'msg' => $msg, 'ck' => md5($topic.'altob'.$msg));
	
	if(in_array($topic, array('altob.888.mqtt')))
	{
		// dispatch
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true); // 啟用POST
		curl_setopt($ch, CURLOPT_URL, 'http://localhost/carpark.html/mqtt_service/'); 
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));   
		$result = curl_exec($ch);   
		trigger_error("..curl|{$result}..");		
	}
}
