
var cm = new Contract_make();
cm.init();    

//模板制作JS
function Contract_make()
{
    var oThis = this;
    
    //富文本编辑器实例
    var ue;
    
    oThis.init = function()
    {   
        //实例化编辑器
        //建议使用工厂方法getEditor创建和引用编辑器实例，如果在某个闭包下引用该编辑器，直接调用UE.getEditor('editor')就能拿到相关的实例
        ue = UE.getEditor('editor');
        
        //绑定模板制作方法
        $("#submit_hetong").bind("click", oThis.templateMake);
        
    }
    
    //将文本内容传递给后台制作模板
    oThis.templateMake = function() {
        
        $("#submit_hetong").attr("disabled", true);
        
        //获取全部文本内容(不带样式)
        var all_content = UE.getEditor('editor').getContent();
        var id = $("#id").val();
        var oid = $("#oid").val();
        var type = $("#type").val();
        var status = $("#status").val();
        
        //传递给后台生成模板文件html
        UE.ajax.request( '/contract/getContentAjax', {

                //请求方法。可选值： 'GET', 'POST'，默认值是'POST'
                method: 'POST',

                //超时时间。 默认为5000， 单位是ms
                timeout: 10000,

                //是否是异步请求。 true为异步请求， false为同步请求
                async: true,
                
                //请求携带的数据。如果请求为GET请求， data会经过stringify后附加到请求url之后。
                data: {
                    all_content: all_content,
                    id: id,
                    oid: oid,
                    type: type,
                    status: status
                },

                //请求成功后的回调， 该回调接受当前的XMLHttpRequest对象作为参数。
                onsuccess: function ( xhr ) {
                    
                    //将返回的字符串解析为json对象
                    json_obj =  JSON.parse(xhr.responseText);
                    if(json_obj.error != 0){
                        //alert(json_obj.errmsg);
                        $.messager.alert('提示', '修改失败，'+json_obj.errmsg);
                        
                        $("#submit_hetong").attr("disabled", false);
                    }
                    else{
                        alert('合同模板生成成功!');
                        
                        $("#submit_hetong").attr("disabled", false);
                        location.replace("/contract/templatelist");
                    }                   
                },

                //请求失败或者超时后的回调。
                onerror: function ( xhr ) {
                     $("#submit_hetong").attr("disabled", false);
                     alert( '模板请求失败' );
                }

        } );
    }
    
    
    //清空表单
    oThis.resetInput = function()
    {
        inputs = $(".inout-mag textarea");
        $.each(inputs, function(i, n){
             $(n).val('');
        });      

        input = $("#workid");
        input.val('');
        $('#uploads').html('');
    }
    

}


