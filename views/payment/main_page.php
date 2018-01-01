<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <title><?=STATION_NAME?>停車場</title>
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
                <a class="navbar-brand" href=""><?=STATION_NAME?>停車場</a>
            </div>

            <!-- /.navbar-top-links(左側選單) -->
            <div class="navbar-default sidebar" role="navigation">
                <div class="sidebar-nav navbar-collapse">
                    <ul class="nav" id="side-menu">
                        <li>
                            <a href="#"><i class="fa fa-user fa-fw"></i>服務項目<span class="fa arrow"></span></a>
                            <ul class="nav nav-second-level">
								<li>
                                    <a href="#" onclick="show_item('payment');">臨停繳費</a>
                                </li>
								<li>
                                    <a href="#" onclick="show_item('car_lock');">會員鎖車</a>
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
                    <h1 class="page-header"><?=STATION_NAME?>停車場</h1><?php /* 右側小表頭 */ ?>
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->

			<?php /* ----- 付款 ----- */ ?>
            <div data-items="payment" class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading"><?php /* 資料顯示區灰色小表頭 */ ?>
                            臨停繳費
                        </div>
                        <div class="panel-body">
                            <div data-rows class="row">
                                <div class="col-lg-6">
									<form id="payment_form" role="form" method="post">
                                        <div class="form-group">
                                            <label>車牌號碼</label>
											<input type="text" id="payment_lpr" class="form-control" style="text-transform:uppercase" placeholder="限英數字" autofocus required pattern="[A-Za-z0-9]*" />
                                        </div>
                                        <button type="submit" class="btn btn-default"onclick="do_payment(event);">確定</button>
                                        <button type="reset" class="btn btn-default">重填</button>
                                    </form>
                                </div>
                                <!-- /.col-lg-6 (nested) -->
                            </div>
                        </div>
                        <!-- /.panel-body -->
                    </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <?php /* ----- 付款(結束) ----- */ ?>
			
			<?php /* ----- 帳單明細顯示 ----- */ ?> 
            <div data-items="price_data" class="row" style="display:none;">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading"><?php /* 資料顯示區灰色小表頭 */ ?>
                            帳單明細
                        </div>
                        <div class="panel-body">
                            <div data-rows class="row">
                                <div class="col-lg-12">
                            	<div class="dataTable_wrapper">
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
											<th style="text-align:center;"></th>
											<th style="text-align:center;">說明</th>
											<th style="text-align:center;">結算</th>
                                        </tr>   
                                        <tr id="price_data_list" style="display:none;">
											<td data-tag="p_no" style="text-align:center;vertical-align: middle;"></td>
											<td data-tag="p_meta" style="text-align:left;vertical-align: middle;"></td>
											<td data-tag="p_result" style="text-align:left;vertical-align: middle;"></td>
                                        </tr>
                                    </thead>
                                    <tbody id="price_data_tbody" style="font-size:10px;"></tbody>
									<tr>
										<td style="text-align:left;vertical-align: middle;">
										</td>
										<td style="text-align:left;vertical-align: middle;">
										</td>
										<td style="text-align:left;vertical-align: middle;">
										</td>
									</tr>
									<tr>
										<td style="text-align:left;vertical-align: middle;">
										</td>
										<td style="text-align:center;vertical-align: middle;">
										</td>
										<td style="text-align:center;vertical-align: middle;">
										</td>
									</tr>
									<tr>
										<td style="text-align:left;vertical-align: middle;">
										</td>
										<td style="text-align:center;vertical-align: middle;">
											時間加總
                                        </td>
										<td style="text-align:center;vertical-align: middle;">
											費用加總
										</td>
									</tr>
									<tr>
										<td style="text-align:left;vertical-align: middle;">
										</td>
										<td style="text-align:center;vertical-align: middle;">
											<span id="show_amt_detail_time"></span>
										</td>
										<td style="text-align:center;vertical-align: middle;">
											<span id="show_amt_detail_price"></span>
										</td>
									</tr>
									<tr>
										<td style="text-align:left;vertical-align: middle;">
										</td>
										<td style="text-align:center;vertical-align: middle;">
										</td>
										<td style="text-align:center;vertical-align: middle;">
											<button type="button" class="btn btn-default" onclick="show_item_without_change('payment_data');">返回結帳畫面</button>
										</td>
									</tr>
                                </table>
                                </div>
                                </div>
                                <!-- /.col-lg-6 (nested) -->
                            </div>
                            <?php /* ----- 報表清單(結束) ----- */ ?> 
                        </div>
                        <!-- /.panel-body -->
                    </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-12 -->
            </div>   
            <?php /* ----- 帳單明細顯示(結束) ----- */ ?> 

			<?php /* ----- 帳單查詢結果 ----- */ ?>
            <!-- div data-items="rent_sync" class="row" style="display:none;"-->
            <div data-items="payment_data" class="row" style="display:none;">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading"><?php /* 資料顯示區灰色小表頭 */ ?>
                            帳單查詢結果
                        </div>
                        <div class="panel-body" style="margin: 0px auto;">
                            <div data-rows class="row">
                                <div class="col-lg-6" style="margin: 0px auto;">
								<form id="payment_data" role="form" method="post">
                                <table class="table table-striped table-bordered table-hover"">
                                    <tbody id="available_curr_tbody" style="font-size:14px;">
                                        <tr>
                                            <td style="text-align:right;vertical-align: middle;" width="20%">車號</td>
                                            <td id="show_payment_lpr" style="text-align:left;vertical-align:middle;text-transform:uppercase;"></td>
                                        </tr>
                                        <tr>
                                            <td style="text-align:right;vertical-align: middle;">進場時間</td>
                                            <td id="show_in_time" style="text-align:left;vertical-align: middle;"></td>
                                        </tr>
                                        <tr>
                                            <td style="text-align:right;vertical-align: middle;">結算時間</td>
                                            <td id="show_balance_time" style="text-align:left;vertical-align: middle;"></td>
                                        </tr>
										<tr>
                                            <td style="text-align:right;vertical-align: middle;">停車時間</td>
                                            <td id="show_amt_detail" style="text-align:left;vertical-align: middle;"></td>
                                        </tr>
                                        <tr>
                                            <td style="text-align:right;vertical-align: middle;">金額 (NTD)</td>
                                            <td id="show_amt" style="text-align:left;vertical-align: middle;"></td>
                                        </tr>
                                        <tr class="form-group"style="display:none;">
                                            <td style="text-align:right;vertical-align: middle;">發票載具 <br/>(手機條碼)</td>
											<td style="text-align:left;vertical-align: middle;">
											<input type="text" id="invoice_receiver" class="form-control" placeholder="如不要發票請留空白"
												data-validation="custom"
												data-validation-regexp="^$|^(?=.{7}$)([A-Za-z0-9]+)$|^(?=.{8}$)\u002F([A-Za-z0-9]+)$"
												data-validation-error-msg="請輸入正確載具<br/>格式： / + 7碼 <br/>(共8碼)"
												/>
                                            </td>
                                        </tr>
										<tr class="form-group" style="display:none;">
											<td style="text-align:right;vertical-align: middle;"></td>
											<td style="text-align:left;vertical-align: middle; color: red;">
												若發票載具留空白，發票將自動送個社福團體
											</td>
										</tr>
										<tr class="form-group">
                                            <td style="text-align:right;vertical-align: middle;">公司統編</td>
											<td style="text-align:left;vertical-align: middle;">
											<input type="tel" id="company_no" class="form-control" placeholder="如不打統編請留空白"
												data-validation="custom"
												data-validation-optional="true"
												data-validation-regexp="^(?=.{8}$)([0-9]+)$"
												data-validation-error-msg="請輸入正確統編<br/>例如：80682490"
												data-validation-error-msg-container="#company_no_error_msg"
												/>
												<span id="company_no_error_msg"></span>
                                            </td>
                                        </tr>
										
										<!--tr class="form-group">
											<td style="text-align:right;vertical-align: middle;">TEST</td>
											<td style="text-align:left;vertical-align: middle;">
												<select multiple="multiple" size="5" data-validation="length" data-validation-length="min2">
												  <option>A</option>
												  <option>B</option>
												  <option>C</option>
												  <option>D</option>
												  <option>E</option>
												</select>
											</td>
										</tr-->
										
										<tr class="form-group">
                                            <td style="text-align:right;vertical-align: middle;">電子信箱</td>
											<td style="text-align:left;vertical-align: middle;">
											<input type="email" id="email" class="form-control" placeholder="發票將寄信通知"
												data-validation="email"
												data-validation-optional="true"
												data-validation-error-msg="請輸入正確信箱<br/>例如：altob@gmail.com"
												data-validation-error-msg-container="#email_error_msg"
												/>
												<span id="email_error_msg"></span>
                                            </td>
                                        </tr>
										<tr class="form-group">
                                            <td style="text-align:right;vertical-align: middle;">手機號碼</td>
											<td style="text-align:left;vertical-align: middle;">
											<input type="tel" id="mobile" class="form-control" placeholder="發票將寄簡訊通知"
												data-validation="custom"
												data-validation-optional="true"
												data-validation-regexp="^(?=.{10}$)09([0-9]+)$"
												data-validation-error-msg="請輸入正確手機號碼<br/>例如：0912345678"
												data-validation-error-msg-container="#mobile_error_msg"
												/>
												<span id="mobile_error_msg"></span>
                                            </td>
                                        </tr>
										<tr>
                                            <td style="text-align:right;vertical-align: middle;">訂單编號</td>
                                            <td id="show_order_no" style="text-align:left;vertical-align: middle;"></td>
                                        </tr>
										<tr>
                                            <td style="text-align:right;vertical-align: middle;">有效期限</td>
                                            <td id="show_balance_time_limit_countdown" style="text-align:left;vertical-align: middle; color: red;"></td>
                                        </tr>
										<!--tr>
                                            <td style="text-align:right;vertical-align: middle;">※</td>
                                            <td id="show_balance_time_limit_countdown" style="text-align:left;vertical-align: middle; color: red;"></td>
                                        </tr-->
                                        <tr>
                                            <td style="text-align:right;vertical-align: middle;">
												<button type="button" class="btn btn-default" onclick="transfer_money(event);">開始付款</button>
                                            </td>
                                            <td style="text-align:left;vertical-align: middle;">
												<button type="button" class="btn btn-default" onclick="show_item('price_data');">查看明細</button>
												
												<button type="button" class="btn btn-default" onclick="show_item('payment');">取消</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
								</form>
                                </div>
                                <!-- /.col-lg-6 (nested) -->
                            </div>
                            <!-- /.row (nested) -->
                        </div>
                        <!-- /.panel-body -->
                    </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <?php /* ----- 帳單查詢結果(結束) ----- */ ?>
			
			
			
			
            <?php /* ----- 鎖車作業 ----- */ ?>
            <div data-items="car_lock" class="row" style="display:none;">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading"><?php /* 資料顯示區灰色小表頭 */ ?>
                            會員鎖車
                        </div>
                        <div class="panel-body">
                            <div data-rows class="row">
                                <div class="col-lg-6">
                                        <div class="form-group">  
                                        	<form id="form_lock" method="post">
                                            <label>請鍵入車牌號碼</label>
                                            <input type="text" id="lpr_lock" class="form-control" style="text-transform:uppercase;" placeholder="限英數字" autofocus required pattern="[A-Za-z0-9]*" /><br />
                                            <label>密碼(第一次與車號相同)</label>
                                            <input type="password" id="pswd_lock" class="form-control" style="text-transform:uppercase;" autofocus required pattern="[A-Za-z0-9]*" /><br />
                                        	<button type="submit" id="qcar_lock" class="btn btn-default">查詢</button>
                                        	<button type="submit" id="change_pswd_lock" class="btn btn-default">更改密碼</button>
                                        	<button type="reset" class="btn btn-default">清除資料</button>
                                            </form>
                                        </div>
                                </div>
                                <!-- /.col-lg-6 (nested) -->
                            </div>
                        </div>
                        <!-- /.panel-body -->
                    </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <?php /* ----- 鎖車(結束) ----- */ ?>  
                                                                
            <?php /* ----- 更改鎖車密碼 ----- */ ?>
            <div data-items="pswd_lock" class="row" style="display:none;">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading"><?php /* 資料顯示區灰色小表頭 */ ?>
                            更改鎖車密碼
                        </div>
                        <div class="panel-body">
                            <div data-rows class="row">
                                <div class="col-lg-6">
                                        <div class="form-group">  
                                        	<form id="form_new_pswd" method="post">
                                            <label>新密碼</label>
                                            <input type="text" id="new_pswd1" class="form-control" style="text-transform:uppercase;" placeholder="限英數字" autofocus required pattern="[A-Za-z0-9]*" /><br />
                                            <label>再鍵一次密碼</label>
                                            <input type="password" id="new_pswd2" class="form-control" style="text-transform:uppercase;" placeholder="限英數字" autofocus required pattern="[A-Za-z0-9]*" /><br />
                                        	<button type="submit" class="btn btn-default">儲存</button>
                                        	<button type="button" class="btn btn-default">取消</button>
                                            </form>
                                        </div>
                                </div>
                                <!-- /.col-lg-6 (nested) -->
                            </div>
                        </div>
                        <!-- /.panel-body -->
                    </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <?php /* ----- 更改密碼(結束) ----- */ ?>   
            
            <?php /* ----- 鎖車與解鎖 ----- */ ?>
            <div data-items="status_lock" class="row" style="display:none;">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading"><?php /* 資料顯示區灰色小表頭 */ ?>
                            鎖車/解鎖
                        </div>
                        <div class="panel-body">
                            <div data-rows class="row">
                                <div class="col-lg-6">
                                        <div class="form-group">  
                                        	<form id="form_active_lock" method="post">
                                            <label><span id="lpr_on_off"></span>: [<span id="lock_on_off">無</span>鎖車]</label><br />
                                        	<input type="button" id="lock_unlock" class="btn btn-default" value="鎖車" />
                                        	<input type="button" id="lock_back" class="btn btn-default" value="結束" />
                                            </form>
                                        </div>
                                </div>
                                <!-- /.col-lg-6 (nested) -->
                            </div>
                        </div>
                        <!-- /.panel-body -->
                    </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <?php /* ----- 鎖車與解鎖(結束) ----- */ ?>   
			
			

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
	<!-- alertify -->
	<script src="<?=WEB_LIB?>js/moment.min.js"></script> 
	
	
	<!-- md5 -->
	<script src="<?=WEB_LIB?>js/md5.min.js"></script> 
	

    <!-- Custom Theme JavaScript -->
    <script src="<?=BOOTSTRAPS?>dist/js/sb-admin-2.js"></script>
    <div id="works" style="display:none;"></div><?php /* 作為浮動顯示區之用 */ ?>
</body>
</html>

<script>

<?php /* alertify function */ ?>
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

var refreshIntervalId = 0; // timer id

<?php /* 顯示指定項目 */ ?>
function show_item(tags)
{
	$("#payment_lpr").val("");<?php /* 清除車號欄位 */ ?>
	$("#show_in_time").val("");
	$("#show_balance_time").val("");
	$("#show_amt_detail").val("");
	$("#show_amt").val("");
	$("#invoice_receiver").val("");<?php /* 清除載具欄位 */ ?>
	$("#show_order_no").val("");
	//$("#show_balance_time_limit").val("");
	$("#show_balance_time_limit_countdown").val("");
	
	if(tags.indexOf('payment_data') < 0 && tags.indexOf('price_data') < 0){
		clearInterval(refreshIntervalId); // 消除倒數計時timer
		//console.log("clearInterval..");
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

// 付款
function do_payment(event)
{
	event.preventDefault();

	if ($("#payment_lpr").val() == "")
    {
		alertify_error("請填寫車號");
       	return false;
    }

	$.ajax
	({
		url: "<?=APP_URL?>payment_lpr",
		type: "post",
		dataType:"text",
		data:{
			"payment_lpr": $("#payment_lpr").val()
		},
		success: function(result)
		{
			if (/^[\],:{}\s]*$/.test(result.replace(/\\["\\\/bfnrtu]/g, '@').
			replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, ']').
			replace(/(?:^|:|,)(?:\s*\[)+/g, ''))) {
				//the json is ok
			}else{
				//the json is not ok
				alertify_error("查無資料，請鍵入正確資料 (回傳資料有誤)");
				return false;
			}
			
			var jdata = JSON.parse(result);
			//console.log(jdata);
			if(!jdata)
			{
            	alertify_error("查無資料，請鍵入正確資料");
                return false;
            }
			
			if(jdata.member_no > 0)
			{
				alertify_log("會員無須繳款，謝謝");
                return false;
			}
			
			if(jdata.amt <= 0)
			{
            	alertify_log("目前無須繳款，謝謝");
                return false;
            }
			
			if(jdata.amt > 1000)
			{
            	alertify_log("[ 代碼:001 ]<br/>請到管理室繳費, 謝謝");
                return false;
            }

			// variables for time units
			var days, hours, minutes, seconds;
			 
			// get tag element
			var countdown = $("#show_balance_time_limit_countdown");
			
			//var countdown_offset_ms = 60*5*1000; // 比資料庫早5分鐘timeout
			var duration = moment.duration({minutes: 5}); // 比資料庫早5分鐘timeout
			
			// set the date we're counting down to
			var target_date = moment(jdata.balance_time_limit).subtract(duration);
			//var target_date = new Date(jdata.balance_time_limit).getTime();
			
			//console.log(target_date.diff(moment(), 'seconds'));
			
			// update the tag with id "countdown" every 1 second
			refreshIntervalId = setInterval(function () {
			    // find the amount of "seconds" between now and target
				var seconds_left = target_date.diff(moment(), 'seconds');
				
				//console.log(seconds_left);
				
				//var current_date = new Date().getTime();
			    //var seconds_left = (target_date - current_date - countdown_offset_ms) / 1000;
			
				if(seconds_left <= 0){
					clearInterval(refreshIntervalId);
					// reload page
					location.reload();
				}
			 
			    // do some time calculations
			    days = parseInt(seconds_left / 86400);
			    seconds_left = seconds_left % 86400;
			     
			    hours = parseInt(seconds_left / 3600);
			    seconds_left = seconds_left % 3600;
			     
			    minutes = parseInt(seconds_left / 60);
			    seconds = parseInt(seconds_left % 60);

				if(minutes < 10){
					minutes = '0' + minutes;
				}
				if(seconds < 10){
					seconds = '0' + seconds;
				}
			    // format countdown string + set tag value
				countdown.text(['還剩 ', minutes, ' : ', seconds, ' 有效'].join(''));
			}, 1000);
			
			$("#show_payment_lpr").text(jdata.lpr);
            $("#show_in_time").text(jdata.in_time);
			$("#show_balance_time").text(jdata.balance_time);
			$("#show_amt_detail").text([jdata.bill_days, ' 天 : ', jdata.bill_hours, ' 小時 : ', jdata.bill_mins, ' 分鐘'].join(''));
            $("#show_amt").text([jdata.amt, ' 元'].join(''));
			$("#show_order_no").text(jdata.order_no);
			//$("#show_balance_time_limit").text(new Date(target_date - countdown_offset_ms));
			show_item("payment_data");
			
			// parse price detail
			$("#price_data_tbody").html("");	
			
			<?php /* 明細頁面產生 (開始) */ ?>
			
			// A. 依r_no 分群, 暫存到 tmp_r_no_array
			var tmp_r_no_array = [];
			for(lv1 in jdata.price_detail)
            {        
				if (lv1 == 0) { continue; }
				var today = jdata.price_detail[lv1];
				for(lv2 in today)
				{
					if(lv2.match(/\u003A/)){ // 取出有時間的部份
						var detail = today[lv2];
						if(!(detail.r_no in tmp_r_no_array)){
							tmp_r_no_array[detail.r_no] = [];
						}
						tmp_r_no_array[detail.r_no].push([detail.r_no, '_', lv1, '_', lv2].join(''));
					}
				}
			}
			//console.log('tmp_r_no_array: ' + tmp_r_no_array);
			// B. 將 tmp_r_no_array 解析, 產生顯示用的 price_result_array
			var price_result_array = [];
			var last_r_no_keys_array = [];
			var check_p = 0;
			for(r_no in tmp_r_no_array)
			{
				var r_no_array = tmp_r_no_array[r_no].sort(); // 依r_no 排序
				//console.log(r_no + ' length: ' + r_no_array.length);
				
				for(key in r_no_array)
				{
					var keys = r_no_array[key].split('_');
					var r_no = keys[0];
					var lv1 = keys[1];
					var lv2 = keys[2];
					var time_str = [lv1, ' ', lv2].join('');
					var detail = jdata.price_detail[lv1][lv2];
					var detail_p0_price = jdata.price_detail[lv1].p0;
					var detail_limit0 = jdata.price_detail[lv1].limit0;
					var detail_free0_min = jdata.price_detail[lv1].free0_min;
									
					if(detail.p > 0){
						check_p += detail.p;
						var before_keys = last_r_no_keys_array.pop(); //r_no_array[key - 1].split('_');
						
						var before_r_no = before_keys[0];
						var before_lv1 = before_keys[1];
						var before_lv2 = before_keys[2];
						var before_time_str = [before_lv1, ' ', before_lv2].join('');
						var before_detail = jdata.price_detail[before_lv1][before_lv2];
						
						// create result
						var data_p_desc = '';
						var data_p_time = '';
						var data_p_time_desc = ['*時段 ', before_time_str, '<br/>至 ', time_str].join('');
						var data_p_price_desc = [detail.p, ' 元'].join('');
						
						// p_desc
						if(detail.status == 1){
							data_p_desc = ['費率：每日最高收費上限 ', detail_limit0, ' 元，已達當日上限'].join(''); // '每日最高收費上限 150元';
						}else{
							data_p_desc = [' 每小時 ', 2 * detail_p0_price, ' 元，前 ', detail_free0_min, ' 分鐘免費。'].join(''); // '費率：每小時 20元';
						}
						
						// p_time
						var detail_part = [];
						if('h' in detail && detail.h > 0){
							detail_part.push(detail.h, ' 小時 ');
						}
						if('i' in detail && detail.i > 0){
							detail_part.push(detail.i, ' 分鐘');
						}
						//if(detail.p < before_detail_p2_price){detail_part.push(' (', r_no, ') ');}
						data_p_time = detail_part.join('');
						
						if(price_result_array.length > 0){
							if(r_no == price_result_array[price_result_array.length - 1].r_no){
								// 與上一筆結算為同一價錢週期時, 更新上一筆結算
								var last_result = price_result_array[price_result_array.length - 1];
								last_result.p_desc = '每日最高收費上限 150元';
								last_result.p_time = [last_result.p_time, '接續<br/><br/>', data_p_time].join('');;
								last_result.p_time_desc = [last_result.p_time_desc, ' 接續<br/><br/>', data_p_time_desc].join('');
								last_result.p_price_desc = [last_result.p_price_desc, ' + ', data_p_price_desc].join('');
								// push last
								last_r_no_keys_array.push(keys);
								continue;
							}
						}
						
						// 與上一筆結算不同價錢週期, 新增一筆結算
						var data = [];
						data.r_no = r_no;
						data.p_desc = data_p_desc;
						data.p_time = data_p_time;
						data.p_time_desc = data_p_time_desc;
						data.p_price_desc = data_p_price_desc;
						price_result_array.push(data);
						// push last
						last_r_no_keys_array.push(keys);
					}else{
						// push last
						last_r_no_keys_array.push(keys);
					}
				}
			}
			
			// C. 根據 price_result_array, 產生頁面顯示
			var seq = 0;
			for(key in price_result_array)
			{	
				var result = price_result_array[key];
				var meta_0_str = ++seq;
				$("#price_data_list>[data-tag=p_no]").text(meta_0_str);
				$("#price_data_list>[data-tag=p_meta]").html(result.p_time_desc);
				$("#price_data_list>[data-tag=p_result]").html(result.p_time);
				$("<tr data-day='day'>"+$("#price_data_list").html()+"</tr>").appendTo("#price_data_tbody"); 
				$("#price_data_list>[data-tag=p_no]").text("");
				$("#price_data_list>[data-tag=p_meta]").html(result.p_desc);
				$("#price_data_list>[data-tag=p_result]").html(result.p_price_desc);
				$("<tr data-day='day' style='color: red;'>"+$("#price_data_list").html()+"</tr>").appendTo("#price_data_tbody"); 
			}
			var bill_time_part = ['共 '];
			if('bill_days' in jdata && jdata.bill_days > 0){
				bill_time_part.push(jdata.bill_days, ' 天 : ');
			}
			if('bill_hours' in jdata && jdata.bill_hours > 0){
				bill_time_part.push(jdata.bill_hours, ' 小時 : ');
			}
			if('bill_mins' in jdata && jdata.bill_mins > 0){
				bill_time_part.push(jdata.bill_mins, ' 分鐘');
			}
			$("#show_amt_detail_time").text(bill_time_part.join(''));
			$("#show_amt_detail_price").text([jdata.amt, ' 元'].join(''));
			
			<?php /* 明細頁面產生 (結束) */ ?>
			
		}
		
	});
}

// 開啟轉帳畫面
function transfer_money(event)
{
	event.preventDefault();
	
	if(! $("#payment_data").isValid()) return false;
	
	if($("#email").val() == '' && $("#mobile").val() == '')
	{
		alertify_error("請至少提供一項發票通知方式<br/>1. 電子信箱 <br/>2. 或 手機號碼<br/><br/>謝謝!!");
		return false;
	}

	if (! confirm("開始結帳嗎 ?"))	return false;
	
	// Create Base64 Object
	var Base64 = {_keyStr:"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",encode:function(e){var t="";var n,r,i,s,o,u,a;var f=0;e=Base64._utf8_encode(e);while(f<e.length){n=e.charCodeAt(f++);r=e.charCodeAt(f++);i=e.charCodeAt(f++);s=n>>2;o=(n&3)<<4|r>>4;u=(r&15)<<2|i>>6;a=i&63;if(isNaN(r)){u=a=64}else if(isNaN(i)){a=64}t=t+this._keyStr.charAt(s)+this._keyStr.charAt(o)+this._keyStr.charAt(u)+this._keyStr.charAt(a)}return t},decode:function(e){var t="";var n,r,i;var s,o,u,a;var f=0;e=e.replace(/[^A-Za-z0-9\+\/\=]/g,"");while(f<e.length){s=this._keyStr.indexOf(e.charAt(f++));o=this._keyStr.indexOf(e.charAt(f++));u=this._keyStr.indexOf(e.charAt(f++));a=this._keyStr.indexOf(e.charAt(f++));n=s<<2|o>>4;r=(o&15)<<4|u>>2;i=(u&3)<<6|a;t=t+String.fromCharCode(n);if(u!=64){t=t+String.fromCharCode(r)}if(a!=64){t=t+String.fromCharCode(i)}}t=Base64._utf8_decode(t);return t},_utf8_encode:function(e){e=e.replace(/\r\n/g,"\n");var t="";for(var n=0;n<e.length;n++){var r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r)}else if(r>127&&r<2048){t+=String.fromCharCode(r>>6|192);t+=String.fromCharCode(r&63|128)}else{t+=String.fromCharCode(r>>12|224);t+=String.fromCharCode(r>>6&63|128);t+=String.fromCharCode(r&63|128)}}return t},_utf8_decode:function(e){var t="";var n=0;var r=c1=c2=0;while(n<e.length){r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r);n++}else if(r>191&&r<224){c2=e.charCodeAt(n+1);t+=String.fromCharCode((r&31)<<6|c2&63);n+=2}else{c2=e.charCodeAt(n+1);c3=e.charCodeAt(n+2);t+=String.fromCharCode((r&15)<<12|(c2&63)<<6|c3&63);n+=3}}return t}}

	var order_no = $("#show_order_no").text() == '' ? '0' : $("#show_order_no").text();
    var invoice_receiver = $("#invoice_receiver").val() == '' ? '0' : $("#invoice_receiver").val();
	var company_no = $("#company_no").val() == '' ? '0' : $("#company_no").val();
	var email = $("#email").val() == '' ? '0' : Base64.encode($("#email").val()).slice(0, -1); // remove base64 '=' for URI rule
	var mobile = $("#mobile").val() == '' ? '0' : $("#mobile").val();
		
    webatm = window.open(
		["<?=APP_URL?>transfer_money/", 
			order_no, "/", 
			encodeURI(invoice_receiver), "/", 
			encodeURI(company_no), "/", 
			email, "/",
			mobile, "/"
			].join('')
		, "_self");
	/*
	location.replace(
		["<?=APP_URL?>transfer_money/", 
			order_no, "/", 
			encodeURI(invoice_receiver), "/", 
			encodeURI(company_no), "/", 
			email, "/",
			mobile, "/"
			].join('')
		);
	*/

	// window.top.close(); // 關視窗
    // setTimeout(function(){ webatm.close(); }, 3000);
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
	
	
	
	
	
	// ----- 鎖車密碼更新 -----
	$("#form_new_pswd").submit(function(event)
	{                  
    	event.preventDefault(); 
        
        $("#new_pswd1").val($("#new_pswd1").val().toUpperCase());
        $("#new_pswd2").val($("#new_pswd2").val().toUpperCase());
		if ($("#new_pswd1").val() != $("#new_pswd2").val() || $("#new_pswd1").val() == "" || $("#new_pswd2").val() == "")
    	{
     		alert("密碼不符規定, 請重新輸入 !");
        	return false;
    	}                 
                        
        // 查詢目前有無鎖車狀況
		$.ajax
    	({                         
        	url:"<?=APP_URL?>change_pswd",
        	dataType:"text",
        	type:"post",
        	data:
            	{	"lpr":$("#lpr_lock").val(),
            		"new_pswd":$("#new_pswd1").val()
            	},
        	success:function(jdata)
        	{                           
        		alert("密碼更新完成 !");
            	$("#pswd_lock").val("");
            	show_item("car_lock");     
    		}                                                                          
    	}); 
    });
    
    
    // ----- 鎖車 -----
	$("#form_lock").submit(function(event)
	{                  
    	event.preventDefault(); 
        submit_id = $(document.activeElement).prop('id');
        
		if ($("#lpr_lock").val() == "" || $("#pswd_lock").val() == "")
    	{
     		alert("請填寫車號及密碼");
        	return false;
    	}                 
        
        $("#lpr_lock").val($("#lpr_lock").val().toUpperCase());
        $("#pswd_lock").val($("#pswd_lock").val().toUpperCase()); 
        
        // 查詢目前有無鎖車狀況
		$.ajax
    	({                         
        	url: "<?=APP_URL?>security_action/"+$("#lpr_lock").val()+"/"+md5($("#pswd_lock").val().toUpperCase())+"/2", 
        	dataType:"json",
        	type:"post",
        	data:{},
        	success:function(jdata)
        	{
        		if (jdata["result_code"] == "FAIL")
            	{
            		alert("車號或密碼錯誤");
                	return false;
            	}
                  
                // 更改密碼, 進入下一個畫面
        		if (submit_id == "change_pswd_lock")
        		{                       
                	$("#new_pswd1").val("");
                	$("#new_pswd2").val("");
           			show_item("pswd_lock");
            		return true;
        		} 
                                                           
                $("#lpr_on_off").text($("#lpr_lock").val());
                lock_on_off = jdata["result"][0]["result"];
                if (lock_on_off == "ON")
                {
                	$("#lock_on_off").text("已");  
                	$("#lock_unlock").val("解鎖");  
                }                           
                else
                {
                	$("#lock_on_off").text("無"); 
                	$("#lock_unlock").val("鎖車");    
                }
            	show_item("status_lock");     
    		}                                                                          
    	}); 
    	return false;
    });
            
           
    // ----- 鎖車或解鎖 -----
	$("#lock_unlock").click(function()
	{                   
    	lock_str = $("#lock_unlock").val();
                          
    	if (!confirm("確定"+lock_str+"嗎 ?")) return false;
            
    	lock_val = lock_str == "解鎖" ? "0" : "1";
        
        // 查詢目前有無鎖車狀況
		$.ajax
    	({                         
        	url: "<?=APP_URL?>security_action/"+$("#lpr_lock").val()+"/"+md5($("#pswd_lock").val())+"/"+lock_val, 
        	dataType:"json",
        	type:"post",
        	data:{},
        	success:function(jdata)
        	{           
                if (lock_val == "1")
                {
                	$("#lock_on_off").text("已");  
                	$("#lock_unlock").val("解鎖");  
                }                           
                else
                {
                	$("#lock_on_off").text("無"); 
                	$("#lock_unlock").val("鎖車");    
                }
            	show_item("status_lock");     
    		}                                                                          
    	}); 
    });     
    
    
    // ----- 鎖車結東 -----
	$("#lock_back,#end_qcar").click(function()
	{                                  
    	window.location.reload(true);        
    });	

	
	
	
	
	
	
});
</script>
