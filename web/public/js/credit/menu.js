var btnGetReport = $('#btn-get-report'),
	btnApplyReport = $('#btn-apply-report'),
	hasReport = parseInt($('body').attr('data-hasReport'));

hasReport && btnGetReport.removeClass('btn-disabled');
btnGetReport.on('click', function(){
	if (hasReport) {
		location.href = '/credit/queryReport';
	}else{
		pop.error('您尚未申请信用信息，请申请后再获取')
	}
})

btnApplyReport.on('click', function(e){
    e.preventDefault(); 
	var _this = $(this);
	if (_this.hasClass('disabled')) {
		return;
	};
	_this.addClass('disabled');
	pop.loading('处理中...');
    $.post('/credit/applicationreportAjax',
    	function(data){
    		if (data.error != 0) {
    			pop.clear();
    			_this.removeClass('disabled');
    			if (data.error == 101) {
    				pop.error('目前系统尚未收录足够的信息对您的身份进行问题验证');
    				return;
    			}
    			if (data.error == 102) {
    				pop.confirm('温馨提示',
    					'您的个人信用信息产品已存在，请点击\"获取信用信息\"查看。若继续申请查询，现有的个人信用信息产品将不再保留，是否继续？',
    					{btnName : ['继续', '取消'], className: 'animated fadeIn'},
    					function(res){
							res ? $('<form action="/credit/checkishasreport" method="POST"><input type="hidden" name="apache_token" value="' + data.res + '"/></form>').submit() : '';
						}
					)
    				return;
    			}
    			if (data.error == 190) {
    				pop.error('登录超时，请重新登录', function(){
    					location.href = '/credit/login'
    				});
    				return;
    			}
    			pop.error(data.msg);
    			return;
    		};
    		$('<form action="/credit/checkishasreport" method="POST"><input type="hidden" name="apache_token" value="' + data.res + '"/></form>').submit()
    	},
	'json')
})

