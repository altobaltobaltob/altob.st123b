<?php 
/*      
-----
file: station_services.php	場站應用系統服務程式

workerman執行:        
/usr/bin/php /home/bigbang/apps/coworker/station_services.php start -d
                    
workerman停止
/usr/bin/php /home/bigbang/apps/coworker/station_services.php stop
-----
*/

require_once '/home/bigbang/libs/Workerman/Autoloader.php';
use Workerman\Worker;  
Worker::$logFile = '/dev/null';		// 不記錄log file
//Worker::$pidFile = '/home/bigbang/libs/Workerman/' .basename(__FILE__).'.pid';

// 場站共用設定檔
require_once '/home/bigbang/apps/coworker/station.config.php'; 
define('APP_NAME', 'stations');	// application name
// 序號檔路徑
define('SEQNO_PATH', '/home/data/seqno/');

define('WORKERMAN_DEBUG', 1);
if (WORKERMAN_DEBUG)
{
	ini_set('display_errors', '1');
	error_reporting(E_ALL); 
	set_error_handler('error_handler', E_ALL);
}   

// 每月最後一日 
$last_date_month = array
(
	1 => 31,
	2 => 28,
	3 => 31,
	4 => 30,
	5 => 31,
	6 => 30,
	7 => 31,
	8 => 31,
	9 => 30,
	10 => 31,
	11 => 30,
	12 => 31
);

$xvars = array();	// 共用變數  
   
// 共用記憶體 
$mem = new Memcache;
if (! $mem->pconnect(MEMCACHE_HOST, MEMCACHE_PORT))
{ 
	echo 'Could not connect memcache';
    Worker::stopAll();    
} 
 
// 連接總管理處資料庫
$pdo = new PDO($dbs['dsn'], $dbs['user_name'], $dbs['password']); 

// 讀取info場站共用資訊
$sql_info = 'select station_no, station_name, station_full_name, company_no, station_ip, hq_url, origin_url, period_name, park_time,member_attr_list, period_list from info where seqno = 1';
$info_arr = $pdo->query($sql_info)->fetch(PDO::FETCH_ASSOC);
    
// global variable
$pdo = null;
$query_syncs = null;
$query_sync2hq_ok = null;

// 未同步者批次同步至總管理處
$sql_syncs = 'select st_sync_no, station_no, act, hq_tname, st_tname, st_seqno, sync_data from syncs where synced = 0 and st_sync_no = ?';
// $query_syncs = $pdo->prepare($sql_syncs); 

// 同步成功
$sql_sync2hq_ok = 'update syncs set  synced = 1, hq_sync_no = ? where st_sync_no = ?';
// $query_sync2hq_ok = $pdo->prepare($sql_sync2hq_ok);

// 預設curl參數
$curl_ch = curl_init(); 
$curl_options = array
(
	CURLOPT_URL => $info_arr['hq_url'],
	CURLOPT_HEADER => 0,    
    CURLOPT_RETURNTRANSFER => 1,	// 返回值不顯示, 只做變數用   
    CURLOPT_POST => 1,
	CURLOPT_CONNECTTIMEOUT => 5,
	CURLOPT_TIMEOUT => 5
);                                
   
init_station_xvars();

// 建立一個Worker監聽60133埠，不使用任何應用層協定
$worker = new Worker("http://0.0.0.0:60133");      

// 啟動N個進程對外提供服務
$worker->count = 4;

// ----- program start -----

// 當用戶端發來數據(主程式)
$worker->onMessage = function($connection, $msg_in)
{          
	global $dbs, $pdo, $sql_syncs, $sql_sync2hq_ok, $query_syncs, $query_sync2hq_ok; 
    
    // 無連線或status != Localhost via UNIX socket, 重連線之
    if (empty($pdo) || preg_match('/socket$/', $pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS)) != 1 )
    {  
     	$pdo = new PDO($dbs['dsn'], $dbs['user_name'], $dbs['password'], array(PDO::ATTR_PERSISTENT => true));
        $query_syncs = $pdo->prepare($sql_syncs); 
        $query_sync2hq_ok = $pdo->prepare($sql_sync2hq_ok);
        trigger_error('new pdo');
    }
                 
	if (!empty($_REQUEST['cmd']))
    {                        
    	trigger_error('worker.onMessage: ' . print_r($_REQUEST, true));
    	$funcs = @$_REQUEST['cmd'];
    	$funcs($connection);
    }
};  

// 執行worker
Worker::runAll();

// ----- end of program -----


// 同步化, 場站 -> 總管理處 , 參數: 
// http://hq_ip:60133/?cmd=st_syncs&station_no=12110&act=U&tname=members&key=member_no&kval=12110101&jdata=json_str&ck=str32
function st_syncs($connection)
{            
	global $pdo_hq, $query_hq_sync;
	// 暫不驗證
   	$syncs_sql = parms2sql($_REQUEST);                                    
   	                               
   	// exec:傳回筆數, query:傳回資料
   	$rows_affected = $pdo_hq->exec($syncs_sql);
   	// 如果新增資料, 回傳insert ID
   	if ($_REQUEST['act'] == 'A') $rows_affected = $pdo_hq->lastInsertId();
   	
   	@$connection->send($rows_affected);		// 回應當前序號或筆數 
   	
   	$confirms = $rows_affected > 0 ? 1 : 0;
   	
   	$query_hq_sync->execute(array($_REQUEST['station_no'], $_REQUEST['act'], $_REQUEST['tname'], $_REQUEST['data'], $confirms));
} 
         

// 讀取序號
// http://localhost:60133/?cmd=seqno&seqname=members&init_no=1025
function seqno($connection)
{
	$seqno_fname = SEQNO_PATH . "{$_REQUEST['seqname']}.txt";  
    $fp = fopen($seqno_fname, 'r+'); 
         	  
	// lock, 讀入序號, 加1寫回, close
	if ($fp) 
	{
		flock($fp, LOCK_EX);
	    $seqno = fread($fp, 80);
	    $next_no = $seqno + 1;
	    rewind($fp);
	    fwrite($fp, $next_no);
	    flock($fp, LOCK_UN); 
	    fclose($fp); 
	} 
	else	// 如無此序號檔, 新建之, 並傳回初始值
	{                     
		$seqno = empty($_REQUEST['init_no']) ? 1 : $_REQUEST['init_no'];
	    $next_no = $seqno + 1;
		file_put_contents($seqno_fname, $next_no, FILE_APPEND);
	}  
    @$connection->send($seqno);	// 回應當前序號 
} 
     

// 檢查是否有連線 ?
function check_connect($connection)
{
  	$connected = @fsockopen($_REQUEST['ip'], $_REQUEST['port'], $errno, $errstr, $_REQUEST['timeout']);
            
    if ($connected)
    {                            
        fclose($connected);
        $is_connected = 1;	//action when connected
    }
    else
    {      
        $is_connected = 0;	//action in connection failure
    } 
    
    return $is_connected;
}   
       

// 取得下一租期期最後一日
function last_date_next($connection)
{
	@cross_header();
    @$connection->send(last_date_next_period($_REQUEST['last_date_curr'], $_REQUEST['fee_period']));	// 參數:本期截止日,繳期 
} 


// 取得下一租期期最後一日, 參數: 本期截止日, 繳期
function last_date_next_period($last_date_curr, $fee_period)
{                     
	global $last_date_month;
    $arr = explode('-', $last_date_curr);
    $yy = (int) $arr[0];	// 取年份
    $mm = (int) $arr[1];    // 取月份
    
    $mm  += $fee_period;
    if ($mm > 12)	// 超過12月, 年度+1, 折算明年度月份
    {
      	++$yy;
        $mm -= 12;
    } 
    
    $dd = $mm == 2 && ($yy % 4) == 0 ? 29 : $last_date_month[$mm]; 
    if ($mm < 10)	$mm = "0{$mm}";		// 個位數前面補0
    return "{$yy}-{$mm}-{$dd}";
} 
             
// web跨網域header設定
function cross_header()
{
	global $info_arr;
	
	\Workerman\Protocols\Http::header('Access-Control-Allow-Origin: ' . "{$info_arr['origin_url']}");
	\Workerman\Protocols\Http::header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
	\Workerman\Protocols\Http::header('Access-Control-Allow-Headers: X-Requested-With, Content-Type, Accept');
}  
        
// 將參數轉成sql命令字串
function parms2sql($parms)
{             
	$act = $parms['act'];
    switch($act)
    {
      	case 'U':	// 更新場站資料庫 ????
        	$sql = "update {$parms['tname']} set {$parms['data']} where {$parms['key']} = '{$parms['kval']}';"; 
            break;
        
        case 'A':	// 新增資料
        	$fields_str = implode(',', array_keys($data));
        	$values_str = "'".implode("',", array_values($data))."'";
        	$sql = "insert into {$parms['tname']} ({$fields_str}) values({$values_str});";
        	break;
            
        case 'D':	// 刪除資料
        	$sql = "delete {$parms['tname']} where {$parms['key']} = '{$parms['kval']}';";
        	break;
    }
} 
  
// 場站初始化參數
function init_station_xvars()
{                          
	global $mem, $info_arr, $xvars;  
        
	$data = array
	(
		'cmd' => 'init_station',
		'station_no' => $info_arr['station_no'],
	    'park_time' => $info_arr['park_time']
	); 
	
    $jdata = worker_tx($data);  
	
	if(empty($jdata))
	{	
		trigger_error('中控已斷線, 建立預設值..'.print_r($jdata, true));

		// 中控已斷線, 建立預設值
		$xvars = array();
		$xvars['info']['period_name'] = array(1 => "月繳", 2 => "雙月繳", 3 => "季繳", 6 => "雙季繳", 12 => "四季繳");
		$xvars['info']['member_attr'] = array(1 => "一般", 2 => "里民", 3 => "身障",  4 => "員工", 5 => "里長", 250 => "VIP");
		$xvars['pt'] = 
			array(
				'RE' => array(
							'seqno' => 1000, 
							'timex' => array(
											'0' => array('type' => 1, 'w_start' => 0, 'w_end' => 6, 'time_start' => '00:00:00', 'time_end' => '23:59:59')
										),
							'remarks' => '全天全時段'
						),
				'NF' => array(
							'seqno' => 1010, 
							'timex' => array(
											'0' => array('type' => 1, 'w_start' => 1, 'w_end' => 5, 'time_start' => '18:00:00', 'time_end' => '23:59:59'),
											'1' => array('type' => 1, 'w_start' => 1, 'w_end' => 5, 'time_start' => '00:00:00', 'time_end' => '07:59:59')
										),
							'remarks' => '週一至週五： 00:00:00 - 07:59:59 <br/>週一至週五： 18:00:00 - 23:59:59'
						),
				'WK66' => array(
							'seqno' => 1026, 
							'timex' => array(
											'0' => array('type' => 1, 'w_start' => 1, 'w_end' => 5, 'time_start' => '06:00:00', 'time_end' => '17:59:59')
										),
							'remarks' => '週一至週五： 06:00:00 - 17:59:59'
						),
				'WK' => array(
							'seqno' => 1020, 
							'timex' => array(
											'0' => array('type' => 1, 'w_start' => 1, 'w_end' => 5, 'time_start' => '07:30:00', 'time_end' => '18:29:59')
										),
							'remarks' => '週一至週五： 07:30:00 - 18:29:59'
						),
				'HO' => array(
							'seqno' => 1030, 
							'timex' => array(
											'0' => array('type' => 1, 'w_start' => 6, 'w_end' => 6, 'time_start' => '00:00:00', 'time_end' => '23:59:59'),
											'1' => array('type' => 1, 'w_start' => 0, 'w_end' => 0, 'time_start' => '00:00:00', 'time_end' => '23:59:59')
										),
							'remarks' => '週六日全時段'
						)
			);
	}
    else
	{
		$xvars = json_decode($jdata, true);
		
		// 篩選繳期名稱
		$arr = array();                 
		$arr_list = explode(',', $info_arr['period_list']); 
		foreach($arr_list as $idx)	$arr[$idx] = $xvars['info']['period_name'][$idx];
		$xvars['info']['period_name'] = $arr; 
		
		// 篩選會員身份
		$arr = array();                 
		$arr_list = explode(',', $info_arr['member_attr_list']); 
		foreach($arr_list as $idx)	$arr[$idx] = $xvars['info']['member_attr'][$idx];
		$xvars['info']['member_attr'] = $arr; 
	}
    
	$mem->set('st_info', $info_arr);
	$mem->set('info', $xvars['info']);
	$mem->set('pt', $xvars['pt']);
	
	trigger_error('st_info: '.print_r($info_arr, true));
	trigger_error('xvars: '.print_r($xvars, true));
}     

 
// 顯示場站初始化參數
function show_all_vars($connection)
{
	global $info_arr, $xvars;     
    $connection->send(json_encode($xvars, JSON_UNESCAPED_UNICODE));
} 


// 場站初始化參數
function get_var($connection)
{ 
	global $xvars; 
    
    $str = gettype($xvars[$_REQUEST['var']]) == 'array' ? json_encode($xvars[$_REQUEST['var']], JSON_UNESCAPED_UNICODE) : $xvars[$_REQUEST['var']];                              
    
    $connection->send($str);
}   


// 顯示場站初始化參數
function set_var($connection)
{ 
	global $xvars; 
    
    $xvars[$_REQUEST['var']] = $_REQUEST['val'];                            
    
    $connection->send('1');
} 

// 批次同步資料表至總管理處
function sync_batch($connection)
{         
	global $query_syncs, $query_sync2hq_ok;
	
	//trigger_error(__FUNCTION__ . '..start..');
    
    $connection->close();	// 先斷線再繼續處理
                   
    foreach(explode(',', $_REQUEST['sync_seqnos']) as $st_sync_no)
    {   
		if(empty($st_sync_no)) continue;
		
		$query_syncs->execute(array($st_sync_no));   
		$rows_syncs = $query_syncs->fetch(PDO::FETCH_ASSOC); 
        $tx = $rows_syncs;
		
		if(empty($tx)) continue;
		
        $tx['cmd'] = 'sync_st2hq';						// 場站同步至總管理處
        $hq_sync_no = worker_tx($tx); 
		
		//trigger_error(__FUNCTION__ . '| 1. st_sync_no: ' . $st_sync_no . ', 2. rows_syncs: ' . $rows_syncs . ', 3. hq_sync_no: ' . $hq_sync_no);
                        
        // 同步成功, 記錄之 ( note: 總管理處的 hq_sync 記錄完成就視為完成，各場站 sync.synced = 1 )
        if ($hq_sync_no > 0)
        {
          	$query_sync2hq_ok->execute(array($hq_sync_no, $st_sync_no));
        }
        else
        {
          	trigger_error('sync err:'.json_encode($tx, JSON_UNESCAPED_UNICODE));  // TODO: sync 失敗的處理方式
        }
    } 
	//trigger_error(__FUNCTION__ . '..end..');
} 

// 月租金額計算 
function calculate_rents_amt($connection)
{                        
	$_REQUEST['rents_amt1'] = 100;                                             
	$_REQUEST['rents_amt2'] = 200;        
    cross_header();                                     
	$connection->send(json_encode($_REQUEST, JSON_UNESCAPED_UNICODE));	  
}

// worker connect至總管理處
function worker_tx($data)
{
  	global $curl_ch, $curl_options; 
    
    $curl_options[CURLOPT_POSTFIELDS] = $data; 
    trigger_error('curl:'. print_r($curl_options, true));
	curl_setopt_array($curl_ch, $curl_options);
	return(curl_exec($curl_ch));    
}
    
// 發生錯誤時集中在此處理
function error_handler($errno, $errstr, $errfile, $errline, $errcontext)
{         
  	$str = date('H:i:s')."|{$errstr}|{$errfile}|{$errline}|{$errno}\n";               
  	error_log($str, 3, LOG_PATH.APP_NAME . '.' . date('Ymd').'.log.txt');	// 3代表參考後面的檔名
}
   