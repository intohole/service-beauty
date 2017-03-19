var form = $('#register'),
	btnSendSms = $('#send-sms'),
	btnSubmit = $('#btn-submit'),
	iptPhone = $('input[name="phone"]'),
	btnLocation = $('#change-location'),
    btnGetLocation = $('#btn-getlocation'),
    locationContainer = $('.location-container'),
    listCities = $('.list-cities'),
    listCities_all=$('.wrapper-list-cities'),
    iptCity = $('#city');

form.on('submit', function(e) {
    e.preventDefault(); // prevent native submit
    pop.loading('处理中');
    var area_id = $('#city').attr('name');
    $(this).ajaxSubmit({
        type: 'post',
        url: '/report/registerAjax',
        dataType: 'json',
        data:{'area_id':area_id},
        success: function (data) {
        	pop.remove();
        	if (data.error != 0) {
        		pop.error(data.res);
        		return;
        	};
        	location.href = '/report';
        },
        error: function (msg) {
        	pop.remove();
            pop.error("网络错误");    
        }
    })
});

btnSubmit.on('click', function(e){
    e.preventDefault(); 
	var _this = $(this);
	if (_this.hasClass('disabled')) {
		return;
	};
	_this.addClass('disabled');
	if (!checkPhone(_this)) {	
		return;
	};
	var cityName = $('#city').val();
	if(!cityName){
		pop.error('请定位或选择您的业务范围');
		btnSubmit.removeClass('disabled');
		return;
	}
	// if(cityName != '北京' && cityName != '上海'){
	// 	pop.error('该业务地区暂不支持，请选择其他地区');
	// 	btnSubmit.removeClass('disabled');
	// 	return;
	// }
	var smsCode = $('input[name="smscode"]').val();
	if(!smsCode){
		pop.error('请填写短信验证码');
		btnSubmit.removeClass('disabled');
		return;
	}
	if(smsCode.length != 4){
		pop.error('短信验证码有误');
		btnSubmit.removeClass('disabled');
		return;
	}
	var password = $('input[name="passwd"]').val();
	if(!password){
		pop.error('请设置密码');
		btnSubmit.removeClass('disabled');
		return;
	}
	if(password.length < 6){
		pop.error('密码不能少于6位');
		btnSubmit.removeClass('disabled');
		return;
	}
	var realName = $('input[name="realname"]').val();
	if(!realName){
		pop.error('用户姓名不能为空');
		btnSubmit.removeClass('disabled');
		return;
	}else{
		var reg = /^([\u4E00-\u9FA5])+$/;
		var yesorno = realName.match(reg) != null;
		if(!yesorno){
			pop.error('请输入汉字姓名');
			btnSubmit.removeClass('disabled');
			return;
		}
	}
    form.submit();
})

btnSendSms.on('click', function(e){
    e.preventDefault(); 
	var _this = $(this);
	if (_this.hasClass('disabled')) {
		return;
	};
	_this.addClass('disabled');
	if (!checkPhone(_this)) {
		return;
	};
	pop.loading('获取中');
	$.post('/report/sendRegSmsAjax', {phone: iptPhone.val()}, function(data) {
    	pop.remove();
    	if (data.error != 0) {
    		_this.removeClass('disabled');
    		pop.error(data.res);
    		return;
    	};
		start_sms(_this);
	}, 'json');
})

function checkPhone(obj){
	var phone = iptPhone.val();
	if (!phone) {
		pop.error('手机号不能为空');
		obj.removeClass('disabled');
		return false;
	};
	var par = /^1[3,4,5,7,8]\d{9}$/;
	if(!par.test(phone)){
		pop.error('请输入正确的手机号');
		obj.removeClass('disabled');
		return false;
	}
	return true;
}

//短信验证码倒计时
function start_sms(o){
	var count=1;
	var sum=60;
	var i= setInterval(function(){
		if(count==60){
			o.removeClass('disabled').text('获取验证码').css('color','#f90');
			clearInterval(i);
		}else{
			o.text(''+ parseInt(sum-count) + "s");
		}
		count++;
	},1000);
};

getUserLocation();//init

btnLocation.on('click', function(){
    iptCity.val('').attr('placeholder', '定位中...');
    btnLocation.text('更换');
    locationContainer.show();
})

btnGetLocation.on('click', function(){
    getUserLocation();
    locationContainer.hide();
})

// listCities.find('li').on('click', function(){
//     renderProduct($(this).text());
//     locationContainer.hide();
// })

listCities_all.on("click","li",function(city){
	    locationContainer.hide();
	    var html = $(this).html();
	    var id=$(this).attr("name");
	    $("#city").attr('name',id);
	    // $.post("/report/areaAjax",{"area_id":id},function(data){
	    //     renderProduct(data);
	    //     $('.product-list').show();
	    // },'json');
	    iptCity.val(html);
})

listCities.on("click","li",function(){
    locationContainer.hide();
	var html = $(this).html();
	var id=$(this).attr("name");
	$("#city").attr('name',id);
	// $.post("/report/areaAjax",{"area_id":id},function(data){
	//     renderProduct(data);
	//     $('.product-list').show();
	// },'json');
	iptCity.val(html);
})

function renderProduct(city){
	iptCity.val(city.msg.dzmessage.areaname);
	iptCity.attr("name",city.msg.dzmessage.area_id);
    $('.location-current').find('span').html('当前位置：<strong>' + city.msg.dzmessage.areaname + '</strong>');
    if(city.msg.allcity){
        productcitylist(city);
        producthotcitylist(city);
    }
    
}

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
                getLocation : function(res, successCode){
                    if (successCode == 1) {
                        $.post('/report/gencodeAjax', {
                            lat: res.latitude,
                            lng: res.longitude
                        }, function(data) {
                        	renderProduct(data);
                        }, 'json');
                    }else{
                        // getLocationError();
                        $.post('/report/gencodeAjax', function(data) {
	                        renderProduct(data);
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
                    }, 'json');
                }, function(){//get location false
                    // getLocationError();
                    $.post('/report/gencodeAjax', function(data) {
                        renderProduct(data);
                    }, 'json');
                })
            }else{
                // getLocationError();
                $.post('/report/gencodeAjax', function(data) {
	                renderProduct(data);
	            }, 'json');
            }
        }
    }else{//is PC
    	// renderProduct($('body').data('iptocity'));
    	$.post('/report/gencodeAjax', function(data) {
	        renderProduct(data);
	    }, 'json');
    }
}

function getLocationError(){
    iptCity.val('').attr('placeholder', '无法获取当前位置信息');
    btnLocation.text('重新获取');
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

