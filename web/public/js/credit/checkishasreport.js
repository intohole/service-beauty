var wrapQuestion = $('.question'),
	questionItem = wrapQuestion.find('li'),
	radio_type1 = $('.radio_type1'),
	btnSubmit = $('#btn-submit');

// questionItem.eq(questionItem.length - 1).remove();
radio_type1.each(function(index, el) {
	var _this = $(this);
	_this.next().on('click', function(){
		_this.click();
	})
});

function setValue(no, obj) {
	document.getElementsByName('kbaList[' + no + '].answerresult')[0].value = obj.value;
}

function checkKBAHasAnswered() {
	var questionNum = questionItem.length - 1;
	for (var i = 0; i < questionNum; i++) {
		var result = document.getElementsByName('kbaList[' + i + '].answerresult')[0].value;
	    if (result == null || result.length == 0) {
	        pop.error('必须对所有的题作答！');
	        return false;
	    }
	}
	return true;
}

function setLimitTime(id, min, sec) {
	if (min <= 0 && sec <= 0) {
		document.getElementById(id).innerHTML = '0秒';
		kbaloginOff();
	} else if (min > 0 && sec >= 0) {
		document.getElementById(id).innerHTML = min + '分' + sec + '秒';
		sec = sec - 1;
		window.setTimeout("setLimitTime('"+ id + "'," + min + "," + sec +")", 1000);
	} else if (min > 0 && sec < 0) {
		min = min - 1;
		if(min == 0) {
			document.getElementById(id).innerHTML = "59秒";
		} else {
			document.getElementById(id).innerHTML = min + "分59秒";
		}
		window.setTimeout("setLimitTime('"+ id + "'," + min + ",58)", 1000);
	} else if (min == 0 && sec > 0) {
		document.getElementById(id).innerHTML = sec + "秒";
		sec = sec - 1
		window.setTimeout("setLimitTime('"+ id + "',0," + sec +")", 1000);
	} else {
		kbaloginOff();
	}
}
setLimitTime("limitTime", 10, 0)

function kbaloginOff(){
	pop.alert('温馨提示', '身份验证已超时，请重新申请', {btnName: '重新申请', className: 'animated fadeIn'}, function(){
		location.href = '/credit/menu'
	})
	btnSubmit.addClass('btn-disabled').text('已超时');
}

btnSubmit.on('click', function(e){
    e.preventDefault(); 
	var _this = $(this);
	if (_this.hasClass('btn-disabled')) {
		return;
	};
	_this.addClass('btn-disabled');
	if(!checkKBAHasAnswered()){
		_this.removeClass('btn-disabled');
		return;
	}
	document.getElementsByTagName("form")[0].action='/credit/submitkba';
	document.getElementsByTagName("form")[0].submit();
	_this.text('处理中...');
})
