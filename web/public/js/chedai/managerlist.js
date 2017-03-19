var listPage = [0, 0],
	btnMore = $('.btn-more');

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
	$.post('/chedai/manager_listajax', {
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
                    '<a class="item item-link" href="/chedai/manager_info?id=' + data.res[i].id + '&list=' + data.res[i].list + '">\
				        <p class="clearfix">\
                            <span>客户编号：<b>' + data.res[i].customer + '</b></span>\
                            <span>客户姓名：<b>' + data.res[i].customer_name + '</b></span>\
                        </p>\
                        <p class="clearfix">\
				            <span>业务人员：<b>' + data.res[i].name + '</b></span>\
				            <span>业务类型：<b>' + data.res[i].type + '</b></span>\
				        </p>\
                        <p class="clearfix">\
                            <span>所属地区：<b>' + data.res[i].area + '</b></span>\
                            <span>评估结果：<b class="blue">' + data.res[i].result + '</b></span>\
                        </p>\
                        <p class="clearfix">\
                            <span>贷款金额：<b>' + data.res[i].amount + '万元</b></span>\
                            <span>利率：<b>' + data.res[i].rate + '%</b></span>\
                        </p>\
                        <p class="clearfix">\
                            <span>期限：<b>' + data.res[i].deadline + '个月</b></span>\
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
