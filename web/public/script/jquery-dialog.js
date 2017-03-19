(function($){
	var modal = '<div id="dialog-modal-panel" class="modal hide fade" tabindex="-1" style="position:fixed;left:50%">';
	modal += '<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button><h3>Stack One</h3></div>'
	modal += '<div class="modal-body"></div>'
	modal += '</div>'
	var modal_panel_id = 'dialog-modal-panel';

	function IsMobile() {  
		var userAgentInfo = navigator.userAgent;  
		var Agents = new Array("Android", "iPhone", "SymbianOS", "Windows Phone", "iPad", "iPod");  
		var flag = false;  
		for (var v = 0; v < Agents.length; v++) {  
		   if (userAgentInfo.indexOf(Agents[v]) > 0) { flag = true; break; }  
		}
		return flag;
	}

	function IsIphone() {  
		var userAgentInfo = navigator.userAgent;  
		var flag = false;  
	    if (userAgentInfo.indexOf('iPhone') > 0) { flag = true; }  
		return flag;
	}

	var methods = {
		open: function(options) {
			$('#'+modal_panel_id).modal('hide').remove();
			$(modal).appendTo('body');
			var s = $('#'+modal_panel_id);
			
			var setting = $.extend({
				title: '',
				height: 'auto',
				href: ''
			}, options);
			
			$('#'+modal_panel_id+' .modal-header h3').text(setting.title)
			
			setTimeout(function(){
				$('#'+modal_panel_id+' .modal-body').load(setting.href, '', function(){
					s.modal({
						'min-width': setting.width,
						height: setting.height
					});
					setTimeout(function(){
						s.css('margin-top', '');	//@todo 解决modal位置问题
						IsMobile() && $('#dialog-modal-panel').css('margin-left', -$('#dialog-modal-panel').width())
						IsIphone() && $('#dialog-modal-panel').css({'left': 10, 'top': 200})
					}, 200)
				})
			}, 500);
		}, 
		close: function() {
			$('#'+modal_panel_id).modal('hide');
		}
	};
	
	$.fn.dialog = function(options, exOptions) {
		if (typeof options == 'string' && options == 'close') {
			methods.close(exOptions);
		} else {
			methods.open(options);
		}
	}
})(jQuery)