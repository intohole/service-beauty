var btnSubmit = $('#btn-submit'),
	oToken = $('input[name="org.apache.struts.taglib.html.TOKEN"]'),
	oDate = $('input[name="date"]'),
	oVcodeImg = $('#vcode-img');

btnSubmit.on('click', function(e){
    e.preventDefault(); 
	var _this = $(this);
	if (_this.hasClass('disabled')) {
		return;
	};
	_this.addClass('disabled');
	var name = $.trim($('input[name="loginname"]').val());
	if (!name) {
		pop.error('登录名不能为空');
		_this.removeClass('disabled');
		return;
	};
	var password = $.trim($('input[name="password"]').val());
	if (!password) {
		pop.error('密码不能为空');
		_this.removeClass('disabled');
		return;
	}
	var vcode = $.trim($('input[name="_@IMGRC@_"]').val());
	if(!vcode){
		pop.error('验证码不能为空');
		_this.removeClass('disabled');
		return;
	}
	if(vcode.length != 6){
		pop.error('验证码有误');
		_this.removeClass('disabled');
		return;
	}
	var date = $.trim(oDate.val());
	pop.loading('登录中...');
    $.post('/credit/loginAjax',
    	{
    		loginname : name,
    		password : password,
    		imgrc : vcode,
    		date : date,
    		html_token : oToken.val()
    	}, function(data){
    		if (data.error != 0) {
    			pop.clear();
    			pop.error(data.msg);
    			oDate.val(data.res.date);
    			//oToken.val(data.res.token);
    			changeCode();
    			_this.removeClass('disabled');
    			return;
    		};
    		location.href = '/credit/menu';
    	},
	'json')
})

oVcodeImg.on('click', function(){
	changeCode();
})

function changeCode(){
	oVcodeImg.parent().prev().val('');
	oVcodeImg.attr('src', '/credit/imgrc?a=' + Math.random());
}