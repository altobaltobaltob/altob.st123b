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
            
            //if ($lpr == '*******') {$lpr = $last_lpr; $err_lpr = '***';}
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
    $seq = '00001';
    $cmdid = '002';     
	$packformat = 'aC';
	
	// 0 筆
	if($records_count == 0)
	{
		$count = 0;
		$data = pack($packformat, 
			"{$count}", 0x1f
			); 
			
		$data_len = strlen($data);    
		$socket_len = $data_len + 16;
    
		$send_data = pack("CCCCa5Ca3C{$packformat}CC",
			0x02,
			$socket_len / 0x0100, $socket_len % 0x0100, 0x1c, $seq, 0x1c, $cmdid, 0x1c,  
			"{$count}", 0x1f,
			0x80, 0x03);
		return $send_data;
	}
	
	// 1. create data
	foreach ($records as $idx => $rows) 
	{
		$pathlen = strlen($rows['in_pic_name']);
		$packformat = $packformat."A7Ca7CaCa19Ca{$pathlen}Ca19Ca10Ca10Ca5Ca5C";
	}
	
	if(count($records) == 1)
	{
		$count = 1;
		$data = pack($packformat, 
			"{$count}", 0x1f, 
			$records[0]['lpr'], 0x1f, $records[0]['seat_no'], 0x1f, $records[0]['ticket'], 0x1f, $records[0]['in_time'], 0x1f, $records[0]['in_pic_name'], 0x1f, $records[0]['pay_time'], 0x1f, $records[0]['start_date'], 0x1f, $records[0]['end_date'], 0x1f, $records[0]['start_time'], 0x1f, $records[0]['end_time'], 0x1f
			); 
			
		$data_len = strlen($data);    
		$socket_len = $data_len + 16;
    
		$send_data = pack("CCCCa5Ca3C{$packformat}CC",
			0x02,
			$socket_len / 0x0100, $socket_len % 0x0100, 0x1c, $seq, 0x1c, $cmdid, 0x1c,  
			"{$count}", 0x1f, 
			$records[0]['lpr'], 0x1f, $records[0]['seat_no'], 0x1f, $records[0]['ticket'], 0x1f, $records[0]['in_time'], 0x1f, $records[0]['in_pic_name'], 0x1f, $records[0]['pay_time'], 0x1f, $records[0]['start_date'], 0x1f, $records[0]['end_date'], 0x1f, $records[0]['start_time'], 0x1f, $records[0]['end_time'], 0x1f, 
			0x80, 0x03);
	}
	else if(count($records) == 2)
	{
		$count = 2;
		$data = pack($packformat, 
			"{$count}", 0x1f, 
			$records[0]['lpr'], 0x1f, $records[0]['seat_no'], 0x1f, $records[0]['ticket'], 0x1f, $records[0]['in_time'], 0x1f, $records[0]['in_pic_name'], 0x1f, $records[0]['pay_time'], 0x1f, $records[0]['start_date'], 0x1f, $records[0]['end_date'], 0x1f, $records[0]['start_time'], 0x1f, $records[0]['end_time'], 0x1f,
			$records[1]['lpr'], 0x1f, $records[1]['seat_no'], 0x1f, $records[1]['ticket'], 0x1f, $records[1]['in_time'], 0x1f, $records[1]['in_pic_name'], 0x1f, $records[1]['pay_time'], 0x1f, $records[1]['start_date'], 0x1f, $records[1]['end_date'], 0x1f, $records[1]['start_time'], 0x1f, $records[1]['end_time'], 0x1f
			); 
			
		$data_len = strlen($data);    
		$socket_len = $data_len + 16;
    
		$send_data = pack("CCCCa5Ca3C{$packformat}CC",
			0x02,
			$socket_len / 0x0100, $socket_len % 0x0100, 0x1c, $seq, 0x1c, $cmdid, 0x1c,  
			"{$count}", 0x1f, 
			$records[0]['lpr'], 0x1f, $records[0]['seat_no'], 0x1f, $records[0]['ticket'], 0x1f, $records[0]['in_time'], 0x1f, $records[0]['in_pic_name'], 0x1f, $records[0]['pay_time'], 0x1f, $records[0]['start_date'], 0x1f, $records[0]['end_date'], 0x1f, $records[0]['start_time'], 0x1f, $records[0]['end_time'], 0x1f, 
			$records[1]['lpr'], 0x1f, $records[1]['seat_no'], 0x1f, $records[1]['ticket'], 0x1f, $records[1]['in_time'], 0x1f, $records[1]['in_pic_name'], 0x1f, $records[1]['pay_time'], 0x1f, $records[1]['start_date'], 0x1f, $records[1]['end_date'], 0x1f, $records[1]['start_time'], 0x1f, $records[1]['end_time'], 0x1f,
			0x80, 0x03);
	}
	else if(count($records) == 3)
	{
		$count = 3;
		$data = pack($packformat, 
			"{$count}", 0x1f, 
			$records[0]['lpr'], 0x1f, $records[0]['seat_no'], 0x1f, $records[0]['ticket'], 0x1f, $records[0]['in_time'], 0x1f, $records[0]['in_pic_name'], 0x1f, $records[0]['pay_time'], 0x1f, $records[0]['start_date'], 0x1f, $records[0]['end_date'], 0x1f, $records[0]['start_time'], 0x1f, $records[0]['end_time'], 0x1f,
			$records[1]['lpr'], 0x1f, $records[1]['seat_no'], 0x1f, $records[1]['ticket'], 0x1f, $records[1]['in_time'], 0x1f, $records[1]['in_pic_name'], 0x1f, $records[1]['pay_time'], 0x1f, $records[1]['start_date'], 0x1f, $records[1]['end_date'], 0x1f, $records[1]['start_time'], 0x1f, $records[1]['end_time'], 0x1f,
			$records[2]['lpr'], 0x1f, $records[2]['seat_no'], 0x1f, $records[2]['ticket'], 0x1f, $records[2]['in_time'], 0x1f, $records[2]['in_pic_name'], 0x1f, $records[2]['pay_time'], 0x1f, $records[2]['start_date'], 0x1f, $records[2]['end_date'], 0x1f, $records[2]['start_time'], 0x1f, $records[2]['end_time'], 0x1f
			); 
			
		$data_len = strlen($data);    
		$socket_len = $data_len + 16;
    
		$send_data = pack("CCCCa5Ca3C{$packformat}CC",
			0x02,
			$socket_len / 0x0100, $socket_len % 0x0100, 0x1c, $seq, 0x1c, $cmdid, 0x1c,  
			"{$count}", 0x1f, 
			$records[0]['lpr'], 0x1f, $records[0]['seat_no'], 0x1f, $records[0]['ticket'], 0x1f, $records[0]['in_time'], 0x1f, $records[0]['in_pic_name'], 0x1f, $records[0]['pay_time'], 0x1f, $records[0]['start_date'], 0x1f, $records[0]['end_date'], 0x1f, $records[0]['start_time'], 0x1f, $records[0]['end_time'], 0x1f, 
			$records[1]['lpr'], 0x1f, $records[1]['seat_no'], 0x1f, $records[1]['ticket'], 0x1f, $records[1]['in_time'], 0x1f, $records[1]['in_pic_name'], 0x1f, $records[1]['pay_time'], 0x1f, $records[1]['start_date'], 0x1f, $records[1]['end_date'], 0x1f, $records[1]['start_time'], 0x1f, $records[1]['end_time'], 0x1f,
			$records[2]['lpr'], 0x1f, $records[2]['seat_no'], 0x1f, $records[2]['ticket'], 0x1f, $records[2]['in_time'], 0x1f, $records[2]['in_pic_name'], 0x1f, $records[2]['pay_time'], 0x1f, $records[2]['start_date'], 0x1f, $records[2]['end_date'], 0x1f, $records[2]['start_time'], 0x1f, $records[2]['end_time'], 0x1f,
			0x80, 0x03);
	}
	else if(count($records) == 4)
	{
		$count = 4;
		$data = pack($packformat, 
			"{$count}", 0x1f, 
			$records[0]['lpr'], 0x1f, $records[0]['seat_no'], 0x1f, $records[0]['ticket'], 0x1f, $records[0]['in_time'], 0x1f, $records[0]['in_pic_name'], 0x1f, $records[0]['pay_time'], 0x1f, $records[0]['start_date'], 0x1f, $records[0]['end_date'], 0x1f, $records[0]['start_time'], 0x1f, $records[0]['end_time'], 0x1f,
			$records[1]['lpr'], 0x1f, $records[1]['seat_no'], 0x1f, $records[1]['ticket'], 0x1f, $records[1]['in_time'], 0x1f, $records[1]['in_pic_name'], 0x1f, $records[1]['pay_time'], 0x1f, $records[1]['start_date'], 0x1f, $records[1]['end_date'], 0x1f, $records[1]['start_time'], 0x1f, $records[1]['end_time'], 0x1f,
			$records[2]['lpr'], 0x1f, $records[2]['seat_no'], 0x1f, $records[2]['ticket'], 0x1f, $records[2]['in_time'], 0x1f, $records[2]['in_pic_name'], 0x1f, $records[2]['pay_time'], 0x1f, $records[2]['start_date'], 0x1f, $records[2]['end_date'], 0x1f, $records[2]['start_time'], 0x1f, $records[2]['end_time'], 0x1f,
			$records[3]['lpr'], 0x1f, $records[3]['seat_no'], 0x1f, $records[3]['ticket'], 0x1f, $records[3]['in_time'], 0x1f, $records[3]['in_pic_name'], 0x1f, $records[3]['pay_time'], 0x1f, $records[3]['start_date'], 0x1f, $records[3]['end_date'], 0x1f, $records[3]['start_time'], 0x1f, $records[3]['end_time'], 0x1f
			); 
			
		$data_len = strlen($data);    
		$socket_len = $data_len + 16;
    
		$send_data = pack("CCCCa5Ca3C{$packformat}CC",
			0x02,
			$socket_len / 0x0100, $socket_len % 0x0100, 0x1c, $seq, 0x1c, $cmdid, 0x1c,  
			"{$count}", 0x1f, 
			$records[0]['lpr'], 0x1f, $records[0]['seat_no'], 0x1f, $records[0]['ticket'], 0x1f, $records[0]['in_time'], 0x1f, $records[0]['in_pic_name'], 0x1f, $records[0]['pay_time'], 0x1f, $records[0]['start_date'], 0x1f, $records[0]['end_date'], 0x1f, $records[0]['start_time'], 0x1f, $records[0]['end_time'], 0x1f, 
			$records[1]['lpr'], 0x1f, $records[1]['seat_no'], 0x1f, $records[1]['ticket'], 0x1f, $records[1]['in_time'], 0x1f, $records[1]['in_pic_name'], 0x1f, $records[1]['pay_time'], 0x1f, $records[1]['start_date'], 0x1f, $records[1]['end_date'], 0x1f, $records[1]['start_time'], 0x1f, $records[1]['end_time'], 0x1f,
			$records[2]['lpr'], 0x1f, $records[2]['seat_no'], 0x1f, $records[2]['ticket'], 0x1f, $records[2]['in_time'], 0x1f, $records[2]['in_pic_name'], 0x1f, $records[2]['pay_time'], 0x1f, $records[2]['start_date'], 0x1f, $records[2]['end_date'], 0x1f, $records[2]['start_time'], 0x1f, $records[2]['end_time'], 0x1f,
			$records[3]['lpr'], 0x1f, $records[3]['seat_no'], 0x1f, $records[3]['ticket'], 0x1f, $records[3]['in_time'], 0x1f, $records[3]['in_pic_name'], 0x1f, $records[3]['pay_time'], 0x1f, $records[3]['start_date'], 0x1f, $records[3]['end_date'], 0x1f, $records[3]['start_time'], 0x1f, $records[3]['end_time'], 0x1f,
			0x80, 0x03);
	}
	else if(count($records) == 5)
	{
		$count = 5;
		$data = pack($packformat, 
			"{$count}", 0x1f, 
			$records[0]['lpr'], 0x1f, $records[0]['seat_no'], 0x1f, $records[0]['ticket'], 0x1f, $records[0]['in_time'], 0x1f, $records[0]['in_pic_name'], 0x1f, $records[0]['pay_time'], 0x1f, $records[0]['start_date'], 0x1f, $records[0]['end_date'], 0x1f, $records[0]['start_time'], 0x1f, $records[0]['end_time'], 0x1f,
			$records[1]['lpr'], 0x1f, $records[1]['seat_no'], 0x1f, $records[1]['ticket'], 0x1f, $records[1]['in_time'], 0x1f, $records[1]['in_pic_name'], 0x1f, $records[1]['pay_time'], 0x1f, $records[1]['start_date'], 0x1f, $records[1]['end_date'], 0x1f, $records[1]['start_time'], 0x1f, $records[1]['end_time'], 0x1f,
			$records[2]['lpr'], 0x1f, $records[2]['seat_no'], 0x1f, $records[2]['ticket'], 0x1f, $records[2]['in_time'], 0x1f, $records[2]['in_pic_name'], 0x1f, $records[2]['pay_time'], 0x1f, $records[2]['start_date'], 0x1f, $records[2]['end_date'], 0x1f, $records[2]['start_time'], 0x1f, $records[2]['end_time'], 0x1f,
			$records[3]['lpr'], 0x1f, $records[3]['seat_no'], 0x1f, $records[3]['ticket'], 0x1f, $records[3]['in_time'], 0x1f, $records[3]['in_pic_name'], 0x1f, $records[3]['pay_time'], 0x1f, $records[3]['start_date'], 0x1f, $records[3]['end_date'], 0x1f, $records[3]['start_time'], 0x1f, $records[3]['end_time'], 0x1f,
			$records[4]['lpr'], 0x1f, $records[4]['seat_no'], 0x1f, $records[4]['ticket'], 0x1f, $records[4]['in_time'], 0x1f, $records[4]['in_pic_name'], 0x1f, $records[4]['pay_time'], 0x1f, $records[4]['start_date'], 0x1f, $records[4]['end_date'], 0x1f, $records[4]['start_time'], 0x1f, $records[4]['end_time'], 0x1f
			); 
			
		$data_len = strlen($data);    
		$socket_len = $data_len + 16;
    
		$send_data = pack("CCCCa5Ca3C{$packformat}CC",
			0x02,
			$socket_len / 0x0100, $socket_len % 0x0100, 0x1c, $seq, 0x1c, $cmdid, 0x1c,  
			"{$count}", 0x1f, 
			$records[0]['lpr'], 0x1f, $records[0]['seat_no'], 0x1f, $records[0]['ticket'], 0x1f, $records[0]['in_time'], 0x1f, $records[0]['in_pic_name'], 0x1f, $records[0]['pay_time'], 0x1f, $records[0]['start_date'], 0x1f, $records[0]['end_date'], 0x1f, $records[0]['start_time'], 0x1f, $records[0]['end_time'], 0x1f, 
			$records[1]['lpr'], 0x1f, $records[1]['seat_no'], 0x1f, $records[1]['ticket'], 0x1f, $records[1]['in_time'], 0x1f, $records[1]['in_pic_name'], 0x1f, $records[1]['pay_time'], 0x1f, $records[1]['start_date'], 0x1f, $records[1]['end_date'], 0x1f, $records[1]['start_time'], 0x1f, $records[1]['end_time'], 0x1f,
			$records[2]['lpr'], 0x1f, $records[2]['seat_no'], 0x1f, $records[2]['ticket'], 0x1f, $records[2]['in_time'], 0x1f, $records[2]['in_pic_name'], 0x1f, $records[2]['pay_time'], 0x1f, $records[2]['start_date'], 0x1f, $records[2]['end_date'], 0x1f, $records[2]['start_time'], 0x1f, $records[2]['end_time'], 0x1f,
			$records[3]['lpr'], 0x1f, $records[3]['seat_no'], 0x1f, $records[3]['ticket'], 0x1f, $records[3]['in_time'], 0x1f, $records[3]['in_pic_name'], 0x1f, $records[3]['pay_time'], 0x1f, $records[3]['start_date'], 0x1f, $records[3]['end_date'], 0x1f, $records[3]['start_time'], 0x1f, $records[3]['end_time'], 0x1f,
			$records[4]['lpr'], 0x1f, $records[4]['seat_no'], 0x1f, $records[4]['ticket'], 0x1f, $records[4]['in_time'], 0x1f, $records[4]['in_pic_name'], 0x1f, $records[4]['pay_time'], 0x1f, $records[4]['start_date'], 0x1f, $records[4]['end_date'], 0x1f, $records[4]['start_time'], 0x1f, $records[4]['end_time'], 0x1f,
			0x80, 0x03);
	}
	else if(count($records) == 6)
	{
		$count = 6;
		$data = pack($packformat, 
			"{$count}", 0x1f, 
			$records[0]['lpr'], 0x1f, $records[0]['seat_no'], 0x1f, $records[0]['ticket'], 0x1f, $records[0]['in_time'], 0x1f, $records[0]['in_pic_name'], 0x1f, $records[0]['pay_time'], 0x1f, $records[0]['start_date'], 0x1f, $records[0]['end_date'], 0x1f, $records[0]['start_time'], 0x1f, $records[0]['end_time'], 0x1f,
			$records[1]['lpr'], 0x1f, $records[1]['seat_no'], 0x1f, $records[1]['ticket'], 0x1f, $records[1]['in_time'], 0x1f, $records[1]['in_pic_name'], 0x1f, $records[1]['pay_time'], 0x1f, $records[1]['start_date'], 0x1f, $records[1]['end_date'], 0x1f, $records[1]['start_time'], 0x1f, $records[1]['end_time'], 0x1f,
			$records[2]['lpr'], 0x1f, $records[2]['seat_no'], 0x1f, $records[2]['ticket'], 0x1f, $records[2]['in_time'], 0x1f, $records[2]['in_pic_name'], 0x1f, $records[2]['pay_time'], 0x1f, $records[2]['start_date'], 0x1f, $records[2]['end_date'], 0x1f, $records[2]['start_time'], 0x1f, $records[2]['end_time'], 0x1f,
			$records[3]['lpr'], 0x1f, $records[3]['seat_no'], 0x1f, $records[3]['ticket'], 0x1f, $records[3]['in_time'], 0x1f, $records[3]['in_pic_name'], 0x1f, $records[3]['pay_time'], 0x1f, $records[3]['start_date'], 0x1f, $records[3]['end_date'], 0x1f, $records[3]['start_time'], 0x1f, $records[3]['end_time'], 0x1f,
			$records[4]['lpr'], 0x1f, $records[4]['seat_no'], 0x1f, $records[4]['ticket'], 0x1f, $records[4]['in_time'], 0x1f, $records[4]['in_pic_name'], 0x1f, $records[4]['pay_time'], 0x1f, $records[4]['start_date'], 0x1f, $records[4]['end_date'], 0x1f, $records[4]['start_time'], 0x1f, $records[4]['end_time'], 0x1f,
			$records[5]['lpr'], 0x1f, $records[5]['seat_no'], 0x1f, $records[5]['ticket'], 0x1f, $records[5]['in_time'], 0x1f, $records[5]['in_pic_name'], 0x1f, $records[5]['pay_time'], 0x1f, $records[5]['start_date'], 0x1f, $records[5]['end_date'], 0x1f, $records[5]['start_time'], 0x1f, $records[5]['end_time'], 0x1f
			); 
			
		$data_len = strlen($data);    
		$socket_len = $data_len + 16;
    
		$send_data = pack("CCCCa5Ca3C{$packformat}CC",
			0x02,
			$socket_len / 0x0100, $socket_len % 0x0100, 0x1c, $seq, 0x1c, $cmdid, 0x1c,  
			"{$count}", 0x1f, 
			$records[0]['lpr'], 0x1f, $records[0]['seat_no'], 0x1f, $records[0]['ticket'], 0x1f, $records[0]['in_time'], 0x1f, $records[0]['in_pic_name'], 0x1f, $records[0]['pay_time'], 0x1f, $records[0]['start_date'], 0x1f, $records[0]['end_date'], 0x1f, $records[0]['start_time'], 0x1f, $records[0]['end_time'], 0x1f, 
			$records[1]['lpr'], 0x1f, $records[1]['seat_no'], 0x1f, $records[1]['ticket'], 0x1f, $records[1]['in_time'], 0x1f, $records[1]['in_pic_name'], 0x1f, $records[1]['pay_time'], 0x1f, $records[1]['start_date'], 0x1f, $records[1]['end_date'], 0x1f, $records[1]['start_time'], 0x1f, $records[1]['end_time'], 0x1f,
			$records[2]['lpr'], 0x1f, $records[2]['seat_no'], 0x1f, $records[2]['ticket'], 0x1f, $records[2]['in_time'], 0x1f, $records[2]['in_pic_name'], 0x1f, $records[2]['pay_time'], 0x1f, $records[2]['start_date'], 0x1f, $records[2]['end_date'], 0x1f, $records[2]['start_time'], 0x1f, $records[2]['end_time'], 0x1f,
			$records[3]['lpr'], 0x1f, $records[3]['seat_no'], 0x1f, $records[3]['ticket'], 0x1f, $records[3]['in_time'], 0x1f, $records[3]['in_pic_name'], 0x1f, $records[3]['pay_time'], 0x1f, $records[3]['start_date'], 0x1f, $records[3]['end_date'], 0x1f, $records[3]['start_time'], 0x1f, $records[3]['end_time'], 0x1f,
			$records[4]['lpr'], 0x1f, $records[4]['seat_no'], 0x1f, $records[4]['ticket'], 0x1f, $records[4]['in_time'], 0x1f, $records[4]['in_pic_name'], 0x1f, $records[4]['pay_time'], 0x1f, $records[4]['start_date'], 0x1f, $records[4]['end_date'], 0x1f, $records[4]['start_time'], 0x1f, $records[4]['end_time'], 0x1f,
			$records[5]['lpr'], 0x1f, $records[5]['seat_no'], 0x1f, $records[5]['ticket'], 0x1f, $records[5]['in_time'], 0x1f, $records[5]['in_pic_name'], 0x1f, $records[5]['pay_time'], 0x1f, $records[5]['start_date'], 0x1f, $records[5]['end_date'], 0x1f, $records[5]['start_time'], 0x1f, $records[5]['end_time'], 0x1f,
			0x80, 0x03);
	}

    return $send_data;
}

function tcp_data($arr, $seq, $cmdid)
{                                   
    $crc = pack('C', 'X');		// 起始值
    $seq = '00001';
    $cmdid = '002';                                    
    $pathlen = strlen($arr['pic_name']);   
    $packformat = "aCA7Ca7CaCa19Ca{$pathlen}Ca19Ca10Ca10Ca5Ca5C";
    // $packformat = "aCA7Ca7CaCa19Ca71Ca19Ca10Ca10Ca5Ca5C";
    $data = pack($packformat, 
    	$arr['nth'], 0x1f, 
        $arr['lpr'], 0x1f, 
        $arr['seat_no'], 0x1f, 
        $arr['ticket'], 0x1f, 
        $arr['start_time'], 0x1f, 
        $arr['pic_name'], 0x1f, 
        $arr['pay_time'], 0x1f, 
        $arr['ticket_start_date'], 0x1f, 
        $arr['ticket_end_date'], 0x1f, 
        $arr['ticket_start_time'], 0x1f, 
        $arr['ticket_end_time'], 0x1f); 
        
    $data_len = strlen($data);    
    $socket_len = $data_len + 16;
    // echo "len data[{$data_len}] socket[{$socket_len}] data[{$data}]";  
    
    $send_data = pack("CCCCa5Ca3C{$packformat}CC",
    	0x02,
    	$socket_len / 0x0100, $socket_len % 0x0100, 0x1c, $seq, 0x1c, $cmdid, 0x1c,  
    	$arr['nth'], 0x1f, 
        $arr['lpr'], 0x1f, 
        $arr['seat_no'], 0x1f, 
        $arr['ticket'], 0x1f, 
        $arr['start_time'], 0x1f, 
        $arr['pic_name'], 0x1f, 
        $arr['pay_time'], 0x1f, 
        $arr['ticket_start_date'], 0x1f, 
        $arr['ticket_end_date'], 0x1f, 
        $arr['ticket_start_time'], 0x1f, 
        $arr['ticket_end_time'], 0x1f, 
        0x80, 0x03);
         
   	// echo 'len:[' . $socket_len. '] send_data:['. $send_data .']';
    // file_put_contents('/tmp/aps.log.txt', date('Y-m-d H:i:s')."\n".$send_data ."\n\n", FILE_APPEND);
    return $send_data;
}  


// 執行worker
Worker::runAll();
