<!DOCTYPE html>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Cache-control" content="no-cache">
<style>
body{background-color:black;font-family:Microsoft JhengHei;overflow:hidden;}
table{width:100%;text-align:center;}
   
.line_1 /* 第一行 */
{
font-size:300px; 
font-family:微軟正黑體;  
font-weight:bolder;
color:red;
}

.line_2 /* 第二行 */
{ 
width:15%;     
font-family:微軟正黑體; 
font-size:280px; 
color:greenyellow; 
text-align:center; 
font-weight:bolder;
}
</style>
<script src="http://code.jquery.com/jquery-2.1.4.js"></script>
<script src="http://61.220.179.128/jsid.i/uniqid=clientid&mqtt_ip=mqtt_ip"></script> 
<html>
<head>
<title>入口-VIP歡迎光臨</title>
</head>
<body>  
<!-- 閒置時間, 顯示: 輸入車號後即可繳費 -->
<div id="waiting" class="vip_notice">
<table style="border:0px;" cellpadding='0' bolder="0">
<tr>
<td style="font-size:100px;">&nbsp;</td>     
</tr>
<tr>
<td class="line_1" align="center" style="font-size:140px;">輸入車號後即可繳費</td>     
</tr>
<tr>
<td class="line_2"><marquee direction="left" scrollamount="40" style="font-size:170px;">本停車場使用無票卡繳費</marquee></td>
</tr>
</table>
</div>
<!-- 會員車入場, 顯示: 車號 歡迎光臨 -->
<div id="member" class="vip_notice" style="display:none;">
<!--div id="member" class="vip_notice"-->
<img src="vip_map.jpg" width="1345" height="750"/>
</div>
<!-- VIP入場時, 顯示: VIP導車 -->
<div id="vip_comein" class="vip_notice" style="display:none;background-color:white;">
<table style="border:0px;" cellpadding='0' bolder="0">
<tr>
<td style="font-size:100px;">&nbsp;</td>     
</tr>
<tr>
<td class="line_1" align="center"  style="font-size:140px;">貴賓請往前左方停車</td>     
</tr>
<tr>
<td class="line_2" style="color:red;"><marquee direction="left" scrollamount="40" style="font-size:170px;">歡迎貴賓蒞臨</marquee></td>
</tr>
</table>
</div>

</body>
<script src="/libs/js/moment.min.js"></script> 
<script src="/libs/js/mqttws.min.js"></script>
<script>
var client = new Paho.MQTT.Client(mqtt_ip, 8000, clientid);

// set callback handlers
client.onConnectionLost = onConnectionLost;
client.onMessageArrived = onMessageArrived; 
client.connect({onSuccess:onConnect});
 
// called when the client connects
function onConnect() 
{
	// Once a connection has been made, make a subscription and send a message.
	console.log("onConnect|"+mqtt_ip+"|"+clientid+"|"+"vip_welcome");
	client.subscribe("vip_welcome", 2);
}

// called when the client loses its connection
function onConnectionLost(responseObject) 
{
	if (responseObject.errorCode !== 0) 
	{
    	console.log("onConnectionLost:"+responseObject.errorMessage);
  	}
}

// called when a message arrives
function onMessageArrived(message) 
{                                                     
	console.log("onMessageArrived:"+message.payloadString);
	$(".vip_notice").hide();
    $("#vip_comein").show(); 
                                   
    // 8秒後恢復原始畫面
    vip_start = setTimeout(function()
    			{
  					$(".vip_notice").hide();	
   					$("#waiting").show(); 
            		clearTimeout(vip_start);
				}, 8000);
}

$(document).ready(function()
{
	// 如果畫面沒動作, 自動更新頁面
	var reloadTimeMillis = 600000;		// 每 10 min 自動重新載入頁面
	var checkReloadTimeMillis = 10000;	// 每 10 sec 判斷一次
	// 如果畫面沒動作, 每10分鐘自動重新載入頁面
	var aliveTime = moment();
	$(document.body).bind("mousemove keypress", function(e) {
		aliveTime = moment();
	});
	function refresh() {
		if(moment() - aliveTime >= reloadTimeMillis) 
			window.location.reload(true);
		else 
			setTimeout(refresh, checkReloadTimeMillis);
	}
	setTimeout(refresh, checkReloadTimeMillis);
	
});
</script>   
</html>