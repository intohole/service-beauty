var listPage = [0, 0, 0],
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
	$.post('/chedai/salesman_customerlistajax', {
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
                    var contents = '';
                    contents +=
                    '<a class="item item-link" href="/chedai/salesman_customerinfo?id=' + data.res[i].id + '&from=salesman">\
				        <p class="clearfix">\
				            <span>客户编号：<b>' + data.res[i].id + '</b></span>\
				            <span>录入时间：<b>' + data.res[i].time + '</b></span>\
				        </p>\
				        <p class="clearfix">\
				            <span>客户姓名：<b>' + data.res[i].name + '</b></span>\
				            <span>客户年龄：<b>' + data.res[i].age + '</b></span>\
				        </p>\
				        <p class="clearfix">\
				            <span>婚姻状况：<b>' + data.res[i].marry + '</b></span>\
				            <span>客户手机：<b>' + data.res[i].phone + '</b></span>\
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
getList(2);

$('.fixed-add a').on('click',function(e){
    var e=e||window.event;
    e.preventDefault();
    $.ajax({
        url:'/chedai/salesman_customeradd_checkExistsNotSaveAjax',
        type:'POST',
        dataType:'json',
        success:function(data){
            if(data.error==0){
                jsonToUrl(data.res);
            }else if(data.error==101){
                pop.error(data.msg);
            }else{
                window.location.href='/chedai/salesman_customeradd';
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
    window.location.href='/chedai/salesman_customeradd?'+url;
}