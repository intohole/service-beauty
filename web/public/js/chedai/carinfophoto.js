$(function(){
	var data = ["左前","右前","车身","左前灯","发动机","侧面","车门操作","仪表盘","内饰","前排中控","后尾灯","后备箱"];
	var contents = "";

	$.post('/chedai/salesman_carinfophotoajax', {
			'id': $('body').data('id'),
			}, function(data){
			if (data.error != 0) {
				pop.clear();
				pop.error(data.msg);
				_this.removeClass('disabled');
				return;
			}
			for(var i=0;i<12;i++){
				contents+= '<li class="img-uploader">\
			            <div class="img-uploader-box">\
			                <img src="" alt="'+data.src+'" data-src="">\
			            </div>\
			            <p class="tac">'+data.name+'</p></li>'
			}
			$(".form").append(contents);
		}, 'json')


	
});
