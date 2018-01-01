<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>歐特儀管理系統 (<?=STATION_NAME?>)</title>
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
                <a class="navbar-brand" href="">歐特儀管理系統 (<?=STATION_NAME?>)</a>
            </div>
            <!-- /.navbar-static-side -->
        </nav>
        <div id="page-wrapper">            
			<div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">管理者登入</h1>                
				</div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->    
				<div data-items="user_login" class="row" >                
					<div class="col-lg-5">
						<div class="panel panel-default">
							<div class="panel-body">
								<div data-rows class="row">
									<div class="col-lg-12">
										<form id="user_login" role="form" method="post">  
											<div class="form-group">
												<label>帳號</label>
												<input id="login_name" name="login_name" class="form-control"
													data-validation="required"
													data-validation-error-msg="請輸入帳號">
											</div>
											
											<div class="form-group">
												<label>密碼</label>
												<input id="pswd" name='pswd' type="password" class="form-control"
													data-validation="required"
													data-validation-error-msg="請輸入密碼">
											</div>
											<button type="submit" class="btn btn-default">登入</button>
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
					<!-- /.col-lg-6 -->
				</div>
                     
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
    <!-- Custom Theme JavaScript -->
    <script src="<?=BOOTSTRAPS?>dist/js/sb-admin-2.js"></script>   
	
	<!-- jQuery validate -->
	<script src="<?=WEB_LIB?>form-validator/jquery.form-validator.min.js"></script>
	<!-- alertify -->
	<link href="<?=WEB_LIB?>css/alertify.core.css" rel="stylesheet">
	<link href="<?=WEB_LIB?>css/alertify.bootstrap.css" rel="stylesheet">
	<script src="<?=WEB_LIB?>js/alertify.min.js"></script> 

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

$(document).ready(function()   
{                 
	<?php /* validate  設定start */ ?> 
	$.validate(
		{
			modules : 'security',
		}
	);
	<?php /* validate  設定end */ ?>
    
	
	$("#user_login").submit(function(event)
	{                  
    	event.preventDefault();
        $.ajax
        ({
        	url: "<?=APP_URL?>user_login",
            type: "post", 
            dataType:"text",
            data: $(this).serialize(),
            success: function(jdata)
            {         
				if(jdata == 'ok')
				{
					location.reload();
				}
				else if(jdata == '-1')
				{
					alertify_error("輸入資料有誤");
				}
				else
				{
					alertify_error("登入資訊無效, 請通知管理員");
				}
            }
        }); 
    });

}); 
</script>