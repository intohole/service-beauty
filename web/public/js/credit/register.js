var btnSubmit = $('#btn-submit'),
	oToken = $('input[name="org.apache.struts.taglib.html.TOKEN"]'),
	oVcodeImg = $('#vcode-img');

btnSubmit.on('click', function(e){
    e.preventDefault(); 
	var _this = $(this);
	if (_this.hasClass('disabled')) {
		return;
	};
	_this.addClass('disabled');
	var name = $.trim($('input[name="userInfoVO.name"]').val());
	if (!name) {
		pop.error('姓名不能为空');
		_this.removeClass('disabled');
		return;
	};
	var par = new RegExp("[\u4e00-\u9fa5]");
	if(!par.test(name)){
		pop.error('姓名必须为中文');
		_this.removeClass('disabled');
		return;
	}
	var certNo = $.trim($('input[name="userInfoVO.certNo"]').val());
	if (!certNo) {
		pop.error('身份证号不能为空');
		_this.removeClass('disabled');
		return;
	}
	if (!IdCardValidate(certNo)) {
		pop.error('您的身份证号有误');
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
	var agree = $('input[name="agree"]');
	if(!agree.is(':checked')){
		pop.error('您尚未同意服务协议');
		_this.removeClass('disabled');
		return;
	}
	pop.loading('处理中...');
    $.post('/credit/registerAjax',
    	{
    		'name' : name,
    		'certno' : certNo,
    		'imgrc' : vcode,
    		'agree' : 'on',
    		'html_token' : oToken.val()
    	}, function(data){
    		if (data.error != 0) {
    			pop.clear();
    			pop.error(data.msg);
    			oToken.val(data.res);
    			changeCode();
    			_this.removeClass('disabled');
    			return;
    		};
    		$('<form action="/credit/initreg" method="POST"><input type="hidden" name="token" value="' + data.res + '"/></form>').submit();
    	},
	'json')
})

oVcodeImg.on('click', function(){
	changeCode();
})

function changeCode(){
	oVcodeImg.attr('src', '/credit/imgrc?a=' + Math.random());
}