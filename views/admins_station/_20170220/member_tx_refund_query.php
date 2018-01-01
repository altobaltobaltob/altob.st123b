			<?php /* ----- 交易退款總覽 ----- */?>
            <div data-items="member_tx_refund_query" class="row" style="display:none;">
                <div class="col-lg-12">
                    <div class="panel panel-default">
						<div class="panel-heading">
                            退租查詢
                            <form id="member_tx_refund_form" role="form">
                            <div class="form-group">
                            <label class="select-inline" for="station_refund_select">
                            <select class="form-control" id="station_refund_select">
                            </select>
                            </label>
                            <label class="radio-inline"><input type="radio" name="q_item" value="lpr" checked />車號</label>
                            <label class="input-inline">&nbsp;&nbsp;<input type="text" id="q_refund_str" placeholder="關鍵字" /></label>
                            <label class="input-inline"><input type="submit" value="查詢" /></label> 
                            </div>
                            </form>
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <div class="dataTable_wrapper">
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
											<th style="text-align:center;">代號</th>
											<th style="text-align:center;">車號</th>
											<th style="text-align:center;">金額</th>
											<th style="text-align:center;">押金</th>
                                            <th style="text-align:center;">總金額 （金額 + 押金）</th>
                                            <th style="text-align:center;">租約結束時間</th>
											<th style="text-align:center;">退租發票</th>
											<th style="text-align:center;">退租狀態</th>
											<th style="text-align:center;">建立時間</th>
                                        </tr>
                                    </thead>
                                    <tbody id="member_tx_refund" style="font-size:18px;"></tbody>
                                </table>
                            </div><?php /* ----- end of dataTable_wrapper ----- */?>  
                        </div><?php /* ----- end of panel-body ----- */?>
                    </div><?php /* ----- end of panel panel-default ----- */?>
                </div><?php /* ----- end of col-lg-12 ----- */?>
				
				
				<div id="member_tx_refund_list_detail_box" class="col-lg-12" style="display:none;">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            交易記錄（車號：<span id='member_tx_refund_list_detail_lpr'></span>，退租時間：<span id='member_tx_refund_list_detail_refund_time'></span>）
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <div class="dataTable_wrapper">
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
											<th style="text-align:left;">代號</th>
											<th style="text-align:center;">入帳日</th>
											<th style="text-align:left;">車號</th>
											<th style="text-align:center;">會員開始日</th>
											<th style="text-align:center;">上期繳期</th>
                                            <th style="text-align:center;">上期結束日</th>
                                            <th style="text-align:center;">上期租金</th>
											<th style="text-align:center;">本期繳期</th>
											<th style="text-align:center;">本期開始日</th>
                                            <th style="text-align:center;">本期結束日</th>
                                            <th style="text-align:center;">本期租金</th>
											<th style="text-align:center;">發票時間</th>
											<th style="text-align:center;">買方統編</th>
											<th style="text-align:center;">賣方統編</th>
											<th style="text-align:center;">發票金額</th>
											<th style="text-align:center;">發票字軌</th>
											<th style="text-align:center;">發票號碼</th>
											<th style="text-align:center;">發票種類</th>
											<th style="text-align:center;">狀態</th>
											<th style="text-align:center;">待辦金額</th>
                                        </tr>
                                    </thead>
                                    <tbody id="member_tx_refund_list_detail" style="font-size:16px;"></tbody>
                                </table>
                            </div><!-- ----- end of dataTable_wrapper ----- -->  
                        </div><!-- ----- end of panel-body ----- -->
                    </div><!-- ----- end of panel panel-default ----- -->
                </div><!-- ----- end of col-lg-12 ----- -->
				
				
            </div>
            <?php /* ----- 交易退款總覽(結束) ----- */?>

<script>  

	// 設定場站資訊 
    for(station_no in st)
    {
		$(new Option(st[station_no],station_no)).appendTo('#station_refund_select');  	// 會員退租場站編號  
    }

	// 退租查詢      
	$("#member_tx_refund_form").submit(function(e)
	{ 
      	e.preventDefault();
        
		if ($("#q_refund_str").val() == "")
    	{
    	  	alertify_log("請填寫查詢關鍵字..");
    	    return false;
    	}
		
		$("#member_tx_refund").html(""); // clean all
		$("#member_tx_refund_list_detail").html("");
		$("#member_tx_refund_list_detail_box").hide();
    	
    	$.ajax
        ({
        	url: "<?=APP_URL?>member_tx_refund_query",
            type: "post", 
            dataType:"json",
            data:{"station_no":$("#station_refund_select").val(), "q_item":$("input:radio:checked[name=q_item]").val(), "q_str":$("#q_refund_str").val()},
			error:function(xhr, ajaxOptions, thrownError)
				{
					var error_msg = xhr.responseText ? xhr.responseText : "連線失敗, 請稍候再試";
					alertify_msg(error_msg);
					console.log("error:"+error_msg+"|"+ajaxOptions+"|"+thrownError);  
				},
            success:function(jdata)
				{       
					var member_list = ['<tr>'];  
					for(idx in jdata)
					{                                         
						member_refund_id = jdata[idx]['member_refund_id'];   
						member_list = member_list.concat(["<td style='text-align:center;'>", jdata[idx]['member_refund_id'], "</td>"]);		
						member_list = member_list.concat(["<td id='member_tx_refund_query_lpr_", member_refund_id, 
							"' data-station_no='", jdata[idx]['station_no'], 
							"' data-member_no='", jdata[idx]['member_no'], 
							"' data-lpr='", jdata[idx]['lpr'], 
							"' data-member_company_no='", jdata[idx]['member_company_no'], 
							"' data-company_no='", jdata[idx]['company_no'], 
							"' data-refund_amt='", jdata[idx]['refund_amt'], 
							"' data-refund_deposit='", jdata[idx]['refund_deposit'], 
							"' data-refund_tot_amt='", jdata[idx]['refund_tot_amt'], 
							"' data-refund_time='", jdata[idx]['refund_time'], 
							"' data-refund_state='", jdata[idx]['refund_state'], 
							"' data-create_time='", jdata[idx]['create_time'], 
							"' style='text-align:left;'>", jdata[idx]['lpr'], "</td>"]);
						
						if(jdata[idx]['refund_amt'] >= 0)
						{
							member_list = member_list.concat(["<td style='text-align:center;'>", jdata[idx]['refund_amt'], " 元</td>"]);		
						}
						else
						{
							member_list = member_list.concat(["<td style='text-align:center;'>需補繳 ", -jdata[idx]['refund_amt'], " 元</td>"]);		
						}
						
						member_list = member_list.concat(["<td style='text-align:center;'>", jdata[idx]['refund_deposit'], " 元</td>"]);
						
						if(jdata[idx]['refund_tot_amt'] >= 0)
						{
							member_list = member_list.concat(["<td style='text-align:center;'>共退還 ", jdata[idx]['refund_tot_amt'], " 元</td>"]);	
						}
						else
						{
							member_list = member_list.concat(["<td style='color:red;text-align:center;'>總共需補繳 ", -jdata[idx]['refund_tot_amt'], " 元</td>"]);	
						}
						
						member_list = member_list.concat(["<td style='text-align:center;'>", jdata[idx]['refund_time'], "</td>"]);	
						
						member_list = member_list.concat(["<td style='text-align:center;'><button class='btn btn-default' onclick='show_member_refund_detail(",  member_refund_id ,");'>瀏覽</button></td>"]);

						if(jdata[idx]['refund_state'] == 0)
						{
							member_list = member_list.concat(["<td style='color:blue;text-align:center;'>待確認</td>"]);							
						}
						else if(jdata[idx]['refund_state'] == 1)
						{
							member_list = member_list.concat(["<td style='color:red;text-align:center;'>待補開</td>"]);
						}
						else if(jdata[idx]['refund_state'] == 2)
						{
							member_list = member_list.concat(["<td style='color:red;text-align:center;'>待折讓</td>"]);
						}
						else if(jdata[idx]['refund_state'] == 100)
						{
							member_list = member_list.concat(["<td style='color:black;text-align:center;'>已完成</td>"]);
						}
						else
						{
							member_list = member_list.concat(["<td style='color:red;text-align:center;'>未定義</td>"]);
						}
						
						member_list = member_list.concat(["<td style='text-align:center;'>", jdata[idx]['create_time'], "</td>"]);	
						
						member_list = member_list.concat(["</tr>"]);	
					}
					$("#member_tx_refund").append(member_list.join(''));  
				}
        });
    }); 
	
// 退租記錄
function show_member_refund_detail(member_refund_id)
{
	var refund_lpr = $("#member_tx_refund_query_lpr_"+member_refund_id).data("lpr");
	var refund_time = $("#member_tx_refund_query_lpr_"+member_refund_id).data("refund_time");
	$("#member_tx_refund_list_detail_lpr").text(refund_lpr);
	$("#member_tx_refund_list_detail_refund_time").text(refund_time);
	
	show_member_tx_refund_bill(0, '', '', '4', 0, member_refund_id);	
}

// 完成退租交易
/*
function complete_member_refund(member_refund_id)
{
	var refund_state = $("#member_tx_refund_query_lpr_"+member_refund_id).data("refund_state");
	var refund_lpr = $("#member_tx_refund_query_lpr_"+member_refund_id).data("lpr");
	
	$("#member_tx_refund_list_detail_lpr").text(refund_lpr);
	
	if(refund_state == 0)
	{
		// 待確認流程
		alertify_log("待確認流程");
	}
	else if(refund_state == 1)
	{
		show_member_tx_refund_bill(0, '', '1', '4');	// 待補開 (已退租)
	}
	else if(refund_state == 2)
	{
		show_member_tx_refund_bill(0, '', '2', '4');	// 待折讓 (已退租, 已開立發票)
	}
	else
	{
		// 未定義
		alertify_log("未定義");
	}
	
	return false;
}
*/

// 發票開立記錄
function show_member_tx_refund_bill(tx_no=0, verify_state_str='', invoice_state_str='', tx_state_str='', tx_bill_no =0, member_refund_id=0)
{	
	$("#member_tx_refund_list_detail").html("");	// -- 清除原內容 --
	
	$.ajax
			({
				url:APP_URL+"member_tx_bill_query",
				type:"post", 
				dataType:"json",
				data:{"station_no":station_no, "tx_no":tx_no, "verify_state_str":verify_state_str, 
					"invoice_state_str":invoice_state_str, "tx_state_str":tx_state_str, 
					"tx_bill_no":tx_bill_no, "member_refund_id":member_refund_id},
				error:function(xhr, ajaxOptions, thrownError)
				{
					var error_msg = xhr.responseText ? xhr.responseText : "連線失敗, 請稍候再試";
					alertify_msg(error_msg);
					console.log("error:"+error_msg+"|"+ajaxOptions+"|"+thrownError);  
					
					$("#member_tx_refund_list_detail_box").hide();
				},
				success:function(jdata)
				{       				
					$("#member_tx_refund_list_detail_box").show();
				
					var member_list = [["<tr>"]];
					for(idx in jdata)
					{                    
						//console.log(jdata.length + " : " + idx + " , " + jdata[idx]['invoice_amt'] + " ： " + jdata[idx]['remain_amt']);				
						
						tx_no = jdata[idx]['tx_no'];   
						member_list = member_list.concat(["<td style='text-align:left;'>", jdata[idx]['tx_no'], "_", jdata[idx]['tx_bill_no'], "</td>"]);
						//member_list = member_list.concat(["<td style='text-align:left;'>", st[jdata[idx]['station_no']], "</td>"]);
						member_list = member_list.concat(["<td id='acc_date_", tx_no, "' style='text-align:center;'>", jdata[idx]['acc_date'], "</td>"]);
						member_list = member_list.concat(["<td id='tx_bill_lpr_", jdata[idx]['tx_bill_no'], 
							"' data-station_no='", jdata[idx]['station_no'], 
							"' data-member_no='", jdata[idx]['member_no'], 
							"' data-tx_bill_no='", jdata[idx]['tx_bill_no'], 
							"' data-tx_no='", jdata[idx]['tx_no'], 
							"' data-member_company_no='", jdata[idx]['member_company_no'], 
							"' data-company_no='", jdata[idx]['company_no'], 
							"' data-invoice_amt='", jdata[idx]['invoice_amt'], 
							"' data-remain_amt='", jdata[idx]['remain_amt'], 
							"' data-period_3_amt='", jdata[idx]['period_3_amt'], 
							"' data-amt='", jdata[idx]['amt'], 
							"' data-amt1='", jdata[idx]['amt1'], 
							"' data-deposit='", jdata[idx]['deposit'], 
							"' data-start_date_last='", jdata[idx]['start_date_last'], 
							"' data-end_date='", jdata[idx]['end_date'], 
							"' data-lpr='", jdata[idx]['lpr'], 
							"' data-fee_period='", jdata[idx]['fee_period'], 
							"' data-refund_amt='", jdata[idx]['refund_amt'], 
							"' data-invoice_state='", jdata[idx]['invoice_state'], 
							"' style='text-align:left;'>", jdata[idx]['lpr'], "</td>"]);

						member_list = member_list.concat(["<td id='sdate_last_", tx_no, "' style='text-align:center;'>", jdata[idx]['start_date_last'], "</td>"]);	
						member_list = member_list.concat(["<td id='fee_period_last_", tx_no, "' style='text-align:center;'>", period_name[jdata[idx]['fee_period_last']], "</td>"]);	
						member_list = member_list.concat(["<td id='edate_last_", tx_no, "' style='text-align:center;'>", jdata[idx]['end_date_last'], "</td>"]);	
						member_list = member_list.concat(["<td id='amt_last_", tx_no, "' style='text-align:center;'>", jdata[idx]['amt_last'], "</td>"]);	
						member_list = member_list.concat(["<td id='fee_period_", tx_no, "' style='text-align:center;'>", period_name[jdata[idx]['fee_period']], "</td>"]);	
						member_list = member_list.concat(["<td id='sdate_", tx_no, "' style='text-align:center;'>", jdata[idx]['start_date'], "</td>"]);	
						
						if(jdata[idx]['invoice_state'] == 1)
						{
							// 待補開
							member_list = member_list.concat(["<td id='edate_", tx_no, "' style='text-align:center;'>指定退租日<br/>", jdata[idx]['end_date'], "</td>"]);	
							member_list = member_list.concat(["<td id='amt_", tx_no, "' style='text-align:center;'>補繳總金額<br/>", jdata[idx]['amt'], " 元</td>"]);		
						}
						else if(jdata[idx]['invoice_state'] == 2)
						{
							// 待折讓
							member_list = member_list.concat(["<td id='edate_", tx_no, "' style='text-align:center;'>指定退租日<br/>", jdata[idx]['end_date'], "</td>"]);	
							member_list = member_list.concat(["<td id='amt_", tx_no, "' style='text-align:center;'>折讓總金額<br/>", jdata[idx]['amt'], " 元</td>"]);		
						}
						else
						{
							member_list = member_list.concat(["<td id='edate_", tx_no, "' style='text-align:center;'>", jdata[idx]['end_date'], "</td>"]);	
							member_list = member_list.concat(["<td id='amt_", tx_no, "' style='text-align:center;'>", jdata[idx]['amt'], " 元</td>"]);			
						}
						
						// 是否已有發票
						if(jdata[idx]['invoice_no'] > 0)
						{
							member_list = member_list.concat(["<td id='invoice_time_", tx_no, "' style='text-align:center;'>", jdata[idx]['invoice_time'], "</td>"]);
							member_list = member_list.concat(["<td id='member_company_no_", tx_no, "' style='text-align:center;'>", jdata[idx]['member_company_no'], "</td>"]);
							member_list = member_list.concat(["<td id='company_no_", tx_no, "' style='text-align:center;'>", jdata[idx]['company_no'], "</td>"]);
							member_list = member_list.concat(["<td id='invoice_amt_", tx_no, "' style='text-align:center;'>", jdata[idx]['invoice_amt'], "</td>"]);
							member_list = member_list.concat(["<td id='invoice_track_", tx_no, "' style='text-align:center;'>", jdata[idx]['invoice_track'], "</td>"]);
							member_list = member_list.concat(["<td id='invoice_no_", tx_no, "' style='text-align:center;'>", jdata[idx]['invoice_no'], "</td>"]);
							
							if(jdata[idx]['invoice_type'] == 0)
							{
								member_list = member_list.concat(["<td id='invoice_type_", tx_no, "' style='text-align:center;'>電子發票</td>"]);
							}
							else if(jdata[idx]['invoice_type'] == 1)
							{
								member_list = member_list.concat(["<td id='invoice_type_", tx_no, "' style='text-align:center;'>手開發票</td>"]);
							}
							else
							{
								member_list = member_list.concat(["<td id='invoice_type_", tx_no, "' style='text-align:center;'>異常</td>"]);
							}
						}
						else
						{
							member_list = member_list.concat(["<td id='invoice_time_", tx_no, "' style='text-align:center;'>未開立</td>"]);
							member_list = member_list.concat(["<td id='member_company_no_", tx_no, "' style='text-align:center;'>", jdata[idx]['member_company_no'], "</td>"]);
							member_list = member_list.concat(["<td id='company_no_", tx_no, "' style='text-align:center;'>", jdata[idx]['company_no'], "</td>"]);
							member_list = member_list.concat(["<td id='invoice_amt_", tx_no, "' style='text-align:center;'>", jdata[idx]['invoice_amt'], "</td>"]);
							member_list = member_list.concat(["<td id='invoice_track_", tx_no, "' style='text-align:center;'></td>"]);
							
							if(jdata[idx]['tx_state'] == 4 && jdata[idx]['invoice_state'] == 0)
							{
								// 已退租, 原先交易將不再開放開立
								member_list = member_list.concat(["<td id='invoice_no_", tx_no, "' style='text-align:center;'></td>"]);
								member_list = member_list.concat(["<td id='invoice_type_", tx_no, "' style='text-align:center;'></td>"]);
							}
							else
							{
								member_list = member_list.concat(["<td id='invoice_no_", tx_no, "' style='text-align:center;'><button class='btn btn-default' onclick='print_tx_invoice(",  jdata[idx]['tx_bill_no'] ,");'>列印發票</button></td>"]);
								member_list = member_list.concat(["<td id='invoice_type_", tx_no, "' style='text-align:center;'><button class='btn btn-default' onclick='hand_tx_invoice(",  jdata[idx]['tx_bill_no'] ,");'>手開發票</button></td>"]);	
							}
						}
						
						//member_list = member_list.concat(["<td style='color:blue;text-align:center;' id='remarks_", jdata[idx]['tx_bill_no'], "'>", jdata[idx]['remarks'], "</td>"]);	

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
						
						// 剩餘開立金額
						if(jdata[idx]['invoice_state'] == 1)
						{
							// 待開立
							if(jdata[idx]['remain_amt'] > 0)
							{
								member_list = member_list.concat(["<td style='text-align:center;'><button class='btn btn-default' onclick='next_refund_bill(",  jdata[idx]['tx_bill_no'] ,");'>尚餘 ", jdata[idx]['remain_amt'] ," 元</button></td>"]);			
							}
							else
							{
								member_list = member_list.concat(["<td style='text-align:center;'>無</td>"]);
							}	
						}
						else if(jdata[idx]['invoice_state'] == 2)
						{
							// 待折讓
							if(jdata[idx]['refund_amt'] > 0)
							{
								member_list = member_list.concat(["<td style='text-align:center;'><button class='btn btn-default' onclick='refund_invoice_allowance(",  jdata[idx]['tx_bill_no'] ,");'>待折讓 ", jdata[idx]['refund_amt'] ," 元</button></td>"]);			
							}
							else
							{
								member_list = member_list.concat(["<td style='text-align:center;'>異常</td>"]);
							}
						}	
						else
						{
							// 待開立
							if(jdata[idx]['remain_amt'] > 0)
							{
								if(jdata[idx]['tx_state'] == 4 && jdata[idx]['invoice_state'] == 0)
								{
									// 已退租, 原先交易將不再開放開立
									member_list = member_list.concat(["<td style='text-align:center;'>尚餘 ", jdata[idx]['remain_amt'] ," 元</td>"]);			
								}
								else
								{
									member_list = member_list.concat(["<td style='text-align:center;'><button class='btn btn-default' onclick='next_tx_bill(",  jdata[idx]['tx_bill_no'] ,");'>尚餘 ", jdata[idx]['remain_amt'] ," 元</button></td>"]);				
								}
								
							}
							else
							{
								member_list = member_list.concat(["<td style='text-align:center;'>無</td>"]);
							}	
						}
						
						member_list = member_list.concat(["</tr>"]);;	
					}
					$("#member_tx_refund_list_detail").append(member_list.join('')); 
				}
			});	
}

</script>