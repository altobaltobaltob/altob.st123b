<!-- ----- 電子發票清帳作業 ----- -->  
<div data-items="check_point" class="row" style="display:none;">
<div class="col-lg-12">
<div class="panel panel-default">
<div class="panel-heading">關帳（手動）</div><!-- 資料顯示區灰色小表頭 -->
<div class="panel-body">
<div data-rows class="row">
<div class="col-lg-6">    
<div class="form-group">
<label style="font-size:16px;">本次關帳時間</label>
<input id="check_point_time" type="datetime" class="form-control" style="font-size:20px;"/>
</div>              
<div class="form-group">
<label style="font-size:16px;">備註（選填）</label>
<input id="check_point_remarks" type="text" class="form-control" style="font-size:20px;" placeholder="補充說明" />
</div>            
<div class="form-group">
<button type="button" class="btn btn-large btn-success pull-left" onclick="set_check_point();">關帳</button> 
</div> 
</div><!-- end of col-lg-6 (nested) -->
</div><!-- end of row (nested) -->
</div><!-- end of panel-body -->
</div><!-- end of panel -->
</div><!-- end of col-lg-12 -->
</div><!-- data-items -->
<!-- ----- 電子發票清帳作業(結束) ----- --> 
<script>  

$("#check_point_time").val(moment(new Date()).format("YYYY-MM-DD HH:mm:ss"));
$("#check_point_time").prop("readonly",true);

// 列印電子發票清帳
function set_check_point()
{                  
	var station_no = $("#station_select").val();
	var check_point_time = $("#check_point_time").val();
	var remarks = $("#check_point_remarks").val();
	
    if (!confirm("確認關帳時間:"+check_point_time+" ?"))	return false;
	
	$.ajax
    ({
        url:APP_URL+"set_check_point", 
        dataType:"text",
        type:"post",
        data:
        {
        	"station_no": station_no,
        	"check_point_time": check_point_time,
			"remarks": remarks
        },
		error:function(xhr, ajaxOptions, thrownError)
		{
			var error_msg = xhr.responseText ? xhr.responseText : "連線失敗, 請稍候再試";
			alertify_msg(error_msg);
			console.log("error:"+error_msg+"|"+ajaxOptions+"|"+thrownError);  
		},
        success:function(jdata)
        {                                                                            
            if (jdata == "ok")	
            {                              
            	alertify_msg("完成！"); 
            	//show_item('check_point', 'check_point');
				show_item('check_point_report', 'check_point_report');
				$("#check_point_form").submit();
            }
			else if(jdata == "not_synced")
			{
				alertify_sync(station_no);
			}
			else if(jdata == "error_amt")
			{
				alertify_msg("金額異常，請確認關帳時間");
			}
            else
            {
              	alertify_msg("操作失敗 !");
            }   
    	}                                                                          
    }); 
}
</script>