var btnSubmit = $('#btn-submit');

btnSubmit.on('click', function(e){
    e.preventDefault(); 
	var _this = $(this);
	if (_this.hasClass('disabled')) {
		return;
	};
	_this.addClass('disabled');
	var tradeCode = $.trim($('input[name="tradeCode"]').val());
	if (!tradeCode) {
		pop.error('身份验证码不能为空');
		_this.removeClass('disabled');
		return;
	};
	if(tradeCode.length != 6){
		pop.error('身份验证码有误');
		_this.removeClass('disabled');
		return;
	}
	pop.loading('处理中...');
    $.post('/credit/checkTradeCodeAjax',
    	{
    		tradeCode : tradeCode
    	}, 
    	function(data){
    		if (data.error != 0) {
    			pop.clear();
    			pop.error(data.msg);
    			_this.removeClass('disabled');
    			return;
    		};
    		$('<form action="/credit/viewreport" method="POST"><input type="hidden" name="tradeCode" value="' + tradeCode + '"/></form>').submit();
    	},
	'json')
})
