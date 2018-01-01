<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
define('FILE_READ_MODE', 0644);
define('FILE_WRITE_MODE', 0666);
define('DIR_READ_MODE', 0755);
define('DIR_WRITE_MODE', 0777);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/

define('FOPEN_READ',							'rb');
define('FOPEN_READ_WRITE',						'r+b');
define('FOPEN_WRITE_CREATE_DESTRUCTIVE',		'wb'); // truncates existing file data, use with care
define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE',	'w+b'); // truncates existing file data, use with care
define('FOPEN_WRITE_CREATE',					'ab');
define('FOPEN_READ_WRITE_CREATE',				'a+b');
define('FOPEN_WRITE_CREATE_STRICT',				'xb');
define('FOPEN_READ_WRITE_CREATE_STRICT',		'x+b');
/*end*/

// user define
// define('DEBUG_MODE', true);
define('APP_BASE', '/home/bigbang/apps/parkings/');		// 應用系統根路徑
define('FILE_BASE', '/home/data/parkings/');			// 檔案存取根路徑    
define('CAR_PIC', FILE_BASE.'cars/pics/');				// 車輛照片存檔路徑 
define('PKS_PIC', FILE_BASE.'pks/pics/');				// 車輛入車格照片存檔路徑

define('EXPORT_BASE', '/home/data/export/');			// 檔案匯出根路徑  

define('SOS_BASE',	FILE_BASE. 'sos/');					// 警急求救路徑
define('SOS_MSG',	SOS_BASE. 'sos.txt');				// 警急求救檔案路徑
   
define('STATION_NAME', '未設定（預設：中榮）');			// 本場站名稱
define('STATION_NO', 40701);							// 本場站編號
define('STATION_IP', $_SERVER['SERVER_NAME']);			// 本場站IP
define('STATION_URL', 'http://'.STATION_IP.'/');		// 本場站URL

define('STATION_LOCAL_IP', '192.168.10.201');			// 內網IP
define('STATION_LOCAL_PORT', '80');						// 服務PORT

define('PHPLIBS_BASE', '/home/bigbang/libs/phplibs/');	// phplibs 根路徑
define('ALTOB_SYNC_FILE', PHPLIBS_BASE.'Altob.Sync.Integration.php');			// ALTOB (同步)
define('ALTOB_CRYPT_FILE', PHPLIBS_BASE.'Altob.Crypt.Integration.php');			// ALTOB (資料加解密)
define('ALTOB_BILL_FILE', PHPLIBS_BASE.'Altob.Payment.Integration.php');		// ALTOB (金流: 費率)
define('ALTOB_TWGC_FILE', PHPLIBS_BASE.'Altob.TWGC.Integration.php');			// ALTOB (歐Pa卡: TWGC)

define('ALTOB_CTBC_FILE', PHPLIBS_BASE.'ctbcbank/Altob.CTBC.Bank.Integration.php');	// 中國信託 (金流)

define('ALLPAY_FILE', PHPLIBS_BASE.'AllPay.Payment.Integration.php');	// 歐付寶 (金流)
define('ALLPAY_INVOICE_FILE', PHPLIBS_BASE.'AllPay_Invoice.php');		// 歐付寶 (電子發票)

define('MQ_CLASS_FILE', PHPLIBS_BASE.'phpMQTT.php');	// MQTT: class file name    
define('MQ_HOST', 'localhost');							// MQTT: host   
define('MQ_PORT', 1883);								// MQTT: port (default:1883) 
define('MQ_TOPIC_SUBLEVEL', 'SUBLEVEL');				// MQTT TOPIC: 樓層在席顯示
define('MQ_TOPIC_SUBTEXT', 'SUBTEXT');					// MQTT TOPIC: 出入口字幕機
define('MQ_TOPIC_OPEN_DOOR', 'OPEN_DOOR');				// MQTT TOPIC: 出入口開門
define('MQ_TOPIC_ALTOB', 'altob.com.tw.mqtt');			// MQTT TOPIC: common mqtt topic
define('MQ_ALTOB_MSG', 'msg');							// MQTT TOPIC: cmd: msg
define('MQ_ALTOB_MSG_END_TAG', ',altob');				// MQTT TOPIC: cmd: msg end tag
define('MQ_ALTOB_888', '888');							// MQTT TOPIC: cmd: 888
define('MQ_ALTOB_888_END_TAG', ',altob');				// MQTT TOPIC: cmd: 888 end tag

define('MEMCACHE_HOST', 'localhost');					// memcache host   
define('MEMCACHE_PORT', 11211);							// memcache post no (default:11211) 

// Date.timezone
//date_default_timezone_set("Asia/Taipei"); // <-- TODO: php.ini 無效 ?????????????????????????????????????????????

/*
|--------------------------------------------------------------------------
| DELIMITER - 共用設定
|--------------------------------------------------------------------------
*/

define('SYNC_DELIMITER_ST_NAME', 	' & ');	// (拆分) 場站名稱
define('SYNC_DELIMITER_ST_NO', 		',');	// (拆分) 場站編號
define('SYNC_DELIMITER_ST_INFO',	'|');	// (拆分) 其它

/*
|--------------------------------------------------------------------------
| MEMCACHE KEY - 共用設定
|--------------------------------------------------------------------------
*/

define('MCACHE_STATION_NO_STR', 'station_no_str');
define('MCACHE_STATION_NO_LIST_STR', 'station_no_list_str');	// 會員資料同步
define('MCACHE_STATION_NAME_STR', 'station_name_str');
define('MCACHE_STATION_IP_STR', 'station_ip_str');
define('MCACHE_STATION_PORT_STR', 'station_port_str');
define('MCACHE_STATION_888_STR', 'station_888_str');
define('MCACHE_SYNC_888_TMP_LOG', 'sync_888_tmp_log');	// 暫存 888 進出

define('MCACHE_STATION_SETTINGS', 'altob_station_settings');	// 場站設定匯整

/*
|--------------------------------------------------------------------------
| 自定義連結 - 共用設定
|--------------------------------------------------------------------------
*/

define("ALTOB_PAYMENT_TXDATA_URL", "http://localhost/txdata.html");		// 費率 ServiceURL

/*
|--------------------------------------------------------------------------
| LOG檔名 - 共用設定
|--------------------------------------------------------------------------
*/

define("MEMBER_LOG_TITLE", 'member-log://');		// 會員資料記錄
define("TX_LOG_TITLE", 'tx-log://');				// 交易資料記錄
define("ADMIN_LOG_TITLE", 'admin-log://');			// 管理操作記錄
define("EXPORT_LOG_TITLE", 'export-log://');		// 檔案匯出記錄
define("PARKTRON_LOG_TITLE", 'parktron-log://');	// 博辰資料記錄

/*
|--------------------------------------------------------------------------
| 資料庫欄位 - 共用設定
|--------------------------------------------------------------------------
*/
															// member_tx.verify_state
define("MEMBER_TX_VERIFY_STATE_NONE", 0); 					// 0: 未審核
define("MEMBER_TX_VERIFY_STATE_OK", 1); 					// 1: 人工審核完成
define("MEMBER_TX_VERIFY_STATE_GG", 99); 					// 99: 審核不通過
		
															// member_tx.tx_state
define("MEMBER_TX_TX_STATE_NONE", 0);						// 0: 無
define("MEMBER_TX_TX_STATE_STOP", 4);						// 4: 已退租
define("MEMBER_TX_TX_STATE_CANCEL", 44);					// 44: 交易取消
		
															// member_tx_bill.invoice_state
define("MEMBER_TX_BILL_INVOICE_STATE_NONE", 0);				// 0: 無
define("MEMBER_TX_BILL_INVOICE_STATE_MORE", 1);				// 1: 待補開
define("MEMBER_TX_BILL_INVOICE_STATE_ALLOWANCE", 2);		// 2: 待折讓
define("MEMBER_TX_BILL_INVOICE_STATE_MORE_DONE", 91);		// 91: 補開完成
define("MEMBER_TX_BILL_INVOICE_STATE_ALLOWANCE_DONE", 92);	// 92: 折讓完成
		
															// member_refund.refund_state
define("MEMBER_REFUND_STATE_NONE", 0);						// 0: 無
define("MEMBER_REFUND_STATE_MORE_INVOICE", 1);				// 1: 待補開
define("MEMBER_REFUND_STATE_LESS_INVOICE", 2);				// 2: 待折讓
define("MEMBER_REFUND_STATE_DONE", 100);					// 100: 已完成
		
															// member_refund.dismiss_state
define("MEMBER_REFUND_DISMISS_STATE_NONE", 0);				// 0: 無
define("MEMBER_REFUND_DISMISS_STATE_TRANSFERRED", 1);		// 1: 已轉租
define("MEMBER_REFUND_DISMISS_STATE_KEEP_DEPOSIT", 10);		// 10: 押金未退
define("MEMBER_REFUND_DISMISS_STATE_DONE", 100);			// 100: 已結清

															// tx_bill_ats.status
define("TX_BILL_ATS_STATUS_NONE", 0); 						// 0: 剛建立
define("TX_BILL_ATS_STATUS_PAID", 1); 						// 1: 結帳完成
define("TX_BILL_ATS_STATUS_INVOICE_VOID", 104);				// 104: 發票已作廢
define("TX_BILL_ATS_STATUS_INVOICE_ALLOWANCE", 105);		// 105: 發票已折讓

															// tx_bill.status
define("TX_BILL_STATUS_NONE", 0); 							// 0: 剛建立
define("TX_BILL_STATUS_PAID", 1); 							// 1: 結帳完成
define("TX_BILL_STATUS_ERROR_AMT", 2);						// 2: 錢沒對上
define("TX_BILL_STATUS_ERROR_INVOICE", 3); 					// 3: 發票沒建立
define("TX_BILL_STATUS_HAND_MODE", 4); 						// 4: 手動調整
define("TX_BILL_STATUS_TIME_OUT", 99); 						// 99: 訂單逾期作廢
define("TX_BILL_STATUS_PROCESSING", 100);					// 100: 交易進行中
define("TX_BILL_STATUS_FAIL", 101);							// 101: 交易失敗
define("TX_BILL_STATUS_DONE", 111);							// 111: 產品已領取




/* End of file constants.php */
/* Location: ./application/config/constants.php */                           