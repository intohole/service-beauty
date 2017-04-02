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

            pglobal.dt = $('#grid').on('preXhr.dt', function ( e, settings, data ) {
			
            data.search_borrower = $('input[name=search_borrower]').val(),
			data.tab = $('input[name=tab]').val(),
            data.type = $('input[name=item_type]').val()
			
	    }).DataTable({
                "serverSide": true,
                "displayLength": 10,
                "serverMethod": "POST",
                "ajax": '/loan/contranctPrintAjax',
                "paginationType": "bootstrap",
                sort: false,//表头不排序
                "aoColumns": [
                    { data: 'id'},
                    { data: 'no' },
                    { data: 'customer_name' },
                    { data: 'phone'},
                    // { data: 'amount' },
                    // { data: 'rate' },
                    // { data: 'deadline' },
                    { data: 'daizhong_loan' },
                    { data: 'daizhong_rate' },
                    { data: 'daizhong_deadline' },
                    { data: 'signcreated' },
                    
                    { data:'id', render:function(data, type, full, meta) {
                            var status = "";
                            status = '<a href="/contract/view/aid/'+data+'/type/print">查看详情</a>&nbsp;<a href="/contract/contracttemplatelist?id='+data+'">打印</a>';
                            var tab = $('input[name=tab]').val()
                            if(tab==1)
                                status += '&nbsp;<a href="/contract/refresh_column?id='+data+'">重新制作</a>';
                            
                        return status;
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
// $('#grid tbody').on( 'click', 'tr', function () {
//     if ( $(this).hasClass('selected') ) {
//         $(this).removeClass('selected');
//     }
//     else {
//         $('#grid').DataTable.$('tr.selected').removeClass('selected');
//         $(this).addClass('selected');
//     }
// } );

var tab = $('input[name=tab]').val();
var type = $('input[name=item_type]').val();

//待打印合同
$('#unprint').on('click', function() {
    location.href='/loan/contranctPrint?tab=1&type='+type;

});
//已打印合同
$('#hasprint').on('click', function() {
    location.href='/loan/contranctPrint?tab=2&type='+type;

});

// //待打印合同
// $('#xiamen').on('click', function() {
//     location.href='/loan/contranctPrint?type=1&tab='+tab;

// });
// //已打印合同
// $('#quanguo').on('click', function() {
//     location.href='/loan/contranctPrint?type=2&tab='+tab;

// });

$(document).on('click', '.jDialog-cancel-btn', function(){
    $.fn.dialog('close')
});


$(document).on('click', '#addRepaymentSubBtn', function () {

//    var number = $('#number').val();
//    if(number != ''){
//        var re = /^[1-9]*[1-9][0-9]*$/;
//        if(!re.test(number)){
//            $.messager.alert('提示', '人员数量输入不合法，请检查');return false;
//        }
//    }

    $.post('/repayment/addRepaymentAjax', $('#addRepaymentForm').serialize(), function(data) {
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
$(document).on('click', '#editRepayment', function () {

    if($('tr').hasClass('selected')){
        var id = '';
        var node = $('tr.selected')['0'].getElementsByTagName("td")['0'];
        if(node.tagName != undefined){
            id = node.innerHTML;
            $.fn.dialog({href:'/repayment/editRepayment?id='+id, title: '编辑提醒'});
        }else{
            $.messager.alert('提示', '选择行的提醒ID获取失败');
        }
    }else{
        $.messager.alert('提示', '请选择提醒记录');
    }
});

$(document).on('click', '#editRepaymentSubBtn', function () {
    $.post('/repayment/editRepaymentAjax', $('#editRepaymentForm').serialize(), function(data) {
        if (data.error) {
            $.messager.alert('提示', '修改失败，'+data.errmsg);
        } else {
            $.messager.alert('提示', '修改成功', function(){
                $.fn.dialog('close');
                pglobal.dt.ajax.reload();
            });
        }
    }, 'json');
});

//删除
$(document).on('click', '#delRepayment', function () {

    if($('tr').hasClass('selected')){
        var id = '';
        var node = $('tr.selected')['0'].getElementsByTagName("td")['0'];
        if(node.tagName != undefined){
            id = node.innerHTML;
            $.messager.confirm('提示', '确认删除吗？', function(data){
                if(data){
                    $.post('/repayment/deleteRepaymentAjax?', {id:id}, function(data) {
                        if (data.error) {
                            $.messager.alert('提示', '删除失败，'+data.errmsg);
                        } else {
                            $.messager.alert('提示', '删除成功', function(){
                                $.fn.dialog('close');
                                pglobal.dt.ajax.reload();
                            });
                        }
                    }, 'json');
                }
            });
        }else{
            $.messager.alert('提示', '选择行的ID获取失败');
        }
    }else{
        $.messager.alert('提示', '请选择还款记录');
    }
});


//提前还款
$(document).on('click', '#aheadRepayment', function () {

    if($('tr').hasClass('selected')){
        var id = '';
        var node = $('tr.selected')['0'].getElementsByTagName("td")['0'];
        if(node.tagName != undefined){
            id = node.innerHTML;
            $.messager.confirm('提示', '确认提前还款吗？', function(data){
                if(data){
                    $.post('/repayment/aheadRepaymentAjax?', {id:id}, function(data) {
                        if (data.error) {
                            $.messager.alert('提示', '操作失败，'+data.errmsg);
                        } else {
                            $.messager.alert('提示', '操作成功', function(){
                                $.fn.dialog('close');
                                pglobal.dt.ajax.reload();
                            });
                        }
                    }, 'json');
                }
            });
        }else{
            $.messager.alert('提示', '选择行的ID获取失败');
        }
    }else{
        $.messager.alert('提示', '请选择还款记录');
    }
});

$(document).on('click', '#remind_search', function () {
        pglobal.dt.ajax.reload();
});