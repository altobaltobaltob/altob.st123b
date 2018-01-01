			<!-- ----- 會員加入填寫資料 ----- -->  
            <div data-items="member_add" class="row" style="display:none;"><!-- 會員加入填寫資料 -->
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div id="member_data_type" class="panel-heading">新增會員資料</div><!-- 資料顯示區灰色小表頭 -->
                        <div class="panel-body">
                            <div data-rows class="row">
                                <div class="col-lg-6">
                                    <!--form id="member_add" role="form" method="post" data-src="action::APP_URL::member_add"-->  
                                    <form id="member_add" role="form" method="post" data-src="/admins_station.html/member_add">  
                                        <div class="form-group">
                                            <label>*場站</label>
                                            <select class="form-control" id="station_no_modify" name="station_no">
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>*車牌號碼</label>
                                            <input id="ma_lpr" name="lpr" class="form-control" placeholder="限英數字碼" style="text-transform:uppercase" />
                                            <input id="ma_old_lpr" name="old_lpr" type="hidden" />
                                        </div> 
                                        <div class="form-group">
                                            <label>eTag</label>
                                            <input id="ma_etag" name="etag" class="form-control" placeholder="限英數字碼" style="text-transform:uppercase">
                                        </div> 
                                        <div class="form-group">
                                            <label>*不足月開始日期（開始日）</label>
                                            <input id="ma_demonth_start_date" type="datetime" name="demonth_start_date" class="form-control" onchange="calculate_rents_amt();"/>
                                        </div> 
										<div class="form-group">
                                            <label>*不足月結束日期</label>
                                            <input id="ma_demonth_end_date" type="datetime" name="demonth_end_date" class="form-control" />
                                        </div>   	
										<div class="form-group">
                                            <label>*足月開始日期</label>
                                            <input id="ma_start_date" type="datetime" name="start_date" class="form-control" />
                                        </div>   
                                        <div class="form-group">
                                            <label>*足月結束日期（到期日）</label>
                                            <input id="ma_end_date" type="datetime" name="end_date" class="form-control" />
                                        </div> 	
										<div class="form-group">
											<label class="select-inline">
												<button type="button" class="btn btn-default btn-xl btn-primary pull-left" onclick="member_park_time();">*進出場時段</button>&nbsp;                             
											</label>
										</div> 											
										<div class="form-group">
											<label class="select-inline">*首期繳期
												<select class="form-control input-sm" id="fee_period1" name="fee_period1" onClick="calculate_rents_amt();"></select>
											</label>
                                            <label class="select-inline">*首期租金 (依使用天數拆分)
												<input id="ma_amt1" name="amt1" class="form-control" value="0" onblur="re_amt();">
											</label>
											<label class="select-inline" id="amt1_max_view">
												&nbsp;說明：&nbsp;
												&nbsp;每日租金（&nbsp;<span id="amt1_max">0</span>&nbsp;元
												&nbsp;/ &nbsp;<span id="amt1_days_total">0</span>&nbsp;天）
												&nbsp;* 實際天數&nbsp;<span id="amt1_days">0</span>&nbsp;天
											</label>		
										</div> 
										<div class="form-group">
											<label class="select-inline">*例行繳期
												<select class="form-control input-sm" id="fee_period" name="fee_period" onClick="calculate_rents_amt();"></select>
											</label>                      
                                            <label class="select-inline">*例行租金 (依使用月數拆分)
												<input id="ma_amt" name="amt" class="form-control" value="0" onblur="re_amt();">
											</label>
											<!-- 第一版, 按日拆
												label class="select-inline" id="amt2_max_view">
												&nbsp;*說明：&nbsp;
												&nbsp;每日租金（&nbsp;<span id="amt2_max">0</span>&nbsp;元
												&nbsp;/ &nbsp;<span id="amt2_days_total">0</span>&nbsp;天）
												&nbsp;* 實際天數&nbsp;<span id="amt2_days">0</span>&nbsp;天
											</label-->
											<label class="select-inline" id="amt2_max_view">
												&nbsp;說明：&nbsp;
												&nbsp;每月租金（&nbsp;<span id="amt2_max">0</span>&nbsp;元
												&nbsp;/ &nbsp;<span id="amt2_months_total">0</span>&nbsp;個月）
												&nbsp;* 實際月數&nbsp;<span id="amt2_months">0</span>&nbsp;個月
											</label>
                                        </div>  
																			
                                        <div class="form-group">
											<label class="select-inline">*會員身份
												<select class="form-control input-sm" id="member_attr" name="member_attr" onClick="calculate_rents_amt();"></select>
											</label>
                                            <label class="select-inline">*押金（不列入發票金額）
												<input id="ma_deposit" name="deposit" class="form-control" value="0" onblur="re_amt();">
											</label>	
											<label class="select-inline" id="amt_accrued_view">
												&nbsp;&nbsp;應計金額 (原價)：<span id="amt_accrued">0</span>
											</label>											
                                        </div>
										<div class="form-group">
											<label class="select-inline" style="color:blue;font-size:18px;" id="amt_tot_view">
												租金：<span id="amt_tot">0</span>
											</label>
										</div>                
                                        <div class="form-group">
                                            <label>合約號碼</label>
                                            <input id="ma_contract_no" name="contract_no" class="form-control">
                                        </div> 
                                        <div class="form-group">
                                            <label>*姓名/公司名稱</label>
                                            <input id="ma_member_name" name="member_name" class="form-control" style="font-size:48px;height:56px;">
                                        </div>
                                        <div class="form-group">
                                            <label>*手機</label>
                                            <input id="ma_mobile_no" name='mobile_no' class="form-control">
                                        </div> 
                                        <div class="form-group">
                                            <label>身份證號</label>
                                            <input id="ma_member_id"  name="member_id" class="form-control">
                                        </div>   
                                        <div class="form-group">
                                            <label>會員統一編號</label>
                                            <input id="ma_member_company_no"  name="member_company_no" class="form-control">
                                        </div>
                                        <div class="form-group">
                                            <label>電話(宅)</label>
                                            <input id="ma_tel_h" name='tel_h' class="form-control">
                                        </div> 
                                        <div class="form-group">
                                            <label>電話(公)</label>
                                            <input  id="ma_tel_o" name='tel_o' class="form-control">
                                        </div> 
                                        <div class="form-group">
                                            <label>地址</label>
                                            <input id="ma_addr" name='addr' class="form-control">
                                        </div>
                                        <div class="form-group">
                                        <button type="submit" class="btn btn-large btn-success pull-left">存檔</button>
										&nbsp;&nbsp;
                                        <!--button type="reset" class="btn btn-large btn-cancel">重填</button-->
										
                                        <input id="ma_member_no" type="hidden" name="member_no" value="0" />
                                        <input id="ma_company_no" type="hidden" name="company_no" />
                                        <input id="ma_park_time" type="hidden" name="park_time" />  
                                        <input id="ma_amt_tot" type="hidden" name="amt_tot" />  
                                        <input id="ma_amt_accrued" type="hidden" name="amt_accrued" />  
										
										<input id="ma_demonth_start_date_done" type="hidden" name="demonth_start_date_done"/>
										<input id="ma_demonth_end_date_done" type="hidden" name="demonth_end_date_done"/>
										<input id="ma_start_date_done" type="hidden" name="start_date_done"/>
										<input id="ma_end_date_done" type="hidden" name="end_date_done"/>
										<!--input id="ma_fee_period1_done" type="hidden" name="fee_period1_done"/>
										<input id="ma_fee_period_done" type="hidden" name="fee_period_done"/>
										<input id="ma_member_attr_done" type="hidden" name="member_attr_done"/-->
										
                                        </div>
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
            <!-- ----- 會員加入填寫資料(結束) ----- --> 
			
<script>  

	// 設定場站資訊 
    for(station_no in st)
    {
    	$(new Option(st[station_no],station_no)).appendTo('#station_no_modify');		// 會員修改場站編號  
    }    
	
	// 會員身份類別 
    for(idx in mem_attr)
    {
    	$(new Option(mem_attr[idx],idx)).appendTo('#member_attr');  
    }
         
    // 繳期表(首期) 
    for(idx in period_name)
    {
    	$(new Option(period_name[idx],idx)).appendTo('#fee_period1');  
    }   
         
    // 繳期表(例行) 
    for(idx in period_name)
    {
    	$(new Option(period_name[idx],idx)).appendTo('#fee_period');  
    }

	// 設定時段表
    str = "";
    for(pt_id in pt)
    {                  
    	str += "<tr>";
        str += "<td style='text-align:center;'><input type='checkbox' id='pt_id_"+pt_id+"' class='pt_id_checkbox' value='"+pt_id+"' /></td>";
        str += "<td style='text-align:center;'>"+pt_id+"</td>";
        str += "<td style='text-align:left;'>"+pt[pt_id]['remarks']+"</td>";
    	str += "</tr>";	  	
    }      
    $("#pt_list").html(str);

	$("#ma_demonth_start_date").datetimepicker({language:"zh-TW",autoclose:true,minView:2,format:"yyyy-mm-dd"});
    $("#ma_demonth_end_date").datetimepicker({language:"zh-TW",autoclose:true,minView:2,format:"yyyy-mm-dd"});
    $("#ma_start_date").datetimepicker({language:"zh-TW",autoclose:true,minView:2,format:"yyyy-mm-dd"});
    $("#ma_end_date").datetimepicker({language:"zh-TW",autoclose:true,minView:2,format:"yyyy-mm-dd"});
	$("#stop_rents_end_date").datetimepicker({language:"zh-TW",autoclose:true,minView:2,format:"yyyy-mm-dd"});

	// 會員新增修改
	$("#member_add").submit(function(event)
	{                   
    	event.preventDefault();
        
        // 停車時段
        $("#ma_park_time").val($('input:checkbox:checked.pt_id_checkbox').map(function(){ return this.value; }).get().join(","));
		
		if ($("#ma_member_no").val() == "0")
        {
        	$("#ma_old_lpr").val($("#ma_lpr").val());
			
			// 新增：檢查必填欄位
			if ($("#ma_lpr").val() == "" ||
				$("#ma_start_date").val() == "" ||
				$("#ma_end_date").val() == "" ||
				$("#ma_member_name").val() == "" ||
				$("#ma_mobile_no").val() == "" ||
				$("#ma_park_time").val() == ""
				)
			{
				alertify_msg("必填欄位不可空白");
				return false;
			}
        }
		else
		{
			// 修改：檢查必填欄位
			if ($("#ma_lpr").val() == "" ||
				//$("#ma_start_date").val() == "" ||
				$("#ma_end_date").val() == "" ||
				$("#ma_member_name").val() == "" ||
				$("#ma_mobile_no").val() == "" ||
				$("#ma_park_time").val() == ""
				)
			{
				alertify_msg("必填欄位不可空白");
				return false;
			}
		}
        
        $("#ma_amt_tot").val($("#amt_tot").text());    
        $("#ma_amt_accrued").val($("#amt_accrued").text()); 
        if($("#ma_member_company_no").val() == "")	$("#ma_member_company_no").val(0); 
		
		// 禁止直接輸入，補值
		$("#ma_demonth_end_date_done").val($("#ma_demonth_end_date").val()); 
		$("#ma_start_date_done").val($("#ma_start_date").val());
		$("#ma_end_date_done").val($("#ma_end_date").val());
           
        $.ajax
        ({
        	url: APP_URL+"member_add",
            type: "post", 
            dataType:"json",
            data: $(this).serialize(),
			error:function(xhr, ajaxOptions, thrownError)
			{
				alertify_msg(xhr.responseText);
				console.log("error:"+xhr.responseText+"|"+ajaxOptions+"|"+thrownError);  
			},
            success: function(jdata)
            {       
				if(jdata == 'update_error')
				{
					alertify_msg("更新失敗, 請稍候再試");
				}
				else if(jdata == 'trans_error')
				{
					alertify_msg("操作失敗, 請稍候再試");
				}
				else if (jdata["member_no"] != "0")
                {                                                                
					if(jdata["action_code"] == "A")
					{
						// 新增會員資料, 印發票
						xvars["rents"] = Array();             
						xvars["rents"]["tx_no"] = jdata["tx_no"];
						xvars["rents"]["station_no"] = station_no;
						xvars["rents"]["member_no"] = jdata["member_no"];
						xvars["rents"]["member_company_no"] = $("#ma_member_company_no").val();
						xvars["rents"]["company_no"] = company_no;
						xvars["rents"]["fee_period"] = $("#fee_period").val();
						xvars["rents"]["amt"] = parseInt($("#ma_amt").val());
						xvars["rents"]["amt1"] = parseInt($("#ma_amt1").val());
						xvars["rents"]["invoice_amt"] = jdata["invoice_amt"];
						xvars["rents"]["remain_amt"] = jdata["remain_amt"];
						xvars["rents"]["period_3_amt"] = jdata["period_3_amt"];
						
						$("#first_rents_name").text($("#ma_member_name").val()+" ("+ $("#ma_lpr").val() +")");
						$("#first_rents_station_name").text(st[xvars["rents"]["station_no"]]);
						$("#first_rents_start_date").text($("#ma_demonth_start_date").val());	// 上期開始日
						$("#first_rents_end_date").text($("#ma_end_date").val());				// 本期結束日
						$("#first_rents_deposit").text($("#ma_deposit").val());
						$("#first_rents_period").text(period_name[xvars["rents"]["fee_period"]]);
						$("#first_rents_amt").text(xvars["rents"]["amt"]);
						$("#first_rents_amt1").text(xvars["rents"]["amt1"]);
						
						$("#first_rents_invoice_amt").text(xvars["rents"]["invoice_amt"]);
						$("#first_rents_remain_desc").html(get_invoice_desc(xvars["rents"]["amt"], xvars["rents"]["amt1"], xvars["rents"]["invoice_amt"], xvars["rents"]["remain_amt"], xvars["rents"]["period_3_amt"]));	// 發票說明
						
						$("#first_rents_company_no").val(xvars["rents"]["company_no"]); // 賣方統編
						
						if(xvars["rents"]["member_company_no"] == 0 || xvars["rents"]["member_company_no"] == '')
						{
							$("#first_rents_member_company_no").val("");
						}
						else
						{
							$("#first_rents_member_company_no").val(xvars["rents"]["member_company_no"]); // 買方統編
						}
						
						$("#first_rents_dialog").modal({backdrop:false,keyboard:false});
						
					}
					else if(jdata["action_code"] == "U")
					{
						// 更新會員資料
						alertify_msg("月租資料存檔完成 !");
					}
					else
					{
						// 未知
						alertify_msg("未知的操作..");
					}
					
					show_item('member_query', 'member_query');
                } 
                else
                {
                 	alertify_msg(jdata["msg"]);
                }
            }
        }); 
    });
	
	
// 計算租金
function calculate_rents_amt()
{     
	// 計算月租金額 
	$.ajax
    ({
        url:APP_URL+"calculate_rents_amt", 
        dataType:"json",
        type:"post",
        data:
        {             
        	"cmd":"calculate_rents_amt",
        	"station_no":station_no, 
            "demonth_start_date":$("#ma_demonth_start_date").val(),
        	"member_attr":$("#member_attr").val(),  
        	"period_1":$("#fee_period1").val(),		// 首期繳期  
        	"period_2":$("#fee_period").val()		// 例行繳期
        },
        success:function(jdata)
        {   
			$("#ma_demonth_start_date").val(jdata["demonth_start_date"]); 
			$("#ma_demonth_end_date").val(jdata["demonth_end_date"]).prop("disabled",true); 
			$("#ma_start_date").val(jdata["start_date"]).prop("disabled",true); 
			$("#ma_end_date").val(jdata["end_date"]).prop("disabled",true);
			
			$("#amt1_max").text(jdata["demonth_amt"]);
			$("#amt1_days").text(jdata["demonth_days"]);
			$("#amt1_days_total").text(jdata["demonth_days_total"]);
			$("#amt1_max_view").show();
			
			// 第一版: 依天數拆分
			/*
			$("#amt2_max").text(jdata["amonth_amt"]);
			$("#amt2_days").text(jdata["amonth_days"]);
			$("#amt2_days_total").text(jdata["amonth_days_total"]);
			$("#amt2_max_view").show();
			*/
			
			// 第二版: 依月數拆分 2017-02-13 updated
			$("#amt2_max").text(jdata["amonth_amt"]);
			$("#amt2_months").text(jdata["amonth_months"]);
			$("#amt2_months_total").text(jdata["amonth_months_total"]);
			$("#amt2_max_view").show();
			
        	$("#ma_amt1").val(jdata["rents_amt1"]);   
        	$("#ma_amt").val(jdata["rents_amt2"]);   
        	$("#ma_deposit").val(jdata["rents_deposit"]);   
        	$("#amt_accrued").text(parseInt(jdata["rents_amt1"])+parseInt(jdata["rents_amt2"])+parseInt(jdata["rents_deposit"])); 
        	$("#ma_amt_accrued").val($("#amt_accrued").text()); 
            re_amt(true);  
    	}                                                                          
    }); 
}   

// 重新計算實際租金
function re_amt(show_sccured_view=false)
{
	if ($("#ma_amt1").val() == "")	$("#ma_amt1").val(0);
	if ($("#ma_amt").val() == "")	$("#ma_amt").val(0);
	if ($("#ma_deposit").val() == "")	$("#ma_deposit").val(0);
    
    $("#amt_tot").text(parseInt($("#ma_amt1").val())+parseInt($("#ma_amt").val())+parseInt($("#ma_deposit").val()));
	//$("#amt_tot").text(parseInt($("#ma_amt1").val())+parseInt($("#ma_amt").val())); // 無押金
    $("#ma_amt_tot").val($("#amt_tot").text());
	
	$("#amt_tot_view").show();
	
	if(show_sccured_view)
	{
		$("#amt_accrued_view").show();	
	}
}

	
</script>