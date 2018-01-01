<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>停車場使用狀態  (<?=STATION_NAME?>)</title>
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
                <a class="navbar-brand" href="#">停車場使用狀態  (<?=STATION_NAME?>)</a>
            </div>

            <!-- /.navbar-top-links(左側選單) -->
            <div class="navbar-default sidebar" role="navigation">
                <div class="sidebar-nav navbar-collapse">
                    <ul class="nav" id="side-menu">
                        <li>
                            <a href="#"><i class="fa fa-user fa-fw"></i>服務項目<span class="fa arrow"></span></a>
                            <ul class="nav nav-second-level">
								<li>
                                    <a href="#" onclick="show_item('homepage');">首頁</a>
                                </li>
                                <li>
                                    <a href="#" onclick="show_item('B1');">B1 樓層 - 車位使用狀態</a>
                                </li>
								<li>
                                    <a href="#" onclick="show_item('F1');">F1 樓層 - 車位使用狀態</a>
                                </li>
								<li>
                                    <a href="#" onclick="show_item('F2');">F2 樓層 - 車位使用狀態</a>
                                </li>
								<li>
                                    <a href="#" onclick="show_item('F3');">F3 樓層 - 車位使用狀態</a>
                                </li>
								<li>
                                    <a href="#" onclick="show_item('F4');">F4 樓層 - 車位使用狀態</a>
                                </li>
								<li>
                                    <a href="#" onclick="show_item('F5');">F5 樓層 - 車位使用狀態</a>
                                </li>
								<li>
                                    <a href="#" onclick="show_item('F6');">F6 樓層 - 車位使用狀態</a>
                                </li>
								<li>
                                    <a href="#" onclick="show_item('F7');">F7 樓層 - 車位使用狀態</a>
                                </li>
								<!--li>
                                    <a href="#" onclick="stopAlertSound();">[ 解除警報聲 ]</a>
                                </li>
								<li>
                                    <a href="#" onclick="cleanMapSOS();">[ 清除位置標示 ]</a>
                                </li-->
								<!--li>
                                    <a href="#" onclick="test();">test</a>
                                </li-->
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
                    <h2 class="page-header">車位使用狀態</h2><?php /* 右側小表頭 */ ?>
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
            <?php /* ----- 首頁 ----- */ ?>
            <div data-items="homepage" class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading"><?php /* 資料顯示區灰色小表頭 */ ?>
                            首頁
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <div class="dataTable_wrapper">
                                <table class="table table-striped table-bordered table-hover">
                                        <tr>
                                            <td style="text-align:center;"><input type="button" value="B1 樓層 - 車位使用狀態" onclick="show_item('B1');" /></td>
                                        </tr>
										<tr>
                                            <td style="text-align:center;"><input type="button" value="F1 樓層 - 車位使用狀態" onclick="show_item('F1');" /></td>
                                        </tr>
										<tr>
                                            <td style="text-align:center;"><input type="button" value="F2 樓層 - 車位使用狀態" onclick="show_item('F2');" /></td>
                                        </tr>
										<tr>
                                            <td style="text-align:center;"><input type="button" value="F3 樓層 - 車位使用狀態" onclick="show_item('F3');" /></td>
                                        </tr>
										<tr>
                                            <td style="text-align:center;"><input type="button" value="F4 樓層 - 車位使用狀態" onclick="show_item('F4');" /></td>
                                        </tr>
										<tr>
                                            <td style="text-align:center;"><input type="button" value="F5 樓層 - 車位使用狀態" onclick="show_item('F5');" /></td>
                                        </tr>
										<tr>
                                            <td style="text-align:center;"><input type="button" value="F6 樓層 - 車位使用狀態" onclick="show_item('F6');" /></td>
                                        </tr>
										<tr>
                                            <td style="text-align:center;"><input type="button" value="F7 樓層 - 車位使用狀態" onclick="show_item('F7');" /></td>
                                        </tr>
                                </table>
                            </div><?php /* ----- end of dataTable_wrapper ----- */?>
                        </div><?php /* ----- end of panel-body ----- */?>
                        <!-- /.panel-body -->
                    </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <?php /* ----- 首頁(結束) ----- */ ?>
			
			<?php /* ----- B1 樓層 ----- */ ?>
            <div data-items="B1" class="row" style="display:none;">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading"><?php /* 資料顯示區灰色小表頭 */ ?>
							<span>B1 車位使用狀態 - 操作：</span>
							<button id="zoom0b1canvas">還原</button>
							<button id="zoomInb1canvas">放大</button>
							<button id="zoomOutb1canvas">縮小</button>
							<span> ( 點撃車位圖示, 取得更多訊息 )</span>
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <canvas id="b1canvas"></canvas>
                        </div><?php /* ----- end of panel-body ----- */?>
                        <!-- /.panel-body -->
                    </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <?php /* ----- B1 樓層(結束) ----- */ ?>
			
			<?php /* ----- F1 樓層 ----- */ ?>
            <div data-items="F1" class="row" style="display:none;">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading"><?php /* 資料顯示區灰色小表頭 */ ?>
							<span>F1 車位使用狀態 - 操作：</span>
							<button id="zoom0f1canvas">還原</button>
							<button id="zoomInf1canvas">放大</button>
							<button id="zoomOutf1canvas">縮小</button>
							<span> ( 點撃車位圖示, 取得更多訊息 )</span>
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <canvas id="f1canvas"></canvas>
                        </div><?php /* ----- end of panel-body ----- */?>
                        <!-- /.panel-body -->
                    </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <?php /* ----- F1 樓層(結束) ----- */ ?>
			
			<?php /* ----- F2 樓層 ----- */ ?>
            <div data-items="F2" class="row" style="display:none;">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading"><?php /* 資料顯示區灰色小表頭 */ ?>
							<span>F2 車位使用狀態 - 操作：</span>
							<button id="zoom0f2canvas">還原</button>
							<button id="zoomInf2canvas">放大</button>
							<button id="zoomOutf2canvas">縮小</button>
							<span> ( 點撃車位圖示, 取得更多訊息 )</span>
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <canvas id="f2canvas"></canvas>
                        </div><?php /* ----- end of panel-body ----- */?>
                        <!-- /.panel-body -->
                    </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <?php /* ----- F2 樓層(結束) ----- */ ?>
			
			<?php /* ----- F3 樓層 ----- */ ?>
            <div data-items="F3" class="row" style="display:none;">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading"><?php /* 資料顯示區灰色小表頭 */ ?>
							<span>F3 車位使用狀態 - 操作：</span>
							<button id="zoom0f3canvas">還原</button>
							<button id="zoomInf3canvas">放大</button>
							<button id="zoomOutf3canvas">縮小</button>
							<span> ( 點撃車位圖示, 取得更多訊息 )</span>
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <canvas id="f3canvas"></canvas>
                        </div><?php /* ----- end of panel-body ----- */?>
                        <!-- /.panel-body -->
                    </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <?php /* ----- F3 樓層(結束) ----- */ ?>
			
			<?php /* ----- F4 樓層 ----- */ ?>
            <div data-items="F4" class="row" style="display:none;">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading"><?php /* 資料顯示區灰色小表頭 */ ?>
							<span>F4 車位使用狀態 - 操作：</span>
							<button id="zoom0f4canvas">還原</button>
							<button id="zoomInf4canvas">放大</button>
							<button id="zoomOutf4canvas">縮小</button>
							<span> ( 點撃車位圖示, 取得更多訊息 )</span>
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <canvas id="f4canvas"></canvas>
                        </div><?php /* ----- end of panel-body ----- */?>
                        <!-- /.panel-body -->
                    </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <?php /* ----- F4 樓層(結束) ----- */ ?>
			
			<?php /* ----- F5 樓層 ----- */ ?>
            <div data-items="F5" class="row" style="display:none;">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading"><?php /* 資料顯示區灰色小表頭 */ ?>
							<span>F5 車位使用狀態 - 操作：</span>
							<button id="zoom0f5canvas">還原</button>
							<button id="zoomInf5canvas">放大</button>
							<button id="zoomOutf5canvas">縮小</button>
							<span> ( 點撃車位圖示, 取得更多訊息 )</span>
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <canvas id="f5canvas"></canvas>
                        </div><?php /* ----- end of panel-body ----- */?>
                        <!-- /.panel-body -->
                    </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <?php /* ----- F5 樓層(結束) ----- */ ?>
			
			<?php /* ----- F6 樓層 ----- */ ?>
            <div data-items="F6" class="row" style="display:none;">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading"><?php /* 資料顯示區灰色小表頭 */ ?>
							<span>F6 車位使用狀態 - 操作：</span>
							<button id="zoom0f6canvas">還原</button>
							<button id="zoomInf6canvas">放大</button>
							<button id="zoomOutf6canvas">縮小</button>
							<span> ( 點撃車位圖示, 取得更多訊息 )</span>
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <canvas id="f6canvas"></canvas>
                        </div><?php /* ----- end of panel-body ----- */?>
                        <!-- /.panel-body -->
                    </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <?php /* ----- F6 樓層(結束) ----- */ ?>
			
			<?php /* ----- F7 樓層 ----- */ ?>
            <div data-items="F7" class="row" style="display:none;">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading"><?php /* 資料顯示區灰色小表頭 */ ?>
							<span>F7 車位使用狀態 - 操作：</span>
							<button id="zoom0f7canvas">還原</button>
							<button id="zoomInf7canvas">放大</button>
							<button id="zoomOutf7canvas">縮小</button>
							<span> ( 點撃車位圖示, 取得更多訊息 )</span>
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <canvas id="f7canvas"></canvas>
                        </div><?php /* ----- end of panel-body ----- */?>
                        <!-- /.panel-body -->
                    </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <?php /* ----- F7 樓層(結束) ----- */ ?>

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




    <!-- virtual keyboard -->
  	<!--link href="<?=WEB_LIB?>virtual-keyboard/css/jquery-ui.min.css" rel="stylesheet">
	<link href="<?=WEB_LIB?>virtual-keyboard/css/keyboard.css" rel="stylesheet">
  	<script src="<?=WEB_LIB?>virtual-keyboard/js/jquery-ui.min.js"></script>
  	<script src="<?=WEB_LIB?>virtual-keyboard/js/jquery.keyboard.js"></script>
  	<script src="<?=WEB_LIB?>virtual-keyboard/js/jquery.keyboard.extension-caret.js"></script-->

	<!-- jQuery validate -->
	<script src="<?=WEB_LIB?>form-validator/jquery.form-validator.min.js"></script>
	<!-- alertify -->
	<link href="<?=WEB_LIB?>css/alertify.core.css" rel="stylesheet">
	<link href="<?=WEB_LIB?>css/alertify.bootstrap.css" rel="stylesheet">
	<script src="<?=WEB_LIB?>js/alertify.min.js"></script> 
	<!-- moment -->
	<script src="<?=WEB_LIB?>js/moment.min.js"></script> 
	
	
	<!-- altob status map -->
	<script src="<?=WEB_LIB?>js/altob-status-map.js"></script> 
	

    <!-- Custom Theme JavaScript -->
    <script src="<?=BOOTSTRAPS?>dist/js/sb-admin-2.js"></script>
    <div id="works" style="display:none;"></div><?php /* 作為浮動顯示區之用 */ ?>
</body>
</html>

<script>

<?php /* 顯示指定項目 */ ?>
function show_item(tags)
{
	$("[data-items]").hide();
	$("[data-items="+tags+"]").show();
    return false;
}

$(document).ready(function()
{
	<?php /* validate  設定start */ ?>
	$.validate(
		{
			modules : 'security',
		}
	);
	<?php /* validate  設定end */ ?>
	
	
	<?php /* 樓層平面圖 */ ?>
	AltobObject.StatusMap({
		queryStationPksUrl: "http://192.168.10.201/pks.html/query_station_pks/<?=STATION_NO?>/",		// 廠站車位資料 API
		queryStationStatusUrl: "http://192.168.10.201/pks.html/query_station_status/<?=STATION_NO?>",	// 廠站地圖資料 API
		dataReloadIntervalTimeMillis: 30000,	// 資料, 自動更新週期 ( 30 sec )
		dataReloadErrorLimit: 5,				// 資料, 連線容錯次數
		mapBaseRadius: 18,						// 地圖, 圈圈大小基數
		mapInfo: {
			map1: {
				floorName: 'B1',
				canvasId: 'b1canvas',
				src: '<?=SERVER_URL?>i3/pics/b1_map.png'
			},
			map2: {
				floorName: 'F1',
				canvasId: 'f1canvas',
				src: '<?=SERVER_URL?>i3/pics/f1_map.png'
			},
			map3: {
				floorName: 'F2',
				canvasId: 'f2canvas',
				src: '<?=SERVER_URL?>i3/pics/f2_map.png'
			},
			map4: {
				floorName: 'F3',
				canvasId: 'f3canvas',
				src: '<?=SERVER_URL?>i3/pics/f3_map.png'
			},
			map5: {
				floorName: 'F4',
				canvasId: 'f4canvas',
				src: '<?=SERVER_URL?>i3/pics/f4_map.png'
			},
			map6: {
				floorName: 'F5',
				canvasId: 'f5canvas',
				src: '<?=SERVER_URL?>i3/pics/f5_map.png'
			},
			map7: {
				floorName: 'F6',
				canvasId: 'f6canvas',
				src: '<?=SERVER_URL?>i3/pics/f6_map.png'
			},
			map8: {
				floorName: 'F7',
				canvasId: 'f7canvas',
				src: '<?=SERVER_URL?>i3/pics/f7_map.png'
			}
		}
	});
	
	// 定時自動更新頁面
	(function autoReloadPage(){
		var pageReloadTimeMillis = 600000;			// 頁面, 自動重新載入週期 ( 10 min )
		var pageCheckReloadTimeMillis = 10000;		// 頁面, 判斷重新載入週期 ( 10 sec )
		var aliveTime = moment();
		$(document.body).bind("mousemove keypress", function(e) {
			aliveTime = moment();
		});
		function refresh() {
			if(moment() - aliveTime >= pageReloadTimeMillis) // 如果頁面沒動作, 才更新
				window.location.reload(true);
			else 
				setTimeout(refresh, pageCheckReloadTimeMillis);
		}
		setTimeout(refresh, pageCheckReloadTimeMillis);
	})();
	
});
</script>
