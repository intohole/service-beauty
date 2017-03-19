var listPage = [0, 0, 0],
    btnMore = $('.btn-more');
    
//用户当前所选角色
var role_id = 0;
//用户当前所选角色对应节点
var nid = 0;

btnMore.on('click', function(){
    var _this = $(this);
    if (_this.hasClass('disabled')) {
        return false;
    };
    _this.addClass('disabled').html('<div class="more-loading"><i class="bounce1"></i><i class="bounce2"></i><i class="bounce3"></i></div>');
    var currentIndex = $('.tabs').find('.active').index();
    console.log(currentIndex)
    getList(currentIndex);
})

function getList(listIndex){
    
    listPage[listIndex]++;
    $.post('/cdnation/customerlistajax', {
            list: listIndex,
            role_id: role_id,
            nid: nid,
            page: listPage[listIndex]
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
                            "<div style='margin-top:.5rem;position:relative'><a style='padding-bottom:.5rem;' class='clearfix' href='/cdnation/addcustomerinfo/aid/"+ data.res[i].id +" '> ";
                        
                        contents +=
                                "<div class='report-list-user-head'>\
                                    <span>订单发起时间: " + data.res[i].create_time + "</span>\
                                </div>\
                                <div class='user-img'>\
                                    <img src='"+ data.res[i].url +"'>\
                                </div>\
                                <div class='report-info'>\
                                    <p>订单编号：" + data.res[i].id + "</p>\
                                    <p>客户姓名：" + data.res[i].name + "</p>\
                                    <p>车辆信息：<strong>"+ data.res[i].brand_name+"&nbsp;"+data.res[i].series_name+"&nbsp;"+data.res[i].style_name + "</strong></p>\
                                    <p></p>\
                                </div>\
                            </a>";
                        contents +=
                            "</div>";
                    
                    
                    $('.wrap-list-block').eq(listIndex).append(contents);
                };
                btnMore.removeClass('disabled').html('点击获取更多');
                tabs('wrap-list-block');
            };
        },
    'json');
}

//获取当前角色
role_id = $("#role_id").val();
//获取当前角色对应节点
nid = $("#nid").val();

getList(0);
getList(1);
getList(2);

$('.fixed-add a').on('click',function(e){
    var e=e||window.event;
    e.preventDefault();
    $.post('/cdnation/checkSalesman', {role_id:role_id} ,function(data){
        if(data.error != 0){
            pop.error(data.msg);
        }else{
            window.location.href='/cdnation/reportadd';
        }
    }, 'json');
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