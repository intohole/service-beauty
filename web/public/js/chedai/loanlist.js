var listPage = [0, 0],
	btnMore = $('.btn-more');

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
    listPage[listIndex]++;
	$.post('/chedai/salesman_loanlistajax', {
            list    : listIndex + 1,
            page	: listPage[listIndex]
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
                    '<a class="item item-link" href="/chedai/salesman_loaninfo?id=' + data.res[i].id + '">\
				        <p class="clearfix">\
                            <span>客户编号：<b>' + data.res[i].customer + '</b></span>\
                            <span>客户姓名：<b>' + data.res[i].realname + '</b></span>\
                        </p>\
                        <p class="clearfix">\
                            <span>贷款金额：<b>' + data.res[i].amount + '万元</b></span>\
				            <span>办理日期：<b>' + data.res[i].createdtime + '</b></span>\
                        </p>\
                        <p class="clearfix">\
                            <span>贷款期限：<b>' + (data.res[i].deadline || '暂无') + '个月</b></span>\
                            <span>贷款利率：<b>' + data.res[i].rate + '%</b></span>\
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
