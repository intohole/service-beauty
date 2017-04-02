(function(){
	var form = $('.form');

	form.find('li').each(function(){
		var _this = $(this),
			iconClose = $('<i class="icon-close"></i>'),
			formExtra = _this.find('.form-extra');
		if (formExtra.length > 0) {
			iconClose.css({'right': (formExtra.width() / parseInt($('html').css('font-size')) + 1.5) + 'rem'})
		};
		_this.append(iconClose);
	})

	form.find('.icon-close').on('touchstart', function(event) {
		event.preventDefault();
		var _this = $(this),
			broInput = _this.prev().hasClass('input-normal') ? _this.prev() : _this.prev().prev();
		_this.hide();
		broInput.val('');
	});

	form.find('.input-normal').on('input propertychange', function(event) {
		event.preventDefault();
		var _this = $(this),
			btnClear = _this.next().hasClass('icon-close') ? _this.next() : _this.next().next();
		if (!_this.val()) {
			btnClear.hide();
			return;
		};
		btnClear.show();
	})
})()


