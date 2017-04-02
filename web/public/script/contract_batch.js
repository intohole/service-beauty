//批量打印JS
var cb = new Contract_batch();
cb.init();    

//批量打印JS
function Contract_batch()
{
    var app_id = $("#app_id").val();
    var oThis = this;
    oThis.init = function()
    {   
        //绑定批量打印方法
        $("#batch_print").bind("click", oThis.batch_print);
    }
    //批量打印方法
    oThis.batch_print = function()
    {
        
        window.print();
        
//        $.post('/contract/setHasPrint', {'app_id': app_id}, function(data){
//            if (data.error) {
//                alert('已全部打印过该合同');
//                window.print();
//            } else {
//                window.print();
//            }
//        }, 'json');

        //ue = UE.getEditor('editor');
        //var all_content = UE.getEditor('editor').getContent();
        //var all_content = UE.getEditor('editor').getAllHtml();
        
//        var batch_form = $("#batch-form");
//        batch_form.submit();

//alert(all_content);
        
    }


}


