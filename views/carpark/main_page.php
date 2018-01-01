<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>歐特儀管理系統</title>
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
    <style>                                            
    	.cario_list{text-align:center;vertical-align:middle;}
    </style>
    
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
                <a class="navbar-brand" href="">歐特儀管理系統</a>
            </div>
            <!-- /.navbar-top-links(左側選單) -->
            <div class="navbar-default sidebar" role="navigation">
                <div class="sidebar-nav navbar-collapse">
                    <ul class="nav" id="side-menu">
                        <!--li>
                            <a href="#"><i class="fa fa-edit fa-fw"></i>帳務管理<span class="fa arrow"></span></a>
                            <ul class="nav nav-second-level">
                                <li>
                                    <a href="#" onclick="show_item('rent_sync_all', 'rent_sync_all');">全部更新</a>
                                </li>
                                <li>
                                    <a href="#" onclick="show_item('rent_sync', 'rent_sync');">單站更新</a>
                                </li>
                                <li>
                                    <a href="#" onclick="show_item('rent_report', 'rent_report');">月租日報表</a>
                                </li>
								<li>
                                    <a href="#" onclick="show_item('tx_bill_query', 'tx_bill_query');">行動支付記錄</a>
                                </li>
								<li>
                                    <a href="#" onclick="show_item('tx_bill_ats_query', 'tx_bill_ats_query');">月租繳款機記錄</a>
                                </li>
                            </ul>
                        </li-->   
                        <li>
                            <a href="#"><i class="fa fa-user fa-fw"></i>現場管理<span class="fa arrow"></span></a>
                            <ul class="nav nav-second-level">     
                                <li>
                                    <a href="#" onclick="show_item('carin_query', 'carin_query');">出入場記錄</a>
                                </li>      
                                <li>
                                    <a href="#" onclick="show_item('cario_list', 'cario_list');">進出場現況表</a>
                                </li>
								<li>
                                    <a href="#" onclick="show_item('member_query', 'member_query');">會員現況</a>
                                </li>     								
                                <!--li>
                                    <a href="#" onclick="show_item('reversible_lane', 'reversible_lane');">調撥車道</a>
                                </li>
                                <li>
                                    <a href="#" onclick="show_item('member_add', 'member_add');">會員加入</a>
                                </li>
                                <li>
                                    <a href="#" onclick="show_item('opendoors', 'opendoors');">出入口開門</a>
                                </li-->  
                            </ul>
                            <!-- /.nav-second-level -->
                        </li>      

						<li>
                            <a href="#"><i class="fa fa-user fa-fw"></i>在席管理<span class="fa arrow"></span></a>
                            <ul class="nav nav-second-level">     
                                <li>
                                    <a href="#" onclick="show_item('pks_group_query', 'pks_group_query');">剩餘車位數</a>
                                </li>
                            </ul>
                        </li>			
						<li>
                            <a href="#"><i class="fa fa-user fa-fw"></i>場站設定<span class="fa arrow"></span></a>
                            <ul class="nav nav-second-level">     
                                <li>
                                    <a href="#" onclick="show_item('station_setting', 'station_setting');">設定檔</a>
                                </li>
                            </ul>
                        </li>							
                          
                        <!--li>
                            <a href="#"><i class="fa fa-user fa-fw"></i>查核<span class="fa arrow"></span></a>
                            <ul class="nav nav-second-level">     
                                <li>
                                    <a href="#" onclick="show_item('pks_check', 'pks_check');">在席查核</a>
                                </li>        
                                <li>
                                    <a href="#" onclick="reset_pks_check();">重設在席查核</a>
                                </li> 
                                <li>
                                    <a href="#" onclick="show_item('carin_check', 'carin_check');;">入場查核</a>
                                </li>   
                            </ul>
                        </li-->
						
						<li>
							<a href="#" onclick="logout(event)">登出</a>
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
                    <h1 class="page-header">管理作業</h1><?php /* 右側小表頭 */ ?>
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->    
            <?php /* ----- 更新所有場站 ----- */ ?>
            <div data-items="rent_sync_all" class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading"><?php /* 資料顯示區灰色小表頭 */ ?>
                            所有場站更新
                        </div>
                        <div class="panel-body">
                            <div data-rows class="row">
                                <div class="col-lg-6">
                                    <form role="form" method="post" id="rent_sync_all" action="<?=APP_URL?>rent_sync"> 
                                        <div class="form-group">
                                            <label>開始日期</label>
                                            <input type="date" name="start_date" class="form-control" />
                                        </div>   
                                        <div class="form-group">
                                            <label>結束日期</label>
                                            <input type="date" name="end_date" class="form-control" />
                                        </div>                       
                                        <input type="hidden" name="station_no" value="0" />
                                        <button type="submit" class="btn btn-default">全部更新</button>
                                        <button type="reset" class="btn btn-default">重填</button>
                                    </form>
                                </div>
                                <!-- /.col-lg-6 (nested) -->
                            </div>
                            <!-- /.row (nested) -->    
                            <div data-rows class="row"><?php /* ----- 全部更新後的訊息 ----- */ ?>
                                <div id="msg_rent_sync_all" class="col-lg-6"></div>
                            </div><?php /* ----- end 全部更新後的訊息 ----- */ ?>
                        </div>
                        <!-- /.panel-body -->
                    </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <?php /* ----- 更新所有場站(結束) ----- */ ?>          
            <?php /* ----- 單一場站月租異動同步 ----- */ ?>          
            <div data-items="rent_sync" class="row" style="display:none;">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading"><?php /* 資料顯示區灰色小表頭 */ ?>
                            單站更新
                        </div>
                        <div class="panel-body">
                            <div data-rows class="row">
                                <div class="col-lg-6">
                                    <form role="form"  method="post" form_type="rent_sync" action="<?=APP_URL?>rent_sync">    
                                    	<div class="form-group">
                                            <label>場站</label>
                                            <select name="station_no" class="form-control">
                                                <option value="12109">文中國小</option>
                                                <option>金城</option>
                                                <option>板橋國中</option>
                                                <option>二重疏洪道</option>
                                                <option>中正紀念堂兩廳院</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>開始日期</label>
                                            <input type="date" name="start_date" class="form-control">
                                        </div>   
                                        <div class="form-group">
                                            <label>結束日期</label>
                                            <input type="date" name="end_date" class="form-control">
                                        </div> 
                                        <button type="submit" class="btn btn-default">單站更新</button>
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
            <?php /* ----- 單一場站月租異動同步(結束) ----- */ ?>   
                      
            <?php /* ----- 剩餘車位清單 ----- */ ?> 
            <div data-items="available_curr" class="row" style="display:none;">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading"><?php /* 資料顯示區灰色小表頭 */ ?>
                            剩餘車位清單
                        </div>
                        <div class="panel-body">
                            <div data-rows class="row">
                                <div class="col-lg-12">
                            	<div class="dataTable_wrapper">
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th style="text-align:center;">編號</th>
                                            <th style="text-align:left;">場站名稱</th>
                                            <th style="text-align:right;">總數</th>
                                            <th style="text-align:right;">空位數</th>
                                            <th style="text-align:right;">入場數</th>
                                            <th style="text-align:right;">使用率</th>
                                        </tr>    
                                        <tr id="available_curr_wk" style="display:none;"><?php /* 提供即時剩餘車位清單 */ ?>
                                            <td data-tag="station_no" style="text-align:center;vertical-align: middle;"></td>
                                            <td data-tag="name" style="text-align:left;vertical-align: middle;"></td>
                                            <td data-tag="tot_pkg" style="text-align:right;vertical-align: middle;"></td>
                                            <td data-tag="ava_pkg" style="text-align:right;vertical-align: middle;"></td>
                                            <td data-tag="used_pkg" style="text-align:right;vertical-align: middle;"></td>
                                            <td data-tag="ratio_pkg" style="text-align:right;vertical-align: middle;"></td>
                                        </tr>                                     
                                    </thead>
                                    <tbody id="available_curr_tbody" style="font-size:14px;"></tbody>
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
            <?php /* ----- 剩餘車位清單(結束) ----- */ ?> 
            
            <?php /* ----- 剩餘車位設定 ----- */ ?> 
            <div data-items="available_set" class="row" style="display:none;">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading"><?php /* 資料顯示區灰色小表頭 */ ?>
                            剩餘車位設定
                        </div>
                        <div class="panel-body">
                            <div data-rows class="row">
                                <div class="col-lg-12">
                            	<div class="dataTable_wrapper">
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th style="text-align:center;">編號</th>
                                            <th style="text-align:left;">場站名稱</th>
                                            <th style="text-align:left;">總數</th>
                                            <th style="text-align:left;">空位數</th>
                                            <th style="text-align:right;">入場數</th>
                                            <th style="text-align:right;">使用率</th>
                                            <th style="text-align:center;">設定</th>
                                        </tr>   
                                        <tr id="available_list" style="display:none;"><?php /* 提供設定剩餘車位設定 */ ?>
                                            <td data-tag="station_no" style="text-align:center;vertical-align: middle;"></td>
                                            <td data-tag="name" style="text-align:left;vertical-align: middle;"></td>
                                            <td data-tag="tot_pkg" style="text-align:left;vertical-align: middle;"></td>
                                            <td data-tag="ava_pkg" style="text-align:left;vertical-align: middle;"></td>
                                            <td data-tag="used_pkg" style="text-align:right;vertical-align: middle;"></td>
                                            <td data-tag="ratio_pkg" style="text-align:right;vertical-align: middle;"></td>
                                            <td data-tag="edits" style="text-align:center;vertical-align: middle;"></td>
                                        </tr>
                                    </thead>
                                    <tbody id="available_tbody" style="font-size:14px;"></tbody>
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
            <?php /* ----- 剩餘車位設定(結束) ----- */ ?>   
            <div data-items="member_add" class="row" style="display:none;"><?php /* 會員加入填寫資料 */ ?>
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div id="member_data_type" class="panel-heading">新增會員資料</div><?php /* 資料顯示區灰色小表頭 */ ?>
                        <div class="panel-body">
                            <div data-rows class="row">
                                <div class="col-lg-6">
                                    <form id="member_add" role="form" method="post" action="<?=APP_URL?>member_add">  
                                        <div class="form-group">
                                            <label>*場站</label>
                                            <select class="form-control" name="station_no">
                                                <option value="<?=STATION_NO?>" selected>本站</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>*車牌號碼</label>
                                            <input id="ma_lpr" name="lpr" class="form-control" placeholder="限英數字碼" style="text-transform:uppercase" />
                                            <input id="ma_old_lpr" name="old_lpr" type="hidden" />
                                        </div> 
                                        <div class="form-group">
                                            <label>eTag</label>
                                            <input id="ma_etag" name="etag" class="form-control" placeholder="限英數字碼" style="text-transform:uppercase">
                                        </div> 
                                        <!--div class="form-group">
                                            <label>*租期選項</label><br />
                                            <label class="radio-inline">
                                                <input type="radio" name="period_pay" id="optionsRadiosInline1" value="1" checked>月繳
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" name="period_pay" id="optionsRadiosInline2" value="2">雙月繳
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" name="period_pay" id="optionsRadiosInline3" value="3">季繳
                                            </label> 
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" name="period_pay" id="optionsRadiosInline3" value="4">半年繳
                                            </label>
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" name="optionsRadiosInline" id="optionsRadiosInline3" value="5">年繳
                                            </label>
                                        </div--> 
                                        <div class="form-group">
                                            <label>開始日期</label>
											<!--input  id="ma_start_date" type="text" name="start_date" class="form-control"
												data-validation="length" 
												data-validation-length="1-20"
												data-validation-error-msg="請輸入開始日期"/-->
                                            <!--input  id="ma_start_date" type="datetime" name="start_date" class="form-control" /-->
											<input  id="ma_start_date" type="datetime-local" name="start_date" class="form-control" step="1"/>
                                        </div>   
                                        <div class="form-group">
                                            <label>結束日期</label>
											<!--input id="ma_end_date" type="text" name="end_date" class="form-control"
												data-validation="length" 
												data-validation-length="1-20"
												data-validation-error-msg="請輸入結束日期"/-->
                                            <!--input id="ma_end_date" type="datetime" name="end_date" class="form-control" /-->
											<input id="ma_end_date" type="datetime-local" name="end_date" class="form-control" step="1"/>
                                        </div>
                                        <div class="form-group">
                                            <label>*姓名/公司名稱</label>
                                            <input id="ma_member_name" name="member_name" class="form-control">
                                        </div>
                                        <div class="form-group">
                                            <label>*手機</label>
                                            <input   id="ma_mobile_no" name='mobile_no' class="form-control">
                                        </div>   
                                        <!-- div class="form-group">
                                            <label>電子郵件</label>
                                            <input name='email' class="form-control">
                                        </div> 
                                        <div class="form-group">
                                            <label>車型</label>
                                            <input name="car_model" class="form-control">
                                        </div> 
                                        <div class="form-group">
                                            <label>顏色</label>
                                            <input name="color" class="form-control">
                                        </div>  
                                        <div class="form-group">
                                            <label>年份</label>
                                            <input name="car_year" class="form-control">
                                        </div -->       
                                        <div class="form-group">
                                            <label>合約號碼</label>
                                            <input id="ma_contract_no" name="contract_no" class="form-control">
                                        </div>             
                                        <div class="form-group">
                                            <label>租金金額</label>
                                            <input id="ma_amt" name="amt" class="form-control">
                                        </div>
                                        <div class="form-group">
                                            <label>身份證號/統一編號</label>
                                            <input id="ma_member_id"  name="member_id" class="form-control">
                                        </div>
                                        <div class="form-group">
                                            <label>電話(宅)</label>
                                            <input id="ma_tel_h" name='tel_h' class="form-control">
                                        </div> 
                                        <div class="form-group">
                                            <label>電話(公)</label>
                                            <input  id="ma_tel_o" name='tel_o' class="form-control">
                                        </div>           
                                        <!--div class="form-group">
                                            <label>里名</label>
                                            <input name='village' class="form-control">
                                        </div-->
                                        <div class="form-group">
                                            <label>地址</label>
                                            <input id="ma_addr" name='addr' class="form-control">
                                        </div>
                                        <button type="submit" class="btn btn-default">存檔</button>
                                        <button type="reset" class="btn btn-default">重填</button>
                                        <input id="ma_member_no" type="hidden" name="member_no" value="0" />
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
            <?php /* ----- 會員加入填寫資料(結束) ----- */ ?> 
            
            <?php /* ----- 會員查詢 ----- */?>
            <div data-items="member_query" class="row" style="display:none;">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            會員現況表
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <div class="dataTable_wrapper">
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th style="text-align:left;">車牌</th>
                                            <th style="text-align:left;">姓名</th>
                                            <th style="text-align:center;">手機</th>
                                            <th style="text-align:center;">開始日</th>
                                            <th style="text-align:center;">結束日</th>
                                            <th style="text-align:center;">合約號</th>
                                            <!--th style="text-align:center;">eTag</th>
                                            <th style="text-align:center;">租金</th>
                                            <th style="text-align:center;">功能</th-->
											<th style="text-align:center;">有效期限</th>
											<th style="text-align:center;">場站編號</th>
											<th style="text-align:center;">更新時間</th>
                                        </tr>
                                    </thead>
                                    <tbody id="member_list" style="font-size:14px;"></tbody>
                                </table>
                            </div><?php /* ----- end of dataTable_wrapper ----- */?>  
                        </div><?php /* ----- end of panel-body ----- */?>
                    </div><?php /* ----- end of panel panel-default ----- */?>
                </div><?php /* ----- end of col-lg-12 ----- */?>
            </div>
            <?php /* ----- 會員查詢(結束) ----- */?>
			
			<?php /* ----- 行動支付記錄 ----- */?>
            <div data-items="tx_bill_query" class="row" style="display:none;">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            行動支付記錄
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <div class="dataTable_wrapper">
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th style="text-align:left;">車牌</th>
                                            <th style="text-align:left;">金額</th>
                                            <th style="text-align:center;">支付訊息</th>
                                            <th style="text-align:center;">支付種類</th>
                                            <th style="text-align:center;">發票號碼</th>
                                            <th style="text-align:center;">入場時間</th>
                                            <th style="text-align:center;">結算時間</th>
											<th style="text-align:center;">限時離場</th>
                                            <th style="text-align:center;">發票統編</th>
                                            <th style="text-align:center;">發票信箱</th>
											<th style="text-align:center;">發票簡訊</th>
											<th style="text-align:center;">交易時間</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tx_bill_list" style="font-size:14px;"></tbody>
                                </table>
                            </div><?php /* ----- end of dataTable_wrapper ----- */?>  
                        </div><?php /* ----- end of panel-body ----- */?>
                    </div><?php /* ----- end of panel panel-default ----- */?>
                </div><?php /* ----- end of col-lg-12 ----- */?>
            </div>
            <?php /* ----- 行動支付記錄(結束) ----- */?>
			
			<?php /* ----- 月租繳款機記錄 ----- */?>
            <div data-items="tx_bill_ats_query" class="row" style="display:none;">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            月租繳款機記錄
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <div class="dataTable_wrapper">
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th style="text-align:left;">車牌</th>
                                            <th style="text-align:left;">金額</th>
                                            <th style="text-align:center;">支付訊息</th>
                                            <th style="text-align:center;">支付種類</th>
                                            <th style="text-align:center;">發票號碼</th>
                                            <th style="text-align:center;">到期日</th>
                                            <th style="text-align:center;">續期開始日</th>
											<th style="text-align:center;">續期到期日</th>
											<th style="text-align:center;">會員名稱</th>
                                            <th style="text-align:center;">發票統編</th>
                                            <th style="text-align:center;">發票信箱</th>
											<th style="text-align:center;">發票簡訊</th>
											<th style="text-align:center;">交易時間</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tx_bill_ats_list" style="font-size:14px;"></tbody>
                                </table>
                            </div><?php /* ----- end of dataTable_wrapper ----- */?>  
                        </div><?php /* ----- end of panel-body ----- */?>
                    </div><?php /* ----- end of panel panel-default ----- */?>
                </div><?php /* ----- end of col-lg-12 ----- */?>
            </div>
            <?php /* ----- 月租繳款機記錄(結束) ----- */?>
                                         
            <?php /* ----- 出入場記錄 ----- */?>
            <div data-items="carin_query" class="row" style="display:none;">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">出入場記錄</div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">       
                            <div class="dataTable_wrapper">
                                <table class="table table-striped table-bordered table-hover">
                                	<tr> 
                                    	<td style="text-align:right;">車號</td>   
                                        <td style="text-align:left;"><input type="text" id="lpr_query" name="lpr_query" class="form-control" style="text-transform:uppercase" placeholder="請至少輸入四碼" /></td>
                                    	<td style="text-align:left;"><input type="button" name="lpr_query" value="查詢" onclick="carin_lpr_query();" /></td>
                                    </tr>
                                    <!--tr> 
                                    	<td style="text-align:right;">時間</td>   
                                        <td style="text-align:left;">
                                        	<input type="datetime-local" id="carin_time_query" />&nbsp;&nbsp;前後範圍
                                        	<select name="minutes_range" id="minutes_range">
												<option value="10" selected>10分鐘</option>
												<option value="15">15分鐘</option>
												<option value="20">20分鐘</option>
												<option value="30">30分鐘</option>
                                            </select>
                                        </td>
                                    	<td style="text-align:left;"><input type="button" value="查詢" onclick="carin_time_query();" /></td>
                                    </tr-->   
									<tr> 
                                    	<td style="text-align:right;">入場<br/>車辨失敗</td>   
                                        <td style="text-align:left;">
                                        	<input type="datetime-local" id="carin_none_query"/>&nbsp;&nbsp;前後範圍
                                        	<select name="hours_range" id="hours_range">
												<option value="1" selected>1小時</option>
												<option value="3">3小時</option>
												<option value="12">12小時</option>
												<option value="24">24小時</option>
                                            </select>
                                        </td>
                                    	<td style="text-align:left;"><input type="button" value="查詢" onclick="carin_none_query();" /></td>
                                    </tr> 
                                </table>
                            </div><?php /* ----- end of dataTable_wrapper ----- */?> 
                            <!--div id="carin_query_list" class="dataTable_wrapper" style="display:none;"-->
                            <div id="carin_query_list" class="dataTable_wrapper">
                                <table id="lpr_query_list" class="table table-striped table-bordered table-hover">
                                <thead>
                                        <tr>
                                            <th style="text-align:center;">出入口</th>
                                            <th style="text-align:center;">時間</th>
                                            <th style="text-align:center;">車號</th>
                                            <th style="text-align:center;">eTag</th>
                                            <th style="text-align:center;">車主</th>
                                            <th style="text-align:center;">照片</th>
                                        </tr> 
    							</thead>
    							<tbody id="carin_query_tbody" style="font-size:14px;"></tbody>
                                </table>
                            </div><?php /* ----- end of dataTable_wrapper ----- */?>  
                        </div><?php /* ----- end of panel-body ----- */?>
                    </div><?php /* ----- end of panel panel-default ----- */?>
                </div><?php /* ----- end of col-lg-12 ----- */?>
            </div>
            <?php /* ----- 出入場記錄(結束) ----- */?>
            
            <?php /* ----- 進出場現況表 ----- */?>
            <div data-items="cario_list" class="row" style="display:none;">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            進出場現況表
							&nbsp;<button id='create_cario_btn' class="btn btn-large btn-success pull-right" style="font-size:16px;" onclick='show_create_cario_dialog();'>手動建檔</button>
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <div class="dataTable_wrapper">
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th style="text-align:center;">出入口</th>
                                            <th style="text-align:center;">時間</th>
                                            <th style="text-align:center;">車號</th>
                                            <th style="text-align:center;">eTag</th>
                                            <th style="text-align:center;">車主</th>
                                            <th style="text-align:center;">照片</th>
                                        </tr>   
                                    </thead>
                                    <tbody id="cario_list_tbody" style="font-size:14px;">  
                                    <tr id="cario_0">
                                    <td class="cario_list" data-tag="io_name"></td>
                                    <td class="cario_list" data-tag="io_time"></td>
                                    <td class="cario_list" data-tag="lpr"></td>
                                    <td class="cario_list" data-tag="etag"></td>
                                    <td class="cario_list" data-tag="owner"></td>
                                    <td class="cario_list" data-tag="pic_name"><img height="57" width="150" class="resize" /></td>
                                    </tr>    
                                    <tr id="cario_1">
                                    <td class="cario_list" data-tag="io_name"></td>
                                    <td class="cario_list" data-tag="io_time"></td>
                                    <td class="cario_list" data-tag="lpr"></td>
                                    <td class="cario_list" data-tag="etag"></td>
                                    <td class="cario_list" data-tag="owner"></td>
                                    <td class="cario_list" data-tag="pic_name"><img height="57" width="150" class="resize" /></td>
                                    </tr>
                                    <tr id="cario_2">
                                    <td class="cario_list" data-tag="io_name"></td>
                                    <td class="cario_list" data-tag="io_time"></td>
                                    <td class="cario_list" data-tag="lpr"></td>
                                    <td class="cario_list" data-tag="etag"></td>
                                    <td class="cario_list" data-tag="owner"></td>
                                    <td class="cario_list" data-tag="pic_name"><img height="57" width="150" class="resize" /></td>
                                    </tr>
                                    <tr id="cario_3">
                                    <td class="cario_list" data-tag="io_name"></td>
                                    <td class="cario_list" data-tag="io_time"></td>
                                    <td class="cario_list" data-tag="lpr"></td>
                                    <td class="cario_list" data-tag="etag"></td>
                                    <td class="cario_list" data-tag="owner"></td>
                                    <td class="cario_list" data-tag="pic_name"><img height="57" width="150" class="resize" /></td>
                                    </tr>
                                    <tr id="cario_4">
                                    <td class="cario_list" data-tag="io_name"></td>
                                    <td class="cario_list" data-tag="io_time"></td>
                                    <td class="cario_list" data-tag="lpr"></td>
                                    <td class="cario_list" data-tag="etag"></td>
                                    <td class="cario_list" data-tag="owner"></td>
                                    <td class="cario_list" data-tag="pic_name"><img height="57" width="150" class="resize" /></td>
                                    </tr>
                                    <tr id="cario_5">
                                    <td class="cario_list" data-tag="io_name"></td>
                                    <td class="cario_list" data-tag="io_time"></td>
                                    <td class="cario_list" data-tag="lpr"></td>
                                    <td class="cario_list" data-tag="etag"></td>
                                    <td class="cario_list" data-tag="owner"></td>
                                    <td class="cario_list" data-tag="pic_name"><img height="57" width="150" class="resize" /></td>
                                    </tr>
                                    <tr id="cario_6">
                                    <td class="cario_list" data-tag="io_name"></td>
                                    <td class="cario_list" data-tag="io_time"></td>
                                    <td class="cario_list" data-tag="lpr"></td>
                                    <td class="cario_list" data-tag="etag"></td>
                                    <td class="cario_list" data-tag="owner"></td>
                                    <td class="cario_list" data-tag="pic_name"><img height="57" width="150" class="resize" /></td>
                                    </tr>
                                    <tr id="cario_7">
                                    <td class="cario_list" data-tag="io_name"></td>
                                    <td class="cario_list" data-tag="io_time"></td>
                                    <td class="cario_list" data-tag="lpr"></td>
                                    <td class="cario_list" data-tag="etag"></td>
                                    <td class="cario_list" data-tag="owner"></td>
                                    <td class="cario_list" data-tag="pic_name"><img height="57" width="150" class="resize" /></td>
                                    </tr>
                                    <tr id="cario_8">
                                    <td class="cario_list" data-tag="io_name"></td>
                                    <td class="cario_list" data-tag="io_time"></td>
                                    <td class="cario_list" data-tag="lpr"></td>
                                    <td class="cario_list" data-tag="etag"></td>
                                    <td class="cario_list" data-tag="owner"></td>
                                    <td class="cario_list" data-tag="pic_name"><img height="57" width="150" class="resize" /></td>
                                    </tr>
                                    <tr id="cario_9">
                                    <td class="cario_list" data-tag="io_name"></td>
                                    <td class="cario_list" data-tag="io_time"></td>
                                    <td class="cario_list" data-tag="lpr"></td>
                                    <td class="cario_list" data-tag="etag"></td>
                                    <td class="cario_list" data-tag="owner"></td>
                                    <td class="cario_list" data-tag="pic_name"><img height="57" width="150" class="resize" /></td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div><?php /* ----- end of dataTable_wrapper ----- */?>  
                        </div><?php /* ----- end of panel-body ----- */?>
                    </div><?php /* ----- end of panel panel-default ----- */?>
                </div><?php /* ----- end of col-lg-12 ----- */?>
            </div>
            <?php /* ----- 進出場現況表(結束) ----- */?>  
                                       
            <?php /* ----- 調撥車道 ----- */?>
            <div data-items="reversible_lane" class="row" style="display:none;">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            調撥車道
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <div class="dataTable_wrapper">
                            	<form id="lane_form">
                                        <div class="form-group">
                                            <label>第1車道-入&nbsp;&nbsp;&nbsp;</label>
                                            <label class="radio-inline">
                                                <input type="radio" name="lane_0" data-lane_no="0" value="1">啟用
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" name="lane_0" data-lane_no="0" value="0">停用
                                            </label>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label>第2車道-入&nbsp;&nbsp;&nbsp;</label>
                                            <label class="radio-inline">
                                                <input type="radio" name="lane_1" data-lane_no="1" value="1">啟用
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" name="lane_1" data-lane_no="1" value="0">停用 (調撥車道入)
                                            </label>
                                        </div> 
                                        
                                        <div class="form-group">
                                            <label>第3車道-出&nbsp;&nbsp;&nbsp;</label>
                                            <label class="radio-inline">
                                                <input type="radio" name="lane_2" data-lane_no="2" value="1">啟用
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" name="lane_2" data-lane_no="2" value="0">停用 (調撥車道出)
                                            </label>
                                        </div>   
                                        
                                        <div class="form-group">
                                            <label>第4車道-出&nbsp;&nbsp;&nbsp;</label>
                                            <label class="radio-inline">
                                                <input type="radio" name="lane_3" data-lane_no="3" value="1">啟用
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" name="lane_3" data-lane_no="3" value="0">停用
                                            </label>
                                        </div>
                            	</form>
                            </div><?php /* ----- end of dataTable_wrapper ----- */?>  
                        </div><?php /* ----- end of panel-body ----- */?>
                    </div><?php /* ----- end of panel panel-default ----- */?>
                </div><?php /* ----- end of col-lg-12 ----- */?>
            </div>
            <?php /* ----- 調撥車道(結束) ----- */?> 
            
            <?php /* ----- 出入口開門 ----- */?>
            <div data-items="opendoors" class="row" style="display:none;">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">出入口開門</div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <div class="dataTable_wrapper">
                                <table class="table table-striped table-bordered table-hover">
                                        <tr>
                                            <td style="text-align:left;">第1車道 - 入場</td>
                                            <td style="text-align:center;"><input type="button" value="開門" onclick="opendoors('0');" /></td>
                                        </tr>   
                                        <tr>
                                            <td style="text-align:left;">第2車道 - 調撥入場</td>
                                            <td style="text-align:center;"><input type="button" value="開門" onclick="opendoors('1');" /></td>
                                        </tr>
                                        <tr>
                                            <td style="text-align:left;">第3車道 - 調撥出場</td>
                                            <td style="text-align:center;"><input type="button" value="開門" onclick="opendoors('2');" /></td>
                                        </tr>
                                        <tr>
                                            <td style="text-align:left;">第4車道 - 出場</td>
                                            <td style="text-align:center;"><input type="button" value="開門" onclick="opendoors('3');" /></td>
                                        </tr>
                                </table>
                            </div><?php /* ----- end of dataTable_wrapper ----- */?>  
                        </div><?php /* ----- end of panel-body ----- */?>
                    </div><?php /* ----- end of panel panel-default ----- */?>
                </div><?php /* ----- end of col-lg-12 ----- */?>
            </div>
            <?php /* ----- 出入口開門(結束) ----- */?>  
            <?php /* ----- 在席查核清單 ----- */?>
            <div data-items="pks_check" class="row" style="display:none;">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            在席查無入場資料清單(共<span id="tot_pks_check"></span>筆, 測試中)
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <div class="dataTable_wrapper">
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th style="text-align:center;">車格號</th>
                                            <th style="text-align:center;">車號</th>
                                            <th style="text-align:center;">入格時間</th>
                                            <th style="text-align:center;">照片</th>
                                        </tr>   
                                    </thead>
                                    <tbody id="pks_list_tbody" style="font-size:14px;">  
                                    </tbody>
                                </table>
                            </div><?php /* ----- end of dataTable_wrapper ----- */?>  
                        </div><?php /* ----- end of panel-body ----- */?>
                    </div><?php /* ----- end of panel panel-default ----- */?>
                </div><?php /* ----- end of col-lg-12 ----- */?>
            </div>
            <?php /* ----- 在席查核清單(結束) ----- */?>  
                                  
            <?php /* ----- 入場查核清單 ----- */?>
            <div data-items="carin_check" class="row" style="display:none;">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            入場查無在席資料清單(共<span id="tot_carin_check"></span>筆, 測試中)
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <div class="dataTable_wrapper">
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th style="text-align:center;">車號</th>
                                            <th style="text-align:center;">時間</th>
                                            <th style="text-align:center;">類別</th>
                                            <th style="text-align:center;">照片</th>
                                        </tr>   
                                    </thead>
                                    <tbody id="carin_list_tbody" style="font-size:14px;">  
                                    </tbody>
                                </table>
                            </div><?php /* ----- end of dataTable_wrapper ----- */?>  
                        </div><?php /* ----- end of panel-body ----- */?>
                    </div><?php /* ----- end of panel panel-default ----- */?>
                </div><?php /* ----- end of col-lg-12 ----- */?>
            </div>
            <?php /* ----- 入場查核清單(結束) ----- */?>  
			
            <?php /* ----- 樓層剩餘車位數調整 ----- */?>
            <div data-items="pks_group_query" class="row" style="display:none;">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            樓層車位數 （按 F5 更新）
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <div class="dataTable_wrapper">
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th style="text-align:left;">樓層</th>
											<th style="text-align:left;">樓層 ID</th>
                                            <th style="text-align:center;">車位總數</th>
											<th style="text-align:center;">已使用</th>
											<th style="text-align:center;">未使用</th>
											<th style="text-align:center;">微調值</th>
                                            <th style="text-align:center;">剩餘車位數微調</th>
											<th style="text-align:center;">空車位顯示值</th>
                                        </tr>
                                    </thead>
                                    <tbody id="pks_group_list" style="font-size:20px;"></tbody>
                                </table>
                            </div><?php /* ----- end of dataTable_wrapper ----- */?>  
                        </div><?php /* ----- end of panel-body ----- */?>
                    </div><?php /* ----- end of panel panel-default ----- */?>
                </div><?php /* ----- end of col-lg-12 ----- */?>
            </div>
            <?php /* ----- 樓層剩餘車位數調整(結束) ----- */?>
                                         
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
	
	
    <!-- Custom Theme JavaScript -->
    <script src="<?=BOOTSTRAPS?>dist/js/sb-admin-2.js"></script>  
    <div id="works" style="display:none;"></div><?php /* 作為浮動顯示區之用 */ ?> 
</body>
</html>   

<script>
var AltobObject = AltobObject || {}; // ALTOB 專用命名空間
/*
	客製化參數
*/
(function(){
	AltobObject.xvars = {};
	AltobObject.station_no = '<?= $station_no; ?>';
	AltobObject.station_name = '<?= $station_name; ?>';
})();

var timer;
var timeout_sec = 10;<?php /* 每多少秒檢查一次空車位 */ ?> 

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
function alertify_msg($msg)
{
	alertify.set({ labels: {
		ok     : "確定"
	} });
	alertify.alert($msg, function (e){
		// do nothing
	});
}

<?php /* 連接mqtt Queue */ ?>
/*
// var client = new Paho.MQTT.Client("192.168.0.135", 8000, "cario_now");
var client = new Paho.MQTT.Client("61.220.179.128", 1883, "cario_now");

// set callback handlers
client.onConnectionLost = onConnectionLost;
client.onMessageArrived = onMessageArrived;
 
// called when the client connects
function onConnect() 
{
	// Once a connection has been made, make a subscription and send a message.
	console.log("onConnect");
	client.subscribe("cario_now", 2);
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
} 
*/  

// 微調剩餘車位數
function pks_availables_update(idx, value, station_no)
{	
	//console.log(idx + ", " + value);
	$.ajax
	({
		url: "<?=APP_URL?>pks_availables_update/" + idx + "/" + value+ "/" + station_no,
		type: "post", 
		dataType:"json",
		data: {},
		success: function(jdata)
		{       
			if(jdata > 0)
			{
				alertify_log("調整完成! 請稍侯");
				
				show_item('pks_group_query', 'pks_group_query'); // refresh
			}else{
				alertify_log("沒有任何變化!");
			}
		}
	});  
}

// 載入頁面
function load_page(tags)
{
	if ($("[data-items='"+tags+"']").length == 0)	// 第一次loading
    {     
		$.ajax
		({
			url:"<?=APP_URL?>get_html",
        			async:false,    
        			timeout:1500,
            		type:"post", 
            		dataType:"text",
            		data:{"tag_name":tags},
            		success:function(jdata)
            		{
            	    	$("#page-wrapper").append(jdata);  
            	    }
		}); 
	}
}
  
<?php /* 顯示指定項目 */ ?>
function show_item(tags, type)
{              
	// if (timer) { window.clearTimeout(timer); }
         
    // client.disconnect();         
    
    <?php /* 新增月租資料, 設定初始值 */ ?>             
    if (type == "member_add")
    {                            
    	$("#ma_lpr").val("");
    	$("#ma_old_lpr").val("");
    	$("#ma_etag").val("");
        $("#ma_start_date").val("");
        $("#ma_end_date").val("");
        $("#ma_member_name").val("");
        $("#ma_mobile_no").val("");
        $("#ma_contract_no").val("");
        $("#ma_amt").val("");
    	$("#ma_member_no").val("0");
    	$("#member_data_type").text("新增會員資料");
    }
    
    switch(tags)
    {          
		// -- 設定檔 --			
		case "station_setting":
			load_page(tags);
			reload_station_setting(type);
			break;
	
		<?php /* 樓層資訊查詢, 並列出清單 */ ?>
    	case "pks_group_query":
        	$("#pks_group_list").html("");<?php /* 清除原內容 */ ?>
            $.ajax
        	({
        		url: "<?=APP_URL?>pks_group_query",
            	type: "post", 
            	dataType:"json",
            	data: {},
            	success: function(jdata)
            	{       
                	var pks_group_list = [];  
                	for(idx in jdata)
                    {                                         
                    	mno = jdata[idx]['group_id'];
                    	pks_group_list = pks_group_list.concat([
						"<tr><td id='pks_group_", mno, "' data-group_id='", mno, "' data-group_id='", jdata[idx]['group_id'], "' data-group_name='", jdata[idx]['group_name'], "' data-tot='", jdata[idx]['tot'] ,"' data-station_no='", jdata[idx]['station_no'] ,"</td>", 	
                    		"<td id='group_name_", mno, "' style='text-align:left; vertical-align:middle; '>", jdata[idx]['group_name'], "</td>", 	
							"<td id='group_id_", mno, "' style='text-align:left; vertical-align:middle; '>", jdata[idx]['group_id'], "</td>", 	
							"<td id='tot_", mno, "' style='text-align:center; vertical-align:middle; '>", jdata[idx]['tot'], "</td>", 
							"<td id='parked_", mno, "' style='text-align:center; vertical-align:middle; '>", jdata[idx]['parked'], "</td>", 
							"<td id='real_availables_", mno, "' style='text-align:center; vertical-align:middle; '>", jdata[idx]['availables'] - jdata[idx]['renum'], "</td>", 
							"<td id='renum_", mno, "' style='text-align:center; vertical-align:middle; color:blue; '>", jdata[idx]['renum'], "</td>", 
                    		"<td style='text-align:center;vertical-align:middle; '>", 
								"<button class='btn btn-default' onclick='pks_availables_update(\"", mno, "\", 1, ", jdata[idx]['station_no'],");'>+1</button>&nbsp", 
								"<button class='btn btn-default' onclick='pks_availables_update(\"", mno, "\", -1, ", jdata[idx]['station_no'],");'>-1</button>&nbsp", 
								"<button class='btn btn-default' onclick='pks_availables_update(\"", mno, "\", 0, ", jdata[idx]['station_no'],");'>重設</button>", 
							"<td id='availables_", mno, "' style='text-align:center; vertical-align:middle; color:red; '>", jdata[idx]['availables'], "</td>", 
							"</td>",
                    	"</tr>"]);
                    }
                	$("#pks_group_list").append(pks_group_list.join(''));  
            	}
        	});  
        	break; 
	
    	<?php /* 會員查詢, 並列出清單 */ ?>
    	case "member_query":
        	$("#member_list").html("");<?php /* 清除原內容 */ ?>
            $.ajax
        	({
        		url: "<?=APP_URL?>member_query",
            	type: "post", 
            	dataType:"json",
            	data: {},
            	success: function(jdata)
            	{       
                	var member_list = [];  
                	for(idx in jdata)
                    {                                         
                    	mno = jdata[idx]['member_no'];
                    	member_list = member_list.concat([
						"<tr><td id='lpr_", mno, "' data-member_no='", mno, "' data-member_id='", jdata[idx]['member_id'], "' data-tel_o='", jdata[idx]['tel_o'], "' data-tel_h='", jdata[idx]['tel_h'], "' data-addr='", jdata[idx]['addr'], "' style='text-align:left; '>", jdata[idx]['lpr'], "</td>", 	
                    		"<td id='name_", mno, "' style='text-align:left; '>", jdata[idx]['member_name'], "</td>", 	
                    		"<td id='mobile_", mno, "' style='text-align:center; '>", jdata[idx]['mobile_no'], "</td>", 	
                    		"<td id='sdate_", mno, "' style='text-align:center; '>", jdata[idx]['start_date'], "</td>", 	
                    		"<td id='edate_", mno, "' style='text-align:center; '>", jdata[idx]['end_date'], "</td>", 	
                    		"<td id='contract_", mno, "' style='text-align:center; '>", jdata[idx]['contract_no'], "</td>", 	    
                    		//"<td id='etag_", mno, "' style='text-align:center; '>", jdata[idx]['etag'], "</td>", 	    
                    		//"<td id='amt_", mno, "' style='text-align:center; '>", jdata[idx]['amt'], "</td>", 	
                    		//"<td style='text-align:center, '><select id='sel_", mno, "' onChange='member_modify(", mno, "); '><option value='choice'>請選擇</option><option value='modify'>修改</option><option value='delete'>刪除</option></select></td>", 	
							"<td id='valid_time_", mno, "' style='text-align:center; '>", jdata[idx]['valid_time'], "</td>", 	    
							"<td id='station_no_", mno, "' style='text-align:center; '>", jdata[idx]['station_no'], "</td>", 	    
							"<td id='update_time_", mno, "' style='text-align:center; '>", jdata[idx]['update_time'], "</td>", 	    
                    	"</tr>"]);
                    }
                	$("#member_list").append(member_list.join(''));  
            	}
        	});  
        	break; 

    	<?php /* 行動支付記錄 */ ?>
    	case "tx_bill_query":
        	$("#tx_bill_list").html("");<?php /* 清除原內容 */ ?>
            $.ajax
        	({
        		url: "<?=APP_URL?>tx_bill_query",
            	type: "post", 
            	dataType:"json",
            	data: {},
            	success: function(jdata)
            	{       
                	var tx_bill_list = [];  
                	for(idx in jdata)
                    {                                         
                    	mno = jdata[idx]['order_no'];
						tx_bill_list = tx_bill_list.concat([
                    	"<tr><td id='lpr_", mno, "' data-order_no='", mno, "' style='text-align:left; '>", jdata[idx]['lpr'], "</td>", 	
							"<td id='amt_", mno, "' style='text-align:center; '>", jdata[idx]['amt'], "</td>", 
							"<td id='rtn_msg_", mno, "' style='text-align:center; '>", jdata[idx]['rtn_msg'], "</td>", 	
							"<td id='payment_type_", mno, "' style='text-align:center; '>", jdata[idx]['payment_type'], "</td>", 							
							"<td id='invoice_no_", mno, "' style='text-align:center; '>", jdata[idx]['invoice_no'], "</td>", 
                    		"<td id='in_time_", mno, "' style='text-align:center; '>", jdata[idx]['in_time'], "</td>", 	
                    		"<td id='balance_time_", mno, "' style='text-align:center; '>", jdata[idx]['balance_time'], "</td>", 	
							"<td id='out_before_time_", mno, "' style='text-align:center; '>", jdata[idx]['out_before_time'], "</td>", 	
							"<td id='company_no_", mno, "' style='text-align:center; '>", jdata[idx]['company_no'], "</td>", 
							"<td id='email_", mno, "' style='text-align:center; '>", jdata[idx]['email'], "</td>", 
							"<td id='mobile_", mno, "' style='text-align:center; '>", jdata[idx]['mobile'], "</td>", 
							"<td id='tx_time_", mno, "' style='text-align:center; '>", jdata[idx]['tx_time'], "</td>", 
                    	"</tr>"]);
                    }
                	$("#tx_bill_list").append(tx_bill_list.join(''));  
            	}
        	});  
        	break;
			
		case "tx_bill_ats_query":
        	$("#tx_bill_ats_list").html("");<?php /* 清除原內容 */ ?>
            $.ajax
        	({
        		url: "<?=APP_URL?>tx_bill_ats_query",
            	type: "post", 
            	dataType:"json",
            	data: {},
            	success: function(jdata)
            	{       
                	var tx_bill_ats_list = [];  
                	for(idx in jdata)
                    {                                         
                    	mno = jdata[idx]['order_no'];
                    	tx_bill_ats_list = tx_bill_ats_list.concat([
						"<tr><td id='lpr_", mno, "' data-order_no='", mno, "' style='text-align:left; '>", jdata[idx]['lpr'], "</td>", 
							"<td id='amt_", mno, "' style='text-align:center; '>", jdata[idx]['amt'], "</td>", 
							"<td id='rtn_msg_", mno, "' style='text-align:center; '>", jdata[idx]['rtn_msg'], "</td>", 	
							"<td id='payment_type_", mno, "' style='text-align:center; '>", jdata[idx]['payment_type'], "</td>", 							
							"<td id='invoice_no_", mno, "' style='text-align:center; '>", jdata[idx]['invoice_no'], "</td>", 
                    		"<td id='end_time_", mno, "' style='text-align:center; '>", jdata[idx]['end_time'], "</td>", 	
                    		"<td id='next_start_time_", mno, "' style='text-align:center; '>", jdata[idx]['next_start_time'], "</td>", 	
							"<td id='next_end_time_", mno, "' style='text-align:center; '>", jdata[idx]['next_end_time'], "</td>", 	
							"<td id='member_name_", mno, "' style='text-align:center; '>", jdata[idx]['member_name'], "</td>", 
							"<td id='company_no_", mno, "' style='text-align:center; '>", jdata[idx]['company_no'], "</td>", 
							"<td id='email_", mno, "' style='text-align:center; '>", jdata[idx]['email'], "</td>", 
							"<td id='mobile_", mno, "' style='text-align:center; '>", jdata[idx]['mobile'], "</td>", 
							"<td id='tx_time_", mno, "' style='text-align:center; '>", jdata[idx]['tx_time'], "</td>", 
                    	"</tr>"]);
                    }
                	$("#tx_bill_ats_list").append(tx_bill_ats_list.join(''));  
            	}
        	});  
        	break; 			
                           
        <?php /* ----- 入場查詢 ----- */ ?>    
        case "carin_query":
        	$("#lpr_query").val(""); 
            $("#carin_query_list").hide();
        	break;       
            
        <?php /* 進出場現況表 */ ?>    
        case "cario_list":
            $.ajax
        	({
        		url: "<?=APP_URL?>cario_list",
            	type: "post", 
            	dataType:"json",
            	data: {},
            	success: function(jdata)
            	{       
                	for(idx in jdata)
                    {                                         
                    	$("#cario_"+idx+">[data-tag=io_name]").text(jdata[idx]['io_name']);
                    	$("#cario_"+idx+">[data-tag=io_time]").html(jdata[idx]['io_time']);
						
						// 車辨失敗可新增車號
						if(jdata[idx]['lpr'] == 'NONE')
							$("#cario_"+idx+">[data-tag=lpr]").html('<span style="font-size:16px;">車辨失敗</span><br/><button class="btn-default" onclick="show_upd_cario_dialog(' + jdata[idx]['cario_no'] + ', \'' + jdata[idx]['lpr'] + '\');">輸入車號</button>');
						else
							$("#cario_"+idx+">[data-tag=lpr]").html('<span style="font-size:28px;">' + jdata[idx]['lpr'] +'</span>');
						
                    	$("#cario_"+idx+">[data-tag=etag]").text(jdata[idx]['etag']);
                    	$("#cario_"+idx+">[data-tag=owner]").text(jdata[idx]['owner']);
                    	$("#cario_"+idx+">[data-tag=pic_name]>img").attr("src",jdata[idx]['pic_name']);   
                    }
            	}
        	}); 
            
            // connect the client
			// client.connect({onSuccess:onConnect}); //mqtt
        	break;
                  
        <?php /* 調撥車道 */ ?>    
        case "reversible_lane":
            $.ajax
        	({
        		url: "<?=APP_URL?>reversible_lane_query",
            	type: "post", 
            	dataType:"json",
            	data: {},
            	success: function(jdata)
            	{       
                	for(idx in jdata)
                    {          
                    	$("input[name=lane_"+idx+"][value='"+jdata[idx]+"']").prop("checked", true);                         
                    }
            	}
        	}); 
        	break;   
            
        // ----- 在席查核 -----   
        case "pks_check":   
        	$("#pks_list_tbody").html("");
            $.ajax
        	({
        		url: "<?=APP_URL?>pks_check_list",	// 讀入在席車號查無資料清單
            	type: "post", 
            	dataType:"json",
            	data: {},
            	success: function(jdata)
            	{               
                	$("#tot_pks_check").text(jdata.length);    
                	for(idx in jdata)
                    {          
                    	str = "<tr onclick='correct_lpr(\""+jdata[idx]["pksno"]+"\", \""+jdata[idx]["lpr"]+"\");'>" +
                        "<td class='cario_list' style='vertical-align:middle;' data-tag='pksno'>"+jdata[idx]["pksno"]+"</td>" +                        
                        "<td class='cario_list' style='vertical-align:middle;' data-tag='lpr'>"+jdata[idx]["lpr"]+"</td>" +                        
                        "<td class='cario_list' style='vertical-align:middle;' data-tag='in_time'>"+jdata[idx]["in_time"]+"</td>" +                        
                        "<td class='cario_list' style='vertical-align:middle;' data-tag='pic_name'><img height='57' width='150' class='resize' src='http://203.75.167.89/pkspic/"+jdata[idx]["pic_name"]+"' /></td></tr>";
                        $("#pks_list_tbody").append(str);                        
                    }
                    
                	set_resize();
            	}
        	}); 
        	break;
            
        // ----- 入場查核 -----   
        case "carin_check":     
        	$("#carin_list_tbody").html("");
            $.ajax
        	({
        		url: "<?=APP_URL?>carin_check_list",	// 讀入入場車號查核在席無資料清單
            	type: "post", 
            	dataType:"json",
            	data: {},
            	success: function(jdata)
            	{               
                	$("#tot_carin_check").text(jdata.length);    
                	for(idx in jdata)
                    {          
                    	str = "<tr onclick='correct_carin_lpr(\""+jdata[idx]["cario_no"]+"\", \""+jdata[idx]["lpr"]+"\", \""+jdata[idx]['in_time']+"\");'>" +
                        "<td class='cario_list' style='vertical-align:middle;' data-tag='lpr'>"+jdata[idx]["lpr"]+"</td>" +                        
                        "<td class='cario_list' style='vertical-align:middle;' data-tag='in_time'>"+jdata[idx]["in_time"]+"</td>" +                        
                        "<td class='cario_list' style='vertical-align:middle;' data-tag='type'>"+jdata[idx]["type"]+"</td>" +                        
                        "<td class='cario_list' style='vertical-align:middle;' data-tag='pic_name'><img height='57' width='150' class='resize' src='http://203.75.167.89/carpic/"+jdata[idx]["pic_name"]+"' /></td></tr>";
                        $("#carin_list_tbody").append(str);                        
                    }
                    
                	set_resize();
            	}
        	});
        	break;
            
        default:
        	break;
    }
    
	$("[data-items]").hide();
	$("[data-items="+tags+"]").show();
    return false;
}        


<?php /* ----- 以車號查詢入場資料 ----- */ ?>
function carin_lpr_query()
{                      
	var lpr = $("#lpr_query").val().toUpperCase();
	if ( lpr == "")
    {
     	alert("車號欄位必填");
        return false;
    } 
    
    $.ajax
    ({
    	url: "<?=APP_URL?>carin_lpr_query/"+lpr,
        type: "post", 
        dataType:"json",
        data: {},
        success: function(jdata)
        {                      
        	if (jdata.length == 0)
            {
            	alert("查無此車 !");
                return false;
            }                
                    
            var str = "";              
        	for(idx in jdata)
            {           
            	str +=	"<tr><td style='text-align:center;vertical-align:middle;'>"+jdata[idx]['io_name']+
            			"</td><td style='text-align:center;vertical-align:middle;'>"+jdata[idx]['io_time']+
            			"</td><td style='text-align:center;vertical-align:middle;'>"+jdata[idx]['lpr']+
            			"</td><td style='text-align:center;vertical-align:middle;'>"+jdata[idx]['etag']+
            			"</td><td style='text-align:center;vertical-align:middle;'>"+jdata[idx]['owner']+
                        "</td><td style='text-align:center;vertical-align:middle;'><img height='57' width='150' class='carin_resize' src='"+jdata[idx]['pic_name']+
                        "' /></td></tr>";
            }
                        
            $("#carin_query_tbody").html(str);
                        
            <?php /* mouse滑過時放大照片 */ ?>
			$('.carin_resize').hover
    		(         
    			function()
        		{       
        			pos_x = $(this).position().left;
        			pos_y = $(this).position().top;
            
            		$("#works").css
            		({	"position":"absolute",
            			"top":pos_y,
                		"left":pos_x
            		}).html("<img src='"+$(this).attr("src")+"' width='300px' height='140px' />").show();
    			},
    			function()
        		{                                 
					$("#works").hide();        
    			}
			);
            
            $("#carin_query_list").show();
        }
    });
}  

                 

<?php /* ----- 以時間查詢入場資料 ----- */ ?>
function carin_time_query()
{                      
	var time_query = $("#carin_time_query").val();
	if (time_query == "")
    {
     	alert("時間欄位必填");
        return false;
    } 
    
    $.ajax
    ({
    	url: "<?=APP_URL?>carin_time_query/",
        type: "post", 
        dataType:"json",
        data: {"time_query":time_query, "minutes_range":$("#minutes_range").val()},
        success: function(jdata)
        {       
        	var str = "";              
        	for(idx in jdata)
            {           
            	str +=	"<tr><td style='text-align:center;vertical-align:middle;'>"+jdata[idx]['io_name']+
            			"</td><td style='text-align:center;vertical-align:middle;'>"+jdata[idx]['io_time']+
            			"</td><td style='text-align:center;vertical-align:middle;'>"+jdata[idx]['lpr']+
            			"</td><td style='text-align:center;vertical-align:middle;'>"+jdata[idx]['etag']+
            			"</td><td style='text-align:center;vertical-align:middle;'>"+jdata[idx]['owner']+
                        "</td><td style='text-align:center;vertical-align:middle;'><img height='57' width='150' class='carin_resize' src='"+jdata[idx]['pic_name']+
                        "' /></td></tr>";
            }
                        
            $("#carin_query_tbody").html(str);
                        
            <?php /* mouse滑過時放大照片 */ ?>
			$('.carin_resize').hover
    		(         
    			function()
        		{       
        			pos_x = $(this).position().left;
        			pos_y = $(this).position().top;
            
            		$("#works").css
            		({	"position":"absolute",
            			"top":pos_y,
                		"left":pos_x
            		}).html("<img src='"+$(this).attr("src")+"' width='300px' height='140px' />").show();
    			},
    			function()
        		{                                 
					$("#works").hide();        
    			}
			);
            
            $("#carin_query_list").show();
        }
    });
}  

// 車辨失敗查詢
function carin_none_query()
{                      
	var time_query = $("#carin_none_query").val();
	if (time_query == "")
    {
     	alert("時間欄位必填");
        return false;
    }
    
    $.ajax
    ({
    	url: "<?=APP_URL?>carin_none_query/",
        type: "post", 
        dataType:"json",
        data: {"time_query":time_query, "hours_range":$("#hours_range").val()},
        success: function(jdata)
        {       
        	var content_arr = [];              
        	for(idx in jdata)
            {           
            	content_arr = content_arr.concat([
									"<tr><td style='text-align:center;vertical-align:middle;'>", jdata[idx]['io_name'], 
									"</td><td style='text-align:center;vertical-align:middle;'>", jdata[idx]['io_time'],
									"</td><td style='text-align:center;vertical-align:middle;'>"
							]);
									
				// 車辨失敗可新增車號
				content_arr = content_arr.concat([
								'<span style="font-size:16px;">車辨失敗</span><br/><button class="btn-default" onclick="show_upd_cario_dialog(', jdata[idx]['cario_no'], ', \'', jdata[idx]['lpr'], '\');">輸入車號</button>'
							]);
									
				content_arr = content_arr.concat([
									"</td><td style='text-align:center;vertical-align:middle;'>", jdata[idx]['etag'], 
									"</td><td style='text-align:center;vertical-align:middle;'>", jdata[idx]['owner'], 
									"</td><td style='text-align:center;vertical-align:middle;'>", 
										"<img height='57' width='150' class='carin_resize' src='", jdata[idx]['pic_name'], "' /></td></tr>"
							]);
            }
                        
            $("#carin_query_tbody").html(content_arr.join(''));
                        
            <?php /* mouse滑過時放大照片 */ ?>
			$('.carin_resize').hover
    		(         
    			function()
        		{       
        			pos_x = $(this).position().left;
        			pos_y = $(this).position().top;
            
            		$("#works").css
            		({	"position":"absolute",
            			"top":pos_y,
                		"left":pos_x
            		}).html("<img src='"+$(this).attr("src")+"' width='300px' height='140px' />").show();
    			},
    			function()
        		{                                 
					$("#works").hide();        
    			}
			);
            
            $("#carin_query_list").show();
        }
    });
}  

                           
<?php /* 修改或刪除選項 */ ?>
function member_modify(member_no)
{
	select_item = $("#sel_"+member_no).val();
    switch(select_item)
    {
     	case "choice":<?php /* 請選擇(忽略不處理) */ ?>
        	return flase;
        
        case "modify": 
            $("#ma_lpr").val($("#lpr_"+member_no).text());
            $("#ma_old_lpr").val($("#lpr_"+member_no).text());
            $("#ma_member_id").val($("#lpr_"+member_no).data("member_id"));
            $("#ma_tel_o").val($("#lpr_"+member_no).data("tel_o"));
            $("#ma_tel_h").val($("#lpr_"+member_no).data("tel_h"));
            $("#ma_addr").val($("#lpr_"+member_no).data("addr"));
            //$("#ma_start_date").val($("#sdate_"+member_no).text());
			//$("#ma_end_date").val($("#edate_"+member_no).text());
			//$("#ma_start_date").val($("#sdate_"+member_no).text().split(' ')[0]);
			//$("#ma_end_date").val($("#edate_"+member_no).text().split(' ')[0]);
			$("#ma_start_date").val($("#sdate_"+member_no).text().replace(' ', 'T'));
			$("#ma_end_date").val($("#edate_"+member_no).text().replace(' ', 'T'));
            $("#ma_member_name").val($("#name_"+member_no).text());
            $("#ma_mobile_no").val($("#mobile_"+member_no).text());
            $("#ma_contract_no").val($("#contract_"+member_no).text());
            $("#ma_etag").val($("#etag_"+member_no).text());
            $("#ma_amt").val($("#amt_"+member_no).text());
            $("#ma_member_no").val(member_no);
    		$("#member_data_type").text("修改會員資料");
        	show_item("member_add", "member_modify"); 
        	break;
            
        case "delete":
			if (!confirm("確定刪除嗎 ?"))	return false;
                                   
			$.ajax
    		({     
        		url:"<?=APP_URL?>member_delete",
            	type:"post",
            	dataType:"text",  
            	data:{"member_no":member_no},
            	success:function(jdata)
            	{
                	if (jdata == "ok")
                    {
                		alert("刪除成功 !");
                        show_item("member_query", "member_query");
                    }  
                }        
    		});        
        	break;
    }	
}  
          

<?php /* 每隔固定時間, 檢查剩餘車位數 */ ?>                 
function available_check(time_point)
{                           
	$.ajax
    ({     
    	url:"<?=APP_URL?>available_check",
        type:"post",
        dataType:"json",  
        data:{"time_point":time_point},
        success:function(jdata)
        {           
        	for(idx in jdata)
            {           
            	if (idx == 0) { continue; }
                
                stno = jdata[idx]['station_no'];                                      
                $("[data-stno_curr="+stno+"]>[data-tag=station_no]").text(stno);
                $("[data-stno_curr="+stno+"]>[data-tag=name]").text(jdata[idx]['name']);
                $("[data-stno_curr="+stno+"]>[data-tag=tot_pkg]").text(jdata[idx]['tot_pkg']);
                $("[data-stno_curr="+stno+"]>[data-tag=ava_pkg]").text(jdata[idx]['ava_pkg']);
                $("[data-stno_curr="+stno+"]>[data-tag=used_pkg]").text(jdata[idx]['used_pkg']);
                $("[data-stno_curr="+stno+"]>[data-tag=ratio_pkg]").text(jdata[idx]['ratio_pkg']); 
            }        
			window.clearTimeout(timer);
    		timer = window.setTimeout("available_check("+jdata[0]['time_point']+")", timeout_sec * 1000);
        }
    });                     
}


<?php /* 讀出剩餘車位數供清單用 */ ?>
function available_curr()
{                   
	$.ajax
    ({
        	url: "<?=APP_URL?>available_set",
            type: "post",
            dataType:"json",
            success: function(jdata)
            {
                $("#available_curr_tbody").html("");	<?php /* 重設剩餘車位數 */ ?>
                for(idx in jdata)
                {     
                	if (idx == 0) { continue; }                                           
                	stno = jdata[idx]['station_no'];
                	$("#available_curr_wk>[data-tag=station_no]").text(stno);
                	$("#available_curr_wk>[data-tag=name]").text(jdata[idx]['name']);
                	$("#available_curr_wk>[data-tag=tot_pkg]").text(jdata[idx]['tot_pkg']);
                	$("#available_curr_wk>[data-tag=ava_pkg]").text(jdata[idx]['ava_pkg']);
                	$("#available_curr_wk>[data-tag=used_pkg]").text(jdata[idx]['used_pkg']);
                	$("#available_curr_wk>[data-tag=ratio_pkg]").text(jdata[idx]['ratio_pkg']);
                    $("<tr data-stno_curr='"+stno+"'>"+$("#available_curr_wk").html()+"</tr>").appendTo("#available_curr_tbody"); 
                }  
                $("#available_curr").show(); 
                timer = window.setTimeout("available_check("+jdata[0]['time_point']+")", timeout_sec * 1000);
            }
    });
}


<?php /* 讀出剩餘車位數供設定用 */ ?>
function available_set()
{     
	$.ajax
        ({
        	url: "<?=APP_URL?>available_set",
            type: "post",
            dataType:"json",
            success: function(jdata)
            {
                $("#available_tbody").html("");	<?php /* 重設剩餘車位數 */ ?>
                for(idx in jdata)
                {        
                	if (idx == 0) { continue; }
                                                                   
                	st_no = jdata[idx]['station_no'];
                	$("#available_list>[data-tag=station_no]").text(st_no);
                	$("#available_list>[data-tag=name]").html("<input id='st_name_"+st_no+"' value='"+jdata[idx]['name']+"' class='form-control' />");
                	$("#available_list>[data-tag=tot_pkg]").html("<input id='st_tot_"+st_no+"' value='"+jdata[idx]['tot_pkg']+"' class='form-control input-sm' />");
                	$("#available_list>[data-tag=ava_pkg]").html("<input id='st_ava_"+st_no+"' value='"+jdata[idx]['ava_pkg']+"' class='form-control input-sm' />");
                	$("#available_list>[data-tag=used_pkg]").text(jdata[idx]['used_pkg']);
                	$("#available_list>[data-tag=ratio_pkg]").text(jdata[idx]['ratio_pkg']);
                	$("#available_list>[data-tag=edits]").html("<button onclick=\"available_update('"+st_no+"');\">修改</button>"); 
                    $("<tr data-st_no='"+st_no+"'>"+$("#available_list").html()+"</tr>").appendTo("#available_tbody"); 
                }  
                <?php /* 清除最後一筆的暫存資料, 以免最後一筆的id發生重覆 */ ?>
                $("#available_list>[data-tag=name]").html("");
                $("#available_list>[data-tag=tot_pkg]").html("");
                $("#available_list>[data-tag=ava_pkg]").html("");
                $("#available_set").show();
            }
    }); 
}  
                                  
<?php /* 剩餘車位數更新 */ ?>
function available_update(station_no)
{             
	$.ajax
    ({
        url: "<?=APP_URL?>available_update", 
        dataType:"json",
        type:"post",
        data:{"station_no":station_no, "st_name":$("#st_name_"+station_no).val(), "tot_pkg":$("#st_tot_"+station_no).val(), "ava_pkg":$("#st_ava_"+station_no).val()},
        success:function(jdata)
        {     
        	$("#available_tbody>tr[data-st_no="+station_no+"]>[data-tag=used_pkg]").text(jdata['used_pkg']);
        	$("#available_tbody>tr[data-st_no="+station_no+"]>[data-tag=ratio_pkg]").text(jdata['ratio_pkg']);
         	alert($("#st_name_"+station_no).val()+"("+station_no+") 已更新完成 !");
    	}
    }); 
}

var current_h;
var current_w;

$(document).ready(function()   
{                                    
	$(".cario_list").css({"vertical-align":"middle"});<?php /* 進出場實況表,強制垂直置中 */ ?> 
    
	$("#member_add").submit(function(event)
	{                   
    	if ($("#ma_member_no").val() == "0")
        {
        	$("#ma_old_lpr").val($("#ma_lpr").val());
        }
    	event.preventDefault();
        $.ajax
        ({
        	url: "<?=APP_URL?>member_add",
            type: "post", 
            dataType:"text",
            data: $(this).serialize(),
            success: function(jdata)
            {         
            	if (jdata == "ok")
                {                             
                	$("#ma_lpr").val("");
                	$("#ma_old_lpr").val("");
                	$("#ma_etag").val("");
                	$("#ma_start_date").val("");
                	$("#ma_end_date").val("");
                	$("#ma_member_name").val("");
                	$("#ma_mobile_no").val("");
                	$("#ma_contract_no").val("");
                	$("#ma_member_id").val("");
                	$("#ma_tel_h").val("");
                	$("#ma_tel_o").val("");
                	$("#ma_addr").val("");
                	$("#ma_member_no").val("0");
                	alert("月租資料存檔完成 !");
                    show_item('member_query', 'member_query');
                } 
                else
                {
                 	alert(jdata);
                }
            }
        }); 
    });
        
    set_resize();	// 建立放大照片
                            
    // 調撥車道設定
	$('#lane_form input[type=radio]').change(function() 
    {       
    	lane_no = $(this).data("lane_no"); 
		$.ajax
    	({
        	url: "<?=APP_URL?>reversible_lane_set", 
        	dataType:"text",
        	type:"post",
        	data:{"lane_no":lane_no, "actions":$(this).val()},
        	success:function(jdata)
        	{     
    		}                                                                          
    	}); 
    
    	return true;
	}); 
	
	
	// Custom: altob-keyaction
  	// ********************
	$.extend($.keyboard.keyaction, {
		accept : function(base) {
			base.close(true); // same as base.accept();
			
			// do something
			
			return false;     // return false prevents further processing
		}
	});
	
	// Custom: altob-input
  	// ********************
  	$('#upd_cario_lpr, #cms_lpr').keyboard({

		usePreview: false,
	
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
		
		position : {
		  // null (attach to input/textarea) or a jQuery object (attach elsewhere)
		  of : null,
		  my : 'center top',
		  at : 'center top',
		  // at2 is used when "usePreview" is false (centers keyboard at the bottom
		  // of the input/textarea)
		  at2: 'center bottom',
		  collision: 'flipfit flipfit'
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
	
}); 

function opendoors(lane_no)
{
	$.ajax
    ({
        url: "<?=APP_URL?>opendoors/"+lane_no, 
        dataType:"text",
        type:"post",
        data:{},
        success:function(jdata)
        {     
    	}                                                                          
    }); 
    
    return true;
}    
             
// 設定放大照片
function set_resize()
{
	$('.resize').hover
    (         
    	function()
        {       
        	pos_x = $(this).position().left - 100;
        	pos_y = $(this).position().top - 100;
            
            $("#works").css
            ({	"position":"absolute",
            	"top":pos_y,
                "left":pos_x
            }).html("<img src='"+$(this).attr("src")+"' width='400px' height='240px' />").show();
    	},
    	function()
        {                                 
			$("#works").hide();        
    	}
	);   
}     

// 重設在席查核
function reset_pks_check()
{
	$.ajax
    ({
        url: "<?=APP_URL?>reset_pks_check", 
        dataType:"json",
        type:"post",
        data:{},
        success:function(jdata)
        {
        	alert("重設在席查核完成,在席總計:"+jdata["tot"]+", 更新筆數:"+jdata["tot_correct"]);     
    	}                                                                          
    }); 
}    


// 更正在席車號
function correct_lpr(pksno, lpr)
{          
	new_lpr = prompt("車格#"+pksno+"正確車號(大小寫皆可):", lpr);
    if (new_lpr == "")	return false;  
    
	$.ajax
    ({
        url: "<?=APP_URL?>correct_pks_lpr/"+pksno+"/"+new_lpr.toUpperCase(), 
        dataType:"json",
        type:"post",
        data:{},
        success:function(jdata)
        {                                                                            
            msg_err = jdata["err"] == 0 ? "在席更新正確車號," : "在席或車號錯誤無法更新,";
            msg_cario = jdata["cario_no"] == 0 ? "查無入場資料" : "且與入場資料相符";
        	alert(msg_err+msg_cario); 
            show_item('pks_check', 'pks_check');    
    	}                                                                          
    }); 
}      


// 更正在席車號
function correct_carin_lpr(cario_no, lpr, in_time)
{          
	new_lpr = prompt("正確車號(大小寫皆可):", lpr);
    if (new_lpr == "")	return false;
    
	$.ajax
    ({
        url: "<?=APP_URL?>correct_carin_lpr/"+cario_no+"/"+new_lpr.toUpperCase()+"/"+encodeURIComponent(in_time), 
        dataType:"json",
        type:"post",
        data:{},
        success:function(jdata)
        {                                                                            
            msg_pks = jdata["pksno"] == 0 ? "但無在席資料" : "且在席資料也更新完成 !";
        	alert("入場資料更新完成, "+msg_pks); 
            show_item('carin_check', 'carin_check');    
    	}                                                                          
    }); 
}






<?php /* 登出 */ ?>
function logout(event)
{
	event.preventDefault();
	$.ajax
        ({
        	url: "<?=APP_URL?>user_logout",
            success: function(jdata)
            {
				window.location = "<?=APP_URL?>";
            }
	}); 
}

</script>






<!-- ----- 進出資料建立小框 ----- -->
<div class="modal fade" id="show_create_cario_dialog">
<div class="modal-dialog" style="width:70%;height:100%">
<div class="modal-content" style="width:70%;height:100%">
<div class="modal-header"><h3>手動建立資料</h3></div>
<div class="modal-body" style="max-height: calc(100vh - 210px); overflow-y: auto">
<form id="show_create_cario_form" class="center-block">    
<div class="main">
<div class="dataTable_wrapper">
<table class="table table-striped table-bordered table-hover" style="font-size:28px;">
<tbody>

<tr class="form-group">
	<td style="text-align:right;">場站</td>
	<td style="text-align:left;" id="create_cario_station_name"></td>
</tr>
<tr class="form-group">
	<td style="text-align:right;">場站編號</td>
	<td style="text-align:left;" id="create_cario_station_no"></td>
</tr> 
<!--tr class="form-group">
<td style="text-align:right;">車道</td>
	<td style="text-align:left;">
		<div id="cms_ivsno_box"/>
	</td>
</tr-->
<tr class="form-group">
<td style="text-align:right;">進出場</td>
	<td style="text-align:left;">
		<div id="cms_io_box"/>
	</td>
</tr>
<tr class="form-group">
<td style="text-align:right;">車種</td>
	<td style="text-align:left;">
		<div id="cms_ctype_box"/>
	</td>
</tr>
<tr class="form-group">
	<td style="text-align:right;">車牌號碼</td>
	<td style="text-align:left;">
		<input id="cms_lpr" name="lpr" class="form-control" placeholder="限英數字碼" style="text-transform:uppercase;font-size:48px;height:56px;" />
	</td>
</tr> 


</tbody>
</table>                    
<button type="button" class="btn btn-large btn-success pull-right" style="font-size:22px;"   onclick="do_create_cario();">確認送出</button>
&nbsp;&nbsp;
<button type="button" class="btn btn-large btn-cancel" onclick="$('#show_create_cario_dialog').modal('hide');">取消</button>
</div><!-- ----- end of dataTable_wrapper ----- -->  
</div><!-- ----- end of main ----- -->
</form>
</div><!-- end of modal-body --> 
</div><!-- end of modal-content --> 
</div><!-- end of modal-dialog -->
</div><!-- end of modal show -->
<!-- ----- 進出資料建立小框(結束) ----- -->  



<script>  

// 顯示新增車辨記錄
function show_create_cario_dialog()
{				
	if(typeof AltobObject.station_no == 'undefined' || AltobObject.station_no == '' || AltobObject.station_no == 0)
	{
		alertify_msg("尚未設定場站。。");
		return false;
	}
	
	if(typeof AltobObject.xvars == 'undefined')
	{
		alertify_msg("未知的錯誤。。");
		return false;
	}

	AltobObject.xvars["create_cario_info"] = {};
	AltobObject.xvars["create_cario_info"]["station_no"] = AltobObject.station_no;

	$("#create_cario_station_name").text(AltobObject.station_name);
	$("#create_cario_station_no").text(AltobObject.station_no);
	
	// 進出場
	var cms_io_content = [];
	cms_io_content = cms_io_content.concat(["<select id='sel_cms_io_", AltobObject.station_no, "'><option value='choice'>請選擇</option>"]);
	cms_io_content = cms_io_content.concat(["<option value='I'>進場</option>"]);
	cms_io_content = cms_io_content.concat(["<option value='O'>離場</option>"]);
	$("#cms_io_box").html('').html(cms_io_content.join(''));
	
	// 車種
	var cms_ctype_content = [];
	cms_ctype_content = cms_ctype_content.concat(["<select id='sel_cms_ctype_", AltobObject.station_no, "'><option value='choice'>請選擇</option>"]);
	cms_ctype_content = cms_ctype_content.concat(["<option value='C'>汽車</option>"]);
	cms_ctype_content = cms_ctype_content.concat(["<option value='M'>機車</option>"]);
	$("#cms_ctype_box").html('').html(cms_ctype_content.join(''));
	
	// 車號
	$("#cms_lpr").val('');
	
	$("#show_create_cario_dialog").modal({backdrop:false,keyboard:false});
}
	
// 完成
function do_create_cario()
{
	if (!confirm("確認資料無誤並送出 ?")) return false; 
	
	var station_no = AltobObject.xvars["create_cario_info"]["station_no"];
	var io = $("#sel_cms_io_" + station_no).val();
	var ctype = $("#sel_cms_ctype_" + station_no).val();
	var lpr = $("#cms_lpr").val();
	
	if(!(station_no && lpr))
	{
		alertify_msg("資料不足。。");
		return false;
	}
	
	$.ajax
    ({
        url: "<?=APP_URL?>local_lprio", 
        dataType: "text",
        type:"post",
        data:
        {
			"cmd": 1,
        	"station_no":station_no,
			"ivsno": 0,	//  都帶 0
			"io":io,
			"ctype":ctype,
			"lpr":lpr
        },
		error:function(xhr, ajaxOptions, thrownError)
        {
			alertify_msg(xhr.responseText);
        	console.log("error:"+xhr.responseText+"|"+ajaxOptions+"|"+thrownError);  
        },
        success:function(jdata)
        {                                                                            
			if (jdata == "ok")	
            {                              
				alertify_msg("操作完成。。");
				
				show_item('cario_list', 'cario_list');	// 更新
            }
            else
            {
				alertify_msg("操作失敗。。" + jdata);
            }
        }
    });
    
    delete AltobObject.xvars["create_cario_info"];
    $('#show_create_cario_dialog').modal('hide'); 
}
</script>  



<!-- ----- 進出資料修改小框 ----- -->
<div class="modal fade" id="show_upd_cario_dialog">
<div class="modal-dialog" style="width:70%;height:100%">
<div class="modal-content" style="width:70%;height:100%">
<div class="modal-header"><h3>手動建立資料</h3></div>
<div class="modal-body" style="max-height: calc(100vh - 210px); overflow-y: auto">
<form id="show_upd_cario_form" class="center-block">    
<div class="main">
<div class="dataTable_wrapper">
<table class="table table-striped table-bordered table-hover" style="font-size:28px;">
<tbody>

<tr class="form-group">
	<td style="text-align:right;">場站</td>
	<td style="text-align:left;" id="upd_cario_station_name"></td>
</tr>
<tr class="form-group">
	<td style="text-align:right;">場站編號</td>
	<td style="text-align:left;" id="upd_cario_station_no"></td>
</tr> 
<tr class="form-group">
	<td style="text-align:right;">車牌號碼</td>
	<td style="text-align:left;">
		<input id="upd_cario_lpr" name="lpr" class="form-control" placeholder="限英數字碼" style="text-transform:uppercase;font-size:48px;height:56px;" />
	</td>
</tr> 


</tbody>
</table>                    
<button type="button" class="btn btn-large btn-success pull-right" style="font-size:22px;"   onclick="do_upd_cario();">確認送出</button>
&nbsp;&nbsp;
<button type="button" class="btn btn-large btn-cancel" onclick="$('#show_upd_cario_dialog').modal('hide');">取消</button>
</div><!-- ----- end of dataTable_wrapper ----- -->  
</div><!-- ----- end of main ----- -->
</form>
</div><!-- end of modal-body --> 
</div><!-- end of modal-content --> 
</div><!-- end of modal-dialog -->
</div><!-- end of modal show -->
<!-- ----- 進出資料修改小框(結束) ----- -->  



<script>  

// 顯示新增車辨記錄
function show_upd_cario_dialog(cario_no, lpr)
{				
	if(typeof AltobObject.station_no == 'undefined' || AltobObject.station_no == '' || AltobObject.station_no == 0)
	{
		alertify_msg("尚未設定場站。。");
		return false;
	}
	
	if(typeof AltobObject.xvars == 'undefined')
	{
		alertify_msg("未知的錯誤。。");
		return false;
	}

	AltobObject.xvars["upd_cario_info"] = {};
	AltobObject.xvars["upd_cario_info"]["station_no"] = AltobObject.station_no;
	AltobObject.xvars["upd_cario_info"]["cario_no"] = cario_no;
	AltobObject.xvars["upd_cario_info"]["old_lpr"] = lpr;

	$("#upd_cario_station_name").text(AltobObject.station_name);
	$("#upd_cario_station_no").text(AltobObject.station_no);
	
	// 車號
	if(lpr == 'NONE')
		$("#upd_cario_lpr").val('');
	else
		$("#upd_cario_lpr").val(lpr);
	
	$("#show_upd_cario_dialog").modal({backdrop:false,keyboard:false});
}
	
// 完成
function do_upd_cario()
{
	if (!confirm("確認資料無誤並送出 ?")) return false; 
	
	var station_no = AltobObject.xvars["upd_cario_info"]["station_no"];
	var cario_no = AltobObject.xvars["upd_cario_info"]["cario_no"];
	var old_lpr = AltobObject.xvars["upd_cario_info"]["old_lpr"];
	var new_lpr = $("#upd_cario_lpr").val();
	
	if(!(station_no && cario_no))
	{
		alertify_msg("資料不足。。");
		return false;
	}
	
	$.ajax
    ({
        url: "<?=APP_URL?>local_lprio", 
        dataType: "text",
        type:"post",
        data:
        {
			"cmd": 2,
        	"station_no":station_no,
			"cario_no":cario_no,
			"old_lpr":old_lpr,
			"new_lpr":new_lpr
        },
		error:function(xhr, ajaxOptions, thrownError)
        {
			alertify_msg(xhr.responseText);
        	console.log("error:"+xhr.responseText+"|"+ajaxOptions+"|"+thrownError);  
        },
        success:function(jdata)
        {                                                                            
			if (jdata == "ok")	
            {                              
				alertify_msg("操作完成。。");
				
				show_item('cario_list', 'cario_list');	// 更新
            }
            else
            {
				alertify_msg("操作失敗。。" + jdata);
            }
        }
    });
    
    delete AltobObject.xvars["upd_cario_info"];
    $('#show_upd_cario_dialog').modal('hide'); 
}
</script> 














