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

            $('.add-menu').hide();

            <!-- 选择某行开始 -->
            $('#grid tbody').on( 'click', 'tr', function () {
                if ( $(this).hasClass('selected') ) {
                    $(this).removeClass('selected');
//                    if(!$(this).hasClass('even')){
//                        $(this).addClass('odd');
//                    }
                }
                else {
                    $('#grid').DataTable.$('tr.selected').removeClass('selected');
                    $(this).addClass('selected');
//                    if($(this).hasClass('odd')){
//                        $(this).removeClass('odd');//解决table-striped影响奇数行底色
//                    }
                }
            } );

            $('#showMenu').click( function (){
                if($('tr').hasClass('selected')){
                    var menu_id = '';
                    var node = $('tr.selected')['0'].getElementsByTagName("td")['0'];
                    if(node.tagName != undefined){
                        menu_id = node.innerHTML;
//                        $('#grid').DataTable({
//                            ajax: '/menu/getParentMenuListAjax'
//                        });
//                        $.post('/menu/addMenuAjax', {'menu_id':menu_id}, function(data) {
//
//                        }, 'json')

                    }else{
                        $.messager.alert('提示', '选择行的菜单ID获取失败');
                    }
                }else{
                    $.messager.alert('提示', '请选择后再查看');
                }

            } );
            <!-- 选择某行结束 -->
            var menu_id = $('#menuId').val();
            pglobal.dt = $('#grid').DataTable({
                "serverSide": true,
                "displayLength": 10,
                "serverMethod": "POST",
                "ajax": '/menu/getMenuListAjax?id='+menu_id,
                "paginationType": "bootstrap",
                sort: false,//表头不排序
                "aoColumns": [
                    { data: 'id'},
                    { data: 'name' },
                    { data: 'url' },
                    { data: 'created' }
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


//新增
$('#addMenu').on('click', function() {
    var menu_id = $('#menuId').val();
    $.fn.dialog({href:'/menu/addMenu?id='+menu_id, title: '添加子菜单', height: 1200, width: 1200});
    pglobal.dt.ajax.reload();
});

$(document).on('click', '.jDialog-cancel-btn', function(){
    $.fn.dialog('close')
});


$(document).on('click', '#addMenuSubBtn', function () {

    $.post('/menu/addMenuAjax', $('#addMenuForm').serialize(), function(data) {
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


//编辑子菜单
$(document).on('click', '#editMenu', function () {

    if($('tr').hasClass('selected')){
        var id = '';
        var node = $('tr.selected')['0'].getElementsByTagName("td")['0'];
        if(node.tagName != undefined){
            id = node.innerHTML;
            $.fn.dialog({href:'/menu/editMenu?id='+id, title: '编辑子菜单'});
        }else{
            $.messager.alert('提示', '选择行的子菜单ID获取失败');
        }
    }else{
        $.messager.alert('提示', '请选择子菜单');
    }
});

$(document).on('click', '#editMenuSubBtn', function () {

    $.post('/menu/editMenuAjax', $('#editMenuForm').serialize(), function(data) {
        if (data.error) {
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
$(document).on('click', '#delMenu', function () {

    if($('tr').hasClass('selected')){
        var id = '';
        var node = $('tr.selected')['0'].getElementsByTagName("td")['0'];
        if(node.tagName != undefined){
            id = node.innerHTML;
            $.messager.confirm('提示', '确认删除吗？', function(data){
                if(data){
                    $.post('/menu/deleteMenuAjax?', {id:id}, function(data) {
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
            $.messager.alert('提示', '选择行的子菜单ID获取失败');
        }
    }else{
        $.messager.alert('提示', '请选择子菜单');
    }
});

//添加车贷上级
$(document).on('click', '#addCdPid', function () {

    if($('tr').hasClass('selected')){
        var id = '';
        var node = $('tr.selected')['0'].getElementsByTagName("td")['0'];
        if(node.tagName != undefined){
            id = node.innerHTML;
            $.fn.dialog({href:'/menu/addCdPid?id='+id, title: '添加车贷上级'});
        }else{
            $.messager.alert('提示', '选择行的子菜单ID获取失败');
        }
    }else{
        $.messager.alert('提示', '请选择子菜单');
    }
});

$(document).on('click', '#addCdPidSubBtn', function () {

    $.post('/menu/addCdPidAjax', $('#addCdPidForm').serialize(), function(data) {
        if (data.error) {
            $.messager.alert('提示', '添加/修改失败，'+data.errmsg);
        } else {
            $.messager.alert('提示', '添加/修改成功', function(){
                $.fn.dialog('close');
                pglobal.dt.ajax.reload();
            });
        }
    }, 'json')
});