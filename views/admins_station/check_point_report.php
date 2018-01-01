
			<?php /* ----- 關帳查詢 ----- */?>
            <div data-items="check_point_report" class="row" style="display:none;">
                <div class="col-lg-12">
                    <div class="panel panel-default">
						<div class="panel-heading">
                            <form id="check_point_form" role="form">
								<label class="input-inline" for="check_point_search_station_no_modify">關帳查詢</label>&nbsp;&nbsp;
								<select id="check_point_search_station_no_modify" name="check_point_search_station_no_modify"></select>
								
								&nbsp;<span class="input-inline" >，</span>&nbsp;
								
								<label class="input-inline" for="check_point_search_time_from">開始</label>&nbsp;&nbsp;
								<input type="text" class="date" id="check_point_search_time_from" name="check_point_search_time_from">
								
								&nbsp;<span class="input-inline" > ~ </span>&nbsp;
								
								<label class="input-inline" for="check_point_search_time_to">結束</label>&nbsp;&nbsp;
								<input type="text" class="date" id="check_point_search_time_to" name="check_point_search_time_to">
								
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
											<th style="text-align:center;">編號</th>
											<th style="text-align:center;">上次關帳時間</th>
                                            <th style="text-align:center;">本次關帳時間</th>
											<th style="text-align:center;">上次最後交易代號</th>
											<th style="text-align:center;">本次最後交易代號</th>
											<th style="text-align:center;">總金額（不含押金）</th>
											<th style="text-align:center;">總押金</th>
											<th style="text-align:center;">操作種類</th>
											<th style="text-align:center;">功能</th>
											<th style="text-align:center;">建立時間</th>
											<th style="text-align:center;">備註</th>
                                        </tr>
                                    </thead>
                                    <tbody id="check_point_list" style="font-size:18px;"></tbody>
                                </table>
                            </div><?php /* ----- end of dataTable_wrapper ----- */?>  
                        </div><?php /* ----- end of panel-body ----- */?>
                    </div><?php /* ----- end of panel panel-default ----- */?>
                </div><?php /* ----- end of col-lg-12 ----- */?>
				
				
                <div id="check_point_list_detail_box" class="col-lg-12" style="display:none;">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            關帳明細（<span id='check_point_list_detail_time_str_1'></span>&nbsp;開始&nbsp;~&nbsp;至&nbsp;<span id='check_point_list_detail_time_str_2'></span>&nbsp;關帳，期間所有交易）
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <div class="dataTable_wrapper">
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
											<th style="text-align:left;">代號</th>
											<th style="text-align:center;">入帳日</th>
											<th style="text-align:left;">入帳車號</th>
											<th style="text-align:left;">目前車號</th>
											<th style="text-align:center;">會員開始日</th>
											<th style="text-align:center;">上期繳期</th>
                                            <th style="text-align:center;">上期結束日</th>
                                            <th style="text-align:center;">上期租金</th>
											<th style="text-align:center;">本期繳期</th>
											<th style="text-align:center;">本期開始日</th>
                                            <th style="text-align:center;">本期結束日</th>
                                            <th style="text-align:center;">本期租金</th>
											<th style="text-align:center;">押金</th>
											<th style="text-align:center;">審核狀態</th>
											<th style="text-align:center;">有效期限</th>
											<th style="text-align:center;">備註</th>
                                        </tr>
                                    </thead>
                                    <tbody id="check_point_list_detail" style="font-size:18px;"></tbody>
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
			$(new Option(st[station_no],station_no)).appendTo('#check_point_search_station_no_modify');		// 場站
		}

		var initMinDate = moment("00:00", "HH:mm").subtract(10, 'days');
		var initMaxDate = moment("23:59", "HH:mm");
		var searchTimeFromPicker = $( "#check_point_search_time_from" );
		var searchTimeToPicker = $( "#check_point_search_time_to" );
		
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

$("#check_point_form").submit(function(event)
{                   
   	event.preventDefault();        
	
	var station_no = $("#check_point_search_station_no_modify").val();
	var check_point_time_from = $( "#check_point_search_time_from" ).val();
	var check_point_time_to = $( "#check_point_search_time_to" ).val();
	
	$("#check_point_list").html("");
	$("#check_point_list_detail").html("");
	$("#check_point_list_detail_box").hide();
	
	$.ajax
    ({
        url:APP_URL+"check_point_query", 
        dataType:"json",
        type:"post",
        data:
        {
        	"station_no": station_no,
        	"check_point_time_from": check_point_time_from,
			"check_point_time_to": check_point_time_to
        },
		error:function(xhr, ajaxOptions, thrownError)
				{
					var error_msg = xhr.responseText ? xhr.responseText : "連線失敗, 請稍候再試";
					alertify_msg(error_msg);
					console.log("error:"+error_msg+"|"+ajaxOptions+"|"+thrownError);  
				},
        success:function(jdata)
				{       
					var check_point_list = ["<tr>"];  
					for(idx in jdata)
					{                        
						check_no = jdata[idx]['check_no'];   
						check_point_list = check_point_list.concat(["<td style='text-align:center;'>", check_no, "</td>"]);
						check_point_list = check_point_list.concat(["<td id='check_point_data_", check_no, 
							"' data-station_no='", jdata[idx]['station_no'], 
							"' data-check_no='", jdata[idx]['check_no'], 
							"' data-check_time='", jdata[idx]['check_time'], 
							"' data-check_time_no='", jdata[idx]['check_time_no'], 
							"' data-check_time_last='", jdata[idx]['check_time_last'], 
							"' data-check_time_last_no='", jdata[idx]['check_time_last_no'], 
							"' data-check_type='", jdata[idx]['check_type'], 
							"' data-check_amt='", jdata[idx]['check_amt'], 
							"' data-check_deposit='", jdata[idx]['check_deposit'],
							"' data-remarks='", jdata[idx]['remarks'], 
							"' style='text-align:center;'>", jdata[idx]['check_time_last'], "</td>"]);
						
						check_point_list = check_point_list.concat(["<td style='text-align:center;'>", jdata[idx]['check_time'], "</td>"]);	
						check_point_list = check_point_list.concat(["<td style='text-align:center;'>", jdata[idx]['check_time_last_no'], "</td>"]);	
						check_point_list = check_point_list.concat(["<td style='text-align:center;'>", jdata[idx]['check_time_no'], "</td>"]);	
						check_point_list = check_point_list.concat(["<td style='text-align:center;'>", jdata[idx]['check_amt'], "</td>"]);							
						check_point_list = check_point_list.concat(["<td style='text-align:center;'>", jdata[idx]['check_deposit'], "</td>"]);	
						
						if(jdata[idx]['check_type'] == 1)
						{
							check_point_list = check_point_list.concat(["<td style='text-align:center;'>手動關帳</td>"]);		
						}
						else
						{
							check_point_list = check_point_list.concat(["<td style='text-align:center;'>未知</td>"]);		
						}
						
						check_point_list = check_point_list.concat(["<td style='color:red;text-align:center;'><button class='btn btn-default' style='color:blue;' onclick='check_point_detail(",  check_no + ");'>明細</button></td>"]);
						
						check_point_list = check_point_list.concat(["<td style='text-align:center;'>", jdata[idx]['create_time'], "</td>"]);	
						check_point_list = check_point_list.concat(["<td style='text-align:left;'>", jdata[idx]['remarks'], "</td>"]);
						check_point_list = check_point_list.concat(["</tr>"]);	
					}
					$("#check_point_list").append(check_point_list.join(''));  
				}                                                                     
    }); 
});


// 關帳查詢（明細）
function check_point_detail(check_no)
{
	$("#check_point_list_detail").html("");	// -- 清除原內容 --
	
	var station_no = $("#check_point_data_"+check_no).data("station_no");
	var check_time = $("#check_point_data_"+check_no).data("check_time");
	var check_time_no = $("#check_point_data_"+check_no).data("check_time_no");
	var check_time_last = $("#check_point_data_"+check_no).data("check_time_last");
	var check_time_last_no = $("#check_point_data_"+check_no).data("check_time_last_no");
	
	$.ajax
        	({
        		url: "<?=APP_URL?>check_point_detail_query",
            	type: "post", 
            	dataType:"json",
            	data: {
					"station_no": station_no,
					"check_time_no": check_time_no,
					"check_time_last_no": check_time_last_no
				},
				error:function(xhr, ajaxOptions, thrownError)
				{
					var error_msg = xhr.responseText ? xhr.responseText : "連線失敗, 請稍候再試";
					alertify_msg(error_msg);
					console.log("error:"+error_msg+"|"+ajaxOptions+"|"+thrownError);  
					
					$("#check_point_list_detail_box").hide();
				},
            	success:function(jdata)
				{   
					$("#check_point_list_detail_time_str_1").text(check_time_last);				
					$("#check_point_list_detail_time_str_2").text(check_time);
					$("#check_point_list_detail_box").show();
				
					var member_list = ["<tr>"];  
					for(idx in jdata)
					{                                         
						tx_no = jdata[idx]['tx_no'];   
						member_list = member_list.concat(["<td style='text-align:center;'>", tx_no, "</td>"]);
						member_list = member_list.concat(["<td id='acc_date_", tx_no, "' style='text-align:center;'>", jdata[idx]['acc_date'], "</td>"]);
						member_list = member_list.concat(["<td id='check_point_list_detail_data_", tx_no, 
							"' data-station_no='", jdata[idx]['station_no'], 
							"' data-member_no='", jdata[idx]['member_no'], 
							"' data-tx_no='", jdata[idx]['tx_no'], 
							"' data-member_company_no='", jdata[idx]['member_company_no'], 
							"' data-company_no='", jdata[idx]['company_no'], 
							"' data-amt='", jdata[idx]['amt'], 
							"' data-amt1='", jdata[idx]['amt1'], 
							"' data-deposit='", jdata[idx]['deposit'], 
							"' data-start_date_last='", jdata[idx]['start_date_last'], 
							"' data-end_date='", jdata[idx]['end_date'], 
							"' data-lpr='", jdata[idx]['lpr'], 
							"' data-fee_period='", jdata[idx]['fee_period'], 
							"' style='text-align:left;'>", jdata[idx]['lpr'], "</td>"]);
						
						if(jdata[idx]['current_lpr'])
						{
							member_list = member_list.concat(["<td id='current_lpr_", tx_no, "' style='text-align:center;'>", jdata[idx]['current_lpr'], "</td>"]);		
						}
						else
						{
							member_list = member_list.concat(["<td id='current_lpr_", tx_no, "' style='color:black;text-align:center;'>無</td>"]);		
						}
						
						member_list = member_list.concat(["<td id='sdate_last_", tx_no, "' style='text-align:center;'>", jdata[idx]['start_date_last'], "</td>"]);	
						member_list = member_list.concat(["<td id='fee_period_last_", tx_no, "' style='text-align:center;'>", period_name[jdata[idx]['fee_period_last']], "</td>"]);	
						member_list = member_list.concat(["<td id='edate_last_", tx_no, "' style='text-align:center;'>", jdata[idx]['end_date_last'], "</td>"]);	
						member_list = member_list.concat(["<td id='amt_last_", tx_no, "' style='text-align:center;'>", jdata[idx]['amt_last'], "</td>"]);	
						member_list = member_list.concat(["<td id='fee_period_", tx_no, "' style='text-align:center;'>", period_name[jdata[idx]['fee_period']], "</td>"]);	
						member_list = member_list.concat(["<td id='sdate_", tx_no, "' style='text-align:center;'>", jdata[idx]['start_date'], "</td>"]);	
						member_list = member_list.concat(["<td id='edate_", tx_no, "' style='text-align:center;'>", jdata[idx]['end_date'], "</td>"]);	
						member_list = member_list.concat(["<td id='amt_", tx_no, "' style='text-align:center;'>", jdata[idx]['amt'], "</td>"]);		
						member_list = member_list.concat(["<td id='deposit_", tx_no, "' style='text-align:center;'>", jdata[idx]['deposit'], "</td>"]);		

						if(jdata[idx]['tx_state'] == 4)
						{
							member_list = member_list.concat(["<td style='color:black;text-align:center;'>已退租</td>"]);
						}						
						else if(jdata[idx]['tx_state'] == 44)
						{
							member_list = member_list.concat(["<td style='color:black;text-align:center;'>交易取消</td>"]);
						}
						else if(jdata[idx]['verify_state'] == 0)
						{
							member_list = member_list.concat(["<td style='color:red;text-align:center;'><button class='btn btn-default' style='color:red;' onclick='member_tx_check(",  tx_no + ");'>待審核</button></td>"]);
						}
						else if(jdata[idx]['verify_state'] == 1)
						{
							member_list = member_list.concat(["<td style='color:green;text-align:center;'>已審核</td>"]);
						}
						else
						{
							member_list = member_list.concat(["<td style='color:red;text-align:center;'><button class='btn btn-default' style='color:blue;' onclick='member_tx_check(",  tx_no + ");'>未通過</button></td>"]);
						}
						
						member_list = member_list.concat(["<td style='color:red;text-align:center;' id='valid_time_", tx_no, "'>", jdata[idx]['valid_time'], "</td>"]);	
						member_list = member_list.concat(["<td style='color:blue;text-align:left;' id='remarks_", tx_no, "'>", jdata[idx]['remarks'], "</td>"]);	
						member_list = member_list.concat(["</tr>"]);	
					}
					$("#check_point_list_detail").append(member_list.join(''));  
				}
        	});
}







</script>