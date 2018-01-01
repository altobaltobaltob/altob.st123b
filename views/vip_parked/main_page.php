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
<script>
var vip_no="<?=$vip_no?>",
clientid ="<?php echo uniqid();?>",
mqtt_ip="<?=$mqtt_ip?>";
</script>   
<html>
<head>
<title>VIP車位</title>
</head>
<body>  
<!-- 閒置時間, 顯示: 公務專用 請勿佔用 -->
<!--div id="no_parking" class="vip_notice" style="display:none;"-->
<div id="no_parking" class="vip_notice">
<table style="border:0px;" cellpadding='0' bolder="0">
<tr>
<td class="line_1" align="center" style="font-size:200px;">光興國小地下停車場</td>     
</tr>
<tr>
<td class="line_2"><br /><marquee direction="left" scrollamount="40" style="font-size:300px;">歡迎光臨</marquee></td>
</tr>
</table>
</div>
<!-- 指示VIP停此, 顯示: 車號 向下指 -->
<div id="vip_here" class="vip_notice" style="background-color:white;display:none;">
<!--div id="vip_here" class="vip_notice" style="background-color:white;"-->
<table style="border:0px;" cellpadding='0' bolder="0">
<tr>
<td id="park_lpr" class="line_1" align="center">貴賓請停此</td>     
</tr>
<tr>
<table>
<tr>
<td></td>
<td class="line_2" align="center">
<marquee direction="down" scrollamount="40">
<img src="/vip_parked/down_arrow.png" height="510"/>
</marquee>
</td>
<td></td>
</tr> 
</table>
</tr>
</table>
</div>
<!-- VIP停車時, 顯示: 車號 貴賓車位 -->
<div id="vip_parked" class="vip_notice" style="display:none;">
<table style="border:0px;" cellpadding='0' bolder="0">
<tr>
<td class="line_1" align="center">公務專用</td>     
</tr>
<tr>
<td class="line_2">貴賓車位</td>
</tr>
</table>
</div> 
<!-- 一般車停車時, 顯示: 車號 歡迎停用 -->
<div id="someone_parked" class="vip_notice" style="display:none;">
<table style="border:0px;" cellpadding='0' bolder="0">
<tr>
<td class="line_1" align="center">一般車輛</td>     
</tr>
<tr>
<td class="line_2">歡迎光臨</td>
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
	console.log("onConnect|"+mqtt_ip+"|"+clientid+"|"+"welcome_"+vip_no);
	client.subscribe("vip_"+vip_no, 2);
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
    lpr = message.payloadString == "movie" ? "<div style='font-size:260px;'>歡迎都教授蒞臨</div>" : message.payloadString;
    // lpr = decodeURIComponent(message.payloadString);
    // $("#park_lpr").text(lpr+"請停此位");
    // $("#park_lpr").text(lpr);
    $("#park_lpr").html(lpr);
    $("#vip_here").show();   
                      
    // 30秒恢復原畫面
    vip_start = setTimeout(function()
    			{
  					$(".vip_notice").hide();	
   					$("#no_parking").show(); 
            		clearTimeout(vip_start);
				}, 30000);
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