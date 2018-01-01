<!-- ----- 電子發票下載(限當日)作業 ----- -->  
<div data-items="invoice_rows" class="row" style="display:none;">
<div class="col-lg-12">
<div class="panel panel-default">
<div class="panel-heading">電子發票下載作業</div><!-- 資料顯示區灰色小表頭 -->
<div class="panel-body">
<div data-rows class="row">
<div class="col-lg-6">    
<div>
<label>統編&nbsp;&nbsp;</label>
<label class="radio-inline"><input type="radio" name="invoice_rows_company_no" value="<?=$company_no?>" checked />本場站</label>
<label class="radio-inline"><input type="radio" name="invoice_rows_company_no" value="<?=$hq_company_no?>" />總公司</label>
</div> 
<div class="form-group">
<button type="button" class="btn btn-large btn-success pull-left" onclick="invoice_rows();">下載</button> 
</div> 
</div><!-- end of col-lg-6 (nested) -->
</div><!-- end of row (nested) -->
</div><!-- end of panel-body -->
</div><!-- end of panel -->
</div><!-- end of col-lg-12 -->
</div><!-- data-items -->
<!-- ----- 電子發票下載作業(結束) ----- --> 
<script>  
// 電子發票下載
function invoice_rows()
{     
    if (!confirm("確定電子發票下載 ?"))	return false;
    
    $.ajax
    ({     
       	url:"http://localhost:60134/",  
        async:false,    
        timeout:1500,
        type:"post",
        dataType:"json",  
        data:
        {	
          	"cmd":"getInvoice_Rows",                   
            "company_no":$("input:radio:checked[name='invoice_rows_company_no']").val()
        },
        error:function(xhr, ajaxOptions, thrownError)
        {
        	console.log("error:"+xhr.responseText+"|"+ajaxOptions+"|"+thrownError);  
        },
        success:function(jdata)
        {              
        	if (jdata["Result"] == "000")
            {
            	alert("下載完成 !");
            }
            else
            {
            	alert("下載失敗:["+jdata["Result"]+"]"+jdata["W_TODAY"]+jdata["Message"]);
            } 
    	}                                                                          
    });
}
</script>