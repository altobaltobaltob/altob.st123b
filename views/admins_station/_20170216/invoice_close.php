<!-- ----- 電子發票清帳作業 ----- -->  
<div data-items="invoice_close" class="row" style="display:none;">
<div class="col-lg-12">
<div class="panel panel-default">
<div class="panel-heading">電子發票清帳作業</div><!-- 資料顯示區灰色小表頭 -->
<div class="panel-body">
<div data-rows class="row">
<div class="col-lg-6">    
<div>
<label>統編&nbsp;&nbsp;</label>
<label class="radio-inline"><input type="radio" name="invoice_close_company_no" value="<?=$company_no?>" checked />本場站</label>
<label class="radio-inline"><input type="radio" name="invoice_close_company_no" value="<?=$hq_company_no?>" />總公司</label>
</div>
<div class="form-group">
<label>清帳日期</label>
<input id="invoice_close_date" type="datetime" class="form-control" />
</div>              
<div class="checkbox">
<label><input id="invoice_print_summary" type="checkbox" />列印清帳條</label>
</div>
<div class="checkbox">
<label><input id="invoice_resend" type="checkbox" />發票重傳</label>
</div>
<div class="form-group">
<button type="button" class="btn btn-large btn-success pull-left" onclick="invoice_close();">清帳</button> 
</div> 
</div><!-- end of col-lg-6 (nested) -->
</div><!-- end of row (nested) -->
</div><!-- end of panel-body -->
</div><!-- end of panel -->
</div><!-- end of col-lg-12 -->
</div><!-- data-items -->
<!-- ----- 電子發票清帳作業(結束) ----- --> 
<script>  
$("#invoice_close_date").datetimepicker({language:"zh-TW",autoclose:true,minView:2,format:"yyyymmdd"});
// 列印電子發票清帳
function invoice_close()
{                  
	var invoice_close_date = $("#invoice_close_date").val();
	if (invoice_close_date == "")
    {
      	alert("日期欄必填 !");
        return false;
    }                
        
    if (!confirm("確認清帳日期:"+invoice_close_date+" ?"))	return false;
    
    $.ajax
    ({     
       	url:"http://localhost:60134/",  
        async:false,    
        timeout:1500,
        type:"post",
        dataType:"json",  
        data:
        {	
          	"cmd":"setShift_Close",                   
            "company_no":$("input:radio:checked[name='invoice_close_company_no']").val(),
          	"vTRN_DATE":invoice_close_date,
          	"vPRINT_SUMMARY":$("#invoice_print_summary").prop("checked") ? "N" : "Y",
          	"vRESEND_FLG":$("#invoice_resend").prop("checked") ? "N" : "Y"
        },
        error:function(xhr, ajaxOptions, thrownError)
        {
        	console.log("error:"+xhr.responseText+"|"+ajaxOptions+"|"+thrownError);  
        },
        success:function(jdata)
        {              
        	if (jdata["Result"] == "000")
            {
            	alert("清帳完成, 日期: "+jdata["W_TODAY"]);
            }
            else
            {
            	alert("清帳失敗:["+jdata["Result"]+"]"+jdata["W_TODAY"]+jdata["Message"]);
            } 
            
            $("#invoice_close_date").val("");
            $("#invoice_print_summary").prop("checked", false);
            $("#invoice_resend").prop("checked", false);
    	}                                                                          
    });
}
</script>