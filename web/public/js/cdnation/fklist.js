var pglobal = {
    dt: null
};

var nid = $("#nid").val();
var fid = $("#fid").val();
var rid = $("#rid").val();

var TableManaged = function () {

    return {

        //main function to initiate the module
        init: function () {

            if (!jQuery().DataTable) {
                return;
            }

            pglobal.dt = $('#grid').on('preXhr.dt', function ( e, settings, data ) {
			
            data.search_borrower = $('input[name=search_borrower]').val(),
			data.status = $('input[name=status]').val(),
            data.nid = $('input[name=nid]').val(),
            data.fid = $('input[name=fid]').val(),
            data.rid = $('input[name=rid]').val(),
            data.customer_name = $('#customer_name').val(),
            data.oid_sel = $('#oid').val();
			
	    }).DataTable({
                "serverSide": true,
                "displayLength": 10,
                "serverMethod": "POST",
                "ajax": '/cdnation/fklistAjax',
                "paginationType": "bootstrap",
                sort: false,//表头不排序
                "aoColumns": [
                    { data: 'id'},
                    { data:'no', render:function(data, type, full, meta) {
                        var type = "以租代购";
                        return type;
                    }},
                    { data: 'realname' },
                    { data: 'phone' },
                    { data:{key1:'brand_name',key2:'series_name',key3:'style_name'}, render:function(data, type, full, meta) {
                        var car_info = data.brand_name + data.series_name;
                        return car_info;
                    }},
                    { data: 'auto_id' },
                    { data: 'frame_id' },
                    { data: 'amount' },
                    { data: 'deadline' },
                    { data: 'rate' },
                    { data: 'estimate_total' },
                    { data: 'creator_name' },
                    { data: 'appraiser_name' },
                    { data: 'createdtime'},
                    { data: 'oname'},
                    { data:{key1:'id',key2:'is_audit'}, render:function(data, type, full, meta) {
                        var action;
                        if(data.is_audit == 1){
                            action = '<a href="/cdnation/showfk/aid/'+data.id+'/role/'+rid+'/nid/'+nid+'/is_audit/'+data.is_audit+'">查看</a>&nbsp;';
                            if(nid==6){
                                action += '&nbsp;<a href="/cdnation/downpic/aid/'+data.id+'">一键打包</a>&nbsp;';
                                action += '&nbsp;<a href="/cdnation/downpic/aid/'+data.id+'/type/3">资方进件打包</a>&nbsp;';
                            }
                        }
                        else{
                            action = '<a href="/cdnation/showfk/aid/'+data.id+'/role/'+rid+'/nid/'+nid+'">查看</a>';
                        }
                            
                        return action;
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


//待审核订单
$('#unprint').on('click', function() {
    location.href='/cdnation/fklist/nid/'+nid+'/fid/'+fid+'?status=1';
});

//已审核订单
$('#hasprint').on('click', function() {
    location.href='/cdnation/fklist/nid/'+nid+'/fid/'+fid+'?status=2';

});

$(document).on('click', '.jDialog-cancel-btn', function(){
    $.fn.dialog('close');
});

$(document).on('click', '#app_search', function () {
    pglobal.dt.ajax.reload();
});