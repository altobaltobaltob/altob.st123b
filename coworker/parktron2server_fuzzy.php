<?php
// 博辰設備對接        
// php //home/bigbang/apps/coworker/parktron2server.php

require_once '/home/bigbang/libs/Workerman/Autoloader.php';

use Workerman\Worker;  
Worker::$logFile = '/dev/null';		// 不記錄log file 
//Worker::$pidFile = '/tmp/run/'.basename(__FILE__).'.pid';

// 傳送主機資料
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true); // 啟用POST

// 建立一個Worker監聽8068埠，不使用任何應用層協定
$tcp_worker = new Worker("tcp://0.0.0.0:8068");      

// 啟動N個進程對外提供服務
$tcp_worker->count = 6;

$tcp_worker->onConnect = function($connection)
{
    echo "New Connection\n";
};

$tcp_worker->onClose = function($connection)
{
    echo "Connection closed\n";
};

// 當用戶端發來數據(主程式)
$tcp_worker->onMessage = function($connection, $tcp_in)
{                       
	global $ch, $last_lpr;
    
    // echo  'start time:'.date('Y-m-d H:i:s');
    
	list(, $seq, $cmd, $data) = explode(chr(28), $tcp_in);		// 0x1C tcp欄位分隔 
    // echo "data_in:[{$seq}|{$cmd}|{$data}|]\n";
    
    switch($cmd)
    {
      	case '001':		// 車輛入場
			list($devno, $token, $lpr, $in_time, $last_field) = explode(chr(31), $data);		// 0x1F data欄位分隔
    		$type = substr($last_field, 0, -2); 
    		echo "{$devno}|{$token}|{$lpr}|{$in_time}|{$type}|\n"; 
            $connection->send('OK');
            break;
        
        case '002':		// APS詢問車牌入場時間 
			list($token, $lpr, $last_field) = explode(chr(31), $data);		// 0x1F data欄位分隔 
            $lpr = str_replace('%', '', $lpr);   
            $last_lpr = $lpr;
    		$in_time = substr($last_field, 0, -2); 
    		// echo "cmd_002:[{$token}|{$lpr}|{$in_time}|]/n"; 
                                                        
            $data = array('lpr' => $lpr);
			curl_setopt($ch, CURLOPT_URL, 'http://localhost/carpayment.html/query_in_fuzzy/'); 
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));   
            $jdata = curl_exec($ch);      
			$results = json_decode($jdata, true);
            
			$connection->send(tcp_data_fuzzy($results['count'], $results['results'], '001', '002'));
        	break;
        	  
        case '003':		// 繳費完成 
			list($ticket_no, $lpr, $in_time, $pay_time, $last_field) = explode(chr(31), $data);		// 0x1F data欄位分隔
    		$pay_type = substr($last_field, 0, -2); 
    		// echo "{$ticket_no}|{$lpr}|{$in_time}|{$pay_time}|{$pay_type}|/n"; 
            $connection->send('OK'); 
            
            if ($lpr == '*******') {$err_lpr = '***';}
            else
            { $err_lpr = '+++';}
            
		    // 傳送繳費資料 
            $data = array
            		(
            			'ticket_no' => $ticket_no,	// 票卡號碼
                  		'lpr' => $lpr,				// 車號
                        'in_time' => $in_time,      // 入場時間
                        'pay_time' => $pay_time,	// 繳款時間
                        'pay_type' => $pay_type		// 繳款方式(0:現金, 1:月票, 2:多卡通)
                    );
                    
			curl_setopt($ch, CURLOPT_URL, 'http://localhost/carpayment.html/p2payed/'); 
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); 
			$results = curl_exec($ch);     
            
    		file_put_contents('/tmp/aps.log.txt', date('Y-m-d H:i:s').":{$err_lpr}\n".print_r($data, true)."\n\n", FILE_APPEND);
        
        	break;
    }   
    
    // echo 'end_time:'.date('Y-m-d H:i:s');
};       

function tcp_data_fuzzy($records_count, $records, $seq, $cmdid)
{                                   
	$STX	= 0x02;		// STX：封包起始碼(0x02)
	$ETX	= 0x03;		// ETX：封包結束碼(0x03)
	$CRC	= 0x80;		// CRC：封包檢查碼
	$S1		= 0x1c;		// 分隔碼
	$D1		= 0x1f;		// 資料每個欄位分隔碼為0x1F
    $seq = '00001';
    $cmdid = '002';     
	
	// 0 筆
	if($records_count == 0)
	{
		$count = 0;
		$data = pack('aC', "{$count}", $D1); // 20170928 為了和舊版一致尾巴都補上 0x1f
			
		$data_len = strlen($data);    
		$socket_len = $data_len + 16;
    
		$send_data = 
			pack("CCCCa5Ca3C",
				$STX,
				$socket_len / 0x0100, 
				$socket_len % 0x0100, $S1, 
				$seq, $S1, 
				$cmdid, $S1).
			
			$data.
			
			pack("CC", $CRC, $ETX);
			
		return $send_data;
	}
	
	// 1. create data
	$packcontent_arr = array();
	foreach ($records as $idx => $rows) 
	{
		array_push($packcontent_arr, pack('A7', $records[$idx]['lpr']));
		array_push($packcontent_arr, pack('a7', $records[$idx]['seat_no']));
		array_push($packcontent_arr, pack('a', $records[$idx]['ticket']));
		array_push($packcontent_arr, pack('a19', $records[$idx]['in_time']));
		array_push($packcontent_arr, pack('a'. strlen($records[$idx]['in_pic_name']) , $records[$idx]['in_pic_name']));
		array_push($packcontent_arr, pack('a19', $records[$idx]['pay_time']));
		array_push($packcontent_arr, pack('a10', $records[$idx]['start_date']));
		array_push($packcontent_arr, pack('a10', $records[$idx]['end_date']));
		array_push($packcontent_arr, pack('a5', $records[$idx]['start_time']));
		array_push($packcontent_arr, pack('a5', $records[$idx]['end_time']));
		array_push($packcontent_arr, pack('a1', $records[$idx]['area_code']));
	}
	
	// gen packcontent
	$packcontent = implode(pack('C', $D1), $packcontent_arr);
	
	// gen data
	$data = pack("aC", count($records), $D1) . $packcontent . pack("C", $D1); // 20170928 為了和舊版一致尾巴都補上 0x1f
	
	// get data length
	$data_len = strlen($data);    
	$socket_len = $data_len + 16;
	
	// gen send_data
	$send_data = 
			pack("CCCCa5Ca3C",
				$STX,
				$socket_len / 0x0100, 
				$socket_len % 0x0100, $S1, 
				$seq, $S1, 
				$cmdid, $S1). 

			$data. 
			
			pack("CC", $CRC, $ETX);
	
	return $send_data;
}

// 執行worker
Worker::runAll();
