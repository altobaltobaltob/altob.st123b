<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>緊急求救地圖</title>
    <!-- Bootstrap Core CSS -->
    <link href="<?=BOOTSTRAPS?>bower_components/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- MetisMenu CSS -->
    <link href="<?=BOOTSTRAPS?>bower_components/metisMenu/dist/metisMenu.min.css" rel="stylesheet">
    <!-- Timeline CSS -->
    <link href="<?=BOOTSTRAPS?>dist/css/timeline.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?=BOOTSTRAPS?>dist/css/sb-admin-2.css" rel="stylesheet">
    <!-- Morris Charts CSS -->
    <link href="<?=BOOTSTRAPS?>bower_components/morrisjs/morris.css" rel="stylesheet">
    <!-- Custom Fonts -->
    <link href="<?=BOOTSTRAPS?>bower_components/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">

    <script src="<?=WEB_LIB?>js/mqttws.min.js"></script>
</head>
<body style="font-family:Microsoft JhengHei;">
    <div id="wrapper">
        <!-- Navigation -->
        <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="#">緊急求救地圖</a>
            </div>

            <!-- /.navbar-top-links(左側選單) -->
            <div class="navbar-default sidebar" role="navigation">
                <div class="sidebar-nav navbar-collapse">
                    <ul class="nav" id="side-menu">
                        <li>
                            <a href="#"><i class="fa fa-user fa-fw"></i>服務項目<span class="fa arrow"></span></a>
                            <ul class="nav nav-second-level" id="side_menu_box">
								<li>
                                    <a href="#" onclick="show_item('homepage');">使用說明</a>
                                </li>
								<li>
                                    <a href="#" onclick="AltobObject.SosMap.stopAlertSound();">[ 解除警報聲 ]</a>
                                </li>
								<li>
                                    <a href="#" onclick="AltobObject.SosMap.cleanMapSOS();">[ 清除位置標示 ]</a>
                                </li>
                            </ul>
                            <!-- /.nav-second-level -->
                        </li>
                    </ul>
                </div>
                <!-- /.sidebar-collapse -->
            </div>
            <!-- /.navbar-static-side -->
        </nav>
        <div id="page-wrapper"><?php /* 主要資料顯示區 */ ?>
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">緊急求救地圖</h1><?php /* 右側小表頭 */ ?>
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
            <?php /* ----- 首頁 ----- */ ?>
            <div data-items="homepage" class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading"><?php /* 資料顯示區灰色小表頭 */ ?>
                            注意事項
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
							<!--div class="form-group">
								<label style="font-size:18px"> [&nbsp;注意事項&nbsp;] </label>
							</div--> 
				
							<ul>
								<li style='color:blue;'>由場站 APP 於停車場內，開啟藍芽情況下，點撃 APP 中的 sos 觸發緊急求救</li>
								<li style='color:blue;'>請搭配桌機音響，才能聽到緊報聲</li>
								<li style='color:red;'>本服務限定 “車辨主機” 開啟</li>
								<li style='color:red;'>本服務限定 “單一網頁” 開啟</li>
								<!--li style='color:red;'>2017/12/01 - 限定單一網頁開啟</li-->
								<!--li style='color:blue;'>2017/12/01 - </li-->
							</ul>
							
                            <!--div class="dataTable_wrapper">
                                <table class="table table-striped table-bordered table-hover">
                                        <tr>
                                            <td style="text-align:center;"><input type="button" value="B1 樓層" onclick="show_item('B1');" /></td>
                                        </tr>
                                </table>
                            </div--><?php /* ----- end of dataTable_wrapper ----- */?>
                        </div><?php /* ----- end of panel-body ----- */?>
                        <!-- /.panel-body -->
                    </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <?php /* ----- 首頁(結束) ----- */ ?>
			
        <!-- /#page-wrapper -->
    </div>
    <!-- /#wrapper -->
    <!-- jQuery -->
    <script src="<?=BOOTSTRAPS?>bower_components/jquery/dist/jquery.min.js"></script>
    <!-- Bootstrap Core JavaScript -->
    <script src="<?=BOOTSTRAPS?>bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
    <!-- Metis Menu Plugin JavaScript -->
    <script src="<?=BOOTSTRAPS?>bower_components/metisMenu/dist/metisMenu.min.js"></script>
    <!-- Morris Charts JavaScript -->
    <script src="<?=BOOTSTRAPS?>bower_components/raphael/raphael-min.js"></script>
    <!--script src="<?=BOOTSTRAPS?>bower_components/morrisjs/morris.min.js"></script-->
    <!--script src="<?=BOOTSTRAPS?>js/morris-data.js"></script-->

	<!-- jQuery validate -->
	<script src="<?=WEB_LIB?>form-validator/jquery.form-validator.min.js"></script>
	<!-- alertify -->
	<link href="<?=WEB_LIB?>css/alertify.core.css" rel="stylesheet">
	<link href="<?=WEB_LIB?>css/alertify.bootstrap.css" rel="stylesheet">
	<script src="<?=WEB_LIB?>js/alertify.min.js"></script> 
	
	<!-- altob sos map -->
	<script src="<?=WEB_LIB?>js/altob-sos-map.js"></script> 

    <!-- Custom Theme JavaScript -->
    <script src="<?=BOOTSTRAPS?>dist/js/sb-admin-2.js"></script>
    <div id="works" style="display:none;"></div><?php /* 作為浮動顯示區之用 */ ?>
</body>
</html>

<script>

// 取得樓層資訊
var FLOOR_MAP_RESULT = {};
FLOOR_MAP_RESULT.SERVER_URL = '<?=SERVER_URL?>';
FLOOR_MAP_RESULT.floor_info = JSON.parse('<?= $floor_info; ?>');

<?php /* 顯示指定項目 */ ?>
function show_item(tags)
{
	$("[data-items]").hide();
	$("[data-items="+tags+"]").show();
    return false;
}

$(document).ready(function()
{
	var side_menu_box = document.getElementById('side_menu_box');
	var page_wrapper = document.getElementById('page-wrapper');
	var sos_map_info = {};
	for(var idx in FLOOR_MAP_RESULT.floor_info)
	{
		// 建立地圖索引資訊
		var floor_name = FLOOR_MAP_RESULT.floor_info[idx]['floor_name'];
		var canvas_key = floor_name.toLowerCase();
		var canvas_name = canvas_key + 'canvas';
		sos_map_info[floor_name] = 
		{
			floorName: floor_name,
			canvasId: canvas_name,
			src: '/i3/pics/' + canvas_key + '_map_iii.png'
		};
		
		// 建立地圖 DIV
		var map_div = document.createElement("div");
		map_div.setAttribute("data-items", floor_name);
		map_div.setAttribute("class", 'row');
		map_div.setAttribute("style", 'display:none;');
		map_div.innerHTML = [
			"<div class='col-lg-12'>", 
				"<div class='panel panel-default'>", 
					"<div class='panel-heading'><span>", floor_name, " 樓層 - 操作：</span>", 
						"<button id='zoom0", canvas_name, "'>還原</button>",
						"<button id='zoomIn", canvas_name, "'>放大</button>", 
						"<button id='zoomOut", canvas_name, "'>縮小</button>",
					"</div>",
				"<div class='panel-body'><canvas id='", canvas_name, "'></canvas></div>",
			"</div></div>"].join('');
		
		page_wrapper.appendChild(map_div);
		
		// 建立樓層切換
		$('<li/>', {html: ["<a href='#' onclick='show_item(\"", floor_name, "\");'>", floor_name, " 樓層</a>"].join('')})
			.appendTo('ul.nav-second-level');
	}
	
	// 動態產生地圖資訊
	var sos_map_setting = {};
	sos_map_setting.getSosUrl = FLOOR_MAP_RESULT.SERVER_URL + "parkingquery.html/floor_map_read_sos";
	sos_map_setting.dataReloadIntervalTimeMillis = 5000;	// 資料, 自動更新週期 ( 5 sec )
	sos_map_setting.dataReloadErrorLimit = 5;				// 資料, 連線容錯次數
	sos_map_setting.soundInfo = { src: FLOOR_MAP_RESULT.SERVER_URL + 'sos/red_alert.wav' };
	sos_map_setting.mapInfo = sos_map_info;
	AltobObject.SosMap(sos_map_setting);
});
</script>
