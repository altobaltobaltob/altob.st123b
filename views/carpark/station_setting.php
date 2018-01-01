			<!-- ----- 場站設定 ----- -->  
            <div data-items="station_setting" class="row" style="display:none;"><!-- 場站設定 -->
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div id="member_data_type" class="panel-heading">
							目前場站設定
							&nbsp;<button id='reload_station_setting_btn' class="btn btn-large btn-success pull-right" style="font-size:20px;" onclick='reset_station_setting();'>重新載入</button>
						</div><!-- 資料顯示區灰色小表頭 -->
                        <div class="panel-body">
                            <div data-rows class="row">
                                <div class="col-lg-6">
                                    <!--form id="station_setting" role="form" method="post" data-src="action::APP_URL::station_setting"-->  
                                    <form id="station_setting" role="form" method="post" data-src="/carpark.html/station_setting">  
                                        <div class="form-group">
                                            <label style="font-size:22px">場站名稱</label>
                                            <input id="ss_station_name" name="station_name" class="form-control"  style="font-size:28px" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label style="font-size:22px">場站編號（以 ',' 隔開）</label>
                                            <input id="ss_station_no" name='station_no' class="form-control"  style="font-size:28px" readonly>
                                        </div> 
										<div class="form-group">
                                            <label style="font-size:22px">會員場站編號（以 ',' 隔開）</label>
                                            <input id="ss_station_no_list" name='station_no_list' class="form-control"  style="font-size:28px" readonly>
                                        </div> 
										<div class="form-group">
                                            <label style="font-size:22px">場站 NAT</label>
                                            <input id="ss_station_service_url" name='station_service_url' class="form-control"  style="font-size:28px" readonly>
                                        </div> 
										<div class="form-group">
                                            <label style="font-size:22px">其它設定</label>
                                            <textarea readonly id="ss_station_info" name='station_info' class="form-control" style="font-size:28px" rows="4">
											</textarea>
                                        </div> 
										<!--button type="submit" class="btn btn-default">存檔</button>
                                        <button type="reset" class="btn btn-default">重填</button-->
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
            <!-- ----- 場站設定(結束) ----- --> 
			
<script>  
	
// 重新載入
function reset_station_setting()
{
	event.preventDefault();
	do_reload_station_setting(1);
}
	

// 載入目前設定
function reload_station_setting(type)
{
	do_reload_station_setting(0);
}

// 執行
function do_reload_station_setting(reload=0)
{
	$.ajax
        ({
        	url: "<?=APP_URL?>station_setting_query",
            type: "post", 
            dataType:"json",
			data:{ 'reload': reload },
			error:function(xhr, ajaxOptions, thrownError)
			{
				alertify_msg(xhr.responseText);
				console.log("error:"+xhr.responseText+"|"+ajaxOptions+"|"+thrownError);  
				return false;
			},
            success: function(jdata)
            {       
				var station_service_url = jdata['station_ip'] + ' : ' + jdata['station_port'];
				var station_info = JSON.stringify(jdata['settings']);
				
				if(jdata == 'fail')
				{
					$("#ss_station_name").val('未設定');
					$("#ss_station_no").val('');
					$("#ss_station_no_list").val('');
					$("#ss_station_service_url").val(station_service_url);
					$("#ss_station_info").val(station_info);
					alertify_error('載入失敗。。');		
					return false;
				}
				
				$("#ss_station_name").val(jdata['station_name']);
				$("#ss_station_no").val(jdata['station_no']);
				$("#ss_station_no_list").val(jdata['station_no_list']);
				$("#ss_station_service_url").val(station_service_url);
				$("#ss_station_info").val(station_info);
				alertify_success('完成。。');	
				
				// 設定暫存檔
				AltobObject.station_no = jdata['station_no'];
				AltobObject.station_name = jdata['station_name'];
            }
        }); 
}
	
</script>