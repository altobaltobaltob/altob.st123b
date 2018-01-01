<!DOCTYPE html>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<style>  
body{background-color:black;font-family:Microsoft JhengHei;}
table{width:100%;text-align:center;}
   
.availables /* 空車位數 */
{
font-size:920px; 
font-family:arial;  
font-weight:bolder;
color:gold;
text-align:center; 
}

</style>
<script src="http://code.jquery.com/jquery-2.1.4.js"></script>
<html>
<head>
<title>剩餘車位數</title>
</head>
<body>  
<table style="border:0px;" cellpadding='0' bolder="0">
<tr>
<td><!--marquee direction="up" scrollamount="50"--><div class="availables"><?=$init_value?></div><!--/marquee--></td>
</tr>
</table>
</body>
<script>
setInterval(function()
{                 
	$.ajax
        	({
        		url: "http://192.168.51.15/get_group_num.php?group_id=<?=$group_id?>",
            	type: "post", 
            	dataType:"text",
            	data: {},
            	success: function(jdata)
            	{     
                	$(".availables").text(jdata);    
            	}
        	});
}, 2000);  
</script>
</html>