/**
 * messager 提供confirm、alert等方法
 */
(function($){
//	$.fn.messager = function(options) {
//		var setting = $.extend({
//			
//		}, options);
//		
//		var methods = {
//			confirm: function(options) {
//				
//			},
//			alert: function(options) {
//				
//			}
//		};
//		
//		
//	}
var confirm_panel_id = 'messager-modal-confirm-panel';
var confirm_btn_cancel = 'messager-modal-cancel-btn';
var confirm_btn_confirm = 'messager-modal-confirm-btn';
var confirm_panel = '<div id="'+confirm_panel_id+'" class="modal hide fade" tabindex="-1" data-focus-on="input:first">';
confirm_panel += '<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button><h3>Stack One</h3></div>'
confirm_panel += '<div class="modal-body"></div>'
confirm_panel += '<div class="modal-footer"><button type="button" data-dismiss="modal" class="btn" id="'+confirm_btn_cancel+'">取消</button><button type="button" id="'+confirm_btn_confirm+'" class="btn red">确认</button></div>'
confirm_panel += '</div>'
	
var alert_id = 'messager-modal-alert-panel';
var alert_btn = 'messager-modal-alert-btn';
var alert_cancel_cls = 'messager-modal-cancel-cls';
var alert_panel = '<div id="'+alert_id+'" class="modal hide fade" tabindex="-1" data-focus-on="input:first">';
alert_panel += '<div class="modal-header"><button type="button" class="close '+alert_cancel_cls+'" data-dismiss="modal" aria-hidden="true"></button><h3>Stack One</h3></div>'
alert_panel += '<div class="modal-body"></div>'
alert_panel += '<div class="modal-footer"><button type="button"  data-dismiss="modal" id="'+alert_btn+'" class="btn red">确定</button></div>'
alert_panel += '</div>'
	
var confirm = function(title, body, cb) {
	$('#'+confirm_panel_id).remove();
	$(confirm_panel).appendTo('body');
	$('#'+confirm_panel_id+' .modal-header h3').text(title);
	$('#'+confirm_panel_id+' .modal-body').text(body)
	var s = $('#'+confirm_panel_id);
	s.modal();
	setTimeout(function(){
		s.css('margin-top', '');	//@todo 解决modal位置问题
	}, 200)
	
	$('#'+confirm_btn_cancel).click(function(){
		cb(false);
	})
	
	$('#'+confirm_btn_confirm).click(function(){
		s.modal('hide');
		cb(true);
	})
}

var confirm2 = function(options) {
	var setting = $.extend({
		title: '',
		body: '',
		btn_1: '取消',
		btn_2: '确定'
	}, options)
	
	$('#'+confirm_panel_id).remove();
	$(confirm_panel).appendTo('body');
	$('#'+confirm_panel_id+' .modal-header h3').text(setting.title);
	$('#'+confirm_panel_id+' .modal-body').text(setting.body)
	var s = $('#'+confirm_panel_id);
	s.modal();
	setTimeout(function(){
		s.css('margin-top', '');	//@todo 解决modal位置问题
	}, 200)
	
	$('#'+confirm_btn_cancel).text(setting.btn_1).click(function(){
		setting.cb(false);
	})
	
	$('#'+confirm_btn_confirm).text(setting.btn_2).click(function(){
		s.modal('hide');
		setting.cb(true);
	})
}

var alert = function(title, body, cb) {
	$('#'+alert_id).remove();
	$(alert_panel).appendTo('body');
	$('#'+alert_id+' .modal-header h3').text(title);
	$('#'+alert_id+' .modal-body').text(body)
	var s = $('#'+alert_id);
	s.modal();
	setTimeout(function(){
		s.css('margin-top', '');	//@todo 解决modal位置问题
	}, 200)
	
	if (typeof cb == 'function') {
		$('#'+alert_btn+',.'+alert_cancel_cls).click(function(){
			cb();
		})
	}
}
	
$.messager = {
	confirm: confirm, 
	confirm2: confirm2,
	alert: alert
}
})(jQuery);