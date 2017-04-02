var btnSubmit = $('#btn-submit'),
	oToken = $('input[name="org.apache.struts.taglib.html.TOKEN"]'),
	btnSendSms = $('#send-sms');

btnSubmit.on('click', function(e){
    e.preventDefault(); 
	var _this = $(this);
	if (_this.hasClass('disabled')) {
		return;
	};
	_this.addClass('disabled');
	var name = $.trim($('input[name="userInfoVO.loginName"]').val());
	if (!name) {
		pop.error('登录名不能为空');
		_this.removeClass('disabled');
		return;
	};
	if(name.length < 6){
		pop.error('登录名不能小于6位');
		_this.removeClass('disabled');
		return;
	}
	if(name.length > 16){
		pop.error('登录名不能大于16位');
		_this.removeClass('disabled');
		return;
	}
	var nameReg = new RegExp("^[a-zA-Z0-9\-_/]*$");
	if(!nameReg.test(name)){
		pop.error('登录名应由字母、数字、_、-、/组成');
		_this.removeClass('disabled');
		return;
	}
	var password = $.trim($('input[name="userInfoVO.password"]').val());
	if (!password) {
		pop.error('密码不能为空');
		_this.removeClass('disabled');
		return;
	}
	if(password.length < 6){
		pop.error('密码不能小于6位');
		_this.removeClass('disabled');
		return;
	}
	if(password.length > 20){
		pop.error('密码不能大于20位');
		_this.removeClass('disabled');
		return;
	}
	var pwdReg = /^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]+$/;
	if(!pwdReg.test(password)){
		pop.error('密码只能使用数字和字母，且必须同时包含数字和字母');
		_this.removeClass('disabled');
		return;
	}
	var cfpwd = $.trim($('input[name="userInfoVO.confirmpassword"]').val());
	if (!cfpwd) {
		pop.error('确认密码不能为空');
		_this.removeClass('disabled');
		return;
	}
	if (password != cfpwd) {
		pop.error('确认密码和密码不一致');
		_this.removeClass('disabled');
		return;
	}
	var email = $.trim($('input[name="userInfoVO.email"]').val());
	var emailReg = /^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-])+/;
	if(email && !emailReg.test(email)){
		pop.error('邮箱地址是无效的');
		_this.removeClass('disabled');
		return;
	}
	var phone = $.trim($('input[name="userInfoVO.mobileTel"]').val());
	if (!checkPhone(phone)) {
		return;
	}
	var tcId = $('input[name="tcId"]').val();
	if(!tcId){
		pop.error('动态码输入错误');
		_this.removeClass('disabled');
		return;
	}
	var vcode = $.trim($('input[name="userInfoVO.verifyCode"]').val());
	if(!vcode){
		pop.error('动态码不能为空');
		_this.removeClass('disabled');
		return;
	}
	if(vcode.length != 6){
		pop.error('动态码有误');
		_this.removeClass('disabled');
		return;
	}
	var vcodeReg = new RegExp("^[a-zA-Z0-9\-_/]*$");
	if(!vcodeReg.test(vcode)){
		pop.error('动态码输入错误');
		_this.removeClass('disabled');
		return;
	}
	pop.loading('处理中...');
    $.post('/credit/initregAjax',
    	{
    		'loginname' : name,
    		'password' : password,
    		'confirmpassword' : cfpwd,
    		'email' : email,
    		'phone' : phone,
    		'verifycode' : vcode,
    		'html_token' : oToken.val(),
    		counttime : $('#counttime').val(),
    		tcid : $('#tcId').val()
    	}, function(data){
			pop.clear();
    		if (data.error != 0) {
    			pop.error(data.msg);
    			oToken.val(data.res);
    			_this.removeClass('disabled');
    			return;
    		};
    		$('#register2').addClass('dpn');
    		$('#register3').removeClass('dpn');
    		$('.register-progress span').eq(1).removeClass('active').next().addClass('active');
    	},
	'json')
})

function checkPhone(phone){
	if (!phone) {
		pop.error('手机号不能为空');
		btnSubmit.removeClass('disabled');
		return false;
	}
	var phoneReg = /^1\d{10}$/;
	if(!phoneReg.test(phone)){
		pop.error('手机号码是无效的');
		btnSubmit.removeClass('disabled');
		return false;
	}
	return true;
}

btnSendSms.on('click', function(e){
    e.preventDefault(); 
	var _this = $(this);
	if (_this.hasClass('disabled')) {
		return;
	};
	_this.addClass('disabled');
	var phone = $.trim($('input[name="userInfoVO.mobileTel"]').val());
	if (!checkPhone(phone)) {
		return;
	};
	pop.loading('获取中');
	$.post('/credit/getAcvitavceCodeAjax', {method:"getAcvitaveCode",mobileTel:phone}, function(data) {
    	pop.clear();
    	if (data.error != 0) {
    		_this.removeClass('disabled');
    		pop.error(data.msg);
			oToken.val(data.res);
    		return;
    	};
    	$('#tcId').val(data.res);
		start_sms(_this);
	}, 'json');
})

//短信验证码倒计时
function start_sms(o){
	var count=1;
	var sum=60;
	var i= setInterval(function(){
		if(count==60){
			o.removeClass('disabled').text('获取动态码').css('color','#f90');
			clearInterval(i);
		}else{
			$('#counttime').val(sum-count);
			o.text(''+ parseInt(sum-count) + "s");
		}
		count++;
	},1000);
};
