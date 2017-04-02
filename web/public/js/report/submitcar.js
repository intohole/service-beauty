var form = $('#report'),
	inputFile = $('.input-file'),
	btnSubmit = $('#btn-submit'),
	iptMoney = $('input[name="money"]'),
	iptMonth = $('input[name="month"]'),
    iptFirstPay = $('#firstpay'),
    iptMonthPay = $('#monthpay'),
    payBackPlan = $('.hide-noplace');

iptMoney.bind('input propertychange', function(){
    calculatePlan()
});

iptMonth.bind('input propertychange', function(){
    calculatePlan()
});

function calculatePlan() {
    var iptMoneyVal = parseFloat(iptMoney.val()),
        iptMonthVal = parseFloat(iptMonth.val());
    if (!iptMoneyVal || !iptMonthVal) {
        iptFirstPay.val('');
        iptMonthPay.val('');
        payBackPlan.stop().animate({height:"0"})
        return;
    };
    iptFirstPay.val(iptMoneyVal * 0.5);//首付=成交价*50%
    iptMonthPay.val(Math.ceil((iptMoneyVal + (iptMoneyVal * 0.0088 * iptMonthVal) - (iptMoneyVal * 0.5)) / iptMonthVal * 10000));//月供=（成交价+利息-首付）/ 月份
    payBackPlan.stop().animate({height:"6rem"});
}

inputFile.each(function(){
	var _this = $(this),
		iconPlus = '<i class="icon-plus"></i> ';
	_this.html5_upload({
        url: '/report/uploadFileAjax',
        sendBoundary: window.FormData || $.browser.mozilla,
        fieldName:'upload_img',
        onStart: function(event, total) {
        	_this.prev().text('上传中...');
        	return true;
        },
        onFinishOne: function(event, response, name, number, total) {
            var data = $.parseJSON(response);
        	if (data.error != '0') {
        		pop.error(data.errmsg);
        		return;
        	}
        	data = data.data;
            _this.prev().html(iconPlus + '重新上传');
	    	var broImg = _this.prev().prev();
	    	broImg.attr('src', data.url);
        	_this.parent().next().val(data.id);
        },
        onError: function(event, name, error) {
        	pop.error('图片上传失败');
        }
    });
})

btnSubmit.on('click', function(e){
    e.preventDefault(); 
	var _this = $(this);
	if (_this.hasClass('disabled')) {
		return;
	};
	_this.addClass('disabled');
	var money = iptMoney.val();
	if(!money || money == 0){
		pop.error('请填写成交价');
		btnSubmit.removeClass('disabled');
		return false;
	}
	var month = iptMonth.val();
	if(!month || month == 0){
		pop.error('请填写期限');
		btnSubmit.removeClass('disabled');
		return false;
	}
	var idCardVal = $('input[name="idcard"]').val();
	if (!idCardVal) {
		pop.error('购车人身份证未上传');
		btnSubmit.removeClass('disabled');
		return false;
	};
	var carCardVal = $('input[name="carcard"]').val();
	if (!carCardVal) {
		pop.error('车身正面照未上传');
		btnSubmit.removeClass('disabled');
		return false;
	};
	submitReport();
})

function submitReport(){
	form.attr('action','/report/submitCarAjax').attr('method','post').submit();
}

