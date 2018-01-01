			<?php /* ----- 會員清單 ----- */?>
            <div data-items="member_query_all" class="row" style="display:none;">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            會員清單
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <div class="dataTable_wrapper">
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th style="text-align:left;">車號</th>
                                            <th style="text-align:left;">姓名</th>
                                            <th style="text-align:center;">手機</th>
                                            <th style="text-align:center;">開始日</th>
                                            <th style="text-align:center;">結束日</th>
                                            <th style="text-align:center;">繳期</th>
											<th style="text-align:center;">身份</th>
											<!--th style="text-align:center;">租金</th-->
											<th style="text-align:center;">停權</th>
											<!--th style="text-align:center;">狀態</th-->
											<th style="text-align:center;">有效期限</th>
											<!--th style="text-align:center;">備註</th-->
                                        </tr>
                                    </thead>
                                    <tbody id="member_list_all" style="font-size:18px;"></tbody>
                                </table>
                            </div><?php /* ----- end of dataTable_wrapper ----- */?>  
                        </div><?php /* ----- end of panel-body ----- */?>
                    </div><?php /* ----- end of panel panel-default ----- */?>
                </div><?php /* ----- end of col-lg-12 ----- */?>
            </div>
            <?php /* ----- 會員清單(結束) ----- */?>

<script>  

// 載入
function reload_member_query_all()
{
			$("#member_list_all").html("");<?php /* 清除原內容 */ ?>
            $.ajax
        	({
        		url: "<?=APP_URL?>member_query_all",
            	type: "post", 
            	dataType:"json",
            	data: {},
            	success: function(jdata)
            	{       
                	var member_list = [];  
                	for(idx in jdata)
                    {                                         
                    	mno = jdata[idx]['member_no'];
                    	member_list = member_list.concat([
						"<tr><td id='member_query_all_lpr_", mno, "' style='text-align:left; '>", jdata[idx]['lpr'], "</td>", 	
                    		"<td id='name_", mno, "' style='text-align:left; '>", jdata[idx]['member_name'], "</td>", 	
                    		"<td id='mobile_", mno, "' style='text-align:center; '>", jdata[idx]['mobile_no'], "</td>", 	
                    		"<td id='sdate_", mno, "' style='text-align:center; '>", jdata[idx]['start_date'], "</td>", 	
                    		"<td id='edate_", mno, "' style='text-align:center; '>", jdata[idx]['end_date'], "</td>", 	
							"<td id='fee_period_", mno, "' style='text-align:center; '>", period_name[jdata[idx]['fee_period']], "</td>", 
							"<td id='member_attr_", mno, "' style='text-align:center; '>", mem_attr[jdata[idx]['member_attr']], "</td>", 
                    		//"<td id='contract_", mno, "' style='text-align:center; '>", jdata[idx]['contract_no'], "</td>", 	    
                    		//"<td id='etag_", mno, "' style='text-align:center; '>", jdata[idx]['etag'], "</td>", 	    
                    		//"<td id='amt_", mno, "' style='text-align:center; '>", jdata[idx]['amt'], "</td>", 	
                    		//"<td style='text-align:center, '><select id='sel_", mno, "' onChange='member_modify(", mno, "); '><option value='choice'>請選擇</option><option value='modify'>修改</option><option value='delete'>刪除</option></select></td>", 	
							//"</tr>"
						]);
						
						if(jdata[idx]['suspended'] == "1")
						{
							member_list = member_list.concat(["<td style='text-align:center;'><input type='checkbox' checked id='suspended_", mno, "' disabled/></td>"]);	
						}
						else
						{
							member_list = member_list.concat(["<td style='text-align:center;'><input type='checkbox' id='suspended_", mno, "' disabled/></td>"]);
						}
						
						/*
						if(jdata[idx]['verify_state'] == 0)
						{
							member_list = member_list.concat(["<td style='color:red;text-align:center;' id='verify_state_", mno, "'><button class='btn btn-default' onclick='member_tx_check(0);'>待審核</button></td>"]);
						}
						else if(jdata[idx]['verify_state'] == 1)
						{
							member_list = member_list.concat(["<td style='color:green;text-align:center;' id='verify_state_", mno, "'>審核通過</td>"]);
						}
						else
						{
							member_list = member_list.concat(["<td style='color:blue;text-align:center;' id='verify_state_", mno, "'><button class='btn btn-default' style='color:blue;' onclick='member_tx_check(0);'>未通過</button></td>"]);
						}
						*/
						
						if(jdata[idx]['valid_time'] < jdata[idx]['end_date'])
						{
							//member_list = member_list.concat(["<td style='color:red;text-align:left;' id='valid_time_", mno, "'>", jdata[idx]['valid_time'], "</td>"]);
							member_list = member_list.concat(["<td style='color:red;text-align:center;' id='valid_time_", mno, "'><button class='btn' style='color:red;' onclick='member_query_all_tx_check(0);'>將於 ", jdata[idx]['valid_time'], " 到期</button></td>"]);
						}
						else
						{
							member_list = member_list.concat(["<td style='color:green;text-align:left;' id='valid_time_", mno, "'>結束日 ", jdata[idx]['end_date'], " 到期</td>"]);
						}
						
						//member_list = member_list.concat(["<td style='color:blue;text-align:center;' id='remarks_", mno, "'>", jdata[idx]['remarks'], "</td>"]);	
						member_list = member_list.concat(["</tr>"]);
                    }
                	$("#member_list_all").append(member_list.join('')); 
            	}
        	});  
}

// 查核作業
function member_query_all_tx_check(tx_no)
{
	alertify_msg("請通知總公司營管!<br/><br/>電話：02-27057716 分機 119<br/><br/>");    	
}

</script>