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
                        window.location.href="/menu/detail?id="+menu_id;

                    }else{
                        $.messager.alert('提示', '选择行的菜单ID获取失败');
                    }
                }else{
                    $.messager.alert('提示', '请选择后再查看');
                }

            } );
            <!-- 选择某行结束 -->

            pglobal.dt = $('#grid').DataTable({
                "serverSide": true,
                "displayLength": 10,
                "serverMethod": "POST",
                "ajax": '/menu/getParentMenuListAjax',
                "paginationType": "bootstrap",
                sort: false,//表头不排序
                "aoColumns": [
                    { data: 'id'},
                    { data: 'name' },
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
$('#addParentMenu').on('click', function() {
    $.fn.dialog({href:'/menu/addParentMenu/', title: '添加机构', height: 1200, width: 1200});
    pglobal.dt.ajax.reload();
});

$(document).on('click', '.jDialog-cancel-btn', function(){
    $.fn.dialog('close')
});


$(document).on('click', '#addParentMenuSubBtn', function () {

    $.post('/menu/addParentMenuAjax', $('#addMenuForm').serialize(), function(data) {
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
$(document).on('click', '#editParentMenu', function () {

    if($('tr').hasClass('selected')){
        var id = '';
        var node = $('tr.selected')['0'].getElementsByTagName("td")['0'];
        if(node.tagName != undefined){
            id = node.innerHTML;
            $.fn.dialog({href:'/menu/editParentMenu?id='+id, title: '编辑菜单'});
        }else{
            $.messager.alert('提示', '选择行的菜单ID获取失败');
        }
    }else{
        $.messager.alert('提示', '请选择菜单');
    }
});

$(document).on('click', '#editParentMenuSubBtn', function () {

    $.post('/menu/editParentMenuAjax', $('#editParentMenuForm').serialize(), function(data) {
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
$(document).on('click', '#delParentMenu', function () {

    if($('tr').hasClass('selected')){
        var id = '';
        var node = $('tr.selected')['0'].getElementsByTagName("td")['0'];
        if(node.tagName != undefined){
            id = node.innerHTML;
            $.messager.confirm('提示', '菜单下面的所有子菜单也会被删除，确认删除？', function(data){
                if(data){
                    $.post('/menu/deleteParentMenuAjax?', {id:id}, function(data) {
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
            $.messager.alert('提示', '选择行的菜单ID获取失败');
        }
    }else{
        $.messager.alert('提示', '请选择菜单');
    }
});


