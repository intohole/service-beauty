var btnLocation = $('.location-current'),
    btnGetLocation = $('#btn-getlocation'),
    locationContainer = $('.location-container'),
    listCities = $('.list-cities'),
    productList = $('.product-list');

//产品数据: 
//  城市名:
//      房抵类: [项目1, 项目2, 项目n],
//      车抵类：[项目1, 项目2, 项目n]
//
//房贷项目: [一抵月息, xx]
//车贷项目: [月息, xx]
var productDatas = {
    '北京': {
        'building' : [
            {
                title : '房抵类',
                rateTitle : '一抵押月息：',
                rate : '0.66%',
                subRate : '二抵押: 0.88%　|　转单: 0.88%'
            }
        ],
        'funded' : [
            {
                title : '垫资',
                rateTitle : '渠道利率日息：',
                rate : '8‱',
                subRate : '机构合作日息: 5‱'
            }
        ],
        'car' : [['0.88']]
    },
    '上海': {
        'building' : [
            {
                title : '房抵类',
                rateTitle : '一抵押月息：',
                rate : '0.98%',
                subRate : '二抵押: 0.98%　|　转单: 1.25%'
            }
        ],
        'funded' : [
            {
                title : '垫资',
                rateTitle : '渠道利率日息：',
                rate : '8‱',
                subRate : '机构合作日息: 5‱'
            }
        ],
        'car' : [['0.88']]
    }
}

getUserLocation();//init

btnLocation.eq(0).on('click', function(){
    locationContainer.show();
})

btnGetLocation.on('click', function(){
    btnLocation.find('span').html('定位中...');
    getUserLocation();
    locationContainer.hide();
})

listCities.find('li').on('click', function(){
    renderProduct($(this).text());
    locationContainer.hide();
})

function renderProduct(city){
    btnLocation.find('span').html('当前位置：<strong>' + city + '</strong>');
    var productBuildingHtmlStr = productBuildingHtml(productDatas[city] ? productDatas[city]['building'] : productDatas['北京']['building'])//若无该城市数据，则输出缺省值，北京市的数据
    //var productCarHtmlStr = productCarHtml(productDatas[city] ? productDatas[city]['car'] : productDatas['北京']['car'])//若无该城市数据，则输出缺省值，北京市的数据
    var productFundedHtmlStr = productFundedHtml(productDatas[city] ? productDatas[city]['funded'] : productDatas['北京']['funded']);
    productList.html(productBuildingHtmlStr + productFundedHtmlStr);
}

function productBuildingHtml(datas){
    var html = [];
    for (var i = 0; i < datas.length; i++) {
        html.push('<li class="product-item">\
                <a class="product-desc" href="/report/submithouse">\
                    <h2 class="product-title"><i class="icon-img-building"></i>' + datas[i]['title'] + '</h2>\
                    <div class="product-desc-title">' + datas[i]['rateTitle'] + '</div>\
                    <div class="product-rate">' + datas[i]['rate'] + '</div>\
                    <div class="product-subrate">' + datas[i]['subRate'] + '</div>\
                    <div class="product-tag tac">\
                        <span>30分钟内反馈初审结果</span>\
                        <span>当天放贷</span>\
                        <span>可转单</span>\
                    </div>\
                </a>\
                <a class="product-btn" href="/report/submithouse">我要报单</a>\
            </li>')
    };
    return html.join('');
}

                    // <div class="product-rate">' + datas[i][0] + '%</div>\
function productCarHtml(datas){
    var html = [];
    for (var i = 0; i < datas.length; i++) {
        html.push('<li class="product-item product-car">\
                <a class="product-desc" href="/report/submitcar">\
                    <h2 class="product-title"><i class="icon-img-car"></i>汽车-以租代购</h2>\
                    <div class="product-desc-title">月息：</div>\
                    <div class="product-rate" style="height:2.7rem"><img src="/image/report/waiting.png" style="width: 6rem;margin-bottom: .5rem"></div>\
                    <div class="product-subrate">还款方式：等额本息</div>\
                    <div class="product-tag tac">\
                        <span>10分钟批贷</span>\
                        <span>服务费全返</span>\
                        <span>保费可返</span>\
                    </div>\
                </a>\
                <a class="product-btn" href="/report/submitcar">我要报单</a>\
            </li>')
    };
    return html.join('');
}

function productFundedHtml(datas){
    var html = [];
    for (var i = 0; i < datas.length; i++) {
        html.push('<li class="product-item">\
                <a class="product-desc" href="/report/submitfunded">\
                    <h2 class="product-title"><i class="icon-img-building"></i>' + datas[i]['title'] + '</h2>\
                    <div class="product-desc-title">' + datas[i]['rateTitle'] + '</div>\
                    <div class="product-rate">' + datas[i]['rate'] + '</div>\
                    <div class="product-subrate">' + datas[i]['subRate'] + '</div>\
                    <div class="product-tag tac">\
                        <span>30分钟内反馈初审结果</span>\
                        <span>当天放贷</span>\
                    </div>\
                </a>\
                <a class="product-btn" href="/report/submitfunded">我要报单</a>\
            </li>')
    };
    return html.join('');
}

function getUserLocation(){
    if (isMobile()) {
        if (isWechat()) {
            wechat({
                shareDatas : {
                   title: '邀你赚快钱来不来，不懂报单二字不要点',
                   desc: '一抵月息0.66%，来看行业最高返费，邀请同行每报1单，20元送你！一般人我不告诉他！',
                   imgUrl : 'https://mmbiz.qlogo.cn/mmbiz/nzhlVibVGiaaI2cPqcXeW6ibukiadgzezhvkkg2ibLlMIZZbLoiaoXUBibjkF1EWHGQlHRPxmdcdmYsR1OGePIVm9a0Ug/0?wx_fmt=jpeg'
                },
                getLocation : function(res, successCode){
                    if (successCode == 1) {
                        $.post('/report/gencodeAjax', {
                            lat: res.latitude,
                            lng: res.longitude
                        }, function(data) {
                            renderProduct(data.msg);
                        }, 'json');
                    }else{
                        getLocationError();
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
                        renderProduct(data.msg)
                    }, 'json');
                }, function(){//get location false
                    getLocationError();
                })
            }else{
                getLocationError();
            }
        }
    }else{//is PC
        renderProduct($('body').data('iptocity'));
    }
}

function getLocationError(){
    btnLocation.find('span').html('无法获取当前位置信息').next().text('重新获取');
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

