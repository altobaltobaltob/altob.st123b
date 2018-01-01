			<?php /* ----- 臨停未結清單 ----- */?>
            <div data-items="cario_temp_not_finished_query_all" class="row" style="display:none;">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            臨停未結清單 （2017-02-01 00:00:00 進場 ~ 至 <span id='altob_current_time_str'></span>）
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <div class="dataTable_wrapper">
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
											<th style="text-align:left;">代號</th>
											<th style="text-align:left;">車牌號碼</th>
											<th style="text-align:center;">進場時間</th>
											<th style="text-align:center;">最後付款時間</th>
											<th style="text-align:center;">限時離場時間</th>
											<th style="text-align:center;">功能</th>
                                        </tr>
                                    </thead>
                                    <tbody id="cario_temp_not_finished_query_all" style="font-size:18px;"></tbody>
                                </table>
                            </div><?php /* ----- end of dataTable_wrapper ----- */?>  
                        </div><?php /* ----- end of panel-body ----- */?>
                    </div><?php /* ----- end of panel panel-default ----- */?>
                </div><?php /* ----- end of col-lg-12 ----- */?>
            </div>
            <?php /* ----- 臨停未結清單(結束) ----- */?>
			
			
<!-- ----- 臨停未結確認小框 ----- -->
<div class="modal fade" id="cario_temp_check_dialog">
<div class="modal-dialog modal-sm">
<div class="modal-content">
<div class="modal-header"><h3>臨停未結查核作業</h3></div>
<div class="modal-body">
<form id="cario_temp_check_form" class="center-block">    
<div class="main">
<div class="dataTable_wrapper">
<table class="table table-striped table-bordered table-hover" style="font-size:12px;">
<tbody id="cario_temp_check_list">
<tr>
<td style="text-align:right;">進場車號</td>
<td style="text-align:left;" id="cario_temp_check_lpr"></td>
</tr>  
<tr>
<td style="text-align:right;">進場時間</td>
<td style="text-align:left;" id="cario_temp_check_in_time"></td>
</tr>
<tr>
<td style="text-align:right;">限時離場時間</td>
<td style="text-align:left;" id="cario_temp_check_out_before_time"></td>
</tr>  
<tr>
<td style="text-align:right;">最後付款時間</td>
<td style="text-align:left;" id="cario_temp_check_pay_time"></td>
</tr>

<tr>
<td style="text-align:right;vertical-align:middle">臨停未結說明</td>
<td style="text-align:left;"><input type="text" id="cario_temp_check_remarks" class="form-control" style="width:150px !important;" /></td>
</tr>

</tbody>
</table>                    
<button type="button" class="btn btn-large btn-success pull-left" onclick="do_cario_temp_check_ok();">確認完成</button>
&nbsp;&nbsp;
<button type="button" class="btn btn-large btn-cancel" onclick="$('#cario_temp_check_dialog').modal('hide');">取消</button>
</div><!-- ----- end of dataTable_wrapper ----- -->  
</div><!-- ----- end of main ----- -->
</form>
</div><!-- end of modal-body --> 
</div><!-- end of modal-content --> 
</div><!-- end of modal-dialog -->
</div><!-- end of modal show -->
<!-- ----- 臨停未結確認小框 (結束) ----- -->  

<script> 

// 載入
function reload_cario_temp_not_finished_query_all()
{
			$("#altob_current_time_str").text(moment(new Date()).format("YYYY-MM-DD HH:mm:ss"));
			
        	$("#cario_temp_not_finished_query_all").html("");	// -- 清除原內容 --
			$.ajax
        	({
        		url: "<?=APP_URL?>cario_temp_not_finished_query_all",
            	type: "post", 
            	dataType:"json",
				data:{"station_no":$("#station_select").val(), "q_item":'in_time', "q_str":'2017-02-01'},
            	success:function(jdata)
				{       
					var cario_temp_list = ["<tr>"];  
					for(idx in jdata)
					{                                         
						cario_no = jdata[idx]['cario_no'];   
						cario_temp_list = cario_temp_list.concat(["<td style='text-align:left;'>", cario_no, "</td>"]);
						cario_temp_list = cario_temp_list.concat(["<td id='cario_temp_not_finished_query_all_lpr_", cario_no, 
							"' data-station_no='", jdata[idx]['station_no'], 
							"' data-cario_no='", jdata[idx]['cario_no'], 
							"' data-lpr='", jdata[idx]['lpr'], 
							"' data-in_time='", jdata[idx]['in_time'], 
							"' data-out_before_time='", jdata[idx]['out_before_time'], 
							"' data-pay_time='", jdata[idx]['pay_time'], 
							"' style='text-align:left;'>", jdata[idx]['lpr'], "</td>"]);
						
						cario_temp_list = cario_temp_list.concat(["<td style='text-align:center;'>", jdata[idx]['in_time'], "</td>"]);	
						cario_temp_list = cario_temp_list.concat(["<td style='text-align:center;'>", jdata[idx]['pay_time'], "</td>"]);	
						cario_temp_list = cario_temp_list.concat(["<td style='text-align:center;'>", jdata[idx]['out_before_time'], "</td>"]);	
						cario_temp_list = cario_temp_list.concat(["<td style='color:red;text-align:center;'><button class='btn btn-default' style='color:red;' onclick='cario_temp_check(",  cario_no + ");'>人工審核</button></td>"]);
						
						cario_temp_list = cario_temp_list.concat(["</tr>"]);	
					}
					$("#cario_temp_not_finished_query_all").append(cario_temp_list.join(''));  
				}
        	});
}

// 臨停未結確認
function cario_temp_check(cario_no)
{
	var station_no = $("#cario_temp_not_finished_query_all_lpr_"+cario_no).data("station_no");
	var lpr = $("#cario_temp_not_finished_query_all_lpr_"+cario_no).data("lpr");
	var in_time = $("#cario_temp_not_finished_query_all_lpr_"+cario_no).data("in_time");
	var pay_time = $("#cario_temp_not_finished_query_all_lpr_"+cario_no).data("pay_time");
	var out_before_time = $("#cario_temp_not_finished_query_all_lpr_"+cario_no).data("out_before_time");
	
	// 發票資訊
	xvars["cario_temp_check"] = Array();             
	xvars["cario_temp_check"]["cario_no"] = cario_no;
	xvars["cario_temp_check"]["station_no"] = station_no;
	xvars["cario_temp_check"]["in_time"] = in_time;
	xvars["cario_temp_check"]["pay_time"] = (pay_time == "") ? '未付款': pay_time;
	xvars["cario_temp_check"]["out_before_time"] = out_before_time;

	$("#cario_temp_check_lpr").text("").text(lpr);
	$("#cario_temp_check_in_time").text("").text(in_time);
	$("#cario_temp_check_pay_time").text("").text(pay_time);
	$("#cario_temp_check_out_before_time").text("").text(out_before_time);
	$("#cario_temp_check_remarks").text("");
	
	$("#cario_temp_check_dialog").modal({backdrop:false,keyboard:false});
}

// 臨停未結確認完成
function do_cario_temp_check_ok()
{                                    
	if (!confirm("確認審核通過 ?")) return false;

	var station_no = xvars["cario_temp_check"]["station_no"];
	var cario_no = xvars["cario_temp_check"]["cario_no"];
	var cario_temp_check_remarks = $("#cario_temp_check_remarks").val();
	
	if (cario_temp_check_remarks == "")
	{
      	alertify_msg("請說明原因，謝謝");
		return false;
    } 
	
	//alertify_msg("施工中..zzz");
	//return false;
	
    // 新增審核資訊
	$.ajax
    ({
        url:APP_URL+"cario_temp_confirmed", 
        dataType:"text",
        type:"post",
        data:
        {
        	"station_no": station_no,
			"cario_no": cario_no,
        	"remarks": cario_temp_check_remarks
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
            	alertify_msg("確認完成！"); 
            	reload_cario_temp_not_finished_query_all();
            }
			else if(jdata == "not_synced")
			{
				alertify_sync(station_no);
			}
			else if (jdata == "check_fail")	
            {                              
            	alertify_msg("操作失敗，已取消"); 
			}
            else
            {
              	alertify_msg("操作失敗 !");
            }   
    	}                                                                          
    }); 
    
    delete xvars["cario_temp_check"];
    $('#cario_temp_check_dialog').modal('hide'); 
}

</script>