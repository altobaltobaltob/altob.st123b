<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>歐特儀股份有限公司</title>
	
</head>
<body style="font-family:Microsoft JhengHei;">

<style>
#play_button {
    position:absolute;
    transition: .5s ease;
    top: 1%;
    left: 1%;
}
#play_button:hover { 
    -webkit-transform: scale(1.05);/*Grows in size like Angry Birds button*/
    -moz-transform: scale(1.05);
    -ms-transform: scale(1.05);
    -o-transform: scale(1.05);
} 

#wrap { position:fixed; left:0; width:100%; top:0; height:100%; }
#iframe { display: block; width:100%; height:100%; }

</style>

<!-- jQuery -->
<script src="<?=BOOTSTRAPS?>bower_components/jquery/dist/jquery.min.js"></script>
<!-- altob settings -->
<script src="<?=WEB_LIB?>js/altob.settings.js"></script>

<script type="text/javascript">
function iframe_onload() 
{
    console.log('..loaded..');
}

function reload_site() 
{
    var sites = AltobObject.settings.any_map.urls;
    document.getElementById('myIframe').src = sites[Math.floor(Math.random() * sites.length)];
}    

$(document).ready(function()
{
	reload_site();
	
	
});
</script>

<div id="wrap">
	<button id="play_button" onClick="reload_site()">重新搜尋</button>

	<iframe id="myIframe" src="https://www.google.com" frameborder="0" style="border-style: none;width: 100%; height: 100%;" onLoad="iframe_onload();"></iframe>
</div>

</body>
</html>