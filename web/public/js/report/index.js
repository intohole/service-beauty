var btnLocation = $('.location-current'),
    btnGetLocation = $('.btn-getlocation'),
    locationContainer = $('.location-container'),
    listCities = $('.list-cities'),
    listCities_all=$('.wrapper-list-cities'),//添加全部业务地区选择
    productList = $('.product-list');

//产品数据: 
//  城市名:
//      房抵类: [项目1, 项目2, 项目n],
//      车抵类：[项目1, 项目2, 项目n]
//
//房贷项目: [一抵月息, xx]
//车贷项目: [月息, xx]
// var productDatas = {
//     '北京': {
//         'building' : [
//                 {
//                     title : '房抵-7成质押业务',
//                     rateTitle : '一抵押月息：',
//                     rate : '0.66%',
//                     subRate : '二抵押: 0.88%　|　转单: 0.88%'
//                 },
//         ],
//         'car' : [['0.88']],
//         'funded' : [['0.08']]
//     },
//     '上海': {
//         'building' : [
//             {
//                 title : '房抵类',
//                 rateTitle : '月息：',
//                 rate : '0.73%',
//                 subRate : '一抵、二抵、转单通通0.73%！'
//             }
//         ],
//         'car' : [['0.88']],
//         'funded' : [['0.08']]
//     }
// }

getUserLocation();//init

btnLocation.eq(0).on('click', function(){
    locationContainer.show();
    productList.hide();
})

btnGetLocation.on('click', function(){
    btnLocation.find('span').html('定位中...');
    if(btnLocation.find('span').text('定位中...')){
        btnGetLocation.html("");
        btnLocation.find('b').html('');
        // $('.icon-location-on').show();
    }
    getUserLocation();
    locationContainer.hide();
})

    // listCities.find('li').on('click', function(){
listCities.on("click","li",function(){
    locationContainer.hide();
    $(".wrapper_hide").css("display","block");
    var id=$(this).attr("name");
    
    $.post("/report/areaAjax",{"area_id":id},function(data){
        renderProduct(data);
        $('.product-list').show();
    },'json');
})

    // 添加全部业务地区选择
    // listCities_all.find('li').on('click', function(){
listCities_all.on("click","li",function(){
    locationContainer.hide();
    $(".wrapper_hide").css("display","block");
    var id=$(this).attr("name");
    $.post("/report/areaAjax",{"area_id":id},function(data){
        renderProduct(data);
        $('.product-list').show();
    },'json');
})
// 添加全部业务地区选择完成
function renderProduct(city){
        // btnLocation.find('span').html('当前位置：<strong>' + city + '</strong>');
    // btnLocation.children(".icon-location-on").css("display","none");
    if(city.msg){
        btnLocation.find('span').html('当前业务地区：<strong>' + city.msg.dzmessage.areaname + '</strong>');
    }else{
        return;
    }
    btnLocation.find('b').html('重新定位');
    $(".btn-getlocation-loca").html('地区更换');
    if(city.msg.allcity){
        productcitylist(city);
        producthotcitylist(city);
    }
     if(city.msg.fdmessage.id){
        var productBuildingHtmlStr = productBuildingHtml(city);
    }else{
        var productBuildingHtmlStr = "";
    }
    if(city.msg.dzmessage.id){
        var productFundedHtmlStr = productFundedHtml(city);
    }else{
        var productFundedHtmlStr = "";
    }
    var productSum= '';
	//'<p class="product-list-sum">累计放款总额：40.80亿</p>';
    // var productBuildingHtmlStr = productBuildingHtml(productDatas[city] ? productDatas[city]['building'] : productDatas['北京']['building'])//若无该城市数据，则输出缺省值，北京市的数据
    // var productCarHtmlStr = productCarHtml(productDatas[city] ? productDatas[city]['car'] : productDatas['北京']['car'])//若无该城市数据，则输出缺省值，北京市的数据
    // var productFundedHtmlStr = productFundedHtml(productDatas[city] ? productDatas[city]['funded'] : productDatas['北京']['funded'])//若无该城市数据，则输出缺省值，北京市的数据
    // productList.html(productBuildingHtmlStr + productFundedHtmlStr + productSum);
    productList.html(productBuildingHtmlStr + productFundedHtmlStr );
}
function productBuildingHtml(datas){
    var html = [];
    var span = '';
    for(var i=0;i<datas.msg.fdmessage.abc.length;i++){
        if(datas.msg.fdmessage.abc[i] != null){
           span+='<span>' + datas.msg.fdmessage.abc[i] + '</span>'; 
        }
    }
       
            html.push('<li class="product-item">\
                    <a class="product-desc product-desc-fw" href="/report/introduc?active=fangdi&id='+datas.msg.fdmessage.id+'">\
                        <h2 class="product-title">房屋抵押</h2>\
                        <div class="product-desc-title">月息:</div>\
                        <div class="product-rate">' + datas.msg.fdmessage.rate + '%</div>\
                        <div class="product-subrate">' + datas.msg.fdmessage.point + '</div>\
                        <div class="product-tag tac">'+ span +'</div>\
                    </a>\
                    <a class="product-btn product-btn-fw" href="/report/details?area_id='+datas.msg.fdmessage.area_id+'&type=0">1秒评房</a>\
                </li>');
            return html.join('');
    }
       
                // <a class="product-desc" href="/report/submitcar">\
                    // <div class="product-rate">' + datas[i][0] + '%</div>\
                // <a class="product-btn" href="/report/submitcar">我要报单</a>\
// function productCarHtml(datas){
//     var html = [];
//         html.push('<li class="product-item product-car">\
//                 <a class="product-desc" href="javascript:;">\
//                     <h2 class="product-title"><i class="icon-img-car"></i>汽车-以租代购</h2>\
//                     <div class="product-desc-title">月息：</div>\
//                     <div class="product-rate" style="height:2.7rem"><img src="/image/report/waiting.png" style="width: 6rem;margin-bottom: .5rem"></div>\
//                     <div class="product-subrate">还款方式：等额本息</div>\
//                     <div class="product-tag tac">\
//                         <span>10分钟批贷</span>\
//                         <span>服务费全返</span>\
//                         <span>保费可返</span>\
//                     </div>\
//                 </a>\
//                 <a class="product-btn" href="javascript:;">敬请期待</a>\
//             </li>')
//     return html.join('');
// }

function productFundedHtml(datas){
    var html = [];
    var span = '';
    for(var i=0;i<datas.msg.dzmessage.abc.length;i++){
        if(datas.msg.dzmessage.abc[i] != null){
           span+='<span>' + datas.msg.dzmessage.abc[i] + '</span>';
        }
    }
        html.push('<li class="product-item product-funded">\
                <a class="product-desc product-desc-dz" href="/report/introduc?active=dianzi&id='+datas.msg.dzmessage.id+'">\
                    <h2 class="product-title">垫资</h2>\
                    <div class="product-desc-title">日息：</div>\
                    <div class="product-rate">' + datas.msg.dzmessage.rate + '%</div>\
                    <div class="product-subrate">' + datas.msg.dzmessage.point + '</div>\
                    <div class="product-tag tac">'+ span +'</div>\
                </a>\
                <a class="product-btn product-btn-dz" href="/report/details?area_id='+datas.msg.dzmessage.area_id+'&type=1">1秒评房</a>\
            </li>')
        return html.join('');

        
}

//站点统计
$(document.body).on('click','.product-btn-fw',function(){
    _czc.push(['_trackEvent', '首页', '房屋抵押-1秒评房' ]);
});

$(document.body).on('click','.product-btn-dz',function(){
    _czc.push(['_trackEvent', '首页', '垫资-1秒评房' ]);
});
$(document.body).on('click','.product-desc-fw',function(){
    _czc.push(['_trackEvent', '首页', '房屋抵押业务详情' ]);
});
$(document.body).on('click','.product-desc-dz',function(){
    _czc.push(['_trackEvent', '首页', '垫资业务详情' ]);
});
//结束
function productcitylist(data){
    var html_citylist="";
    for(var i=0;i<data.msg.allcity.length;i++){
        html_citylist += '<li name='+data.msg.allcity[i].area_id+'>'+data.msg.allcity[i].name+'</li>';
    }
    $("#citylist").html(html_citylist);
}

function producthotcitylist(data){
    var html_citylist=[];
    for(var i=0;i<data.msg.hotcity.length;i++){
        html_citylist.push('<li name='+data.msg.hotcity[i].area_id+'>'+data.msg.hotcity[i].name+'</li>');
    }
    $("#hotcity").html(html_citylist.join(""));
}

function getUserLocation(){
    if (isMobile()) {
        if (isWechat()) {
            wechat({
                shareDatas : {
                   title: '银行垫资，按日计息，高出全返',
                   desc: '行业最高返费，邀请同行报单，每报1单，20元送你！一般人我不告诉他！',
                   imgUrl : 'https://mmbiz.qlogo.cn/mmbiz/VEuFzgBFcycf5svTnlbpKdRTicRKLxlT6RaxJZW6uTLLq3thmOJA1sY0QJfibV1pC1n2mDKLESiaQwTAsAJgXibGNQ/0?wx_fmt=jpeg'
                },
                getLocation : function(res, successCode){
                    if (successCode == 1) {
                        $.post('/report/gencodeAjax', {
                            lat: res.latitude,
                            lng: res.longitude
                        }, function(data) {
                           
                            renderProduct(data);
                            if(locationContainer.css("display")=="none"){
                                $('.product-list').show();
                            } 
                        }, 'json');
                    }else{
                        // getLocationError();
                        $.post('/report/gencodeAjax', function(data) {
                            renderProduct(data);
                            if(locationContainer.css("display")=="none"){
                                $('.product-list').show();
                            }                           
                    }, 'json');
                    }
                }
            });
        }else{//not weixin
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position){//get location success
                    $.post('/report/gencodeAjax', {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    }, function(data) {
                        renderProduct(data);
                        if(locationContainer.css("display")=="none"){
                            $('.product-list').show();
                        } 
                    }, 'json');
                }, function(data){//get location false
                    $.post('/report/gencodeAjax', function(data) {
                        renderProduct(data);
                        if(locationContainer.css("display")=="none"){
                            $('.product-list').show();
                        } 
                    }, 'json');
                    // getLocationError();
                })
            }else{
                // getLocationError();
                $.post('/report/gencodeAjax', function(data) {
                    renderProduct(data);
                    if(locationContainer.css("display")=="none"){
                        $('.product-list').show();
                    } 
                }, 'json');
            }
        }
    }else{//is PC
        // renderProduct($('body').data('iptocity'));
        $.post('/report/gencodeAjax', function(data) {
            renderProduct(data);
            if(locationContainer.css("display")=="none"){
                $('.product-list').show();
            } 
        }, 'json');//测试有
    }
}
function getLocationError(){
    // btnLocation.children(".icon-refresh").show();
    btnLocation.find('span').html('无法获取当前位置信息').next().addClass("btn-getlocation").text('重新定位');
}

function isMobile() {
    var userAgentInfo = navigator.userAgent;
    var Agents = new Array("Android", "iPhone", "SymbianOS", "Windows Phone", "iPad", "iPod");  
    var flag = false;  
    for (var v = 0; v < Agents.length; v++) {  
       if (userAgentInfo.indexOf(Agents[v]) > 0) { flag = true; break; }  
    }
    return flag;
}

function isWechat(){  
    var ua = navigator.userAgent.toLowerCase();  
    if(ua.match(/MicroMessenger/i)=="micromessenger") {  
        return true;  
    } else {
        return false;  
    }  
} 


