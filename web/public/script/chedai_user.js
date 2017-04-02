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
                data.name = $('input[name=customer_name]').val()
                data.phone = $('input[name=customer_phone]').val()
                
            }).DataTable({
                "serverSide": true,
                "displayLength": 10,
                "serverMethod": "POST",
                "ajax": '/chedaiadmin/getUserListAjax',
                "paginationType": "bootstrap",
                sort: false,//表头不排序
                "aoColumns": [
                    { data: 'id'},
                    { data: 'name' },
                    { data: 'gender' },
                    { data: 'age' },
                    { data: 'phone' },
                    { data: 'marriage' },
                    { data: 'create_time' },
                    {targets:8, data:'id', render:function(data, type, full, meta) {
                        var html = '<a href="/chedai/salesman_customerinfo?id='+data+'">查看详情</a>';
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

