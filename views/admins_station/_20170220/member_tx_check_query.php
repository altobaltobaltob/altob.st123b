			<?php /* ----- 交易查核總覽 ----- */?>
            <div data-items="member_tx_check_query" class="row" style="display:none;">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            待審核清單
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <div class="dataTable_wrapper">
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
											<th style="text-align:left;">代號</th>
                                            <!--th style="text-align:left;">場站</th-->
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
											<!--th style="text-align:center;">發票開立</th-->
											<th style="text-align:center;">審核狀態</th>
											<th style="text-align:center;">有效期限</th>
											<th style="text-align:center;">備註</th>
                                        </tr>
                                    </thead>
                                    <tbody id="member_tx_check" style="font-size:18px;"></tbody>
                                </table>
                            </div><?php /* ----- end of dataTable_wrapper ----- */?>  
                        </div><?php /* ----- end of panel-body ----- */?>
                    </div><?php /* ----- end of panel panel-default ----- */?>
                </div><?php /* ----- end of col-lg-12 ----- */?>
            </div>
            <?php /* ----- 交易查核總覽(結束) ----- */?>

<script>  

// 載入
function reload_member_tx_check_query()
{
			$("#member_tx_check").html("");	// -- 清除原內容 --
			$.ajax
        	({
        		url: "<?=APP_URL?>member_tx_check_query",
            	type: "post", 
            	dataType:"json",
            	data: {},
            	success:function(jdata)
				{       
					var member_list = ["<tr>"];  
					for(idx in jdata)
					{                                         
						tx_no = jdata[idx]['tx_no'];   
						member_list = member_list.concat(["<td style='text-align:center;'>", tx_no, "</td>"]);
						//member_list = member_list.concat(["<td style='text-align:center;'>", st[jdata[idx]['station_no']], "</td>"]);
						member_list = member_list.concat(["<td id='acc_date_", tx_no, "' style='text-align:center;'>", jdata[idx]['acc_date'], "</td>"]);
						member_list = member_list.concat(["<td id='member_tx_lpr_", tx_no, 
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
							member_list = member_list.concat(["<td id='current_lpr_", tx_no, "' style='text-align:center;'>已刪除</td>"]);		
						}
						
						member_list = member_list.concat(["<td id='sdate_last_", tx_no, "' style='text-align:center;'>", jdata[idx]['start_date_last'], "</td>"]);	
						member_list = member_list.concat(["<td id='fee_period_last_", tx_no, "' style='text-align:center;'>", period_name[jdata[idx]['fee_period_last']], "</td>"]);	
						member_list = member_list.concat(["<td id='edate_last_", tx_no, "' style='text-align:center;'>", jdata[idx]['end_date_last'], "</td>"]);	
						member_list = member_list.concat(["<td id='amt_last_", tx_no, "' style='text-align:center;'>", jdata[idx]['amt_last'], "</td>"]);	
						member_list = member_list.concat(["<td id='fee_period_", tx_no, "' style='text-align:center;'>", period_name[jdata[idx]['fee_period']], "</td>"]);	
						member_list = member_list.concat(["<td id='sdate_", tx_no, "' style='text-align:center;'>", jdata[idx]['start_date'], "</td>"]);	
						member_list = member_list.concat(["<td id='edate_", tx_no, "' style='text-align:center;'>", jdata[idx]['end_date'], "</td>"]);	
						member_list = member_list.concat(["<td id='amt_", tx_no, "' style='text-align:center;'>", jdata[idx]['amt'], "</td>"]);		
						//member_list = member_list.concat(["<td style='text-align:center;'><button class='btn btn-default' onclick='show_member_tx_bill(",  tx_no ,");'>瀏覽</button></td>"]);

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
							member_list = member_list.concat(["<td style='color:red;text-align:center;'><button class='btn btn-default' style='color:red;' onclick='member_tx_check_query_tx_check(",  tx_no + ");'>待審核</button></td>"]);
						}
						else if(jdata[idx]['verify_state'] == 1)
						{
							member_list = member_list.concat(["<td style='color:green;text-align:center;'>已審核</td>"]);
						}
						else
						{
							member_list = member_list.concat(["<td style='color:red;text-align:center;'><button class='btn btn-default' style='color:blue;' onclick='member_tx_check_query_tx_check(",  tx_no + ");'>未通過</button></td>"]);
						}
						
						member_list = member_list.concat(["<td style='color:red;text-align:center;' id='valid_time_", tx_no, "'>", jdata[idx]['valid_time'], "</td>"]);	
						member_list = member_list.concat(["<td style='color:blue;text-align:left;' id='remarks_", tx_no, "'>", jdata[idx]['remarks'], "</td>"]);	
						member_list = member_list.concat(["</tr>"]);	
					}
					$("#member_tx_check").append(member_list.join(''));  
				}
        	});
}

// 查核作業
function member_tx_check_query_tx_check(tx_no)
{
	alertify_msg("請通知總公司營管!<br/><br/>電話：02-27057716 分機 119<br/><br/>");    	
}

</script>