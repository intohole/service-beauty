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
                data.search_org = $('select[name=search_org]').val();
			
	    }).DataTable({
                "serverSide": true,
                "displayLength": 10,
                "serverMethod": "POST",
                "ajax": '/contract/templatelistAjax',
                "paginationType": "bootstrap",
                sort: false,//表头不排序
                "aoColumns": [
                    { data: 'id'},
                    { data: 'org_name' },
                    { data: 'name' },
                    { data: 'type' },
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
$('#addTemplate').on('click', function() {
    $.fn.dialog({href:'/contract/addTemplate/', title: '添加模板'});
    pglobal.dt.ajax.reload();

});

$(document).on('click', '.jDialog-cancel-btn', function(){
    $.fn.dialog('close');
});


$(document).on('click', '#addTemplateSubBtn', function () {

    $.post('/contract/addTemplateAjax', $('#addTemplateForm').serialize(), function(data) {
        if (data.error) {
            $.messager.alert('提示', '添加失败，'+data.errorMsg);
        } else {
            $.messager.alert('提示', '添加成功', function(){
                console.log('add success');
                $.fn.dialog('close');
                pglobal.dt.ajax.reload();
            });
        }
    }, 'json');
});


//编辑
$(document).on('click', '#editTemplate', function () {

    if($('tr').hasClass('selected')){
        var id = '';
        var node = $('tr.selected')['0'].getElementsByTagName("td")['0'];
        if(node.tagName != undefined){
            id = node.innerHTML;
            $.fn.dialog({href:'/contract/editTemplate?id='+id, title: '编辑模板'});
        }else{
            $.messager.alert('提示', '选择行的模板ID获取失败');
        }
    }else{
        $.messager.alert('提示', '请选择模板');
    }
});

$(document).on('click', '#editTemplateSubBtn', function () {
    $.post('/contract/editTemplateAjax', $('#editTemplateForm').serialize(), function(data) {
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
$(document).on('click', '#delTemplate', function () {

    if($('tr').hasClass('selected')){
        var id = '';
        var node = $('tr.selected')['0'].getElementsByTagName("td")['0'];
        if(node.tagName != undefined){
            id = node.innerHTML;
            $.messager.confirm('提示', '确认删除吗？', function(data){
                if(data){
                    $.post('/contract/deleteTemplateAjax?', {id:id}, function(data) {
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


//按机构搜索
$(document).on('click', '#template_search', function () {
        pglobal.dt.ajax.reload();
});

//关联模板
$(document).on('click', '#relationTemplate', function () {
    $.fn.dialog({href:'/contract/relationTemplate', title: '关联模板'});
});

$(document).on('click', '#relationTemplateSubBtn', function () {
    $("#relationTemplateSubBtn").attr("disabled", true);
    var oid = $('#oid').val();
    if(oid==0){
        $.messager.alert('提示', '请选择机构');
        $("#relationTemplateSubBtn").attr("disabled", false);
        return false;
    }
    var temps = [];
    $('.checkbox-input').each(function(){
        if($(this).attr('checked') == 'checked'){
            temps.push($(this).val());
        }
    })
    if(temps.length == 0){
        $.messager.alert('提示', '请选择模板');
        $("#relationTemplateSubBtn").attr("disabled", false);
        return false;
    }
    $.post('/contract/relationTemplateAjax', {oid:oid, temps:temps.join(',')}, function(data) {
        if (data.error) {
            $("#relationTemplateSubBtn").attr("disabled", false);
            $.messager.alert('提示', '修改失败，'+data.errmsg);
        } else {
            $("#relationTemplateSubBtn").attr("disabled", false);
            $.messager.alert('提示', '修改成功', function(){
                $.fn.dialog('close');
                pglobal.dt.ajax.reload();
                
            });
        }
    }, 'json');
});

var getNewTemp = function(){
    var oid = $('#oid').val();

    $.post('/contract/getNewTemp', {oid:oid}, function(data){
        if(data.error == 0){
            var html = '';
                html += '<label class="checkbox"><input type="checkbox" id="allcheck">全选</label><br>';
            for (var i in data.data) {
                var temp = data.data[i];
                html += '<label class="checkbox">';
                html += '<input class="checkbox-input" type="checkbox" name="roleForms" value="'+temp.id+'"';
                if(temp.is_check){
                    html += ' checked disabled="true" ';
                }
                html += '>';
                html += temp.name;
                html += '</label><br>';
            }
            $('#templist').html(html);
        }
    },'json');

}

$(document).on('click', '#allcheck', function(){
    if($('#allcheck').attr('checked')=='checked'){
        $('.checkbox-input').each(function(){   
            if(!$(this).attr('disabled')){
                $(this).attr('checked','checked');
            }
        })
        
    }else{
        $('.checkbox-input').each(function(){   
            if(!$(this).attr('disabled')){
                $(this).attr('checked',false);
            }
        })
    }
})