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
											<th style="text-align:center;">總金額（不含押金）</th>
											<th style="text-align:center;">押金</th>
											<th style="text-align:center;">租約結束時間</th>
                                            <th style="text-align:center;">結算金額</th>
											<th style="text-align:center;">結算操作</th>
											<th style="text-align:center;">轉租操作</th>
											<th style="text-align:center;">操作狀態</th>
											<th style="text-align:center;">退租發票</th>
											<th style="text-align:center;">發票狀態</th>
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
						var refund_tot_amt = parseInt(jdata[idx]['refund_tot_amt']);
						var refund_deposit = parseInt(jdata[idx]['refund_deposit']);
						var refund_amt = parseInt(jdata[idx]['refund_amt']);
				
						member_refund_id = jdata[idx]['member_refund_id'];   
						member_list = member_list.concat(["<td style='text-align:center;'>", jdata[idx]['member_refund_id'], "</td>"]);		
						member_list = member_list.concat(["<td id='member_tx_refund_query_lpr_", member_refund_id, 
							"' data-station_no='", jdata[idx]['station_no'], 
							"' data-member_no='", jdata[idx]['member_no'], 
							"' data-lpr='", jdata[idx]['lpr'], 
							"' data-member_company_no='", jdata[idx]['member_company_no'], 
							"' data-company_no='", jdata[idx]['company_no'], 
							"' data-refund_amt='", refund_amt, 
							"' data-refund_deposit='", refund_deposit, 
							"' data-refund_tot_amt='", refund_tot_amt, 
							"' data-refund_time='", jdata[idx]['refund_time'], 
							"' data-refund_state='", jdata[idx]['refund_state'], 
							"' data-dismiss_state='", jdata[idx]['dismiss_state'], 
							"' data-create_time='", jdata[idx]['create_time'], 
							"' style='text-align:left;'>", jdata[idx]['lpr'], "</td>"]);
						
						if(refund_amt >= 0)
						{
							member_list = member_list.concat(["<td style='text-align:center;'>", refund_amt, " 元</td>"]);		
						}
						else
						{
							member_list = member_list.concat(["<td style='text-align:center;color:red;'>補繳 ", -refund_amt, " 元</td>"]);		
						}
						
						member_list = member_list.concat(["<td style='text-align:center;'>", refund_deposit, " 元</td>"]);
						member_list = member_list.concat(["<td style='text-align:center;'>", jdata[idx]['refund_time'], "</td>"]);	
						
						// a.結算描述
						if(refund_tot_amt >= refund_deposit)
						{
							// a.1 總金額，超過押金
							member_list = member_list.concat(["<td style='text-align:left;color:green;'>", 
								"可轉移押金", refund_deposit, "元, 共可退", refund_tot_amt, " 元</td>"]);
						}
						else if(refund_tot_amt >= 0)
						{
							// a.2 總金額，未達押金
							member_list = member_list.concat(["<td style='text-align:left;color:blue;'>", 
								"扣除部份押金後，共可退", refund_tot_amt, " 元</td>"]);	
						}
						else
						{
							// a.3 總金額需補繳
							member_list = member_list.concat(["<td style='color:red;text-align:left;color:red;'>",
								"扣除所有押金後，需補繳 ", -refund_tot_amt, " 元</td>"]);	
						}
						
						// b.結算描述操作
						if(jdata[idx]['dismiss_state'] == 0)
						{
							// b.1 剛退租
							member_list = member_list.concat(["<td style='text-align:left;'>"]);
							if(refund_tot_amt >= refund_deposit)
							{
								// b.1.1 剛退租, 押金保留，退還剩餘金額
								member_list = member_list.concat(["<button class='btn btn-default' onclick='member_refund_keep_deposit(",  member_refund_id ,");'>退還", refund_amt, "元，押金保留</button>"]);
							}
							else
							{
								// b.1.2 剛退租, 結清所有金額
								member_list = member_list.concat(["<button class='btn btn-default' onclick='member_refund_dismiss_all(",  member_refund_id ,");'>結清所有金額</button>"]);	
							}
							member_list = member_list.concat(["</td>"]);
							member_list = member_list.concat(["<td style='text-align:left;'></td>"]);
							member_list = member_list.concat(["<td style='color:red;text-align:center;'>未結清</td>"]);
						}
						else if(jdata[idx]['dismiss_state'] == 1)
						{
							// b.2 已轉租
							member_list = member_list.concat(["<td style='text-align:left;'></td>"]);
							member_list = member_list.concat(["<td style='text-align:left;'></td>"]);
							member_list = member_list.concat(["<td style='color:green;text-align:center;'>已轉租</td>"]);
						}
						else if(jdata[idx]['dismiss_state'] == 10)
						{
							// b.3 尚餘押金, 結清所有金額 或 轉租
							member_list = member_list.concat(["<td style='text-align:left;'>"]);
							member_list = member_list.concat(["<button class='btn btn-default' onclick='member_refund_keep_deposit_dismiss_all(",  member_refund_id ,");'>退還押金</button>"]);	
							member_list = member_list.concat(["</td>"]);
							member_list = member_list.concat(["<td style='text-align:left;'><button class='btn btn-default' onclick='member_refund_transfer(",  member_refund_id ,");'>轉租</button></td>"]);
							member_list = member_list.concat(["<td style='color:blue;text-align:center;'>待退押金</td>"]);
						}
						else if(jdata[idx]['dismiss_state'] == 100)
						{
							// b.4 已結清
							member_list = member_list.concat(["<td style='text-align:left;'></td>"]);
							member_list = member_list.concat(["<td style='text-align:left;'></td>"]);
							member_list = member_list.concat(["<td style='color:black;text-align:center;'>已結清</td>"]);
						}
						else
						{
							// b.z 未知
							member_list = member_list.concat(["<td style='text-align:left;'></td>"]);
							member_list = member_list.concat(["<td style='text-align:left;'></td>"]);
							member_list = member_list.concat(["<td style='color:red;text-align:center;'>未定義</td>"]);
						}
						
						member_list = member_list.concat(["<td style='text-align:center;'><button class='btn btn-default' onclick='show_member_refund_detail(",  member_refund_id ,");'>瀏覽</button></td>"]);

						// c. 發票狀態
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
	
// 押金保留，結清其它金額
function member_refund_keep_deposit(member_refund_id)
{
	var station_no = $("#member_tx_refund_query_lpr_"+member_refund_id).data("station_no");
	var refund_amt = $("#member_tx_refund_query_lpr_"+member_refund_id).data("refund_amt");
	var refund_deposit = $("#member_tx_refund_query_lpr_"+member_refund_id).data("refund_deposit");
	var meta_str_1 = "已退還金額 " + refund_amt + " 元";
	var meta_str_2 = "未退還金額 " + refund_deposit + " 元（押金）";
	var msg = "注意！！請先確認以下事項：<br/><br/>" + 
			"1. 此用戶，" + meta_str_1 + "<br/>" +
			"2. 此用戶，" + meta_str_2 + "<br/>" +
			"<br/>※ 此操作完成後，才能進行轉租操作！**";
			
	ajax_member_refund_dismiss('member_refund_keep_deposit', member_refund_id, msg);
}

// 押金保留，結清所有金額
function member_refund_keep_deposit_dismiss_all(member_refund_id)
{
	var station_no = $("#member_tx_refund_query_lpr_"+member_refund_id).data("station_no");
	var refund_tot_amt = $("#member_tx_refund_query_lpr_"+member_refund_id).data("refund_tot_amt");
	var refund_deposit = $("#member_tx_refund_query_lpr_"+member_refund_id).data("refund_deposit");
	var meta_str = "已退還押金 " + refund_deposit + " 元，合計金額共" + refund_tot_amt + '元，已全數退還完畢！'
	var msg = "注意！！請先確認以下事項：<br/><br/>" + 
			"1. 此用戶，" + meta_str + "<br/>" +
			"2. 此用戶，與本場站互不相欠<br/>" +
			"<br/>※ 以上兩點都必需確認完成，才結清！**" +
			"<br/>※ 結清後，將無法進行任何轉租操作！**";
			
	ajax_member_refund_dismiss('member_refund_dismiss_all', member_refund_id, msg);
}
	
// 結清所有金額
function member_refund_dismiss_all(member_refund_id)
{
	var station_no = $("#member_tx_refund_query_lpr_"+member_refund_id).data("station_no");
	var refund_tot_amt = $("#member_tx_refund_query_lpr_"+member_refund_id).data("refund_tot_amt");
	var meta_str = (refund_tot_amt > 0) ? ('總待退金額共：' + refund_tot_amt + '元，己全數退還！') : ('需補繳金額共：' + -refund_tot_amt + '元，已全數補繳！');
	var msg = "注意！！請先確認以下兩點事項：<br/><br/>" + 
			"1. 此用戶，" + meta_str + "<br/>" +
			"2. 此用戶，已無任何押金留在本場站<br/>" +
			"<br/>※ 以上兩點都必需確認完成，才結清！**" +
			"<br/>※ 結清後，將無法進行任何轉租操作！**";
			
	ajax_member_refund_dismiss('member_refund_dismiss_all', member_refund_id, msg);
}

// 結清 (ajax)
function ajax_member_refund_dismiss(cmd, member_refund_id, msg)
{
	var station_no = $("#member_tx_refund_query_lpr_"+member_refund_id).data("station_no");
	
	alertify.set({ 
		buttonFocus: "cancel",
		labels: {
			ok     : "確認完成",
			cancel : "取消"
		}
	});
	alertify.confirm(
		msg
		, function (e){
		if (e) {
			$.ajax
			({
				url: APP_URL + cmd, 
				dataType:"text",
				type:"post",
				data: {"station_no": station_no, "member_refund_id": member_refund_id},
				error:function(xhr, ajaxOptions, thrownError)
				{
					var error_msg = xhr.responseText ? xhr.responseText : "連線失敗, 請稍候再試";
					alertify_msg(error_msg);
					console.log("error:"+error_msg+"|"+ajaxOptions+"|"+thrownError);  
				},
				success:function(result)
				{
					if(result == 'ok')
					{
						alertify_log('操作完成!');
						
						$("#member_tx_refund_form").submit();
					}
					else
					{
						alertify_error('操作失敗..');
					}
				}                                                                          
			});
			
		} else {
			alertify_log('請記得處理, 謝謝');
		}
	});
}
	
// 轉租
function member_refund_transfer(member_refund_id)
{
	var station_no = $("#member_tx_refund_query_lpr_"+member_refund_id).data("station_no");
	var member_no = $("#member_tx_refund_query_lpr_"+member_refund_id).data("member_no");
	
	// 取得退租資料
	$.ajax
    ({     
        url:APP_URL+"member_refund_transfer_data_query",
        type:"post",
        dataType:"json",
		data:{"station_no":station_no, "member_no":member_no, "member_refund_id":member_refund_id},
		error:function(xhr, ajaxOptions, thrownError)
		{
			alertify_msg(xhr.responseText);
			console.log("error:"+xhr.responseText+"|"+ajaxOptions+"|"+thrownError);  
			return false;
		},
        success:function(jdata)
        {
			if(jdata['result_code'] == 'OK')
			{
				show_item('member_add', 'member_add');
				
				var member_id = jdata['result']['member_id'];
				var member_no = jdata['result']['member_no'];
				var member_name = jdata['result']['member_name'];
				var member_nick_name = jdata['result']['member_nick_name'];
				var member_attr = jdata['result']['member_attr'];
				var fee_period = jdata['result']['fee_period'];
				var contract_no = jdata['result']['contract_no'];
				var lpr = jdata['result']['lpr'];
				var etag = jdata['result']['etag'];
				var member_company_no = jdata['result']['member_company_no'];
				var mobile_no = jdata['result']['mobile_no'];
				var tel_o = jdata['result']['tel_o'];
				var tel_h = jdata['result']['tel_h'];
				var addr = jdata['result']['addr'];
				var park_time = jdata['result']['park_time'];
				var refund_deposit = jdata['result']['refund_deposit'];
				var refund_amt = jdata['result']['refund_amt'];
				var refund_state = jdata['result']['refund_state'];
				var dismiss_state = jdata['result']['dismiss_state'];
				var refund_time = jdata['result']['refund_time'];
				
				// 取得折扣金額 （目前就是押金）
				var member_refund_discount = refund_deposit;
				
				$("#station_no_modify").val(station_no);
				$("#ma_station_no").val(station_no);
				
				$("#ma_lpr").val(lpr);
				$("#ma_old_lpr").val(lpr);
				
				$("#ma_etag").val(etag);
				$("#ma_member_id").val(member_id);
				$("#ma_member_company_no").val(member_company_no);
				$("#ma_tel_o").val(tel_o);
				$("#ma_tel_h").val(tel_h);
				$("#ma_addr").val(addr);
				$("#ma_member_name").val(member_name);        
				$("#ma_mobile_no").val(mobile_no);
				
				// 記錄本此轉租資訊
				$("#ma_refund_transfer_id").val(member_refund_id);
				$("#ma_refund_transfer_discount").val(member_refund_discount);
				
				// 留空
				//$("#ma_demonth_start_date").val(''); 	
				//$("#ma_contract_no").val('');
				//$("#ma_deposit").val('');
				//$("#ma_amt1").val('');
				//$("#ma_amt").val('');
				
				$("#fee_period1 option[value='"+fee_period+"']").prop("selected", "selected");
				$("#fee_period option[value='"+fee_period+"']").prop("selected", "selected");
				$("#member_attr option[value='"+member_attr+"']").prop("selected", "selected"); 
				
				if(contract_no)
				{
					$("#member_data_type").html("轉租操作：上期合約編號 [" + contract_no + "] 未退押金" + refund_deposit + "元，可折扺本次交易押金（多退少補）</button>"); 	
				}
				else
				{
					$("#member_data_type").html("轉租操作：上期未退押金" + refund_deposit + "元，可折扺本次交易押金（多退少補）</button>"); 
				}
				
				$("input[id^=pt_id_]").prop("checked",false);	// 全部取消勾 
				arr = park_time.split(","); 
				for(idx in arr)
				{
					$("#pt_id_"+arr[idx]).prop("checked",true);
				}
			}
			else if(jdata['result_code'] == 'NOT_FOUND')
			{
				alertify_msg("資料異常，請通知總公司處理。。");
			}
			else
			{
				alertify_error('操作失敗..');
			}
		}
    });	
}

// 折讓發票
function member_refund_invoice_allowance(tx_bill_no)
{
	if (!confirm("確定折讓發票 ?"))	return false;
	
	var station_no = $("#tx_bill_lpr_"+tx_bill_no).data("station_no");
	var member_no = $("#tx_bill_lpr_"+tx_bill_no).data("member_no");
	var refund_amt = $("#tx_bill_lpr_"+tx_bill_no).data("refund_amt");
	var tx_no = $("#tx_bill_lpr_"+tx_bill_no).data("tx_no");
	
	if(refund_amt > 0)
	{
		// 尚餘金額繼續開立
		$.ajax
			({
				url:APP_URL+"refund_invoice_allowance",
				type:"post", 
				dataType:"text",
				data:{	"station_no":station_no, "tx_no":tx_no, 
						"tx_bill_no":tx_bill_no, "member_no":member_no, 
						"refund_amt":refund_amt},
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
						alertify_msg("折讓完成 ! ");
						
						$("#member_tx_refund_form").submit();
					}
					else if (jdata == "tx_error_not_found")	
					{                              
						alertify_msg("異常：查無開立資訊");
					}
					else if (jdata == "tx_error_not_ready")	
					{                              
						alertify_msg("異常：查無發票資訊");
					}
					else
					{
						alertify_msg("異常：" + jdata);
					} 
				}
			});
	}
	else
	{
		// 各期月租發票補印
		alertify_msg("流程異常：請通知總公司處理：" + tx_bill_no);
	}
}
	
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
				url:APP_URL+"member_refund_bill_query",
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
								//member_list = member_list.concat(["<td style='text-align:center;'><button class='btn btn-default' onclick='member_refund_invoice_allowance(",  jdata[idx]['tx_bill_no'] ,");'>待折讓 ", jdata[idx]['refund_amt'] ," 元</button></td>"]);			
								member_list = member_list.concat(["<td style='text-align:center;'><button class='btn btn-default' onclick='member_tx_check(",  jdata[idx]['tx_bill_no'] ,");'>待折讓 ", jdata[idx]['refund_amt'] ," 元</button></td>"]);			
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