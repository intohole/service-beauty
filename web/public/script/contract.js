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
                data.column_name = $('input[name=column_name]').val()
                
            }).DataTable({
                "serverSide": true,
                "displayLength": 10,
                "serverMethod": "POST",
                "ajax": '/contract/getContractColumnListAjax',
                "paginationType": "bootstrap",
                sort: false,//表头不排序
                "aoColumns": [
                    { data: 'id'},
                    { data: 'column_name' },
                    { data: 'item_type' },
                    { data: 'location_type' },
                    { data: 'location' }
                ],
                "language": {
                    "lengthMenu": "每页显示 _MENU_ 条记录",
                    "paginate": {
                        "previous": "上一页",
                        "next": "下一页"
                    }
                }
            },'json');

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


//新增机构
$('#addColumn').on('click', function() {
    $.fn.dialog({href:'/contract/addColumn/', title: '添加字段', height: 1200, width: 1200});
    pglobal.dt.ajax.reload();
});

$(document).on('click', '.jDialog-cancel-btn', function(){
    $.fn.dialog('close')
});

$(document).on('click', '#addColumnSubBtn', function () {

    $.post('/contract/addColumnAjax', $('#addColumnForm').serialize(), function(data) {
        if (data.error) {
            $.messager.alert('提示', '添加失败，'+data.errorMsg);
        } else {
            $.messager.alert('提示', '添加成功', function(){
                $.fn.dialog('close');
                pglobal.dt.ajax.reload();
            });
        }
    }, 'json')
});


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

//编辑
$(document).on('click', '#editColumn', function () {

    if($('tr').hasClass('selected')){
        var id = '';
        var node = $('tr.selected')['0'].getElementsByTagName("td")['0'];
        if(node.tagName != undefined){
            id = node.innerHTML;
            $.fn.dialog({href:'/contract/editColumn?id='+id, title: '编辑字段'});
        }else{
            $.messager.alert('提示', '选择行的字段ID获取失败');
        }
    }else{
        $.messager.alert('提示', '请选择字段');
    }
});

$(document).on('click', '#editColumnSubBtn', function () {

    $.post('/contract/editColumnAjax', $('#editColumnForm').serialize(), function(data) {
        if(data.error) {
            $.messager.alert('提示', '修改失败，'+data.errormsg);
        } else {
            $.messager.alert('提示', '修改成功', function(){
                $.fn.dialog('close');
                pglobal.dt.ajax.reload();
            });
        }
    }, 'json')
});

//删除
$(document).on('click', '#deleteColumn', function () {

    if($('tr').hasClass('selected')){
        var id = '';
        var node = $('tr.selected')['0'].getElementsByTagName("td")['0'];
        if(node.tagName != undefined){
            id = node.innerHTML;
            $.messager.confirm('提示', '确认删除吗？', function(data){
                if(data){
                    $.post('/contract/deleteColumnAjax?', {id:id}, function(data) {
                        if (data.error != 0) {
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
            $.messager.alert('提示', '选择行的字段ID获取失败');
        }
    }else{
        $.messager.alert('提示', '请选择字段');
    }
});

//点击搜索重新查询
$(document).on('click', '#column_search', function(){
    pglobal.dt.ajax.reload()
});

$(document).keydown(function(event){ 
    if(event.keyCode == 13){ //绑定回车 
        // $('#column_search').click(); //自动触发登录按钮 
        pglobal.dt.ajax.reload()
    } 
}); 

//重置清除搜索
$(document).on('click', '#refreshColumn', function(){
    $('input[name=column_name]').val('')
    pglobal.dt.ajax.reload()
});

$('#showPermission').click( function (){
    if($('tr').hasClass('selected')){
        var permission_id = '';
        var node = $('tr.selected')['0'].getElementsByTagName("td")['0'];
        if(node.tagName != undefined){
            permission_id = node.innerHTML;
            $.fn.dialog({href:'/permission/detailPermission/?permission_id='+permission_id, title: '查看详情', height: 1200, width: 1200});
        }else{
            $.messager.alert('提示', '选择行的菜单ID获取失败');
        }
    }else{
        $.messager.alert('提示', '请选择菜单');
    }

} );


$('.saveActionSettingLnk').live('click', function(){
    if($('tr').hasClass('selected')){
        var permission_id = '';
        var node = $('tr.selected')['0'].getElementsByTagName("td")['0'];
        if(node.tagName != undefined){
            permission_id = node.innerHTML;
            var form = $('#perDetailForm');
            var set_action = [];
            var not_set_action = [];

            $('.set-action').each(function(){
                var that = $(this);
                set_action.push(that.attr('pid')+';'+that.val());
            });
            $('.not-set-action').each(function(){
                var that = $(this);
                not_set_action.push(that.attr('action_name')+';'+that.val());
            });

            $.post('/permission/saveActionSetting', {
                pid: permission_id,
                set_action: set_action.join('|'),
                not_set_action: not_set_action.join('|')
            }, function(data){
                if (data.error == '0') {
                    $.messager.alert('提示', '保存成功', 'info');
                    $.fn.dialog('close');
                    pglobal.dt.ajax.reload();
                } else {
                    $.messager.alert('提示', '保存失败,'+data.errorMsg, 'info');
                }
            }, 'json');
        }else{
            $.messager.alert('提示', '选择行的菜单ID获取失败');
        }
    }else{
        $.messager.alert('提示', '请选择菜单');
    }
});

//扩展关联条件
$(document).on('click', '#columnLocation', function () {

    if($('tr').hasClass('selected')){
        var id = '';
        var node = $('tr.selected')['0'].getElementsByTagName("td")['0'];
        if(node.tagName != undefined){
            id = node.innerHTML;
            $.fn.dialog({href:'/contract/columnLocation?id='+id, title: '扩展关联条件'});
        }else{
            $.messager.alert('提示', '选择行的字段ID获取失败');
        }
    }else{
        $.messager.alert('提示', '请选择字段');
    }
});
//扩展关联条件提交
$(document).on('click', '#columnLocationSubBtn', function () {

    $.post('/contract/columnLocationAjax', $('#columnLocationForm').serialize(), function(data) {
        if(data.error) {
            $.messager.alert('提示', data.errormsg);
        } else {
            $.messager.alert('提示', data.errormsg, function(){
                $.fn.dialog('close');
                pglobal.dt.ajax.reload();
            });
        }
    }, 'json')
});