<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1 ,maximum-scale=1.0, user-scalable=no">
    <title>歐特儀停車場</title>
    <meta name="keywords" content="歐特儀,停車場">
    <meta name="description" content="歐特儀,停車場">
    
	<!-- opay -->
    <link href="/libs/opay/css/reset_new.css" rel='stylesheet' type='text/css' />
    <link href="/libs/opay/css/global_new.css" rel='stylesheet' type='text/css' />
    <link href="/libs/opay/css/page_new.css" rel='stylesheet' type='text/css' />
	<script src="/libs/opay/lib/jquery-1.7.1.min.js"></script>
	
	<!-- alertify -->
	<link href="/libs/css/alertify.core.css" rel="stylesheet">
	<link href="/libs/css/alertify.bootstrap.css" rel="stylesheet">
	<script src="/libs/js/alertify.min.js"></script>
	
	<!-- jQuery validate -->
	<script src="/libs/form-validator/jquery.form-validator.min.js"></script>
	
	<!-- md5 -->
	<script src="/libs/js/md5.min.js"></script> 
	
	<!-- other -->
	<script src="/libs/js/js.cookie.js"></script>
	<link href="/libs/css/custom-table.css" rel="stylesheet">
	
</head>

<body class="body-wbg">
    <!-- Start: Content -->
    <header>
        <div class="previous" onclick="back_page(event);"></div>
        <h1>歐特儀停車場</h1>
    </header>
	
    <div class="content-new parking-search-setting">
        <section class="page">
            <div data-items="home_page" class="wrapper">

                <div class="wbt-fill-box">
                    <div class="wbtf-box-line none-underline">
                        <div class="wbt-fill-title">
                            <img src="/libs/opay/images/fee/ic_parking_fee.svg" class="wft-gov-icon">
                            <h3>兌換卷</h3>
                        </div>
                    </div>
                </div>

                <div class="pss-choose-box">
                    <h4 class="spacing-title">兌換快捷鍵</h4>
                    <div class="parkinsys-search-wrap">
                        <ul id="product_bill_list" class="psw-choose-num">
                            <!--li><a href="javascript:void(0)">ABC-1234</a></li>
                            <li><a href="javascript:void(0)">HT-114</a></li>
                            <li><a href="javascript:void(0)">YA-520</a></li>
                            <li><a href="javascript:void(0)">FC-500</a></li>
                            <li><a href="javascript:void(0)">PO-100</a></li>
                            <li><a href="javascript:void(0)">7128-AMY</a></li-->
                        </ul>
                    </div>
                </div>

            </div>
			
			<div id="page-wrapper"><!-- 動態切換顯示 -->
			</div>
			
        </section>
    </div>
    <!-- End: Content -->
	

</body>

</html>

<script> 

<?php /* alertify function */ ?>
function alertify_log($msg)
{
	alertify.set({delay : 2000});
	alertify.log($msg);
}
function alertify_error($msg)
{
	alertify.set({delay : 2000});
	alertify.error($msg);
}
function alertify_success($msg)
{
	alertify.set({delay : 2000});
	alertify.success($msg);
}

// -- 顯示指定項目 (切換) --
function show_item_without_change(tags)
{
	$("[data-items]").hide();
	$("[data-items="+tags+"]").show();
    return false;
}

// -- 顯示指定項目 --
function show_item(tags, type)
{              
	current_page_tags = tags;	// 記錄目前頁面

    switch(tags)
    {      
		// -- 首頁 --			
		case "home_page":
			reload_order_list();
			break;	
			
		// -- 產品展示 --			
		case "item_page":
			load_page(tags);
			load_item_page();
			break;
			
		// -- 結帳 --			
		case "checkout_page":
			load_page(tags);
			load_checkout_page();
			break;	
			
        default: 
        	$("#"+tags+"_list").html("");	// -- 清除原內容 --
        	break;
    }
    
	$("[data-items]").hide();
	$("[data-items="+tags+"]").show();
    return false;
}

// 載入頁面
function load_page(tags)
{
	if ($("[data-items='"+tags+"']").length == 0)	// 第一次loading
    {     
		$.ajax
		({
			url:"<?=APP_URL?>get_html",
        			async:false,    
        			timeout:1500,
            		type:"post", 
            		dataType:"text",
            		data:{"tag_name":tags},
            		success:function(jdata)
            		{
            	    	$("#page-wrapper").append(jdata);  
            	    }
		}); 
	}
}

/////////////////////////////////////
//
// 開始
//
/////////////////////////////////////

// 取得產品資訊
var PRODUCT_RESULT = {};
PRODUCT_RESULT.product_id = '<?= $product_id; ?>';
PRODUCT_RESULT.product_code = '<?= $product_code; ?>';
PRODUCT_RESULT.product_name = '<?= $product_name; ?>';
PRODUCT_RESULT.product_desc = '<?= $product_desc; ?>';
PRODUCT_RESULT.amt = '<?= $amt; ?>';
PRODUCT_RESULT.remarks = '<?= $remarks; ?>';
PRODUCT_RESULT.invoice_no = '<?= $invoice_no; ?>';

// 暫存區
var current_page_tags;				// 目前所在頁面
var current_altob_check_list;		// 目前待結清單
var current_altob_checkout_bill;	// 目前待繳帳單
var AltobCookies = Cookies.noConflict();

// 用戶代號
function get_altob_shop_uuid()
{
	if(AltobCookies.get('ALTOB_SHOP_UUID') !== undefined)
	{
		return AltobCookies.get('ALTOB_SHOP_UUID');
	}
	
	set_cookie('ALTOB_SHOP_UUID', '<?= $ALTOB_SHOP_UUID; ?>');
	return AltobCookies.get('ALTOB_SHOP_UUID');
}

// 設定 cookie
function set_cookie(key, value)
{
	AltobCookies.set(key, value, { expires: 365 });
}

// 載入兌換資訊
function reload_order_list()
{
	var altob_shop_uuid = get_altob_shop_uuid();
	
	// 取得兌換資訊
	$.ajax
	({
		url: "<?=APP_URL?>query_uuid_bill",
		type: "post", 
		dataType: "json",
		data: {"uuid":altob_shop_uuid},
		success:function(jdata)
		{
			var query_list = [];
			
			for(idx in jdata)
			{   
				var invoice_remark = jdata[idx]['invoice_remark'];
				var product_plan = JSON.parse(jdata[idx]['product_plan']);
				var order_no = jdata[idx]['order_no'];
				var item_msg = '';
				
				// 分析產品內容
				if(product_plan.amount > 0)
					item_msg = '領取 ' + invoice_remark + ' 兌換卷';
				else
					item_msg = '兌換 ' + product_plan.memo + ' x 1';	
				
				query_list = query_list.concat(['<li><a href="javascript:void(0)" onclick="get_item(', order_no ,',\'', item_msg, '\');">', item_msg ,'</a></li>']);
			}
					
			$("#product_bill_list").html('').append(query_list.join(''));
		}
	})
}

// 兌換
function get_item(order_no, item_msg)
{
	alertify.set({ 
		buttonFocus: "cancel",
		labels: {
			ok     : "兌換",
			cancel : "取消"
		}
	});
	alertify.confirm(
		item_msg
		, function (e){
		if (e) {
			$.ajax
			({
				url: "<?=APP_URL?>redeem_order",
				dataType:"text",
				type:"post",
				data: {"order_no":order_no},
				success:function(redeem_order_result)
				{
					if(redeem_order_result == 'ok')
					{
						alertify_success('操作完成');
						
						// 重新載入
						reload_order_list();
					}
					else if(redeem_order_result == 'not_found')
					{
						alertify_error('查無訂單..');	
					}
					else
					{
						alertify_error('發生異常..' . redeem_order_result);
					}
				}
			})
		}})
}

// 回上頁
function back_page(event)
{
	if(event !== undefined)
		event.preventDefault();
	
	// 預設回首頁
	show_item('home_page', 'home_page');
}

$(document).ready(function()   
{                 
	<?php /* validate  設定start */ ?>
	$.validate(
		{
			modules : 'security',
		}
	);
	<?php /* validate  設定end */ ?>

	// 若有帶產品编號, 前往展示頁
	if(PRODUCT_RESULT.product_id != '')
	{
		show_item('item_page', 'item_page');
	}
	else
	{
		show_item('home_page', 'home_page');
	}
}); 

</script>