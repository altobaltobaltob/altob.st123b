<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>歐特儀停車場 (<?=STATION_NAME?>) - 歐Pa卡</title>
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
                <a class="navbar-brand" href="<?=APP_URL?>">歐特儀停車場 (<?=STATION_NAME?>)</a>
            </div>

            <!-- /.navbar-top-links(左側選單) -->
            <div class="navbar-default sidebar" role="navigation">
                <div class="sidebar-nav navbar-collapse">
                    <ul class="nav" id="side-menu">
                        <li>
                            <a href="#"><i class="fa fa-user fa-fw"></i>服務項目<span class="fa arrow"></span></a>
                            <ul class="nav nav-second-level">
								<li>
                                    <a href="#" onclick="show_item('check_user');">用戶查詢</a>
                                </li>
								<li>
                                    <a href="#" onclick="get_allpa_products(event);">加值清單</a>
                                </li>
								<!--li>
                                    <a href="#" onclick="show_item('card_register_barcode_input');">卡片記名</a>
                                </li-->
								<li>
                                    <a href="#" onclick="show_item('about_allpa');">使用說明</a>
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
        <div id="page-wrapper">            
			<div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">歡迎使用 - 歐Pa卡</h1>                
				</div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->    
			
			<?php /* ----- 查詢用戶  ----- */ ?> 
			<div data-items="check_user" class="row">            
					<div class="col-lg-12">
						<div class="panel panel-default">
							<div class="panel-body">
								<div data-rows class="row">
								
									<div class="col-lg-6">
										<form id="check_user_form" role="form" method="post">  
											<div class="form-group">
												<label>車牌號碼</label>
												<input type="text" id="user_lpr" class="form-control" style="text-transform:uppercase" 
													placeholder="請輸入您的車牌號碼"
													data-validation="custom"
													data-validation-regexp="^([a-zA-Z0-9]+)$"
													data-validation-error-msg="限英數字 (範例: ABC2016)"/>
											</div>
											
											<button type="submit" class="btn btn-default" onclick="do_check_user(event);">查詢</button>
											<button type="reset" class="btn btn-default">重填</button>
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
			<?php /* ----- 查詢用戶 (END)  ----- */ ?> 
			
			<?php /* ----- 加值清單  ----- */ ?> 
            <div data-items="allpa_products" class="row" style="display:none;">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading"><?php /* 資料顯示區灰色小表頭 */ ?>
                            加值清單
                        </div>
                        <div class="panel-body">
                            <div data-rows class="row">
                                <div class="col-lg-12">
                            	<div class="dataTable_wrapper">
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
											<th style="text-align:center;">卡片</th>
											<th style="text-align:center;">說明</th>
											<th style="text-align:center;">功能</th>
                                        </tr>   
                                        <tr id="product_list" style="display:none;">
											<td data-tag="p_name" style="text-align:center;vertical-align: middle;"></td>
											<td data-tag="p_desc" style="text-align:left;vertical-align: middle;"></td>
											<td data-tag="p_function" style="text-align:center;vertical-align: middle;"></td>
                                        </tr>
                                    </thead>
                                    <tbody id="product_data_tbody" style="font-size:10px;"></tbody>
                                </table>
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
            <?php /* ----- 加值清單 (END) ----- */ ?> 
			
			<?php /* ----- 用戶資訊  ----- */ ?> 
            <div data-items="user_allpa_info" class="row" style="display:none;">
			
				<div id="user_invalid_allpa_info" class="col-lg-12" style="display:none;">
						<div class="panel panel-default">
							<div class="panel-heading"><?php /* 資料顯示區灰色小表頭 */ ?>
								卡片異常
							</div>
							<div class="panel-body">
								<div data-rows class="row">
								<div class="col-lg-6" style="margin: 0px auto;">
								<form id="invalid_allpa_info" role="form" method="post">
                                <table class="table table-striped table-bordered table-hover"">
                                    <tbody id="available_curr_tbody" style="font-size:14px;">
										<tr>
                                            <td style="text-align:right;vertical-align: middle; color: blue;">綁定車牌號碼</td>
                                            <td id="show_invalid_lpr" style="text-align:left;vertical-align: middle;"></td>
                                        </tr>
										<tr>
                                            <td style="text-align:right;vertical-align: middle; color: red;">歐Pa卡號 (32碼)</td>
                                            <td id="show_invalid_barcode" style="text-align:left;vertical-align: middle;"></td>
                                        </tr>
										<tr>
                                            <td style="text-align:right;vertical-align: middle;">卡片餘額</td>
                                            <td id="show_invalid_balance" style="text-align:left;vertical-align: middle;"></td>
                                        </tr>
										<tr>
                                            <td style="text-align:right;vertical-align: middle;">紅利點數</td>
                                            <td id="show_invalid_bonus" style="text-align:left;vertical-align: middle;"></td>
                                        </tr>
										<tr>
                                            <td style="text-align:right;vertical-align: middle; color: red;">卡片狀態</td>
                                            <td id="show_invalid_card_status" style="text-align:left;vertical-align: middle;"></td>
                                        </tr>
                                    </tbody>
                                </table>
								</form>
                                </div>
								</div>
								<!-- /.row (nested) -->
							</div>
							<!-- /.panel-body -->
						</div>
						<!-- /.panel -->
				</div>
				<!-- /.col-lg-12 -->
				
				<div id="user_current_allpa_info" class="col-lg-12" style="display:none;">
						<div class="panel panel-default">
							<div class="panel-heading"><?php /* 資料顯示區灰色小表頭 */ ?>
								我的歐Pa卡
							</div>
							<div class="panel-body">
								<div data-rows class="row">
								<div class="col-lg-6" style="margin: 0px auto;">
								<form id="current_allpa_info" role="form" method="post">
                                <table class="table table-striped table-bordered table-hover"">
                                    <tbody id="available_curr_tbody" style="font-size:14px;">
										<tr>
                                            <td style="text-align:right;vertical-align: middle; color: blue;">綁定車牌號碼</td>
                                            <td id="show_info_lpr" style="text-align:left;vertical-align: middle;"></td>
                                        </tr>
										<tr>
                                            <td style="text-align:right;vertical-align: middle;">歐Pa卡號 (32碼)</td>
                                            <td id="show_info_barcode" style="text-align:left;vertical-align: middle;"></td>
                                        </tr>
										<tr>
                                            <td style="text-align:right;vertical-align: middle;">卡片餘額</td>
                                            <td id="show_info_balance" style="text-align:left;vertical-align: middle;"></td>
                                        </tr>
										<tr>
                                            <td style="text-align:right;vertical-align: middle;">紅利點數</td>
                                            <td id="show_info_bonus" style="text-align:left;vertical-align: middle;"></td>
                                        </tr>
                                    </tbody>
                                </table>
								</form>
                                </div>
								</div>
								<!-- /.row (nested) -->
							</div>
							<!-- /.panel-body -->
						</div>
						<!-- /.panel -->
				</div>
				<!-- /.col-lg-12 -->
				
				<div id="allpa_user_bill_info" class="col-lg-12" style="display:none;">
                    <div class="panel panel-default">
                        <div class="panel-heading"><?php /* 資料顯示區灰色小表頭 */ ?>
                            使用記錄
                        </div>
                        <div class="panel-body">
                            <div data-rows class="row">
                                <div class="col-lg-12">
                            	<div class="dataTable_wrapper">
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
											<th style="text-align:center;">項目</th>
											<th style="text-align:center;">說明</th>
											<th style="text-align:center;">金額</th>
											<th style="text-align:center;">狀態</th>
                                        </tr>   
                                        <tr id="allpa_user_bill_list" style="display:none;">
											<td data-tag="b_name" style="text-align:center;vertical-align: middle;"></td>
											<td data-tag="b_desc" style="text-align:left;vertical-align: middle;"></td>
											<td data-tag="b_amt" style="text-align:center;vertical-align: middle;"></td>
											<td data-tag="b_function" style="text-align:center;vertical-align: middle;"></td>
                                        </tr>
                                    </thead>
                                    <tbody id="allpa_user_bill_data_tbody" style="font-size:10px;"></tbody>
                                </table>
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
			
                <div id="user_bill_info" class="col-lg-12" style="display:none;">
                    <div class="panel panel-default">
                        <div class="panel-heading"><?php /* 資料顯示區灰色小表頭 */ ?>
                            加值清單
                        </div>
                        <div class="panel-body">
                            <div data-rows class="row">
                                <div class="col-lg-12">
                            	<div class="dataTable_wrapper">
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
											<th style="text-align:center;">卡片</th>
											<th style="text-align:center;">說明</th>
											<th style="text-align:center;">狀態</th>
                                        </tr>   
                                        <tr id="bill_list" style="display:none;">
											<td data-tag="b_name" style="text-align:center;vertical-align: middle;"></td>
											<td data-tag="b_desc" style="text-align:left;vertical-align: middle;"></td>
											<td data-tag="b_function" style="text-align:center;vertical-align: middle;"></td>
                                        </tr>
                                    </thead>
                                    <tbody id="bill_data_tbody" style="font-size:10px;"></tbody>
                                </table>
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
            <?php /* ----- 用戶資訊 (END) ----- */ ?> 
			
			
			<?php /* ----- 結帳頁  ----- */ ?> 
            <div data-items="payment_data" class="row" style="display:none;">
                <div class="col-lg-12">
						<div class="panel panel-default">
							<div class="panel-body">
								<div data-rows class="row">
								<div class="col-lg-6" style="margin: 0px auto;">
								<form id="payment_data" role="form" method="post">
                                <table class="table table-striped table-bordered table-hover"">
                                    <tbody id="available_curr_tbody" style="font-size:14px;">
										<tr class="form-group">
                                            <td style="text-align:right;vertical-align: middle; color: blue;">車牌號碼</td>
											<td style="text-align:left;vertical-align: middle;">
											<input type="text" id="payment_lpr" class="form-control" style="text-transform:uppercase" 
													placeholder="請輸入您要加值的車牌號碼"
													data-validation="custom"
													data-validation-regexp="^([a-zA-Z0-9]+)$"
													data-validation-error-msg="限英數字 (範例: ABC2016)"
													data-validation-error-msg-container="#lpr_error_msg"
													/>
												<span id="lpr_error_msg"></span>
                                            </td>
                                        </tr>
										<tr>
                                            <td style="text-align:right;vertical-align: middle;">卡片名稱</td>
                                            <td id="show_product_name" style="text-align:left;vertical-align: middle;"></td>
                                        </tr>
										<tr>
                                            <td style="text-align:right;vertical-align: middle;">說明</td>
                                            <td id="show_product_desc" style="text-align:left;vertical-align: middle;"></td>
                                        </tr>
										<tr>
                                            <td style="text-align:right;vertical-align: middle;">備註</td>
                                            <td id="show_remarks" style="text-align:left;vertical-align: middle;"></td>
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
                                            <td id="show_valid_time_countdown" style="text-align:left;vertical-align: middle; color: red;"></td>
                                        </tr>
                                        <tr>
                                            <td style="text-align:right;vertical-align: middle;">
												<button type="button" class="btn btn-default" onclick="transfer_money(event);">開始付款</button>
                                            </td>
                                            <td style="text-align:left;vertical-align: middle;">
												<button type="button" class="btn btn-default" onclick="show_item('allpa_products');">取消</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
								</form>
                                </div>
								</div>
								<!-- /.row (nested) -->
							</div>
							<!-- /.panel-body -->
						</div>
						<!-- /.panel -->
				</div>
				<!-- /.col-lg-12 -->
            </div>   
            <?php /* ----- 結帳頁 (END) ----- */ ?> 
			
			
			<?php /* ----- PIN碼, 儲值  ----- */ ?> 
            <div data-items="allpa_reload" class="row" style="display:none;">
                <div class="col-lg-12">
						<div class="panel panel-default">
							<div class="panel-body">
								<div data-rows class="row">
								<div class="col-lg-6" style="margin: 0px auto;">
								<form id="allpa_reload_data" role="form" method="post">
                                <table class="table table-striped table-bordered table-hover"">
                                    <tbody id="available_curr_tbody" style="font-size:14px;">
										<tr>
                                            <td style="text-align:right;vertical-align: middle; color: blue;">車牌號碼</td>
                                            <td id="show_reload_lpr" style="text-align:left;vertical-align: middle;"></td>
                                        </tr>
										<tr>
                                            <td style="text-align:right;vertical-align: middle;">歐Pa卡號 (32碼)</td>
                                            <td id="show_reload_barcode" style="text-align:left;vertical-align: middle;"></td>
                                        </tr>
										<tr>
                                            <td style="text-align:right;vertical-align: middle;">儲值前金額</td>
                                            <td id="show_reload_amount_before" style="text-align:left;vertical-align: middle;"></td>
                                        </tr>
										<tr>
                                            <td style="text-align:right;vertical-align: middle; color: blue;">本次加值金額</td>
                                            <td id="show_reload_amt" style="text-align:left;vertical-align: middle;"></td>
                                        </tr>
                                        <tr>
                                            <td style="text-align:right;vertical-align: middle;">儲值後金額</td>
                                            <td id="show_reload_amount_next" style="text-align:left;vertical-align: middle;"></td>
                                        </tr>
										<tr>
                                            <td style="text-align:right;vertical-align: middle;">儲值 PIN 碼</td>
                                            <td id="show_pin_pic" style="text-align:left;vertical-align: middle;"></td>
                                        </tr>
										<tr class="form-group">
                                            <td style="text-align:right;vertical-align: middle;"></td>
											<td style="text-align:left;vertical-align: middle;">
											<input type="text" id="reload_pin" class="form-control" placeholder="請輸入儲值PIN碼"
												data-validation="custom"
												data-validation-regexp="^([a-zA-Z0-9]+)$"
												data-validation-error-msg="請輸入儲值PIN碼"
												data-validation-error-msg-container="#pin_error_msg"
												/>
												<span id="pin_error_msg"></span>
                                            </td>
                                        </tr>
										<tr>
                                            <td style="text-align:right;vertical-align: middle;">有效期限</td>
                                            <td id="show_pin_valid_time_countdown" style="text-align:left;vertical-align: middle; color: red;"></td>
                                        </tr>
                                        <tr>
                                            <td style="text-align:right;vertical-align: middle;">
												<button type="button" class="btn btn-default" onclick="do_allpa_reload(event);">確定儲值</button>
                                            </td>
                                            <td style="text-align:left;vertical-align: middle;">
												<button type="button" class="btn btn-default" onclick="show_item('user_allpa_info');">取消</button>
                                            </td>
                                        </tr>
										<input id="reload_pin_check_id" type="hidden" name="reload_pin_check_id" value="0" />
										<input id="reload_order_no" type="hidden" name="reload_order_no" value="0" />
                                    </tbody>
                                </table>
								</form>
                                </div>
								</div>
								<!-- /.row (nested) -->
							</div>
							<!-- /.panel-body -->
						</div>
						<!-- /.panel -->
				</div>
				<!-- /.col-lg-12 -->
            </div>   
            <?php /* ----- PIN碼, 儲值 (END) ----- */ ?> 
			
			
			<?php /* ----- 實體卡記名A  ----- */ ?> 
			<div data-items="card_register_barcode_input" class="row" style="display:none;">            
					<div class="col-lg-12">
						<div class="panel panel-default">
							<div class="panel-body">
								<div data-rows class="row">
								
									<div class="col-lg-6">
										<form id="card_register_barcode_input_form" role="form" method="post">  
											<div class="form-group">
												<label>歐Pa卡號</label>
												<input type="text" id="card_register_barcode" class="form-control" 
													placeholder="請輸入歐Pa卡號 (32碼)"
													data-validation="custom"
													data-validation-regexp="^(?=.{32}$)([0-9]+)$"
													data-validation-error-msg="歐Pa卡號碼格式: 32碼 數字">
											</div>
											<button type="submit" class="btn btn-default" onclick="do_card_register_barcode_input(event);">確定</button>
											<button type="reset" class="btn btn-default">重填</button>
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
			<?php /* ----- 實體卡記名A (END) ----- */ ?> 
			
			<?php /* ----- 實體卡記名B (卡片查詢結果顯示, 輸入要綁定的車牌) ----- */ ?> 
			<div data-items="card_register_lpr_input" class="row" style="display:none;">            
					<div class="col-lg-12">
						<div class="panel panel-default">
							<div class="panel-heading"><?php /* 資料顯示區灰色小表頭 */ ?>
								卡片上的金額, 將轉移給車牌號碼
							</div>
							<div class="panel-body">
								<div data-rows class="row">
									<div class="col-lg-6">
										<form id="card_register_lpr_input_form" role="form" method="post">
										<table class="table table-striped table-bordered table-hover"">
											<tbody id="available_curr_tbody" style="font-size:14px;">
											<tr>
												<td style="text-align:right;vertical-align: middle; color: blue;">歐Pa卡號 (32碼)</td>
												<td id="card_register_barcode_value" style="text-align:left;vertical-align: middle;"></td>
											</tr>
											<tr>
												<td style="text-align:right;vertical-align: middle;">卡片金額</td>
												<td id="card_register_balance_value" style="text-align:left;vertical-align: middle;"></td>
											</tr>
											<tr>
												<td style="text-align:right;vertical-align: middle;">紅利點數</td>
												<td id="card_register_bonus_value" style="text-align:left;vertical-align: middle;"></td>
											</tr>
											<tr>
												<td style="text-align:right;vertical-align: middle;">卡片狀態</td>
												<td id="card_register_card_status_value" style="text-align:left;vertical-align: middle;"></td>
											</tr>											
											</tbody>
										</table>
										<div class="form-group">
											<label>要綁定的車牌號碼</label>
											<input type="text" id="card_register_lpr" class="form-control" style="text-transform:uppercase" 
												placeholder="請輸入您要綁定的車牌號碼"
												data-validation="custom"
												data-validation-regexp="^([a-zA-Z0-9]+)$"
												data-validation-error-msg="限英數字 (範例: ABC2016)"/>
										</div>
										<button type="submit" class="btn btn-default" onclick="do_card_register(event);">確認綁定</button>
										<button type="reset" class="btn btn-default">重填</button>
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
			<?php /* ----- 實體卡記名B (卡片查詢結果顯示, 輸入要綁定的車牌) (END) ----- */ ?> 
			
			<?php /* ----- 歐趴卡使用說明  ----- */ ?> 
			<div data-items="about_allpa" class="row" style="display:none;">            
					<div class="col-lg-12">
						<div class="panel panel-default">
							<div class="panel-heading"><?php /* 資料顯示區灰色小表頭 */ ?>
								歐Pa卡使用說明
							</div>
							<div class="panel-body">
								<div data-rows class="row">
									<div class="col-lg-6">
										<div class="col-lg-8 col-lg-offset-2 text-center">
											<h3 class="section-heading">履約事項</h2>
											<hr class="light">
											本卡所收取之金額，已存入發行商於中國信託商業銀行開立之信託專戶，專款專用；所稱專用係指供發行人履行交付商品或提供服務義務使用。
											本信託受益人為發行人，非本卡持有人，信託期間自儲值日起一年，信託存續期間屆滿後，由中國信託商業銀行將信託專戶餘額交由發行人領回。
										</div>	
										<div class="col-lg-8 col-lg-offset-2 text-center">
											<h3 class="section-heading">說明、一</h2>
											<hr class="light">
											※目前停車場付費方式包括在自動繳費機付費、月租繳費以及行動支付，此外，公司也發行歐Pa卡，提供客人另一種付費管道。
										</div>
										<div class="col-lg-8 col-lg-offset-2 text-center">
											<h3 class="section-heading">說明、二</h2>
											<hr class="light">
											※客人只須上網登錄車號，當進入停車場時，系統會直接在雲端扣點數，無須搖下車窗，即可透過車牌辨識進場。
										</div>
										<div class="col-lg-8 col-lg-offset-2 text-center">
											<h3 class="section-heading">說明、三</h2>
											<hr class="light">
											※出場時，系統也會自動比對餘額與自動扣款。
										</div>
										<div class="col-lg-8 col-lg-offset-2 text-center">
											<h3 class="section-heading">說明、四</h2>
											<hr class="light">
											※未來歐Pa卡可透過現金、上網等管道儲值。
										</div>
										<div class="col-lg-8 col-lg-offset-2 text-center">
											<h3 class="section-heading">說明、五</h2>
											<hr class="light">
											※目前僅供部份場站使用，接下來將陸續導入本公司各停車場，未來只要是本公司歐Pa卡客戶，即可方便進出各停車場。
										</div>
										<div class="col-lg-8 col-lg-offset-2 text-center">
											<h3 class="section-heading">謝謝!!</h2>
											<hr class="light">
											※若遇卡片使用問題，請參照：<a href="<?=APP_URL?>">官網</a>說明或洽各<p><a href="maps://maps.google.com/maps?daddr=25.0267747,121.5410621&amp;ll=">歐特儀特約停車場站管理室</a>或電洽歐特儀<a href="tel:+886227057716">(02) 2705-7716 </a>將有專人為您服務。
										</div>
									</div>
									<!-- /.col-lg-6 (nested) -->
								</div>
								<!-- /.row (nested) -->
							</div>
							<!-- /.panel-body -->
						</div>
						<!-- /.panel -->
						
						<div class="fb-like" data-share="true" data-width="450" data-show-faces="true"></div>
						
					</div>
					<!-- /.col-lg-12 -->
            </div>
			<?php /* ----- 歐趴卡使用說明 (END) ----- */ ?> 
                     
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

    <!-- Custom Theme JavaScript -->
    <script src="<?=BOOTSTRAPS?>dist/js/sb-admin-2.js"></script>
    <div id="works" style="display:none;"></div><?php /* 作為浮動顯示區之用 */ ?>
</body>


<script>
  window.fbAsyncInit = function() {
    FB.init({
      appId      : '191092301278009',
      xfbml      : true,
      version    : 'v2.6'
    });
  };

  (function(d, s, id){
     var js, fjs = d.getElementsByTagName(s)[0];
     if (d.getElementById(id)) {return;}
     js = d.createElement(s); js.id = id;
     js.src = "//connect.facebook.net/en_US/sdk.js";
     fjs.parentNode.insertBefore(js, fjs);
   }(document, 'script', 'facebook-jssdk'));
</script>


</html>

<script>

var pinIntervalId = 0; // pin timer
var refreshIntervalId = 0; // payment timer

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
	alertify.set({ labels: {
		ok     : "確定"
	} });
	alertify.alert($msg, function (e){
		location.reload(); // F5
	});
}

<?php /* 顯示指定項目 */ ?>
function show_item(tags)
{
	// 查詢
	$("#user_lpr").val("");
	// 啟用
	$("#reload_pin").val("");
	// 付款
	$("#payment_lpr").val("");
	$("#company_no").val("");
	$("#email").val("");
	$("#mobile").val("");
	// 記名
	$("#card_register_barcode").val("");
	$("#card_register_lpr").val("");
	
	if(tags.indexOf('payment_data') < 0){
		clearInterval(refreshIntervalId); // 消除倒數計時timer
		//console.log("clearInterval..");
	}
	
	if(tags.indexOf('allpa_reload') < 0){
		clearInterval(pinIntervalId); // 消除倒數計時timer
		//console.log("pinIntervalId..");
	}
	
	$("[data-items]").hide();
	$("[data-items="+tags+"]").show();
    return false;
}

// 判斷回傳資料是否有效
function is_valid_result(result)
{
	if(result){
		return true;
	}else{
		alertify_error("未知的錯誤..");
		return false;
	}
}

// 判斷回傳資料是否為json
function is_json_result(result)
{
	if (/^[\],:{}\s]*$/.test(result.replace(/\\["\\\/bfnrtu]/g, '@').
		replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, ']').
		replace(/(?:^|:|,)(?:\s*\[)+/g, ''))) {
		//the json is ok
		return true;
	}else{
		//the json is not ok
		alertify_error("查無資料，請鍵入正確資料 (回傳資料有誤)");
		return false;
	}
}

// 執行卡片記名 step 1
function do_card_register_barcode_input(event)
{
	event.preventDefault();
	
	if(! $("#card_register_barcode_input_form").isValid()) return false;

	var barcode = $("#card_register_barcode").val() == '' ? '0' : $("#card_register_barcode").val();
		
    $.ajax
	({
		url: "<?=APP_URL?>get_barcode_info",
		type: "post",
		dataType:"text",
		data:{
			"barcode": barcode
		},
		success: function(result)
		{
			if(!is_valid_result(result)){ return false; }
			
			if(!is_json_result(result)){ return false; }
			
			var jdata = JSON.parse(result);
			
			if(jdata.result_code == "OK")
			{
				//console.log(jdata);
				
				// 已開卡
				if(jdata.result){
					alertify_log("已開卡..");
					user_allpa_info_handler(jdata);
					return false;
				}
				
				// 綁定頁
				$("#card_register_barcode_value").text(jdata.barcode);
				$("#card_register_balance_value").text(jdata.balance);
				$("#card_register_card_status_value").text(jdata.card_status);
				$("#card_register_bonus_value").text(jdata.bonus);
				show_item("card_register_lpr_input");
					
			}else{
				alertify_error([
					"查詢失敗..<br/><br/>",
					"[代碼] : ", jdata.result_code, "<br/>",
					"[訊息] : ", jdata.result_msg, "<br/>"
					].join(''));	
			}
			
		},
		error: function (xhr, ajaxOptions, thrownError) 
		{
			alertify_error("發生未知錯誤, 請稍候再試");
		}
	})
}

// 執行卡片記名 step 2
function do_card_register(event)
{
	event.preventDefault();
	
	if(! $("#card_register_lpr_input_form").isValid()) return false;

	var barcode = $("#card_register_barcode_value").text() == '' ? '0' : $("#card_register_barcode_value").text();
	var lpr = $("#card_register_lpr").val();
		
    $.ajax
	({
		url: "<?=APP_URL?>card_register",
		type: "post",
		dataType:"text",
		data:{
			"barcode": barcode,
			"lpr": lpr
		},
		success: function(result)
		{
			if(!is_valid_result(result)){ return false; }
			
			if(!is_json_result(result)){ return false; }
			
			var jdata = JSON.parse(result);
			
			if(jdata.result_code == "OK")
			{
				alertify_log("卡片記名完成");
				user_allpa_info_handler(jdata);
				return false;
					
			} else if(jdata.result_code == "-201") // 已註冊的車牌, 詢問點數轉移
			{
				console.log("switch?? " + jdata);
				
				alertify_log([
					"車牌已綁卡, 請直接購卡加值..<br/><br/>",
					"[代碼] : ", jdata.result_code, "<br/>",
					"[訊息] : ", jdata.result_msg, "<br/>"
					].join(''));	
				
			} else {
				alertify_error([
					"卡片記名失敗..<br/><br/>",
					"[代碼] : ", jdata.result_code, "<br/>",
					"[訊息] : ", jdata.result_msg, "<br/>"
					].join(''));	
			}
			
		},
		error: function (xhr, ajaxOptions, thrownError) 
		{
			alertify_error("發生未知錯誤, 請稍候再試");
		}
	})
}

// 開啟轉帳畫面
function transfer_money(event)
{
	event.preventDefault();
	
	if($("#payment_lpr").val() == '')
	{
		alertify_log("請指定要加值的車牌號碼!!");
		return false;
	}
	
	if(! $("#payment_data").isValid()) return false;
	
	if($("#email").val() == '' && $("#mobile").val() == '')
	{
		alertify_error("請至少提供一項發票通知方式<br/>1. 電子信箱 <br/>2. 或 手機號碼<br/><br/>謝謝!!");
		return false;
	}

	if (! confirm("開始結帳嗎 ?"))	return false;
	
	// Create Base64 Object
	var Base64 = {_keyStr:"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",encode:function(e){var t="";var n,r,i,s,o,u,a;var f=0;e=Base64._utf8_encode(e);while(f<e.length){n=e.charCodeAt(f++);r=e.charCodeAt(f++);i=e.charCodeAt(f++);s=n>>2;o=(n&3)<<4|r>>4;u=(r&15)<<2|i>>6;a=i&63;if(isNaN(r)){u=a=64}else if(isNaN(i)){a=64}t=t+this._keyStr.charAt(s)+this._keyStr.charAt(o)+this._keyStr.charAt(u)+this._keyStr.charAt(a)}return t},decode:function(e){var t="";var n,r,i;var s,o,u,a;var f=0;e=e.replace(/[^A-Za-z0-9\+\/\=]/g,"");while(f<e.length){s=this._keyStr.indexOf(e.charAt(f++));o=this._keyStr.indexOf(e.charAt(f++));u=this._keyStr.indexOf(e.charAt(f++));a=this._keyStr.indexOf(e.charAt(f++));n=s<<2|o>>4;r=(o&15)<<4|u>>2;i=(u&3)<<6|a;t=t+String.fromCharCode(n);if(u!=64){t=t+String.fromCharCode(r)}if(a!=64){t=t+String.fromCharCode(i)}}t=Base64._utf8_decode(t);return t},_utf8_encode:function(e){e=e.replace(/\r\n/g,"\n");var t="";for(var n=0;n<e.length;n++){var r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r)}else if(r>127&&r<2048){t+=String.fromCharCode(r>>6|192);t+=String.fromCharCode(r&63|128)}else{t+=String.fromCharCode(r>>12|224);t+=String.fromCharCode(r>>6&63|128);t+=String.fromCharCode(r&63|128)}}return t},_utf8_decode:function(e){var t="";var n=0;var r=c1=c2=0;while(n<e.length){r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r);n++}else if(r>191&&r<224){c2=e.charCodeAt(n+1);t+=String.fromCharCode((r&31)<<6|c2&63);n+=2}else{c2=e.charCodeAt(n+1);c3=e.charCodeAt(n+2);t+=String.fromCharCode((r&15)<<12|(c2&63)<<6|c3&63);n+=3}}return t}}

	var lpr = $("#payment_lpr").val() == '' ? 'UNKNOWN' : $("#payment_lpr").val();
	var order_no = $("#show_order_no").text() == '' ? '0' : $("#show_order_no").text();
    var invoice_receiver = $("#invoice_receiver").val() == '' ? '0' : $("#invoice_receiver").val();
	var company_no = $("#company_no").val() == '' ? '0' : $("#company_no").val();
	var email = $("#email").val() == '' ? '0' : Base64.encode($("#email").val()).slice(0, -1); // remove base64 '=' for URI rule
	var mobile = $("#mobile").val() == '' ? '0' : $("#mobile").val();
		
    webatm = window.open(
		["<?=APP_URL?>transfer_money/", 
			lpr, "/", 
			order_no, "/", 
			encodeURI(invoice_receiver), "/", 
			encodeURI(company_no), "/", 
			email, "/",
			mobile, "/"
			].join('')
		, "_self");
}

// 執行 PIN 儲值
function do_allpa_reload(event)
{
	event.preventDefault();
	
	if(! $("#allpa_reload_data").isValid()) return false;

	var pin_check_id = $("#reload_pin_check_id").text() == '' ? '0' : $("#reload_pin_check_id").text();
	var order_no = $("#reload_order_no").text() == '' ? '0' : $("#reload_order_no").text();
    var reload_pin = $("#reload_pin").val() == '' ? '0' : $("#reload_pin").val();
		
    $.ajax
	({
		url: "<?=APP_URL?>allpa_reload",
		type: "post",
		dataType:"text",
		data:{
			"pin_check_id": pin_check_id,
			"order_no": order_no,
			"reload_pin": reload_pin
		},
		success: function(result)
		{
			if(!is_valid_result(result)){ return false; }
			
			if(!is_json_result(result)){ return false; }
			
			var jdata = JSON.parse(result);
			
			if(jdata.result_code == "OK")
			{
				alertify_log([
					"PIN 儲值成功..<br/><br/>",
					"[代碼] : ", jdata.result_code, "<br/>",
					"[訊息] : ", jdata.result_msg, "<br/>"
					].join(''));
				
				user_allpa_info_handler(jdata);
					
			}else{
				alertify_error([
					"PIN 儲值失敗..<br/><br/>",
					"[代碼] : ", jdata.result_code, "<br/>",
					"[訊息] : ", jdata.result_msg, "<br/>"
					].join(''));	
			}
			
		},
		error: function (xhr, ajaxOptions, thrownError) 
		{
			alertify_error("發生未知錯誤, 請稍候再試");
		}
	})
}

// 啟用卡片
function do_activate(order_no)
{
	$.ajax
	({
		url: "<?=APP_URL?>activate_bill",
		type: "post",
		dataType:"text",
		data:{
			"order_no": order_no
		},
		success: function(result)
		{
			if(!is_valid_result(result)){ return false; }
			
			if(!is_json_result(result)){ return false; }
			
			var jdata = JSON.parse(result);
			
			if(jdata.result_code == "OK")
			{
				//console.log(jdata);
				
				// 新開卡
				if(!jdata.encoded_pic){
					user_allpa_info_handler(jdata);
					return false;
				}
				
				
				// variables for time units
				var days, hours, minutes, seconds = 0;
				 
				// get tag element
				var countdown = $("#show_pin_valid_time_countdown");
				countdown.val("");
				
				//var countdown_offset_ms = 60*5*1000; // 比資料庫早5分鐘timeout
				var duration = moment.duration({minutes: 0}); // 比資料庫早5分鐘timeout
				
				// set the date we're counting down to
				var target_date = moment(jdata.valid_before).subtract(duration);
				//var target_date = new Date(jdata.balance_time_limit).getTime();
				
				pinIntervalId = setInterval(function () {
				    // find the amount of "seconds" between now and target
					var seconds_left = target_date.diff(moment(), 'seconds');
					
					//console.log(seconds_left);
					
					//var current_date = new Date().getTime();
				    //var seconds_left = (target_date - current_date - countdown_offset_ms) / 1000;
				
					if(seconds_left <= 0){
						clearInterval(pinIntervalId);
						// reload page
						show_item('check_user');
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
				
				$("#show_reload_lpr").text(jdata.lpr);
				$("#show_reload_barcode").text(jdata.barcode);
				$("#show_reload_amount_before").text([jdata.amount_before, ' 元'].join(''));
				$("#show_reload_amt").text([jdata.amt, ' 元'].join(''));
				$("#show_reload_amount_next").text([jdata.amount_before, ' 元', " + ", jdata.amt, ' 元'].join(''));
				$("#show_pin_pic").html('<img src="data:image/gif;base64,' +jdata.encoded_pic+ '" />');
				
				// hidden
				$("#reload_pin_check_id").text(jdata.pin_check_id);
				$("#reload_order_no").text(jdata.order_no);
				
				show_item("allpa_reload");
					
			}else{
				alertify_error([
					"啟用失敗..<br/><br/>",
					"[代碼] : ", jdata.result_code, "<br/>",
					"[訊息] : ", jdata.result_msg, "<br/>"
					].join(''));	
			}
			
		},
		error: function (xhr, ajaxOptions, thrownError) 
		{
			alertify_error("發生未知錯誤, 請稍候再試");
		}
	})
}

// 購買卡片
function do_purchase(product_id)
{
	$.ajax
	({
		url: "<?=APP_URL?>purchase",
		type: "post",
		dataType:"text",
		data:{
			"product_id": product_id
		},
		success: function(result)
		{
			if(!is_valid_result(result)){ return false; }
			
			if(!is_json_result(result)){ return false; }
			
			var jdata = JSON.parse(result);
			
			if(jdata.result_code == "OK")
			{
				//console.log(jdata);
				
				// variables for time units
				var days, hours, minutes, seconds = 0;
				 
				// get tag element
				var countdown = $("#show_valid_time_countdown");
				countdown.val("");
				
				//var countdown_offset_ms = 60*5*1000; // 比資料庫早5分鐘timeout
				var duration = moment.duration({minutes: 5}); // 比資料庫早5分鐘timeout
				
				// set the date we're counting down to
				var target_date = moment(jdata.valid_time).subtract(duration);
				//var target_date = new Date(jdata.balance_time_limit).getTime();
				
				refreshIntervalId = setInterval(function () {
				    // find the amount of "seconds" between now and target
					var seconds_left = target_date.diff(moment(), 'seconds');
					
					//console.log(seconds_left);
					
					//var current_date = new Date().getTime();
				    //var seconds_left = (target_date - current_date - countdown_offset_ms) / 1000;
				
					if(seconds_left <= 0){
						clearInterval(refreshIntervalId);
						// reload page
						show_item('allpa_products');
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
				
				$("#show_product_name").text(jdata.product_name);
				$("#show_product_desc").text(jdata.product_desc);
				$("#show_remarks").text(jdata.remarks);
				$("#show_amt").text([jdata.amt, ' 元'].join(''));
				$("#show_order_no").text(jdata.order_no);
				
				show_item("payment_data");
					
			}else{
				alertify_error([
					"購買失敗..<br/><br/>",
					"[代碼] : ", jdata.result_code, "<br/>",
					"[訊息] : ", jdata.result_msg, "<br/>"
					].join(''));	
			}
			
		},
		error: function (xhr, ajaxOptions, thrownError) 
		{
			alertify_error("發生未知錯誤, 請稍候再試");
		}
	})
}

// 加值清單
function get_allpa_products(event)
{
	event.preventDefault();
	
	$.ajax
	({
		url: "<?=APP_URL?>get_allpa_products",
		type: "post",
		dataType:"text",
		success: function(result)
		{
			if(!is_valid_result(result)){ return false; }
			
			if(!is_json_result(result)){ return false; }
			
			var jdata = JSON.parse(result);
			
			// 清空
			$("#product_data_tbody").html("");
			
			if(jdata.length > 0){
				for(key in jdata) {
					var result = jdata[key];
					$("#product_list>[data-tag=p_name]").text(result['product_name']);
					$("#product_list>[data-tag=p_desc]").html(result['product_desc']);
					$("#product_list>[data-tag=p_function]").html("<pre type='button' class='btn btn-default' onclick='do_purchase("+result['product_id']+")'> 購 買 </pre>");
					$("<tr data-day='day'>"+$("#product_list").html()+"</tr>").appendTo("#product_data_tbody"); 
				}
			}else{
				$("#product_list>[data-tag=p_name]").text("暫不提供, 敬請期待..");
				$("#product_list>[data-tag=p_desc]").html("");
				$("#product_list>[data-tag=p_function]").html("");
				$("<tr data-day='day'>"+$("#product_list").html()+"</tr>").appendTo("#product_data_tbody");
				
			}

			show_item('allpa_products');
		},
		error: function (xhr, ajaxOptions, thrownError) 
		{
			alertify_error("發生未知錯誤, 請稍候再試");
		}
	})
}

// 查詢用戶
function do_check_user(event)
{
	event.preventDefault();

	if ($("#user_lpr").val() == "")
    {
		alertify_error("請填寫車號");
       	return false;
    }
	
	if(! $("#check_user_form").isValid()) return false;
	
	$.ajax
	({
		url: "<?=APP_URL?>get_allpa_info",
		type: "post",
		dataType:"text",
		data:{
			"user_lpr": $("#user_lpr").val()
		},
		success: function(result)
		{
			if(!is_valid_result(result)){ return false; }
			
			if(!is_json_result(result)){ return false; }
			
			var jdata = JSON.parse(result);
			
			if(jdata.result_code == "OK")
			{
				user_allpa_info_handler(jdata);
					
			}else{
				alertify_error([
					"查無資料..<br/><br/>",
					"[代碼] : ", jdata.result_code, "<br/>",
					"[訊息] : ", jdata.result_msg, "<br/>"
					].join(''));	
			}
			
		},
		error: function (xhr, ajaxOptions, thrownError) 
		{
			alertify_error("發生未知錯誤, 請稍候再試");
		}
	})
}

// 歐Pa卡 - 繳費
function do_allpa_pay_bill(order_no)
{	
	$.ajax
	({
		url: "<?=APP_URL?>allpa_pay_bill",
		type: "post",
		dataType:"text",
		data:{
			"order_no": order_no
		},
		success: function(result)
		{
			if(!is_valid_result(result)){ return false; }
			
			if(!is_json_result(result)){ return false; }
			
			var jdata = JSON.parse(result);
			
			if(jdata.result_code == "OK")
			{
				user_allpa_info_handler(jdata);
					
			}else{
				alertify_error([
					"處理失敗..<br/><br/>",
					"[代碼] : ", jdata.result_code, "<br/>",
					"[訊息] : ", jdata.result_msg, "<br/>"
					].join(''));	
			}
			
		},
		error: function (xhr, ajaxOptions, thrownError) 
		{
			alertify_error("發生未知錯誤, 請稍候再試");
		}
	})
}

// user_allpa_info, 頁面回傳流程
function user_allpa_info_handler(jdata)
{
	//console.log(jdata.result);
	
	var allpa_current = jdata.result.allpa_current;
	var allpa_invalid = jdata.result.allpa_invalid;
	var allpa_other = jdata.result.allpa_other;
	
	var bill_ready = jdata.result.bill_ready;
	var bill_finished = jdata.result.bill_finished;
	
	var allpa_user_bill_finished = jdata.result.allpa_user_bill_finished;
	var allpa_user_bill_gg = jdata.result.allpa_user_bill_gg;
	var allpa_user_bill_debt = jdata.result.allpa_user_bill_debt;
	
	//console.log("allpa_current: " + allpa_current);
	//console.log("allpa_other: " + allpa_other);
	//console.log("bill_ready: " + bill_ready);
	//console.log("bill_finished: " + bill_finished);
	
	// 目前卡片資訊
	if(allpa_current){
		$("#show_info_lpr").text(allpa_current.lpr);
		$("#show_info_barcode").text(allpa_current.barcode);
		$("#show_info_balance").text(allpa_current.balance);
		$("#show_info_bonus").text(allpa_current.bonus);	
		$("#user_current_allpa_info").show();
	}else{
		$("#user_current_allpa_info").hide();
	}
	
	// 無效的卡片資訊
	if(allpa_invalid){
		$("#show_invalid_card_status").text(allpa_invalid.card_status);
		$("#show_invalid_lpr").text(allpa_invalid.lpr);
		$("#show_invalid_barcode").text(allpa_invalid.barcode);
		$("#show_invalid_balance").text(allpa_invalid.balance);
		$("#show_invalid_bonus").text(allpa_invalid.bonus);
		$("#user_invalid_allpa_info").show();
	}else{
		$("#user_invalid_allpa_info").hide();
	}
	
	// 卡片使用資訊
	if(allpa_user_bill_finished || allpa_user_bill_gg || allpa_user_bill_debt){
		$("#allpa_user_bill_data_tbody").html("");
		
		for(key in allpa_user_bill_debt) {
			var result = allpa_user_bill_debt[key];
			var b_name = "尚未付款";
			var b_desc = ['*時段 ', result['in_time'], '<br/>至 ', result['balance_time']].join('');
			var b_amt = [result['amt'], ' 元'].join('');
			$("#allpa_user_bill_list>[data-tag=b_name]").text(b_name);
			$("#allpa_user_bill_list>[data-tag=b_desc]").html(b_desc);
			$("#allpa_user_bill_list>[data-tag=b_amt]").text(b_amt);
			$("#allpa_user_bill_list>[data-tag=b_function]").html("<pre type='button' class='btn btn-default' onclick='do_allpa_pay_bill("+result['order_no']+")'>繳 費</pre>");
			$("<tr data-day='day' style='color: blue;'>"+$("#allpa_user_bill_list").html()+"</tr>").appendTo("#allpa_user_bill_data_tbody"); 
		}
		
		for(key in allpa_user_bill_gg) {
			var result = allpa_user_bill_gg[key];
			var b_name = "系統錯誤";
			var b_desc = ['*時段 ', result['in_time'], '<br/>至 ', result['balance_time']].join('');
			var b_amt = [result['amt'], ' 元'].join('');
			$("#allpa_user_bill_list>[data-tag=b_name]").text(b_name);
			$("#allpa_user_bill_list>[data-tag=b_desc]").html(b_desc);
			$("#allpa_user_bill_list>[data-tag=b_amt]").text(b_amt);
			$("#allpa_user_bill_list>[data-tag=b_function]").html("請通知管理員");
			$("<tr data-day='day' style='color: red;'>"+$("#allpa_user_bill_list").html()+"</tr>").appendTo("#allpa_user_bill_data_tbody"); 
		}
		
		for(key in allpa_user_bill_finished) {
			var result = allpa_user_bill_finished[key];
			var b_name = "結帳完成";
			var b_desc = ['*時段 ', result['in_time'], '<br/>至 ', result['balance_time']].join('');
			var b_amt = [result['amt'], ' 元'].join('');
			$("#allpa_user_bill_list>[data-tag=b_name]").text(b_name);
			$("#allpa_user_bill_list>[data-tag=b_desc]").html(b_desc);
			$("#allpa_user_bill_list>[data-tag=b_amt]").text(b_amt);
			$("#allpa_user_bill_list>[data-tag=b_function]").html("已完成");
			$("<tr data-day='day'>"+$("#allpa_user_bill_list").html()+"</tr>").appendTo("#allpa_user_bill_data_tbody"); 
		}
		
		$("#allpa_user_bill_info").show();
	}else{
		$("#allpa_user_bill_info").hide();
	}
	
	// 卡片購買資訊
	if(bill_ready || bill_finished){
		$("#bill_data_tbody").html("");
		
		for(key in bill_ready) {
			var result = bill_ready[key];
			$("#bill_list>[data-tag=b_name]").text(result['product_name']);
			$("#bill_list>[data-tag=b_desc]").html(result['product_desc']);
			$("#bill_list>[data-tag=b_function]").html("<pre type='button' class='btn btn-default' onclick='do_activate("+result['order_no']+")'>啟 用</pre>");
			$("<tr data-day='day'>"+$("#bill_list").html()+"</tr>").appendTo("#bill_data_tbody"); 
		}
		
		for(key in bill_finished) {
			var result = bill_finished[key];
			$("#bill_list>[data-tag=b_name]").text(result['product_name']);
			$("#bill_list>[data-tag=b_desc]").html(result['product_desc']);
			$("#bill_list>[data-tag=b_function]").html("已啟用");
			$("<tr data-day='day'>"+$("#bill_list").html()+"</tr>").appendTo("#bill_data_tbody"); 
		}
		
		$("#user_bill_info").show();
	}else{
		$("#user_bill_info").hide();
	}
	
	show_item('user_allpa_info');
}

// 執行卡號綁定
/*
function do_register(event)
{
	event.preventDefault();

	if ($("#register_lpr").val() == "")
    {
		alertify_error("請填寫車號");
       	return false;
    }
	
	if ($("#register_barcode").val() == "")
    {
		alertify_error("請填寫卡號");
       	return false;
    }
	
	if(! $("#register_form").isValid()) return false;

	$.ajax
	({
		url: "<?=APP_URL?>register",
		type: "post",
		dataType:"text",
		data:{
			"register_lpr": $("#register_lpr").val(),
			"register_barcode": $("#register_barcode").val()
		},
		success: function(result)
		{
			if(!is_valid_result(result)){ return false; }
			
			if(!is_json_result(result)){ return false; }
			
			var jdata = JSON.parse(result);
			
			if(jdata.result_code == "OK")
			{
				alertify_success([
					jdata.result_msg, "<br/><br/>",
					"[歐Pa卡, 記名處理完成]", "<br/>",
					"* 車牌號碼 : ", jdata.register_lpr, "<br/>",
					"* 卡片號碼 : ", jdata.register_barcode, "<br/>"
					].join(''));
					
			}else{
				alertify_error([
					"綁定失敗..<br/><br/>",
					"[代碼] : ", jdata.result_code, "<br/>",
					"[訊息] : ", jdata.result_msg, "<br/>"
					].join(''));	
			}
			
		},
		error: function (xhr, ajaxOptions, thrownError) 
		{
			alertify_error("發生未知錯誤, 請稍候再試");
		}
	})
}
*/

$(document).ready(function()
{
	<?php /* validate  設定start */ ?>
	$.validate(
		{
			modules : 'security',
		}
	);
	<?php /* validate  設定end */ ?>
	
	
	
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
