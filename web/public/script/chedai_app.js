var pglobal = {
    dt: null
};

var TableManaged = function () {

    return {

        //main function to initiate the module
        init: function () {

            if (!jQuery().DataTable) {
                return;
            }

            pglobal.dt = $('#grid').on('preXhr.dt', function(e, settings, data){
                data.customer_name = $('input[name=customer_name]').val()
                data.customer_phone = $('input[name=customer_phone]').val()
                data.auto_id = $('input[name=auto_id]').val()
                data.frame_id = $('input[name=frame_id]').val()
                
            }).DataTable({
                "serverSide": true,
                "displayLength": 10,
                "serverMethod": "POST",
                "ajax": '/chedaiadmin/getAppListAjax',
                "paginationType": "bootstrap",
                sort: false,//表头不排序
                "aoColumns": [
                    { data: 'id'},
                    { data: 'no' },
                    { data: 'realname' },
                    { data: 'phone' },
                    { data: 'auto_id' },
                    { data: 'frame_id' },
                    { data: 'amount' },
                    { data: 'rate' },
                    { data: 'deadline' },
                    { data: 'createdtime' },
                    { data: {key1:'id', key2:'launch', key3:'role_id', key4:'is_admin'}, render:function(data, type, full, meta) {
                        if(data.launch==0 || data.launch==1 || data.launch==2){
                            var html = '<a href="/chedai/salesman_loaninfo?id='+data.id+'">查看详情</a>';
                        }else{
                            if(data.is_admin==1){
                                var html1 = "<a href='/cdnation/showaudit/aid/"+data.id+"/role/"+data.role_id+"/checkfrom/3'>查看详情</a>";
                                //var html2 = " / <a href='/chedaiadmin/deleteApp/aid/"+data.id+"'>删除</a>";
                                var html2 = " / <a href='javascript:void(0)' id='del_app' data-appid='"+data.id+"' >删除</a>";
                                var html = html1+html2;
                            }
                            else{
                                var html = "<a href='/cdnation/showaudit/aid/"+data.id+"/role/"+data.role_id+"/checkfrom/3'>查看详情</a>";
                            }
                        }
                        
                        return html;
                    }}
                ],
                "language": {
                    "lengthMenu": "每页显示 _MENU_ 条记录",
                    "paginate": {
                        "previous": "上一页",
                        "next": "下一页"
                    }
                }
            });

            jQuery('#grid .group-checkable').change(function () {
                var set = jQuery(this).attr("data-set");
                var checked = jQuery(this).is(":checked");
                jQuery(set).each(function () {
                    if (checked) {
                        $(this).attr("checked", true);
                    } else {
                        $(this).attr("checked", false);
                    }
                });
                jQuery.uniform.update(set);
            });

            jQuery('#grid_wrapper .dataTables_filter input').addClass("m-wrap medium"); // modify table search input
            jQuery('#grid_wrapper .dataTables_length select').addClass("m-wrap small"); // modify table per page dropdown
            //jQuery('#sample_1_wrapper .dataTables_length select').select2(); // initialzie select2 dropdown

        }

    };

}();


//选择行
$('#grid tbody').on( 'click', 'tr', function () {
    if ( $(this).hasClass('selected') ) {
        $(this).removeClass('selected');
    }
    else {
        $('#grid').DataTable.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
    }
} );

//搜索
$(document).on('click', '#customer_search', function(){
    pglobal.dt.ajax.reload()
});

//新增日志
$('#addOperation').on('click', function() {
    $.fn.dialog({href:'/operation/addOperation/', title: '添加日志', height: 1200, width: 1200});
    pglobal.dt.ajax.reload();
});

$(document).on('click', '.jDialog-cancel-btn', function(){
    $.fn.dialog('close')
});


$(document).on('click', '#addOperationSubBtn', function () {
    var rate = $('#rate').val();
    var deposit = $('#deposit').val();
    var business_limit = $('#business_limit').val();
    var number = $('#number').val();

    if(rate != ''){
        var re = /^\d+\.?\d*$/;
        if(!re.test(rate)){
            $.messager.alert('提示', '利率输入不合法，请检查');return false;
        }
    }

    if(deposit != ''){
        var re = /^\d+\.?\d*$/;
        if(!re.test(deposit)){
            $.messager.alert('提示', '保证金输入不合法，请检查');return false;
        }
    }

    if(business_limit != ''){
        var re = /^\d+\.?\d*$/;
        if(!re.test(business_limit)){
            $.messager.alert('提示', '业务量上限输入不合法，请检查');return false;
        }
    }

    if(number != ''){
        var re = /^[1-9]*[1-9][0-9]*$/;
        if(!re.test(number)){
            $.messager.alert('提示', '人员数量输入不合法，请检查');return false;
        }
    }

    $.post('/operation/addOperationAjax', $('#addOperationForm').serialize(), function(data) {
        if (data.error) {
            $.messager.alert('提示', '添加失败，'+data.errorMsg);
        } else {
            $.messager.alert('提示', '添加成功', function(){
                console.log('add success');
                $.fn.dialog('close');
                pglobal.dt.ajax.reload();
            });
        }
    }, 'json')
});


//编辑
$(document).on('click', '#editOperation', function () {

    if($('tr').hasClass('selected')){
        var id = '';
        var node = $('tr.selected')['0'].getElementsByTagName("td")['0'];
        if(node.tagName != undefined){
           id = node.innerHTML;
            $.fn.dialog({href:'/operation/editOperation?id='+id, title: '编辑日志'});
        }else{
            $.messager.alert('提示', '选择行的日志ID获取失败');
        }
    }else{
        $.messager.alert('提示', '请选择日志');
    }
});

$(document).on('click', '#editOperationSubBtn', function () {

    var rate = $('#rate').val();
    var deposit = $('#deposit').val();
    var business_limit = $('#business_limit').val();
    var number = $('#number').val();

    if(rate != ''){
        var re = /^\d+\.?\d*$/;
        if(!re.test(rate)){
            $.messager.alert('提示', '利率输入不合法，请检查');return false;
        }
    }

    if(deposit != ''){
        var re = /^\d+\.?\d*$/;
        if(!re.test(deposit)){
            $.messager.alert('提示', '保证金输入不合法，请检查');return false;
        }
    }

    if(business_limit != ''){
        var re = /^\d+\.?\d*$/;
        if(!re.test(business_limit)){
            $.messager.alert('提示', '业务量上限输入不合法，请检查');return false;
        }
    }

    if(number != ''){
        var re = /^[1-9]*[1-9][0-9]*$/;
        if(!re.test(number)){
            $.messager.alert('提示', '人员数量输入不合法，请检查');return false;
        }
    }

    $.post('/operation/editOperationAjax', $('#editOperationForm').serialize(), function(data) {
        console.log(data);

        if (data.error){
            $.messager.alert('提示', '修改失败，'+data.errmsg);
        } else {
            $.messager.alert('提示', '修改成功', function(){
                $.fn.dialog('close');
                pglobal.dt.ajax.reload();
            });
        }
    }, 'json')
});

//删除
$(document).on('click', '#delOperation', function () {

    if($('tr').hasClass('selected')){
        var id = '';
        var node = $('tr.selected')['0'].getElementsByTagName("td")['0'];
        if(node.tagName != undefined){
            id = node.innerHTML;
            $.messager.confirm('提示', '确认删除吗？', function(data){
                if(data){
                    $.post('/operation/deleteOperationAjax?', {id:id}, function(data) {
                        if (data.error) {
                            $.messager.alert('提示', '删除失败，'+data.errmsg);
                        } else {
                            $.messager.alert('提示', '删除成功', function(){
                                $.fn.dialog('close');
                                pglobal.dt.ajax.reload();
                            });
                        }
                    }, 'json')
                }
            });
        }else{
            $.messager.alert('提示', '选择行的日志ID获取失败');
        }
    }else{
        $.messager.alert('提示', '请选择日志');
    }
});


$('.add-operation-form').validate({
    errorElement: 'label', //default input error message container
    errorClass: 'help-inline', // default input error message class
    focusInvalid: true, // do not focus the last invalid input
    onsubmit : true,
    rules: {
        name: {
            required: true
        },
        rete: {
            required: true
        },
        deposit:{
            required: true
        }
    },

    messages: {
        name: {
            required: "请输入日志名称"
        },
        rete: {
            required: "请输入利率"
        },
        deposit: {
            required: "请输入保证金"
        }
    },

    invalidHandler: function (event, validator) { //display error alert on form submit
        alert('invalidHandler');
        $('.alert-error', $('.login-form')).show();
    },

    highlight: function (element) { // hightlight error inputs
        alert('highlight');
        $(element)
            .closest('.control-group').addClass('error'); // set error class to the control group
    },

    success: function (label) {
        console.log('success valite');
        label.closest('.control-group').removeClass('error');
        label.remove();
    },

    errorPlacement: function (error, element) {
        alert('errorPlacement');
        error.addClass('help-small no-left-padding').insertAfter(element.closest('.input-icon'));
    },

    submitHandler: function (form) {
        alert('submitHandler');

    }
});


$(document).on('click', '#editOrganizeSubBtn', function () {
    var name = $('#name').val();
    var rate = $('#rate').val();
    var deposit = $('#deposit').val();
    var business_limit = $('#business_limit').val();
    var number = $('#number').val();
    var area_id = $('#earea_id').val();

    if(name == ''){
        $.messager.alert('提示', '机构名称不能为空');return false;
    }

    if(rate == ''){
        $.messager.alert('提示', '利率不能为空');return false;
    }

    if(rate != ''){
        var re = /^\d+\.?\d*$/;
        if(!re.test(rate)){
            $.messager.alert('提示', '利率输入不合法，请检查');return false;
        }
    }

    $.post('/organize/editOrganizeAjax', $('#editOrganizeForm').serialize(), function(data) {
        console.log(data);

        if (data.error){
            $.messager.alert('提示', '修改失败，'+data.errmsg);
        } else {
            $.messager.alert('提示', '修改成功', function(){
                $.fn.dialog('close');
                pglobal.dt.ajax.reload();
            });
        }
    }, 'json')
});


//删除订单
$(document).on('click', '#del_app', function () {
    //获取订单id
    var appid = $(this).attr('data-appid');

    if(appid){
        $.messager.confirm('提示', '确认删除吗？', function(data){
            if(data){
                $.post('/chedaiadmin/deleteAppAjax', {id:appid}, function(data) {
                    if (data.error) {
                        $.messager.alert('提示', '删除失败，'+data.errmsg);
                    } else {
                        $.messager.alert('提示', '删除成功', function(){
                            //$.fn.dialog('close');
                            pglobal.dt.ajax.reload();
                        });
                    }
                }, 'json');
            }
        });
        
    }else{
        $.messager.alert('提示', '订单编号错误');
    }
    
});
