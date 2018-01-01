<?php
/*
file: station.config.php		資料庫設定帳密設定
*/
define('MEMCACHE_HOST', 'localhost');					// memcache host   
define('MEMCACHE_PORT', 11211);							// memcache post no (default:11211)

define('LOG_PATH', '/home/data/logs/'); 

$dbs['host'] = 'localhost';
$dbs['dbname'] = 'master_db';
$dbs['user_name'] = 'altob';
$dbs['password'] = '0227057716';
$dbs['dsn'] = "mysql:host={$dbs['host']};dbname={$dbs['dbname']};charset=utf8";   

