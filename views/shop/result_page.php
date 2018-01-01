<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>歐特儀停車場</title>
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
            </div>
            <!-- /.row -->
			
			<?php /* ----- 查詢結果 ----- */ ?>
            <div data-items="not_found" class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading" style="font-size:28px;"><?php /* 資料顯示區灰色小表頭 */ ?>
                            查詢結果：查無 <span id="not_found_product" style="font-size:28px;color:blue;"></span> 商品資料
                        </div>
					</div>
				</div>
			</div>

			<?php /* ----- 查詢結果 ----- */ ?>
            <div data-items="output_product" class="row" style="display:none;">
                <div class="col-lg-7 col-sm-7">
                    <div class="panel panel-default">
						<div class="panel-heading" style="font-size:36px;">
                            <button class="btn btn-large btn-success pull-right" style="font-size:28px;" onclick='i_do(event);'>我要買</button>
							歐特儀精選
                        </div>
                        <div class="panel-body" style="margin: 0px auto;">
                            <div data-rows class="row">
                                <div class="col-lg-12" style="margin: 0px auto;">
                                <table class="table table-striped table-bordered table-hover">
                                    <tbody style="font-size:20px;">
                                        <tr>
                                            <td style="text-align:right;vertical-align: middle;">名稱</td>
                                            <td id="show_product_name" style="text-align:left;vertical-align: middle;"></td>
                                        </tr>
                                        <tr>
                                            <td style="text-align:right;vertical-align: middle;">描述</td>
                                            <td id="show_product_remarks" style="text-align:left;vertical-align: middle; font-size:20px; color:blue;"></td>
                                        </tr>
                                        <tr>
                                            <td style="text-align:right;vertical-align: middle;">費用</td>
                                            <td style="text-align:left;vertical-align: middle;">
												<span id="show_product_amt" style="font-size:20px;color:green;"/>
											</td>
                                        </tr>
										<!--tr>
											<td style="text-align:right;vertical-align: middle;"></td>
											<td>
												<button class="btn btn-large btn-success pull-right" style="font-size:28px;">我要買</button>
											</td>
										</tr-->
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
				
				<div class="col-lg-5 col-sm-5">
					<div class="panel panel-default">
                        <div class="panel-heading" style="font-size:20px;"><?php /* 資料顯示區灰色小表頭 */ ?>
							<span id="show_product_desc"/>
                        </div>
					</div>
					<div class="panel-body" style="margin: 0px auto;">
						<div class="col-lg-12" style="margin: 0px auto;">
							<table class="table table-striped table-bordered table-hover">
								<tbody>
									<tr>
										<td style="text-align:center;vertical-align: middle;">
											<img id="show_img" style="width: 100%" />
                                        </td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>
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

	<!-- alertify -->
	<link href="<?=WEB_LIB?>css/alertify.core.css" rel="stylesheet">
	<link href="<?=WEB_LIB?>css/alertify.bootstrap.css" rel="stylesheet">
	<script src="<?=WEB_LIB?>js/alertify.min.js"></script>
	<!-- moment -->
	<script src="<?=WEB_LIB?>js/moment.min.js"></script>

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

// 取得搜尋結果
var PRODUCT_RESULT = {};
PRODUCT_RESULT.product_id = '<?= $product_id; ?>';
PRODUCT_RESULT.product_name = '<?= $product_name; ?>';
PRODUCT_RESULT.product_desc = '<?= $product_desc; ?>';
PRODUCT_RESULT.amt = '<?= $amt; ?>';
PRODUCT_RESULT.remarks = '<?= $remarks; ?>';
PRODUCT_RESULT.product_plan = '<?= $product_plan; ?>';
	
if(PRODUCT_RESULT.product_id == '')
{
	$("#not_found_product").text('');
	show_item("not_found");
}
else
{
	show_item("output_product");
}

<?php /* 顯示指定項目 */ ?>
function show_item(tags)
{
	$("#show_product_name").text(PRODUCT_RESULT.product_name);
	$("#show_product_desc").text(PRODUCT_RESULT.product_desc);
	$("#show_product_remarks").text(PRODUCT_RESULT.remarks);
	$("#show_product_amt").text('NTD ' + PRODUCT_RESULT.amt + ' 元');
	$("#show_img").attr("src", "<?=SERVER_URL?>i3/pics/coffee.jpg");

	$("[data-items]").hide();
	$("[data-items="+tags+"]").show();
    return false;
}

$(document).ready(function()
{
	<?php /* 鎖右鍵 */ ?>
	$(document).bind('contextmenu', function (e) {
	  e.preventDefault();
	});

});

// 買
function i_do(e)
{	
	e.preventDefault();
	
	$.ajax
    ({
       	url: "<?=APP_URL?>i_do/" + PRODUCT_RESULT.product_id,
       	dataType:"text",
       	type:"get",
       	data:{},
       	success:function(result)
       	{
			console.log(result);
		}
	});
}

</script>
