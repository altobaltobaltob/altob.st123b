<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>歐特儀自動化服務機</title>
    <!-- Bootstrap Core CSS -->
    <link href="<?=BOOTSTRAPS?>bower_components/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- MetisMenu CSS -->
    <link href="<?=BOOTSTRAPS?>bower_components/metisMenu/dist/metisMenu.min.css" rel="stylesheet">
    <!-- Timeline CSS -->
    <link href="<?=BOOTSTRAPS?>dist/css/timeline.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?=BOOTSTRAPS?>dist/css/sb-admin-3.css" rel="stylesheet">
    <!-- Morris Charts CSS -->
    <link href="<?=BOOTSTRAPS?>bower_components/morrisjs/morris.css" rel="stylesheet">
    <!-- Custom Fonts -->
    <link href="<?=BOOTSTRAPS?>bower_components/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">

</head>
<body style="font-family:Microsoft JhengHei;">
    <div id="wrapper">
        
        <div id="page-wrapper"><?php /* 主要資料顯示區 */ ?>
            <div class="row">
                <div class="col-lg-12">
                    <!--h1 class="page-header">歐特儀自動化服務機</h1--><?php /* 右側小表頭 */ ?>
					&nbsp;
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
			
			<?php /* ----- 查詢結果 ----- */ ?>
            <div data-items="not_found" class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading" style="font-size:64px;"><?php /* 資料顯示區灰色小表頭 */ ?>
                            查車結果：查無 <span id="not_found_lpr" style="font-size:64px;color:blue;"></span> 在席資料
                        </div>
					</div>
				</div>
			</div>

			<?php /* ----- 查詢結果 ----- */ ?>
            <!-- div data-items="rent_sync" class="row" style="display:none;"-->
            <div data-items="output_pks" class="row" style="display:none; height:100%">
                <div class="col-lg-3 col-sm-3">
                    <div class="panel panel-default">
                        <div class="panel-heading" style="font-size:28px;"><?php /* 資料顯示區灰色小表頭 */ ?>
                            查車結果
                        </div>
                        <div class="panel-body" style="margin: 0px auto;">
                            <div data-rows class="row">
                                <div class="col-lg-12" style="margin: 0px auto;">
                                <table class="table table-striped table-bordered table-hover"">
                                    <tbody style="font-size:28px;">
                                        <tr>
                                            <td style="text-align:right;vertical-align: middle;">車號</td>
                                            <td id="show_lpr" style="text-align:left;vertical-align: middle;"></td>
                                        </tr>
                                        <tr>
                                            <td style="text-align:right;vertical-align: middle;">所在樓層</td>
                                            <td id="show_floors" style="text-align:left;vertical-align: middle; font-size:28px; color:blue;"></td>
                                        </tr>
                                        <tr>
                                            <td style="text-align:right;vertical-align: middle;">停入時間</td>
                                            <td id="show_update_time" style="text-align:left;vertical-align: middle;"></td>
                                        </tr>
                                        <!--tr>
                                            <td colspan="2" style="text-align:center;vertical-align: middle;">
												<button type="button" class="btn btn-large btn-success pull-right" style="font-size:28px;" onclick="show_item('input_lpr');">結束查詢</button>
                                            </td>
                                        </tr-->
										
										<tr>
                                            <td style="text-align:right;vertical-align: middle;">在席照片</td>
                                            <td style="text-align:left;vertical-align: middle;">
												<table class="table table-striped table-bordered table-hover"">
													<tbody>
														<tr>
															<td style="text-align:center;vertical-align: middle;">
																<img id="show_img" style="max-width:150px" />
															</td>
														</tr>
													</tbody>
												</table>
											</td>
                                        </tr>
										
                                        </tbody>
                                </table>
                                </div>
                                <!-- /.col-lg-6 (nested) -->
                            </div>
                            <!-- /.row (nested) -->
                        </div>
                        <!-- /.panel-body -->
                    </div>
                    <!-- /.panel -->
                </div>
				
				<!--div class="col-lg-9 col-sm-9">
					<table class="table table-striped table-hover">
						<thead><tr><th style="text-align:center;">停車位置 : B1 樓層</th></tr></thead>
						<tbody>
							<canvas id="b1canvas"></canvas>
						</tbody>
					</table>
				</div-->
				
				<div class="col-lg-9 col-sm-9">
					<div class="panel panel-default" style="min-height: 1200px">
						<div class="panel-heading" style="font-size:28px;"><span>停車位置 : B1 樓層</span></div>
						<div class="panel-body"><canvas id="b1canvas"></canvas></div>
					</div>
				</div>
				
				<!--div class="col-lg-9 col-sm-9" style="height=1000px">
					<div class="panel panel-default">
						<div class="panel-heading"><span>停車位置 : B1 樓層</span></div>
						<div class="panel-body"><canvas id="b1canvas"></canvas></div>
					</div>
				</div-->
				
				<!--div class="col-lg-3 col-sm-6">
					<div class="panel panel-default">
                        <div class="panel-heading" style="font-size:28px;">
                            在席照片
                        </div>
					</div>
					<div class="panel-body" style="margin: 0px auto;">
						<div class="col-lg-12" style="margin: 0px auto;">
							<table class="table table-striped table-bordered table-hover"">
								<tbody>
									<tr>
										<td style="text-align:center;vertical-align: middle;">
											<img id="show_img" style="max-height:200px" />
                                        </td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div-->
                <!-- /.col-lg-12 -->

		</div>




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
  	<link href="<?=WEB_LIB?>virtual-keyboard/css/jquery-ui.min.css" rel="stylesheet">
	<link href="<?=WEB_LIB?>virtual-keyboard/css/keyboard.css" rel="stylesheet">
  	<script src="<?=WEB_LIB?>virtual-keyboard/js/jquery-ui.min.js"></script>
  	<script src="<?=WEB_LIB?>virtual-keyboard/js/jquery.keyboard.js"></script>
  	<script src="<?=WEB_LIB?>virtual-keyboard/js/jquery.keyboard.extension-caret.js"></script>

	<!-- alertify -->
	<link href="<?=WEB_LIB?>css/alertify.core.css" rel="stylesheet">
	<link href="<?=WEB_LIB?>css/alertify.bootstrap.css" rel="stylesheet">
	<script src="<?=WEB_LIB?>js/alertify.min.js"></script>
	<!-- moment -->
	<script src="<?=WEB_LIB?>js/moment.min.js"></script>
	
	<!-- jQuery validate -->
	<script src="<?=WEB_LIB?>form-validator/jquery.form-validator.min.js"></script>
	
	<!-- altob ats map -->
	<script src="<?=WEB_LIB?>js/altob-ats-map.js"></script> 
	
	<!-- altob settings -->
	<script src="<?=WEB_LIB?>js/altob.settings.js"></script>

    <!-- Custom Theme JavaScript -->
    <script src="<?=BOOTSTRAPS?>dist/js/sb-admin-2.js"></script>
    <div id="works" style="display:none;"></div><?php /* 作為浮動顯示區之用 */ ?>
</body>
</html>

<script>

<?php /* alertify function */ ?>
function alertify_count_down($msg, $delay)
{
	alertify.set({delay : $delay});
	alertify.log($msg);
}
function alertify_log($msg)
{
	alertify.set({delay : 2000});
	alertify.log($msg);
}
function alertify_error($msg)
{
	alertify.set({delay : 2000});
	alertify.error($msg);
}
function alertify_success($msg)
{
	alertify.set({delay : 2000});
	alertify.success($msg);
}

function alertify_msg($msg)
{
	alertify.set({ labels: {
		ok     : "確定"
	} });
	alertify.alert($msg, function (e){
		// do nothing
	});
}

function reset_query()
{
	$("#fuzzy_input").val("");
	$("#carin_query_list").hide();
	return false;
}

// 取得搜尋結果
var PKS_RESULT = {};
PKS_RESULT.lpr = '<?= $lpr; ?>';
PKS_RESULT.pksno = '<?= $pksno; ?>';
PKS_RESULT.pic_name = '<?= $pic_name; ?>';
PKS_RESULT.update_time = '<?= $update_time; ?>';
PKS_RESULT.in_time = '<?= $in_time; ?>';
PKS_RESULT.posx = '<?= $posx; ?>';
PKS_RESULT.posy = '<?= $posy; ?>';
PKS_RESULT.group_id = '<?= $group_id; ?>';
PKS_RESULT.group_name = '<?= $group_name; ?>';
PKS_RESULT.floors = '<?= $floors; ?>';

if(PKS_RESULT.pksno == '')
{
	$("#not_found_lpr").text(PKS_RESULT.lpr);
	show_item("not_found");
}
else
{
	$("#show_lpr").text(PKS_RESULT.lpr);
	$("#show_floors").html(PKS_RESULT.group_name+"<br/> ( 車格: " + PKS_RESULT.pksno.substr(-3, 3) +" )");
	$("#show_update_time").text(PKS_RESULT.in_time);
    $("#show_img").attr("src", "<?=SERVER_URL?>pkspic/" + PKS_RESULT.pic_name);

	// 顯示查車結果
	show_item("output_pks");
}

var refreshIntervalId = 0; // timer id

<?php /* 顯示指定項目 */ ?>
function show_item(tags)
{
	// 查車
	reset_query();

	// 付款
	$("#payment_lpr").val("");<?php /* 清除車號欄位 */ ?>
	$("#show_member_name").val("");
	$("#show_payment_lpr").val("");
	$("#show_end_date").val("");
	$("#show_next_start").val("");
	$("#show_next_end").val("");
	$("#show_amt").val("");
	$("#invoice_receiver").val("");
	$("#company_no").val("");
	$("#email").val("");
	$("#mobile").val("");
	$("#show_order_no").val("");
	$("#show_amt_detail").val("");
	$("#show_balance_time_limit_countdown").val("");

	if(tags.indexOf('payment_data') < 0 && tags.indexOf('price_data') < 0){
		clearInterval(refreshIntervalId); // 消除倒數計時timer
	}

	$("[data-items]").hide();
	$("[data-items="+tags+"]").show();
    return false;
}

<?php /* 顯示指定項目, 不修改資料 */ ?>
function show_item_without_change(tags)
{
	$("[data-items]").hide();
	$("[data-items="+tags+"]").show();
    return false;
}

// 查車牌
function check_lpr(idx)
{
	$.ajax
    	({
        	url: "<?=APP_URL?>q_pks",
        	dataType:"json",
        	type:"post",
        	data:{ "lpr" : $("#lpr_"+idx).text() },
        	success:function(jdata)
        	{
				if(!jdata)
				{
					//alertify_msg("您的愛車可能在頂樓！ 謝謝");
					alertify_msg("找不到。。謝謝");
                	return false;
				}
        		else if (jdata["pksno"] == "0")
            	{
					alertify_msg("查無資料，請鍵入正確資料");
                	return false;
            	}

				$("#show_lpr").text($("#lpr_"+idx).text());
            	//$("#show_floors").html(jdata["group_name"]+"<br/> ( 車格: " + jdata["pksno"].charAt(0) + "-" + jdata["pksno"].substr(2) +" )");
				//$("#show_floors").html(jdata["group_name"]+"<br/> ( 車格: " + jdata["pksno"] +" )");
				$("#show_floors").html(jdata["group_name"]+"<br/> ( 車格: " + jdata["pksno"].substr(-3, 3) +" )");
				$("#show_update_time").text(jdata["in_time"]);
            	$("#show_img").attr("src", "<?=SERVER_URL?>pkspic/"+jdata["pic_name"]);
            	show_item("output_pks");

				// 顯示位置圖
				if (jdata["group_id"]){
					//var groupSplit = jdata["group_id"].split('-'); // ex. B3-3
					//var floor = groupSplit[0];
					var floor = jdata["floors"];
					var x = jdata["posx"];
					var y = jdata["posy"];

					// 畫出指定位置
					AltobObject.AtsMap.drawPosition(floor, x, y);

					// show map
					$("[data-items="+floor+"]").show();
				}
    		}
    	});

	return false;
}

$(document).ready(function()
{
	<?php /* 鎖右鍵 */ ?>
	$(document).bind('contextmenu', function (e) {
	  e.preventDefault();
	});

	<?php /* 車牌模糊搜尋 */ ?>
	$("#fuzzy_search_lpr").submit(function(event)
	{
    	event.preventDefault();
		
		// 清除搜尋提示訊息
		$("#fuzzy_search_lpr_msg").text('');

		if(! $("#fuzzy_search_lpr").isValid()) return false;

        $.ajax
        ({
        	url: "<?=APP_URL?>q_fuzzy_pks",
            type: "post",
            dataType:"json",
            data: $(this).serialize(),
            success: function(jdata)
            {
				if (!jdata)
				{
					//alert("查無此車 !");
					$("#fuzzy_search_lpr_msg").text('查無此車');
					return false;
				}
				
				// 清除搜尋提示訊息
				$("#fuzzy_search_lpr_msg").text('');

				var tmp_str_array = [];

				for(idx in jdata.result)
				{
					tmp_str_array = tmp_str_array.concat(
						[
							"<tr><td id='lpr_", idx, "' style='text-align:center;vertical-align:middle;'>", jdata.result[idx]['lpr'] ,
							"</td><td id='in_time_", idx, "'style='text-align:center;vertical-align:middle;'>", jdata.result[idx]['in_time'],
							"</td><td id='pks_pic_path_", idx, "'style='text-align:center;vertical-align:middle;'><img height='57' width='150' src='", jdata.result[idx]['pks_pic_path'],  "' />",
							"</td><td style='text-align:center;vertical-align:middle;'><button class='btn btn-large btn-success' style='font-size:28px;' onclick='check_lpr(", idx, ");'>查詢</button>" ,
							"</td></tr>"
						]);
				}

				$("#carin_query_tbody").html(tmp_str_array.join(''));

				$("#carin_query_list").show();
            }
        });
    });
	
	// Custom: altob-input
  	// ********************
  	$('#fuzzy_input').keyboard({

		css : {
		  // input & preview styles
		  input          : 'ui-widget-content ui-corner-all',
		  // keyboard container - this wraps the preview area (if `usePreview` is true) and all keys
		  container      : 'ui-widget-content ui-widget ui-corner-all ui-helper-clearfix',
		  // default keyboard button state, these are applied to all keys, the remaining css options are toggled as needed
		  buttonDefault  : 'ui-state-default ui-corner-all',
		  // hovered button
		  buttonHover    : 'ui-state-hover',
		  // Action keys (e.g. Accept, Cancel, Tab, etc); this replaces the "actionClass" option
		  buttonAction   : 'ui-state-active',
		  // used when disabling the decimal button {dec} when a decimal exists in the input area
		  buttonDisabled : 'ui-state-disabled'
		},

  		display: {
  			'bksp'    : '\u2190',
  			'default' : 'ABC',
  			'accept'  : '確 認'
  		},

  		layout: 'custom',

  		customLayout: {

  			'default': [
  				'1 2 3 4 5 6 7 8 9 0 {bksp}',
  				'Q W E R T Y U I O P',
  				'A S D F G H J K L',
  				'Z X C V B N M {accept}'
  			]

  		}

  	});
	
	<?php /* 樓層平面圖 */ ?>
	AltobObject.AtsMap({
		mapInfo: {
			map1: {
				floorName: 'B1',
				canvasId: 'b1canvas',
				src: '<?=SERVER_URL?>i3/pics/b1_map.png',
				initialImageRatio: AltobObject.settings.qcar2.result_page2.B1.initialImageRatio,
				shiftLeft: AltobObject.settings.qcar2.result_page2.B1.shiftLeft,
				shiftUp: AltobObject.settings.qcar2.result_page2.B1.shiftUp
			}
		}
	});
	
	// 畫出指定位置
	AltobObject.AtsMap.drawPosition(PKS_RESULT.floors, PKS_RESULT.posx, PKS_RESULT.posy);

});
</script>
