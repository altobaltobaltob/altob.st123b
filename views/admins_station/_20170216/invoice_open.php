<!-- ----- 電子發開帳作業 ----- -->  
<div data-items="invoice_open" class="row" style="display:none;">
<div class="col-lg-12">
<div class="panel panel-default">
<div class="panel-heading">電子發票開帳作業</div><!-- 資料顯示區灰色小表頭 -->
<div class="panel-body">
<div data-rows class="row">
<div class="col-lg-6">    
<div>
<label>統編&nbsp;&nbsp;</label>
<label class="radio-inline"><input type="radio" name="invoice_open_company_no" value="<?=$company_no?>" checked />本場站</label>
<label class="radio-inline"><input type="radio" name="invoice_open_company_no" value="<?=$hq_company_no?>" />總公司</label>
</div>
<div class="form-group">
<label>開帳日期</label>
<input id="invoice_open_date" type="datetime" class="form-control" />
</div> 
<div class="form-group">
<button type="button" class="btn btn-large btn-success pull-left" onclick="invoice_open();">開帳</button> 
</div> 
</div><!-- end of col-lg-6 (nested) -->
</div><!-- end of row (nested) -->
</div><!-- end of panel-body -->
</div><!-- end of panel -->
</div><!-- end of col-lg-12 -->
</div><!-- data-items -->
<!-- ----- 電子發票開帳作業(結束) ----- --> 
<script>  
$("#invoice_open_date").datetimepicker({language:"zh-TW",autoclose:true,minView:2,format:"yyyymmdd"});
// 列印電子發票開帳
function invoice_open()
{                      
	var invoice_open_date = $("#invoice_open_date").val();
	if (invoice_open_date == "")
    {
      	alert("日期欄必填 !");
        return false;
    }                
    
    if (!confirm("確認開帳日期:"+invoice_open_date+" ?"))	return false;
    
    $.ajax
    ({     
       	url:"http://localhost:60134/",  
        async:false,    
        timeout:1500,
        type:"post",
        dataType:"json",  
        data:
        {	
          	"cmd":"setShift_Open",                   
            "company_no":$("input:radio:checked[name='invoice_open_company_no']").val(),
          	"vTRN_DATE":invoice_open_date
        },
        error:function(xhr, ajaxOptions, thrownError)
        {
        	console.log("error:"+xhr.responseText+"|"+ajaxOptions+"|"+thrownError);  
        },
        success:function(jdata)
        {              
        	if (jdata["Result"] == "000")
            {
            	alert("開帳完成, 日期: "+jdata["W_TODAY"]);
            }
            else
            {
            	alert("開帳失敗:["+jdata["Result"]+"]"+jdata["W_TODAY"]+jdata["Message"]);
            } 
            
            $("#invoice_open_date").val("");
    	}                                                                          
    });
}
</script>