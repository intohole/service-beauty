var form = $('#signin'),
	btnSubmit = $('#btn-submit');
form.on('submit', function(e) {
    e.preventDefault(); // prevent native submit
    pop.loading('处理中');
	console.log('submit')
    $(this).ajaxSubmit({
        type: 'post',
        url: '/report/signinAjax',
        dataType: 'json',
        success: function (data) {
        	pop.remove();
        	if (data.error != 0) {
        		pop.error(data.res);
        		btnSubmit.removeClass('disabled');
        		return;
        	};
        	location.href = '/report/';
        },
        error: function (msg) {
        	btnSubmit.removeClass('disabled');
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
		return false;
	};
	var password = $('input[name="passwd"]').val();
	if(!password){
		pop.error('密码不能为空');
		btnSubmit.removeClass('disabled');
		return false;
	}
    form.submit();
})

function checkPhone(obj){
	var phone = $('input[name="phone"]').val();
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
