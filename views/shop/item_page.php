<div data-items="item_page" class="content-new parking-bill-total">
        <section class="page">
            <div class="wrapper">

                <div class="pbt-plate-number">
                    <h3><span id='checkout_desc'></span></h3>
                </div>

				<form id="payment_data" role="form" method="post" target="_self" action="<?=APP_URL?>transfer_money/">
				
                <div class="pb-detail-list">
                    <div class="pbd-list-box pbd-lb-separate">
                        <dl>
                            <dt>名稱</dt>
                            <dd><span id='checkout_name'></span></dd>
                        </dl>
						<dl>
                            <dt>說明</dt>
                            <dd><span id='checkout_remarks'></span></dd>
                        </dl>
                        <dl>
                            <dt>價格</dt>
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
						<img id='checkout_image' src="" alt="">
                    </div>
					
					<div class="form-group">
						<input id="product_id" type="hidden" name="product_id" value="0" />
						<input id="product_code" type="hidden" name="product_code" value="" />
						<input id="product_uuid" type="hidden" name="uuid" value="" />
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
function load_item_page()
{
	$("#checkout_name").text(PRODUCT_RESULT.product_name);
	$("#checkout_desc").text(PRODUCT_RESULT.product_desc);
	$("#checkout_remarks").text(PRODUCT_RESULT.remarks);
	$("#checkout_amt").text(PRODUCT_RESULT.amt);
	$("#checkout_image").attr("src", "<?=SERVER_URL?>i3/pics/coffee.jpg");
	$("#product_id").val(PRODUCT_RESULT.product_id);
	$("#product_code").val(PRODUCT_RESULT.product_code);
	$("#product_uuid").val(get_altob_shop_uuid());
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
	
	payment_data.submit();
}

</script>

<!-- 這段要放後面才能運作 -->
<script src="/libs/opay/lib/actions.js"></script>