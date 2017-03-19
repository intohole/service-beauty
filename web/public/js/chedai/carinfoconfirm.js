$('#btn-cancel').on('click', function(){
    var _this = $(this);
    var id = $('body').data('id');
    var submitDatas = {
        'id' :id
    }
    pop.confirm('温馨提示', '是否删除此次所有录入信息？', {btnName : ['确定', '取消'], className: 'animated fadeIn'}, 
        function(res){
            res && $.post('/chedai/salesman_carinfodeleteajax', submitDatas, function(data){
                    pop.clear();
                    if (data.error != 0) {
                        pop.error(data.msg);
                        return;
                    }
                    location.href = '/chedai/salesman_caradd';
                }, 'json')
    })
});

$('#btn-submit').on('click', function(){
    location.href = '/chedai/salesman_carlist';
})