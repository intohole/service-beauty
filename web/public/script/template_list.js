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
                data.id = $('input[name=app_id]').val();
            
        }).DataTable({
                // "serverSide": true,
                "displayLength": 50,
                "serverMethod": "POST",
                "ajax": '/contract/contracttemplatelistAjax',
                "paginationType": "bootstrap",
                sort: false,//表头不排序
                "aoColumns": [
                    // { data: 'fctid'},
                    { data: 'type'},
                    { data: 'name' },
                    { data: 'operation' }
                    
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
        // $('#grid').DataTable.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
    }
} );


//替换字段
$('#replace').on('click', function() {
    $("#replace").attr("disabled", true);
    var ids = [];
    var app_id = $('input[name=app_id]').val();
    var oid = $('input[name=oid]').val();
    $('tr').each(function(){
        if($(this).hasClass('selected')){
            var node = $(this)['0'].getElementsByTagName("td")['0'];
            if(node.tagName != undefined){
                ids.push(node.innerHTML);
            }
        }
    })
    
     $.post('/contract/replaceColumn', {'ids':ids.join(","),'app_id':app_id, 'oid':oid}, function(data) {
        if (data.error) {
            $.messager.alert('提示', '添加失败，'+data.errormsg);
            $("#replace").attr("disabled", false);
            pglobal.dt.ajax.reload();
        } else {
            $.messager.alert('提示', '添加成功', function(){
                $("#replace").attr("disabled", false);
                console.log('add success');
                $.fn.dialog('close');
                pglobal.dt.ajax.reload();
            });
        }
    }, 'json');

});

//全部替换
$('#replaceall').on('click', function() {
    $("#replaceall").attr("disabled", true);
    var ids = [];
    var app_id = $('input[name=app_id]').val();
    var oid = $('input[name=oid]').val();

     $.post('/contract/replaceAllColumn', {'app_id':app_id,'oid':oid}, function(data) {
        if (data.error) {
            $.messager.alert('提示', '添加失败，'+data.errormsg);
        } else {
            $.messager.alert('提示', '添加成功', function(){
                $("#replaceall").attr("disabled", false);
                console.log('add success');
                $.fn.dialog('close');
                pglobal.dt.ajax.reload();
            });
        }
    }, 'json');

});

//删除合同
$('#delete').on('click', function() {
    $("#delete").attr("disabled", true);
    var ids = [];
    var app_id = $('input[name=app_id]').val();
    var oid = $('input[name=oid]').val();
    $('tr').each(function(){
        if($(this).hasClass('selected')){
            var node = $(this)['0'].getElementsByTagName("td")['0'];
            if(node.tagName != undefined){
                ids.push(node.innerHTML);
            }
        }
    })

    if(ids.length == 0){
        $.messager.alert('提示', '没有选择合同');
        $("#delete").attr("disabled", false);
        pglobal.dt.ajax.reload();
        return false;
    }

     $.post('/contract/deleteContract', {'ids':ids.join(","),'app_id':app_id,'oid':oid}, function(data) {
        if (data.error) {
            $.messager.alert('提示', data.errormsg);
            $("#delete").attr("disabled", false);
            pglobal.dt.ajax.reload();
        } else {
            $.messager.alert('提示', data.errormsg, function(){
                $("#delete").attr("disabled", false);
                console.log('add success');
                $.fn.dialog('close');
                pglobal.dt.ajax.reload();
            });
        }
    }, 'json');

});

