var form = $('#forgotpwd'),
	btnSendSms = $('#send-sms'),
	btnSubmit = $('#btn-submit'),
	iptPhone = $('input[name="phone"]');
form.on('submit', function(e) {
    e.preventDefault(); // prevent native submit
    pop.loading('处理中');
    $(this).ajaxSubmit({
        type: 'post',
        url: '/report/forgotpwdAjax',
        dataType: 'json',
        success: function (data) {
        	pop.remove();
        	if (data.error != 0) {
        		pop.error(data.res);
        		return;
        	};
        	location.href = '/report/signin';
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
	$.post('/report/sendForgotPwdSmsAjax', {phone: iptPhone.val()}, function(data) {
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
