var tabsIsRender = 0;
//tabs
function tabs(listClassName){
	var tabs = $('.tabs'),
		tabsLi = tabs.find('li'),
		list = $('.' + listClassName);
	if (!tabsIsRender) {
		tabsIsRender = 1;
		tabsLi.css('width', 100 / tabsLi.length + '%') && tabs.fadeIn();
		tabsLi.on('click', function(event) {
			var _this = $(this);
			tabsLi.removeClass('active');
			_this.addClass('active');
			list.hide().eq(_this.index()).fadeIn();
			checkEmptyList(listClassName);
		});
		list.eq(0).fadeIn();
	}
	checkEmptyList(listClassName);
}

function checkEmptyList(listClassName){
	var wrapMore = $('#wrap-more'),
		wrapEmpty = $('#wrap-empty');
	if (wrapMore.length <= 0 || wrapEmpty.length <= 0) {
		return;
	}
	var currentIndex = $('.tabs').find('.active').index(),
		list = $('.' + listClassName);
	if (list.eq(currentIndex).find('.item').length <= 0) {//没有tab获取更多隐藏，暂无记录显示
		wrapMore.addClass('dpn');
		wrapEmpty.removeClass('dpn');
	}else{
		wrapMore.removeClass('dpn');
		wrapEmpty.addClass('dpn');
	}
}