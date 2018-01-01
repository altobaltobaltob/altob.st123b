
			<?php /* ----- 電子發票查詢 ----- */?>
            <div data-items="member_invoice_report" class="row" style="display:none;">
                <div class="col-lg-12">
                    <div class="panel panel-default">
						<div class="panel-heading">
                            <form id="member_invoice_form" role="form">
								<label class="input-inline" for="member_invoice_search_station_no_modify">電子發票查詢</label>&nbsp;&nbsp;
								<select id="member_invoice_search_station_no_modify" name="member_invoice_search_station_no_modify"></select>
								
								&nbsp;<span class="input-inline" >，</span>&nbsp;
								
								<label class="input-inline" for="member_invoice_search_time_from">開始</label>&nbsp;&nbsp;
								<input type="text" class="date" id="member_invoice_search_time_from" name="member_invoice_search_time_from">
								
								&nbsp;<span class="input-inline" > ~ </span>&nbsp;
								
								<label class="input-inline" for="member_invoice_search_time_to">結束</label>&nbsp;&nbsp;
								<input type="text" class="date" id="member_invoice_search_time_to" name="member_invoice_search_time_to">
								
								&nbsp;&nbsp;
								
								<label class="input-inline"><input type="submit" value="查詢" /></label> 
                            </form>
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <div class="dataTable_wrapper">
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
											<th style="text-align:left;">建立時間</th>
											<th style="text-align:center;">車牌號碼</th>
                                            <th style="text-align:center;">發票金額</th>
                                            <th style="text-align:center;">發票號碼</th>
                                            <th style="text-align:center;">發票統編</th>
                                            <th style="text-align:center;">通知信箱</th>
											<th style="text-align:center;">通知簡訊</th>
											<th style="text-align:center;">備註</th>
											<th style="text-align:center;">操作</th>
                                        </tr>
                                    </thead>
                                    <tbody id="member_invoice_list" style="font-size:18px;"></tbody>
                                </table>
                            </div><?php /* ----- end of dataTable_wrapper ----- */?>  
                        </div><?php /* ----- end of panel-body ----- */?>
                    </div><?php /* ----- end of panel panel-default ----- */?>
                </div><?php /* ----- end of col-lg-12 ----- */?>
				
            </div>
            <?php /* ----- 關帳查詢(結束) ----- */?>
			
<script>  

		// 設定場站資訊 
		for(station_no in st)
		{
			$(new Option(st[station_no],station_no)).appendTo('#member_invoice_search_station_no_modify');		// 場站
		}

		var initMinDate = moment("00:00", "HH:mm").subtract(10, 'days');
		var initMaxDate = moment("23:59", "HH:mm");
		var searchTimeFromPicker = $( "#member_invoice_search_time_from" );
		var searchTimeToPicker = $( "#member_invoice_search_time_to" );
		
		// 日期
		/* DatePicker 設定start */
		searchTimeFromPicker.datetimepicker({
			dateFormat: 'yy-mm-dd',
			maxDate: '0',
			changeMonth: true,
			numberOfMonths: 1,
			timeFormat: "HH:mm",
			addSliderAccess: true,
			sliderAccessArgs: { touchonly: false },
			onClose: function( selectedDate ) {
				searchTimeToPicker.datetimepicker( "option", "minDate", selectedDate );
				
				console.log("search_time_from: " + selectedDate);
			}
		});
		searchTimeFromPicker.datetimepicker('setDate', initMinDate.toDate());
		searchTimeToPicker.datetimepicker({
			dateFormat: 'yy-mm-dd',
			maxDate: '0',
			changeMonth: true,
			numberOfMonths: 1,
			timeFormat: "HH:mm",
			addSliderAccess: true,
			sliderAccessArgs: { touchonly: false },
			onClose: function( selectedDate ) {
				searchTimeFromPicker.datetimepicker( "option", "maxDate", selectedDate );
				
				console.log("search_time_to: " + selectedDate);
			}
		});
		searchTimeToPicker.datetimepicker('setDate', initMaxDate.toDate());
		/* DatePicker 設定end */

$("#member_invoice_form").submit(function(event)
{                   
   	event.preventDefault();        
	
	var station_no = $("#member_invoice_search_station_no_modify").val();
	var member_invoice_time_from = $( "#member_invoice_search_time_from" ).val();
	var member_invoice_time_to = $( "#member_invoice_search_time_to" ).val();
	
	$("#member_invoice_list").html("");
	
	$.ajax
    ({
        url:APP_URL+"member_invoice_query", 
        dataType:"json",
        type:"post",
        data:
        {
        	"station_no": station_no,
        	"member_invoice_time_from": member_invoice_time_from,
			"member_invoice_time_to": member_invoice_time_to
        },
		error:function(xhr, ajaxOptions, thrownError)
				{
					var error_msg = xhr.responseText ? xhr.responseText : "連線失敗, 請稍候再試";
					alertify_msg(error_msg);
					console.log("error:"+error_msg+"|"+ajaxOptions+"|"+thrownError);  
				},
        success:function(jdata)
				{       
					var member_invoice_list = ["<tr>"];  
					for(idx in jdata)
					{                		
						order_no = jdata[idx]['order_no'];   
						member_invoice_list = member_invoice_list.concat(["<td id='member_invoice_data_", idx, 
							"' data-station_no='", jdata[idx]['station_no'], 
							"' data-order_no='", jdata[idx]['order_no'], 
							"' data-lpr='", jdata[idx]['lpr'], 
							"' data-tx_time='", jdata[idx]['tx_time'], 
							"' data-tx_type='", jdata[idx]['tx_type'], 
							"' data-invoice_no='", jdata[idx]['invoice_no'], 
							"' data-invoice_remark='", jdata[idx]['invoice_remark'], 
							"' data-company_no='", jdata[idx]['company_no'], 
							"' data-amt='", jdata[idx]['amt'], 
							"' data-email='", jdata[idx]['email'], 
							"' data-mobile='", jdata[idx]['mobile'], 
							"' data-status='", jdata[idx]['status'], 
							"' style='text-align:left;'>", jdata[idx]['tx_time'], "</td>"]);
						
						member_invoice_list = member_invoice_list.concat(["<td style='text-align:center;'>", jdata[idx]['lpr'], "</td>"]);	
						member_invoice_list = member_invoice_list.concat(["<td style='text-align:center;'>", jdata[idx]['amt'], "</td>"]);	
						member_invoice_list = member_invoice_list.concat(["<td style='text-align:center;'>", jdata[idx]['invoice_no'], "</td>"]);	
						member_invoice_list = member_invoice_list.concat(["<td style='text-align:center;'>", jdata[idx]['company_no'], "</td>"]);	
						member_invoice_list = member_invoice_list.concat(["<td style='text-align:left;'>", jdata[idx]['email'], "</td>"]);	
						member_invoice_list = member_invoice_list.concat(["<td style='text-align:left;'>", jdata[idx]['mobile'], "</td>"]);							

						if(jdata[idx]['status'] == 104)
						{
							member_invoice_list = member_invoice_list.concat(["<td style='text-align:left;color:red;'>已作廢</td>"]);		
							member_invoice_list = member_invoice_list.concat(["<td style='text-align:center;'></td>"]);
						}
						else if(jdata[idx]['status'] == 105)
						{
							member_invoice_list = member_invoice_list.concat(["<td style='text-align:left;'>已折讓</td>"]);		
							member_invoice_list = member_invoice_list.concat(["<td style='text-align:center;'></td>"]);
						}
						else
						{
							member_invoice_list = member_invoice_list.concat(["<td style='text-align:left;'></td>"]);		
							member_invoice_list = member_invoice_list.concat(["<td style='text-align:center;'><select id='member_invoice_sel_", idx,"' onclick='member_invoice_modify(", idx, "); '><option value='choice'>請選擇</option><option value='void'>作廢</option></select></td>"]);
						}
						
						member_invoice_list = member_invoice_list.concat(["</tr>"]);	
					}
					$("#member_invoice_list").append(member_invoice_list.join(''));  
				}                                                                     
    }); 
});

// 電子發票選項 
function member_invoice_modify(idx)
{
	select_item = $("#member_invoice_sel_" + idx).val(); 
    $("#member_invoice_sel_"+idx+" option[value='choice']").prop("selected", true);
	
	// 已超過一天禁止操作
	if(moment().subtract(1, 'days').isAfter($("#member_invoice_data_"+idx).data("tx_time")))
	{
		alertify_msg("已逾時，請通知營管處理。。");
		return false;
	}
	
	// 建立電子發票資訊
	xvars["invoice"] = Array();             
	xvars["invoice"]["station_no"] = $("#member_invoice_data_"+idx).data("station_no");
	xvars["invoice"]["invoice_no"] = $("#member_invoice_data_"+idx).data("invoice_no");
	xvars["invoice"]["order_no"] = $("#member_invoice_data_"+idx).data("order_no");
	
    switch(select_item)
    {   
		// 作廢發票
		case "void":
			invoice_void();
		break;
		
		// 折讓發票
		case "allowance":
			alertify_log('開發中..');
		break;
		
		default:	// -- 其餘選擇(忽略不處理) --
        	return false;
    }	
}  

// 作廢發票
function invoice_void()
{
	var ok_msg = "作廢發票 " + xvars["invoice"]["invoice_no"];
	
	alertify.set({ 
		buttonFocus: "cancel",
		labels: {
			cancel : "取消",
			ok     : ok_msg
		}
	});
	alertify.confirm("確定 " + ok_msg + " 嗎?", function (e){
		if (e) {
			// 執行作廢
			$.ajax
			({
				url:APP_URL+"member_invoice_void", 
				dataType:"text",
				type:"post",
				data:
				{
					"station_no":xvars["invoice"]["station_no"],
					"invoice_no":xvars["invoice"]["invoice_no"],
					"order_no":xvars["invoice"]["order_no"],
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
						alertify_msg("發票作廢完成 ! "); 
						$("#member_invoice_form").submit();
					}
					else
					{
						alertify_msg("操作失敗。。" + jdata);
					}
				}
			});
			
			delete xvars["invoice"];
			
		} else {
			alertify_log('操作已取消');
		}
	});
}


</script>