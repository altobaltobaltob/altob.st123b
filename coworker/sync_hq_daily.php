<?php
/*
file: call_sync_daily.php       每日自動呼叫
*/           
                                                    
// 場站共用設定檔
require_once '/home/bigbang/apps/coworker/station.config.php'; 

define('APP_NAME', 'sync_hq_daily');    // application name

// 發生錯誤時集中在此處理
function error_handler($errno, $errstr, $errfile, $errline, $errcontext)
{         
	$str = date('H:i:s')."|{$errstr}|{$errfile}|{$errline}|{$errno}\n";               
	error_log($str, 3, LOG_PATH.APP_NAME . '.' . date('Ymd').'.log.txt');   // 3代表參考後面的檔名
}

set_error_handler('error_handler', E_ALL);

trigger_error('..start..');

try
{
	$ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://localhost/admins_station.html/do_sync_batch_100");
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_POST, FALSE);
    $output = curl_exec($ch);
    curl_close($ch);
}
catch(Exception $e)
{
	trigger_error('ERROR: ' . $e->getMessage());
}

trigger_error('..completed..');