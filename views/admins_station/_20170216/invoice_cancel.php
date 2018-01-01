<!-- ----- 電子發票作廢(限當日)作業 ----- -->  
<div data-items="invoice_cancel" class="row" style="display:none;">
<div class="col-lg-12">
<div class="panel panel-default">
<div class="panel-heading">電子發票作廢作業</div><!-- 資料顯示區灰色小表頭 -->
<div class="panel-body">
<div data-rows class="row">
<div class="col-lg-6">    
<div>
<label>統編&nbsp;&nbsp;</label>
<label class="radio-inline"><input type="radio" name="invoice_cancel_company_no" value="<?=$company_no?>" checked />本場站</label>
<label class="radio-inline"><input type="radio" name="invoice_cancel_company_no" value="<?=$hq_company_no?>" />總公司</label>
</div>
<div class="form-group">
<label>發票號碼</label>
<input id="invoice_cancel_invoice_no" type="text" class="form-control text-uppercase" />
</div>    
<div class="form-group">
<button type="button" class="btn btn-large btn-success pull-left" onclick="invoice_cancel();">作廢</button> 
</div> 
</div><!-- end of col-lg-6 (nested) -->
</div><!-- end of row (nested) -->
</div><!-- end of panel-body -->
</div><!-- end of panel -->
</div><!-- end of col-lg-12 -->
</div><!-- data-items -->
<!-- ----- 電子發票作廢作業(結束) ----- --> 
<script>  
// 列印電子發票清帳
function invoice_cancel()
{                          
	var invoice_no = $("#invoice_cancel_invoice_no").val().toUpperCase();
	if (invoice_no == "")
    {
      	alert("發票號碼欄位必填 !");
        return false;
    }                
    
    if (!confirm("確認作廢發票:"+invoice_no+" ?"))	return false;
    
    $.ajax
    ({     
       	url:"http://localhost:60134/",  
        async:false,    
        timeout:1500,
        type:"post",
        dataType:"json",  
        data:
        {	
          	"cmd":"CancelInvoice",                   
            "company_no":$("input:radio:checked[name='invoice_cancel_company_no']").val(),
          	"vINVOICE_NO":invoice_no
        },
        error:function(xhr, ajaxOptions, thrownError)
        {
        	console.log("error:"+xhr.responseText+"|"+ajaxOptions+"|"+thrownError);  
        },
        success:function(jdata)
        {              
        	if (jdata["Result"] == "000")
            {
            	alert("作廢完成, 發票號碼: "+invoice_no);
            }
            else
            {
            	alert("作廢失敗:["+jdata["Result"]+"]"+jdata["Message"]);
            } 
            
            $("#invoice_cancel_invoice_no").val("");
    	}                                                                          
    });
}
</script>