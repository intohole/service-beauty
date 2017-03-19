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
	$.post('/chedai/salesman_carlistajax', {
            list    : listIndex,
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
                    var contents = '',
                        // resultDom = listIndex == 1 ? '<p class="clearfix">\
                        //     <span>评估结果：<b>' + data.res[i].result + '</b></span>\
                        // </p>' : '',
                        resultDom = '',
                        // carResult = listIndex ? '<b>待评估</b>' : '<b class="blue">' + data.res[i].result + '</b>',
                        carValue = listIndex ? '<b class="orange">' + data.res[i].value + '</b>万' : '<b>暂无</b>';
                    contents +=
                    '<a class="item item-link" href="/chedai/salesman_carinfo?id=' + data.res[i].id + '&from=salesman">\
				        <p class="clearfix">\
				            <span>客户编号：<b>' + data.res[i].id + '</b></span>\
				            <span>录入时间：<b>' + data.res[i].time + '</b></span>\
				        </p>\
				        <p class="clearfix">\
				            <span>车辆品牌：<b>' + data.res[i].brand + '</b></span>\
				            <span>车辆车系：<b>' + data.res[i].series + '</b></span>\
				        </p>\
                        <p class="clearfix">\
                            <span>车辆型号：<b>' + data.res[i].version + '</b></span>\
                        </p>\
                        <p class="clearfix">\
                            <span>车架号：<b>' + data.res[i].frame + '</b></span>\
                        </p>' + resultDom +'\
                        <p class="clearfix">\
                            <span>评估价值：' + carValue + '</span>\
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

$('.fixed-add a').on('click',function(e){    
    var e=e||window.event;
    e.preventDefault();
    $.ajax({
        url:'/chedai/salesman_caradd_checkExistsNotSaveAjax',
        type:'POST',
        dataType:'json',
        success:function(data){
            if(data.error==0){
                jsonToUrl(data.res);
            }else if(data.error==101){
                pop.error(data.msg);
            }else{
                window.location.href='/chedai/salesman_caradd';
            }
        }
    });
});

function jsonToUrl(obj){
    var i, url='',n=1;
    for(i in obj){
        if(n==1){
            url+=i+'='+obj[i];
            n=2;
        }else{
            url+='&'+i+'='+obj[i];
        }
    }
    window.location.href='/chedai/salesman_caradd?'+url;
}