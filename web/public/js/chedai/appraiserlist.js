var listPage = [0, 0],
	btnMore = $('.btn-more'),
    typeName = ['以租代购', '车辆质押', '车辆抵押'];

$('#type').on('change', function(){
    $('.wrap-list-block').html('');
    listPage = [0, 0];
    getList(0);
    getList(1);
})

btnMore.on('click', function(){
    var _this = $(this);
    if (_this.hasClass('disabled')) {
        return false;
    };
    _this.addClass('disabled').html('<div class="more-loading"><i class="bounce1"></i><i class="bounce2"></i><i class="bounce3"></i></div>');
	var currentIndex = $('.tabs').find('.active').index();
    getList(currentIndex);
})

function getList(listIndex){
    var currentType = $('#type option:selected').index() + 1;
    listPage[listIndex]++;
	$.post('/chedai/appraiser_listajax', {
            list    : listIndex,
            page	: listPage[listIndex],
            type    : currentType
		}, 
        function(data) {
            if (data.error != 0) {
                listPage[listIndex]--;
                listPage[listIndex] != 0 && pop.error('暂无更多数据');
                btnMore.removeClass('disabled').html('点击获取更多');
                tabs('wrap-list-block');
                return false;
            }else{
                for (var i = 0; i < data.res.length; i++){
                    var contents = '';
                    contents +=
                    '<a class="item item-link" href="/chedai/appraiser_info?id=' + data.res[i].id + '">\
				        <p class="clearfix">\
                            <span>客户编号：<b>' + data.res[i].customer + '</b></span>\
                            <span>客户姓名：<b>' + data.res[i].customer_name + '</b></span>\
                        </p>\
                        <p class="clearfix">\
				            <span>业务人员：<b>' + data.res[i].realname + '</b></span>\
				            <span>业务类型：<b>' + typeName[data.res[i].type - 1] + '</b></span>\
				        </p>\
                        <p class="clearfix">\
                            <span>贷款金额：<b>' + data.res[i].amount + '万元</b></span>\
                            <span>利&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;率：<b>' + data.res[i].rate + '%</b></span>\
                        </p>\
                        <p class="clearfix">\
                            <span>期&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;限：<b>' + data.res[i].deadline + '个月</b></span>\
                            <span>所属地区：<b>' + data.res[i].org_name + '</b></span>\
                        </p>\
			        </a>'
                    $('.wrap-list-block').eq(listIndex).append(contents);
                };
                btnMore.removeClass('disabled').html('点击获取更多');
                tabs('wrap-list-block');
            };
		},
	'json');
}

getList(0);
getList(1);
