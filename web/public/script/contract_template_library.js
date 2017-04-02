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
		
                //按机构搜索
                data.template_name = $('input[name=template_name]').val();
			
	    }).DataTable({
                "serverSide": true,
                "displayLength": 10,
                "serverMethod": "POST",
                "ajax": '/contract/templatelibrarylistAjax',
                "paginationType": "bootstrap",
                sort: false,//表头不排序
                "aoColumns": [
                    { data: 'id'},
                    { data: 'name' },
                    { data: 'created_date' },
                    { data: 'edit' }
                    
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


//新增模板
$('#addTemplateLib').on('click', function() {
    $.fn.dialog({href:'/contract/addTemplateLib/', title: '添加模板'});
    pglobal.dt.ajax.reload();

});

$(document).on('click', '.jDialog-cancel-btn', function(){
    $.fn.dialog('close');
});


$(document).on('click', '#addTemplateLibSubBtn', function () {
    $("#addTemplateLibSubBtn").attr("disabled", true);
    $.post('/contract/addTemplateLibAjax', $('#addTemplateLibForm').serialize(), function(data) {
        if (data.error) {
            $.messager.alert('提示', '添加失败，'+data.errorMsg);
            $("#addTemplateLibSubBtn").attr("disabled", false);
        } else {
            $.messager.alert('提示', '添加成功', function(){
                $("#addTemplateLibSubBtn").attr("disabled", false);
                console.log('add success');
                $.fn.dialog('close');
                pglobal.dt.ajax.reload();
            });
        }
    }, 'json');
});


//编辑
$(document).on('click', '#editTemplateLib', function () {

    if($('tr').hasClass('selected')){
        var id = '';
        var node = $('tr.selected')['0'].getElementsByTagName("td")['0'];
        if(node.tagName != undefined){
            id = node.innerHTML;
            $.fn.dialog({href:'/contract/editTemplateLib?id='+id, title: '编辑模板'});
        }else{
            $.messager.alert('提示', '选择行的模板ID获取失败');
        }
    }else{
        $.messager.alert('提示', '请选择模板');
    }
});

$(document).on('click', '#editTemplateLibSubBtn', function () {
    $("#editTemplateLibSubBtn").attr("disabled", true);
    $.post('/contract/editTemplateLibAjax', $('#editTemplateLibForm').serialize(), function(data) {
        if (data.error) {
            $.messager.alert('提示', '修改失败，'+data.errmsg);
            $("#editTemplateLibSubBtn").attr("disabled", false);
        } else {
            $.messager.alert('提示', '修改成功', function(){
                $("#editTemplateLibSubBtn").attr("disabled", false);
                $.fn.dialog('close');
                pglobal.dt.ajax.reload();
                
            });
            
        }
    }, 'json');
});

//删除
$(document).on('click', '#delTemplateLib', function () {

    if($('tr').hasClass('selected')){
        var id = '';
        var node = $('tr.selected')['0'].getElementsByTagName("td")['0'];
        if(node.tagName != undefined){
            id = node.innerHTML;
            $.messager.confirm('提示', '确认删除吗？', function(data){
                if(data){
                    $.post('/contract/deleteTemplateLibAjax?', {id:id}, function(data) {
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


//按模板名称搜索
$(document).on('click', '#template_search', function () {
        pglobal.dt.ajax.reload();
});