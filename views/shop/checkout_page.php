<div data-items="checkout_page" class="content-new parking-bill-total">
        <section class="page">
            <div class="wrapper">

                <div class="pbt-plate-number">
                    <h3><span id='checkout_lpr'></span></h3>
                </div>

				<form id="payment_data" role="form" method="post" target="_self" action="<?=APP_URL?>transfer_money/">
				
                <div class="pb-detail-list">
                    <div class="pbd-list-box pbd-lb-separate">
                        <dl>
                            <dt>停車位置</dt>
                            <dd><span id='checkout_station_name'></span></dd>
                        </dl>
                        <dl>
                            <dt>繳費單號</dt>
                            <dd><span id='checkout_order_no'></span></dd>
                        </dl>
                        <dl>
                            <dt>入場時間</dt>
                            <dd><span id='checkout_in_time'></span></dd>
                        </dl>
                        <dl>
                            <dt>計價時間</dt>
                            <dd><span id='checkout_balance_time'></span></dd>
                        </dl>
                        <dl>
                            <dt>預計可離場時間</dt>
                            <dd><span id='checkout_out_before_time'>交易成功 15 分鐘內</span></dd>
                        </dl>
                        <dl>
                            <dt>停車費</dt>
                            <dd class="red">NT$ <span id='checkout_amt'></span></dd>
                        </dl>
                    </div>

                    <div class="pbd-list-box pbd-invoice">
						<dl>
                            <dt>電子信箱</dt>
                            <dd>
								<input type="text" id="email" name="email" class="form-control" placeholder="發票將寄信通知"
												data-validation="email"
												data-validation-optional="true"
												data-validation-error-msg="請輸入正確信箱<br/>例如：altob@gmail.com"
												data-validation-error-msg-container="#checkout_error_msg"
												/>
							</dd>
                        </dl>
						
						<dl>
                            <dt>手機號碼</dt>
                            <dd>
								<input type="text" id="mobile" name="mobile" class="form-control" placeholder="發票將寄簡訊通知"
												data-validation="custom"
												data-validation-optional="true"
												data-validation-regexp="^(?=.{10}$)09([0-9]+)$"
												data-validation-error-msg="請輸入正確手機號碼<br/>例如：0912345678"
												data-validation-error-msg-container="#checkout_error_msg"
												/>
							</dd>
                        </dl>
					
                        <dl>
                            <dt>開立發票方式</dt>
                            <dd>
                                <div class="pbd-select">
                                    <select>
                                        <option class="invoice_way1">二聯式發票</option>
                                        <option class="invoice_way2">手機條碼載具</option>
                                        <option class="invoice_way3">輸入統一編號</option>
                                        <!--option class="invoice_way4">捐贈發票</option-->
                                    </select>
                                </div>
                            </dd>
                        </dl>

                        <div class="inv_way iw_1"></div>

                        <div class="inv_way iw_2">
                            <dl>
                                <dt></dt>
                                <dd>
									<input type="text" id="invoice_receiver" name="invoice_receiver" class="form-control" placeholder="請輸入手機條碼"
												data-validation="custom"
												data-validation-regexp="^$|^(?=.{7}$)([A-Za-z0-9]+)$|^(?=.{8}$)\u002F([A-Za-z0-9]+)$"
												data-validation-error-msg="請輸入正確載具<br/>格式： / + 7碼 <br/>(共8碼)"
												data-validation-error-msg-container="#checkout_error_msg"
												/>
                                </dd>
                            </dl>
                        </div>

                        <div class="inv_way iw_3">
                            <dl>
                                <dt></dt>
                                <dd>
									<input type="text" id="company_no" name="company_no" class="form-control" placeholder="請輸入統一編號"
												data-validation="custom"
												data-validation-optional="true"
												data-validation-regexp="^(?=.{8}$)([0-9]+)$"
												data-validation-error-msg="請輸入正確統編<br/>例如：80682490"
												data-validation-error-msg-container="#checkout_error_msg"
												/>
                                </dd>
                            </dl>
                        </div>

                        <div class="inv_way iw_4">
                            <dl>
                                <dt></dt>
                                <dd>
                                    25885-伊甸基金會
                                </dd>
                            </dl>
                        </div>
						
                    </div>

					<dl>
						<dt></dt>
						<dd class="red"><span id='checkout_error_msg'></span></dd>
					</dl>
						
                    <div class="pbd-img-box">
                        <!--img id='checkout_image' src="http://fakeimg.pl/768x461/ddd" alt=""-->
						<img id='checkout_image' src="" alt="">
                    </div>
					
					<div class="form-group">
						<input id="order_no" type="hidden" name="order_no" value="0" />
					</div>
					
                </div>
				
				</form>
				
                <div class="fixed-btn-box fbb-1">
                    <a class="btn blue-btn" onclick="transfer_money(event);">前往繳費</a>
                </div>

            </div>
			
	</section>
</div>


			


<script> 

// 載入
function load_checkout_page()
{
	$("#checkout_lpr").text('').text(current_altob_checkout_bill['lpr']);
	$("#checkout_station_name").text('').text(current_altob_checkout_bill['station_name']);
	$("#checkout_order_no").text('').text(current_altob_checkout_bill['order_no']);
	$("#checkout_in_time").text('').text(current_altob_checkout_bill['in_time']);
	$("#checkout_balance_time").text('').text(current_altob_checkout_bill['balance_time']);
	//$("#checkout_out_before_time").text('').text(current_altob_checkout_bill['balance_time']);
	$("#checkout_amt").text('').text(current_altob_checkout_bill['amt']);
	$("#checkout_image").attr('src', '').attr('src', current_altob_checkout_bill['image_url']);
	
	// 設定訂單編號
	$("#order_no").val("0").val(current_altob_checkout_bill['order_no']);
	
	// 明細
	/*
	$("#price_data_tbody").html("");	
	
		// A. 依r_no 分群, 暫存到 tmp_r_no_array
		var tmp_r_no_array = [];
		for(lv1 in current_altob_checkout_bill['price_detail'])
            {        
			if (lv1 == 0) { continue; }
			var today = current_altob_checkout_bill['price_detail'][lv1];
			for(lv2 in today)
			{
				if(lv2.match(/\u003A/)){ // 取出有時間的部份
				var detail = today[lv2];
				if(!(detail.r_no in tmp_r_no_array)){
					tmp_r_no_array[detail.r_no] = [];
				}
				tmp_r_no_array[detail.r_no].push([detail.r_no, '_', lv1, '_', lv2].join(''));
				}
			}
		}
		//console.log('tmp_r_no_array: ' + tmp_r_no_array);
		// B. 將 tmp_r_no_array 解析, 產生顯示用的 price_result_array
		var price_result_array = [];
		var last_r_no_keys_array = [];
		var check_p = 0;
		for(r_no in tmp_r_no_array)
		{
			var r_no_array = tmp_r_no_array[r_no].sort(); // 依r_no 排序
			//console.log(r_no + ' length: ' + r_no_array.length);
			
			for(key in r_no_array)
			{
				var keys = r_no_array[key].split('_');
				var r_no = keys[0];
				var lv1 = keys[1];
				var lv2 = keys[2];
				var time_str = [lv1, ' ', lv2].join('');
				var detail = current_altob_checkout_bill['price_detail'][lv1][lv2];
				var detail_p0_price = current_altob_checkout_bill['price_detail'][lv1].p0;
				var detail_limit0 = current_altob_checkout_bill['price_detail'][lv1].limit0;
				var detail_free0_min = current_altob_checkout_bill['price_detail'][lv1].free0_min;
						
				if(detail.p > 0){
				check_p += detail.p;
				var before_keys = last_r_no_keys_array.pop(); //r_no_array[key - 1].split('_');
				
				var before_r_no = before_keys[0];
				var before_lv1 = before_keys[1];
				var before_lv2 = before_keys[2];
				var before_time_str = [before_lv1, ' ', before_lv2].join('');
				var before_detail = current_altob_checkout_bill['price_detail'][before_lv1][before_lv2];
				
				// create result
				var data_p_desc = '';
				var data_p_time = '';
				var data_p_time_desc = ['*時段 ', before_time_str, '<br/>至 ', time_str].join('');
				var data_p_price_desc = [detail.p, ' 元'].join('');
				
				// p_desc
				if(detail.status == 1){
					data_p_desc = ['費率：每日最高收費上限 ', detail_limit0, ' 元，已達當日上限'].join(''); // '每日最高收費上限 150元';
				}else{
					data_p_desc = [' 每小時 ', 2 * detail_p0_price, ' 元，前 ', detail_free0_min, ' 分鐘免費。'].join(''); // '費率：每小時 20元';
				}
				
				// p_time
				var detail_part = [];
				if('h' in detail && detail.h > 0){
					detail_part.push(detail.h, ' 小時 ');
				}
				if('i' in detail && detail.i > 0){
					detail_part.push(detail.i, ' 分鐘');
				}
				//if(detail.p < before_detail_p2_price){detail_part.push(' (', r_no, ') ');}
				data_p_time = detail_part.join('');
				
				if(price_result_array.length > 0){
					if(r_no == price_result_array[price_result_array.length - 1].r_no){
						// 與上一筆結算為同一價錢週期時, 更新上一筆結算
						var last_result = price_result_array[price_result_array.length - 1];
						last_result.p_desc = '每日最高收費上限 150元';
						last_result.p_time = [last_result.p_time, '接續<br/><br/>', data_p_time].join('');;
						last_result.p_time_desc = [last_result.p_time_desc, ' 接續<br/><br/>', data_p_time_desc].join('');
						last_result.p_price_desc = [last_result.p_price_desc, ' + ', data_p_price_desc].join('');
						// push last
						last_r_no_keys_array.push(keys);
						continue;
					}
				}
				
				// 與上一筆結算不同價錢週期, 新增一筆結算
				var data = [];
				data.r_no = r_no;
				data.p_desc = data_p_desc;
				data.p_time = data_p_time;
				data.p_time_desc = data_p_time_desc;
				data.p_price_desc = data_p_price_desc;
				price_result_array.push(data);
				// push last
				last_r_no_keys_array.push(keys);
				}else{
				// push last
				last_r_no_keys_array.push(keys);
				}
			}
		}
		
		// C. 根據 price_result_array, 產生頁面顯示
		var seq = 0;
		for(key in price_result_array)
		{	
			var result = price_result_array[key];
			var meta_0_str = ++seq;
			$("#price_data_list>[data-tag=p_no]").text(meta_0_str);
			$("#price_data_list>[data-tag=p_meta]").html(result.p_time_desc);
			$("#price_data_list>[data-tag=p_result]").html(result.p_time);
			$("<tr data-day='day'>"+$("#price_data_list").html()+"</tr>").appendTo("#price_data_tbody"); 
			$("#price_data_list>[data-tag=p_no]").text("");
			$("#price_data_list>[data-tag=p_meta]").html(result.p_desc);
			$("#price_data_list>[data-tag=p_result]").html(result.p_price_desc);
			$("<tr data-day='day' style='color: red;'>"+$("#price_data_list").html()+"</tr>").appendTo("#price_data_tbody"); 
		}
		var bill_time_part = ['共 '];
		if('bill_days' in jdata && jdata.bill_days > 0){
			bill_time_part.push(jdata.bill_days, ' 天 : ');
		}
		if('bill_hours' in jdata && jdata.bill_hours > 0){
			bill_time_part.push(jdata.bill_hours, ' 小時 : ');
		}
		if('bill_mins' in jdata && jdata.bill_mins > 0){
			bill_time_part.push(jdata.bill_mins, ' 分鐘');
		}
		$("#show_amt_detail_time").text(bill_time_part.join(''));
		$("#show_amt_detail_price").text([jdata.amt, ' 元'].join(''));
	*/
}

// 開啟轉帳畫面
function transfer_money(event)
{
	event.preventDefault();
	
	if(! $("#payment_data").isValid()) return false;
	
	if($("#email").val() == '' && $("#mobile").val() == '')
	{
		alertify_error("請至少提供一項發票通知方式<br/>1. 電子信箱 <br/>2. 或 手機號碼<br/><br/>謝謝!!");
		return false;
	}
	
	/*
	var order_no = $("#checkout_order_no").text() == '' ? '0' : $("#checkout_order_no").text();
    var invoice_receiver = $("#invoice_receiver").val() == '' ? '0' : $("#invoice_receiver").val();
	var company_no = $("#company_no").val() == '' ? '0' : $("#company_no").val();
	var email = $("#email").val() == '' ? '0' : $("#email").val();
	var mobile = $("#mobile").val() == '' ? '0' : $("#mobile").val();
	*/
	
	payment_data.submit();
}

</script>

<!-- 這段要放後面才能運作 -->
<script src="/libs/opay/lib/actions.js"></script>