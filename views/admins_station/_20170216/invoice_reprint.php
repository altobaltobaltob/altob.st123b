<!-- ----- 電子發票補印(限當日)作業 ----- -->  
<div data-items="invoice_reprint" class="row" style="display:none;">
<div class="col-lg-12">
<div class="panel panel-default">
<div class="panel-heading">電子發票補印作業</div><!-- 資料顯示區灰色小表頭 -->
<div class="panel-body">
<div data-rows class="row">
<div class="col-lg-6">    
<div>
<label>統編&nbsp;&nbsp;</label>
<label class="radio-inline"><input type="radio" name="invoice_reprint_company_no" value="<?=$company_no?>" checked />本場站</label>
<label class="radio-inline"><input type="radio" name="invoice_reprint_company_no" value="<?=$hq_company_no?>" />總公司</label>
</div>
<div class="form-group">
<label>發票號碼</label>
<input id="invoice_reprint_invoice_no" type="text" class="form-control text-uppercase" />
</div>    
<div class="form-group">
<label>下方訊息</label>
<input id="invoice_reprint_message" type="text" class="form-control" />
</div>          
<div class="checkbox">
<label><input id="invoice_reprint_force" type="checkbox" />印出[補印]字樣</label>
</div>
<div class="form-group">
<button type="button" class="btn btn-large btn-success pull-left" onclick="invoice_reprint();">補印</button> 
</div> 
</div><!-- end of col-lg-6 (nested) -->
</div><!-- end of row (nested) -->
</div><!-- end of panel-body -->
</div><!-- end of panel -->
</div><!-- end of col-lg-12 -->
</div><!-- data-items -->
<!-- ----- 電子發票補印作業(結束) ----- --> 
<script>  
// 補印電子發票
function invoice_reprint()
{               
	var invoice_reprint_invoice_no = $("#invoice_reprint_invoice_no").val().toUpperCase(); 
	if (invoice_reprint_invoice_no == "")
    {
      	alert("發票號碼欄位必填 !");
        return false;
    }                
      
    if (!confirm("確認補印電子發票:"+invoice_reprint_invoice_no+" ?"))	return false;
    
    $.ajax
    ({     
       	url:"http://localhost:60134/",  
        async:false,    
        timeout:1500,
        type:"post",
        dataType:"json",  
        data:
        {	
          	"cmd":"rePrintInvoice",                   
            "company_no":$("input:radio:checked[name='invoice_reprint_company_no']").val(),
          	"vINVOICE_NO":invoice_reprint_invoice_no,
          	"vTAIL_MESSAGE":$("#invoice_reprint_message").val(),
          	"vFORCE_FLG":$("#invoice_reprint_force").prop("checked") ? "N": "Y" 
        },
        error:function(xhr, ajaxOptions, thrownError)
        {
        	console.log("error:"+xhr.responseText+"|"+ajaxOptions+"|"+thrownError);  
        },
        success:function(jdata)
        {              
        	if (jdata["Result"] == "000")
            {
            	alert("補印完成, 日期: "+jdata["W_TODAY"]);
            }
            else
            {
            	alert("補印失敗:["+jdata["Result"]+"]"+jdata["W_TODAY"]+jdata["Message"]);
            } 
            
            $("#invoice_reprint_invoice_no").val("");
            $("#invoice_reprint_message").val(""); 
            $("#invoice_reprint_force").prop("checked", false);
    	}                                                                          
    });
}
</script>