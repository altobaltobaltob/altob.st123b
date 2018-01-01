			<?php /* ----- 費率清單 ----- */?>
            <div data-items="price_plan_query_all" class="row" style="display:none;">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            費率設定 <button class='btn btn-default' style='font-size:16px;' onclick='sync_price_plan();'>更新</button>
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <div class="dataTable_wrapper">
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th style="text-align:left;">場站</th>
											<th style="text-align:left;">收費類型</th>
                                            <th style="text-align:left;" width="35%">費率設定</th>
											<th style="text-align:left;">註記事項</th>
											<th style="text-align:center;">啟用時間</th>
                                            <th style="text-align:center;">有效期限</th>
                                        </tr>
                                    </thead>
                                    <tbody id="price_plan_list" style="font-size:20px;"></tbody>
                                </table>
                            </div><?php /* ----- end of dataTable_wrapper ----- */?>  
                        </div><?php /* ----- end of panel-body ----- */?>
                    </div><?php /* ----- end of panel panel-default ----- */?>
                </div><?php /* ----- end of col-lg-12 ----- */?>
            </div>
            <?php /* ----- 費率清單(結束) ----- */?>

<script>  

reload_price_plan_query_all();

// 載入
function reload_price_plan_query_all()
{
			$("#price_plan_list").html("");<?php /* 清除原內容 */ ?>
            $.ajax
        	({
        		url: "<?=APP_URL?>price_plan_query_all",
            	type: "post", 
            	dataType:"json",
            	data: {},
            	success: function(jdata)
            	{       
                	var member_list = [];  
                	for(idx in jdata)
                    {                                         
                    	txid = jdata[idx]['txid'];
						member_list = member_list.concat(["<tr><td style='text-align:left;'>", st[jdata[idx]['station_no']], "</td>"]);
						
						var price_plan_content = [];
						if(jdata[idx]['tx_type'] == 0)
						{
							price_plan_name = "臨停費率";
							//jdata[idx]['price_plan'];
						}
						else
						{
							price_plan_name = "月租費率";
							target = JSON.parse(jdata[idx]['price_plan']);
							
							Object.keys(target)
							  .sort(
								function(a,b){
									attr_a = a.split('_');
									attr_b = b.split('_');
									if(parseInt(attr_a[0], 10) > parseInt(attr_b[0], 10)) return 1;
									if(parseInt(attr_a[0], 10) < parseInt(attr_b[0], 10)) return -1;
									if(parseInt(attr_a[1], 10) > parseInt(attr_b[1], 10)) return 1;
									if(parseInt(attr_a[1], 10) < parseInt(attr_b[1], 10)) return -1;
									return 0;
								}
							  )
							  .forEach(function(k, i) 
							  {
								//console.log(k, target[k]);
								period_idx = k.split("_")[0];
								mem_idx = k.split("_")[1];
									
									if(k == '0_0')
									{
										price_plan_content = price_plan_content.concat(['*月租押金：', target[k], ' 元']);
									}
									else if(mem_idx == '0')
									{
										idx_period_name = (period_name[period_idx] == undefined) ? '繳期 ' + period_idx : period_name[period_idx];
										price_plan_content = price_plan_content.concat(['<br/> * [ ', idx_period_name, ' : ', target[k], ' 天 ] ']);
									}
									else
									{
										idx_mem_attr = (mem_attr[mem_idx] == undefined) ? '？？ (' + mem_idx + ')': mem_attr[mem_idx];
										price_plan_content = price_plan_content.concat([' > ', idx_mem_attr, "：", target[k], ' 元 ']);	
									}
									
								price_plan_content = price_plan_content.concat(['<br/>']);
							});

						}	

						member_list = member_list.concat(["<td style='color:green;text-align:center;' id='tx_type_", txid, "'>", price_plan_name ,"</td>"]);		
						member_list = member_list.concat(["<td style='color:blue;text-align:left;' id='price_plan_", txid, "'>", price_plan_content.join('') , "</td>"]);							
						member_list = member_list.concat(["<td style='color:green;text-align:left;' id='remarks_", txid, "'>", jdata[idx]['remarks'], "</td>"]);
						member_list = member_list.concat(["<td id='start_time_", txid, "' style='text-align:center;'>", jdata[idx]['start_time'], "</td>"]);	
						member_list = member_list.concat(["<td id='valid_time_", txid, "' style='text-align:center;'>", jdata[idx]['valid_time'], "</td>"]);	
						member_list = member_list.concat(["</tr>"]);
                    }
                	$("#price_plan_list").append(member_list.join('')); 
            	}
        	});  
}


// 同步場站費率
function sync_price_plan()
{
	$.ajax
    ({
        url:APP_URL + "sync_price_plan", 
        dataType:"text",
        type:"post",
        data: {},
		error:function(xhr, ajaxOptions, thrownError)
        {
			alertify_msg(xhr.responseText);
        	console.log("error:"+xhr.responseText+"|"+ajaxOptions+"|"+thrownError);  
        },
        success:function(jdata)
        {                                                                            
			if (jdata == "ok")	
            {                              
            	alertify_msg("同步成功 ! ");
				
				reload_price_plan_query_all();
            }
			else if (jdata == "sync_fail")	
            {                              
            	alertify_msg("同步失敗"); 
			}
            else
            {
              	alertify_msg("同步失敗 !");
            }   
        }
    });
}

</script>