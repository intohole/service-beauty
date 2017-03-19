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
    getList(currentIndex);
})

function getList(listIndex){
    
    listPage[listIndex]++;
    $.post('/cdnation/listajax', {
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
                    
                        //如果订单在此节点已被审核过,或者用户角色为业务员,则点击订单时跳转到已审核详情页
                        if(data.msg == '1'){
                            contents += 
                            "<div style='margin-top:.5rem;position:relative'><a style='padding-bottom:1rem;' class='clearfix' href='/cdnation/showaudit/aid/"+ data.res[i].id +"/role/"+ role_id +" '> ";
                        }
                        //否则跳转到订单审核页
                        else{
                            contents += 
                            "<div style='margin-top:.5rem;position:relative'><a style='padding-bottom:1rem;' class='clearfix' href='/cdnation/show/aid/"+ data.res[i].id +"/role/"+ role_id +" '> ";
                        }
                        contents +=
                                "<div class='report-list-user-head'>\
                                    <span>审核进度: " + data.res[i].node_name + "</span>\
                                    <p>申请时间：" + data.res[i].createdtime + "</p>\
                                </div>\
                                <div class='user-img'>\
                                    <img src='"+ data.res[i].url +"'>\
                                    <strong>"+ data.res[i].realname + "</strong>\
                                </div>\
                                <div class='report-info'>\
                                    <p>业务编号：<strong>"+ data.res[i].id + "</strong></p>\
                                    <p>车辆信息：<strong>" + data.res[i].brand_name + " " + data.res[i].series_name + " " +data.res[i].style_name +"</strong></p>\
                                    <p>贷款金额：<strong>"+ data.res[i].amount + "万 月利率："+ data.res[i].rate + "% 期限："+ data.res[i].deadline +"个月</strong></p>\
                                    <p>车辆评估：<strong>"+ data.res[i].is_assess + "</strong></p>\
                                    <p></p>\
                                </div>\
                            </a>";
                    
                    //只有在进行中的订单,并且用户当前是业务员角色时才可以补录用户信息
                    // if((data.res[i].status == 1 || data.res[i].status == 3) && nid == 1){
                    //     contents += 
                    //         "<p style='text-align: right;position:absolute;bottom:.5rem;right:1rem;'><button onclick=window.location.href='/cdnation/addCustomerInfo?aid="+ data.res[i].id +"' type='button' style='border:none;background-color:#fff;border:1.5px solid #2583e3;padding:.2rem .3rem;border-radius:.4rem;color:#2583e3;'>编辑客户信息</button></p>";
                    // }
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