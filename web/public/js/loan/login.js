var btnSubmit = $('#btn-submit');

btnSubmit.on('click', function(e){
    e.preventDefault(); 
	var _this = $(this);
	if (_this.hasClass('disabled')) {
		return;
	};
	_this.addClass('disabled');
	var name = $.trim($('input[name="loginname"]').val());
	if (!name) {
		pop.error('帐号不能为空');
		_this.removeClass('disabled');
		return;
	};
	var password = $.trim($('input[name="password"]').val());
	if (!password) {
		pop.error('密码不能为空');
		_this.removeClass('disabled');
		return;
	}
	pop.loading('登录中...');
    $.post('/loan/loginAjax',
    	{
    		loginname : name,
    		password : password
    	}, function(data){
    		if (data.error == 0) {
    			// location.href = data.res;
    			location.href = '/loan/index';
    		} else {
    			pop.clear();
    			pop.error(data.msg);
    			_this.removeClass('disabled');
    			return;
    		}
    	},
	'json')
})
