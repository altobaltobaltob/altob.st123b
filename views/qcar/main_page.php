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
                <a class="navbar-brand" href="">歐特儀自動化服務</a>
            </div>

            <!-- /.navbar-top-links(左側選單) -->
            <div class="navbar-default sidebar" role="navigation">
                <div class="sidebar-nav navbar-collapse">
                    <ul class="nav" id="side-menu" style="font-size:18px;">
                        <li>
                            <a href="#"><i class="fa fa-user fa-fw"></i>服務項目<span class="fa arrow"></span></a>
                            <ul class="nav nav-second-level">
                                <li>
                                    <a href="#" onclick="show_item('homepage');">首頁</a>
                                </li>
                                <li>
                                    <a href="#" onclick="show_item('input_lpr');">查詢車輛</a>
                                </li>
                                <li>
                                    <a href="#" onclick="show_item('payment');">月租轉帳</a>
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
                    <h1 class="page-header">歐特儀自動化服務機</h1><?php /* 右側小表頭 */ ?>
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
                                            <td style="text-align:center;font-size:64px;"><input type="button" style="border-bottom-left-radius: 12px;border-bottom-right-radius: 12px;border-top-left-radius: 12px;border-top-right-radius: 12px;" value="查車" onclick="show_item('input_lpr');" /></td>
                                            <td style="text-align:center;font-size:64px;"><input type="button" style="border-bottom-left-radius: 12px;border-bottom-right-radius: 12px;border-top-left-radius: 12px;border-top-right-radius: 12px;" value="月租轉帳" onclick="show_item('payment');" /></td>
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
            <?php /* ----- 查車作業 ----- */ ?>
            <div data-items="input_lpr" class="row" style="display:none;">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading"><?php /* 資料顯示區灰色小表頭 */ ?>
                            車位查詢
                        </div>
                        <div class="panel-body">
							<div data-rows class="row" style="font-size:20px;">
								<div class="col-lg-6">
									<form id="fuzzy_search_lpr" role="form" method="post">
											<div class="form-group">
												<input type="text" id="fuzzy_input" name="fuzzy_input" class="form-control" style="text-transform:uppercase;height:56px;"
													placeholder="請輸入車牌關鍵字 ( 3 到 7 碼 ex. 111)"
													autofocus required pattern="[A-Za-z0-9]*"
													data-validation="length"
													data-validation-length="3-7"
													data-validation-error-msg="請輸入車牌關鍵字 ( 3 到 7 碼  ex. 111)">
											</div>
											<button type="submit" class="btn btn-default">搜尋車牌</button>
											<button type="reset" class="btn btn-default" onclick="show_item('input_lpr');">清除</button>
									</form>
								</div>
							</div>

							<br/>

							<div id="carin_query_list" class="dataTable_wrapper" style="display:none; font-size:20px;">
                                <table id="lpr_query_list" class="table table-striped table-bordered table-hover">
                                <thead>
                                        <tr>
											<th style="text-align:center;">車號</th>
                                            <th style="text-align:center;">進場時間</th>
											<th style="text-align:center;">在席照片</th>
											<th style="text-align:center;">功能</th>
                                        </tr>
    							</thead>
    							<tbody id="carin_query_tbody" style="font-size:18px;"></tbody>
                                </table>
                            </div><?php /* ----- end of dataTable_wrapper ----- */?>
                        </div>
                        <!-- /.panel-body -->
                    </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <?php /* ----- 查詢作業(結束) ----- */ ?>

			<?php /* ----- 查詢結果 ----- */ ?>
            <!-- div data-items="rent_sync" class="row" style="display:none;"-->
            <div data-items="output_pks" class="row" style="display:none;">
                <div class="col-lg-4">
                    <div class="panel panel-default">
                        <div class="panel-heading"><?php /* 資料顯示區灰色小表頭 */ ?>
                            查車結果
                        </div>
                        <div class="panel-body" style="margin: 0px auto;">
                            <div data-rows class="row">
                                <div class="col-lg-12" style="margin: 0px auto;">
                                <table class="table table-striped table-bordered table-hover"">
                                    <tbody id="available_curr_tbody" style="font-size:16px;">
                                        <tr><?php /* 提供即時車位資訊 */ ?>
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
                                        <tr>
                                            <td colspan="2" style="text-align:center;vertical-align: middle;">
                                            	<img id="show_img" height="180" width="260" />
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" style="text-align:center;vertical-align: middle;">
                                        	<!-- button type="button" class="btn btn-default" onclick="show_item('input_lpr');">結束查詢</button-->
                                        	<button type="button" class="btn btn-default" onclick="show_item('input_lpr');">結束查詢</button>
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
                <!-- /.col-lg-12 -->


			<div data-items="B1" class="col-lg-8" style="display:none;">
                  <div class="col-lg-12"><div class="panel panel-default"><div class="panel-heading"><span>停車位置 : B1 樓層</span></div>
                  <div class="panel-body"><canvas id="b1canvas"></canvas></div></div></div>
            </div>
			<div data-items="F1" class="col-lg-8" style="display:none;">
                  <div class="col-lg-12"><div class="panel panel-default"><div class="panel-heading"><span>停車位置 : F1 樓層</span></div>
                  <div class="panel-body"><canvas id="f1canvas"></canvas></div></div></div>
            </div>
			<div data-items="F2" class="col-lg-8" style="display:none;">
                  <div class="col-lg-12"><div class="panel panel-default"><div class="panel-heading"><span>停車位置 : F2 樓層</span></div>
                  <div class="panel-body"><canvas id="f2canvas"></canvas></div></div></div>
            </div>
			<div data-items="F3" class="col-lg-8" style="display:none;">
                  <div class="col-lg-12"><div class="panel panel-default"><div class="panel-heading"><span>停車位置 : F3 樓層</span></div>
                  <div class="panel-body"><canvas id="f3canvas"></canvas></div></div></div>
            </div>
			<div data-items="F4" class="col-lg-8" style="display:none;">
                  <div class="col-lg-12"><div class="panel panel-default"><div class="panel-heading"><span>停車位置 : F4 樓層</span></div>
                  <div class="panel-body"><canvas id="f4canvas"></canvas></div></div></div>
            </div>
			<div data-items="F5" class="col-lg-8" style="display:none;">
                  <div class="col-lg-12"><div class="panel panel-default"><div class="panel-heading"><span>停車位置 : F5 樓層</span></div>
                  <div class="panel-body"><canvas id="f5canvas"></canvas></div></div></div>
            </div>
			<div data-items="F6" class="col-lg-8" style="display:none;">
                  <div class="col-lg-12"><div class="panel panel-default"><div class="panel-heading"><span>停車位置 : F6 樓層</span></div>
                  <div class="panel-body"><canvas id="f6canvas"></canvas></div></div></div>
            </div>
			<div data-items="F7" class="col-lg-8" style="display:none;">
                  <div class="col-lg-12"><div class="panel panel-default"><div class="panel-heading"><span>停車位置 : F7 樓層</span></div>
                  <div class="panel-body"><canvas id="f7canvas"></canvas></div></div></div>
            </div>

		</div>



			<?php /* ----- 付款 ----- */ ?>
            <div data-items="payment" class="row" style="display:none;">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading"><?php /* 資料顯示區灰色小表頭 */ ?>
                            繳月租
                        </div>
                        <div class="panel-body">
                            <div data-rows class="row">
                                <div class="col-lg-6">
									<form id="payment_form" role="form" method="post">
                                        <div class="form-group">
											<input type="text" id="payment_lpr" class="form-control" style="text-transform:uppercase;height:56px;"
												placeholder="請輸入完整車牌號碼 ex. ABC123" autofocus required pattern="[A-Za-z0-9]*" />
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
                                            <td style="text-align:right;vertical-align: middle;" width="20%">會員姓名</td>
                                            <td id="show_member_name" style="text-align:left;vertical-align:middle;text-transform:uppercase;"></td>
                                        </tr>
                                        <tr>
                                            <td style="text-align:right;vertical-align: middle; color: blue;" width="20%">車號</td>
                                            <td id="show_payment_lpr" style="text-align:left;vertical-align:middle;text-transform:uppercase;"></td>
                                        </tr>
                                        <tr>
                                            <td style="text-align:right;vertical-align: middle;">到期日</td>
                                            <td id="show_end_date" style="text-align:left;vertical-align: middle;"></td>
                                        </tr>
										<tr>
                                            <td style="text-align:right;vertical-align: middle;">備註</td>
                                            <td id="show_remarks" style="text-align:left;vertical-align: middle;"></td>
                                        </tr>
										<tr>
                                            <td style="text-align:right;vertical-align: middle; color: red;">金額 (NTD)</td>
                                            <td id="show_amt" style="text-align:left;vertical-align: middle;"></td>
                                        </tr>
                                        <tr>
                                            <td style="text-align:right;vertical-align: middle; color: blue;">次期起始日</td>
                                            <td id="show_next_start" style="text-align:left;vertical-align: middle;"></td>
                                        </tr>
										<tr>
                                            <td style="text-align:right;vertical-align: middle; color: blue;">次期到期日</td>
                                            <td id="show_next_end" style="text-align:left;vertical-align: middle;"></td>
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
											<input type="tel" id="company_no" class="form-control" placeholder="如不打統編請留空白" style="height:56px;"
												data-validation="custom"
												data-validation-optional="true"
												data-validation-regexp="^(?=.{8}$)([0-9]+)$"
												data-validation-error-msg="請輸入正確統編<br/>例如：80682490"
												data-validation-error-msg-container="#company_no_error_msg"
												/>
												<span id="company_no_error_msg"></span>
                                            </td>
                                        </tr>
										<tr class="form-group">
                                            <td style="text-align:right;vertical-align: middle;">電子信箱</td>
											<td style="text-align:left;vertical-align: middle;">
											<input type="email" id="email" class="form-control" placeholder="發票將寄信通知" style="height:56px;"
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
											<input type="tel" id="mobile" class="form-control" placeholder="發票將寄簡訊通知" style="height:56px;"
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
                                        <tr>
                                            <td style="text-align:right;vertical-align: middle;">
												<button type="button" class="btn btn-default" onclick="transfer_money(event);">開始付款</button>
                                            </td>
                                            <td style="text-align:left;vertical-align: middle;">
												<!--button type="button" class="btn btn-default" onclick="show_item('price_data');">查看明細</button-->
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
					alertify_msg("您的愛車可能在頂樓！ 謝謝");
                	return false;
				}
        		else if (jdata["pksno"] == "0")
            	{
					alertify_msg("查無資料，請鍵入正確資料");
                	return false;
            	}

				$("#show_lpr").text($("#lpr_"+idx).text());
            	$("#show_floors").html(jdata["group_name"]+"<br/> ( 車格: " + jdata["pksno"].charAt(0) + "-" + jdata["pksno"].substr(2) +" )");
				$("#show_update_time").text(jdata["in_time"]);
            	$("#show_img").attr("src", "http://<?=STATION_IP?>/pkspic/"+jdata["pic_name"]);
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

			if(jdata.member_no <= 0)
			{
				alertify_log("查無會員資料");
                return false;
			}

			if(jdata.amt <= 0)
			{
            	alertify_log("目前無須繳款，謝謝");
                return false;
            }

			if(jdata.amt > 2000)
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

			$("#show_member_name").text(jdata.member_name);
			$("#show_end_date").text(jdata.end_time);
			$("#show_next_start").text(jdata.next_start_time);
			$("#show_next_end").text(jdata.next_end_time);
			$("#show_remarks").text(jdata.remarks);
			$("#show_amt").text(jdata.amt);
			$("#show_in_time").text(jdata.in_time);
            $("#show_amt").text([jdata.amt, ' 元'].join(''));
			$("#show_order_no").text(jdata.order_no);

			show_item("payment_data");

			// parse price detail
			$("#price_data_tbody").html("");
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
}

$(document).ready(function()
{
	<?php /* 鎖右鍵 */ ?>
	$(document).bind('contextmenu', function (e) {
	  e.preventDefault();
	});

	<?php /* 樓層平面圖 */ ?>
	AltobObject.AtsMap({
		mapInfo: {
			map1: {
				floorName: 'B1',
				canvasId: 'b1canvas',
				src: '<?=SERVER_URL?>i3/pics/b1_map.png',
				initialImageRatio: 0.6,
				shiftLeft: 300,
				shiftUp: 0
			},
			map2: {
				floorName: 'F1',
				canvasId: 'f1canvas',
				src: '<?=SERVER_URL?>i3/pics/f1_map.png',
				initialImageRatio: 0.6,
				shiftLeft: 300,
				shiftUp: 0
			},
			map3: {
				floorName: 'F2',
				canvasId: 'f2canvas',
				src: '<?=SERVER_URL?>i3/pics/f2_map.png',
				initialImageRatio: 0.6,
				shiftLeft: 300,
				shiftUp: 0
			},
			map4: {
				floorName: 'F3',
				canvasId: 'f3canvas',
				src: '<?=SERVER_URL?>i3/pics/f3_map.png',
				initialImageRatio: 0.6,
				shiftLeft: 300,
				shiftUp: 0
			},
			map5: {
				floorName: 'F4',
				canvasId: 'f4canvas',
				src: '<?=SERVER_URL?>i3/pics/f4_map.png',
				initialImageRatio: 0.6,
				shiftLeft: 300,
				shiftUp: 0
			},
			map6: {
				floorName: 'F5',
				canvasId: 'f5canvas',
				src: '<?=SERVER_URL?>i3/pics/f5_map.png',
				initialImageRatio: 0.6,
				shiftLeft: 300,
				shiftUp: 0
			},
			map7: {
				floorName: 'F6',
				canvasId: 'f6canvas',
				src: '<?=SERVER_URL?>i3/pics/f6_map.png',
				initialImageRatio: 0.6,
				shiftLeft: 300,
				shiftUp: 0
			},
			map8: {
				floorName: 'F7',
				canvasId: 'f7canvas',
				src: '<?=SERVER_URL?>i3/pics/f7_map.png',
				initialImageRatio: 0.6,
				shiftLeft: 300,
				shiftUp: 0
			}
		}
	});


	<?php /* 車牌模糊搜尋 */ ?>
	$("#fuzzy_search_lpr").submit(function(event)
	{
    	event.preventDefault();

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
					alert("查無此車 !");
					return false;
				}

				var tmp_str_array = [];

				for(idx in jdata.result)
				{
					tmp_str_array = tmp_str_array.concat(
						[
							"<tr><td id='lpr_", idx, "' style='text-align:center;vertical-align:middle;'>", jdata.result[idx]['lpr'] ,
							"</td><td id='in_time_", idx, "'style='text-align:center;vertical-align:middle;'>", jdata.result[idx]['in_time'],
							"</td><td id='pks_pic_path_", idx, "'style='text-align:center;vertical-align:middle;'><img height='57' width='150' src='", jdata.result[idx]['pks_pic_path'],  "' />",
							"</td><td style='text-align:center;vertical-align:middle;'><button class='btn btn-default' onclick='check_lpr(", idx, ");'>查詢</button>" ,
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
  	$('#payment_lpr,#fuzzy_input').keyboard({

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

	$('#mobile,#company_no').keyboard({

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
  			'accept'  : '好'
  		},

  		layout: 'custom',

  		customLayout: {

  			'default': [
  				' 1 2 3 ',
				' 4 5 6 ',
				' 7 8 9 ',
				' {bksp} 0 {accept}'
  			]

  		}

  	});

	$('#email').keyboard({

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
  			'accept'  : '確 認'
  		},

  		layout: 'custom',

		customLayout: {
			'default': [
  				'@ 1 2 3 4 5 6 7 8 9 0 {bksp}',
  				'q w e r t y u i o p - _',
  				'{s} a s d f g h j k l {s}',
  				'z x c v b n m . {accept}',
  			],
			'shift': [
  				'@ 1 2 3 4 5 6 7 8 9 0 {bksp}',
  				'Q W E R T Y U I O P - _',
  				'{s} A S D F G H J K L {s}',
  				'Z X C V B N M . {accept}',
  			]
		}

  	});

	<?php /* validate  設定*/ ?>
	$.validate(
		{
			modules : 'security',
		}
	);

	// 定時自動更新頁面
	(function autoReloadPage(){
		var pageReloadTimeMillis = 60000;			// 頁面, 自動重新載入週期 ( 1 min )
		var pageCheckReloadTimeMillis = 10000;		// 頁面, 判斷重新載入週期 ( 10 sec )
		var pageShowReloadTimeMillis = 50000;		// 頁面, 開始顯示倒數週期 ( 50 sec )
		var aliveTime = moment();
		var countdownTimeMillis = pageReloadTimeMillis;
		$(document.body).bind("mousemove keypress", function(e) {
			aliveTime = moment();
			countdownTimeMillis = pageReloadTimeMillis;
		});
		function refresh() {
			if(moment() - aliveTime >= pageReloadTimeMillis) // 如果頁面沒動作, 才更新
				window.location.reload(true);
			else{
				countdownTimeMillis -= pageCheckReloadTimeMillis;
				if(countdownTimeMillis < pageCheckReloadTimeMillis)
				{
					alertify_count_down("重新載入中..請稍候..", pageCheckReloadTimeMillis);	
				}
				else if(countdownTimeMillis < pageShowReloadTimeMillis){
					alertify_count_down("倒數: " + (countdownTimeMillis / 1000) + " 秒, 重新載入畫面..", pageCheckReloadTimeMillis);	
				}
				setTimeout(refresh, pageCheckReloadTimeMillis);
			}
		}
		setTimeout(refresh, pageCheckReloadTimeMillis);
	})();

});
</script>
