$(function(){
	var btnBack = $('#next-step1'),
		btnSubmit = $('#next-step3'),
		img_id = [],
		imgNameData = {
			'chetou': '车头',
			'chewei': '车尾',
			'yibiaopan': '仪表盘',
			'zhongkong': '中控',
			'neishi': '内室',
			'zuoqian': '车头斜角左边',
			'youqian': '车头斜角右边',
			'zuohou': '车尾斜角左边',
			'youhou': '车尾斜角右边',
			'chemen': '车门操作',
			'fadongji': '发动机',
			'houbeixiang': '后备箱'
		};

	$('.input-file').on('change', function () {
	    var _this = $(this),
	        iconPlus = '<i class="icon-plus"></i> ';
	    if (_this.hasClass('disabled')) {
	        return;
	    }
	    _this.addClass('disabled');
	    // this.files[0] 是用户选择的文件
	    lrz(this.files[0], {width: 1024, fieldName: 'upload_img'})
	        .then(function (rst) {
	            // onstart
	            // _this.prev().text('上传中...');
	            return rst;
	        })

	        .then(function (rst) {
	            // upload
	            var xhr = new XMLHttpRequest();
	            xhr.open('POST', '/report/uploadFileAjax');

	            xhr.onload = function () {
	                if (xhr.status === 200) {
	                    var data = $.parseJSON(xhr.responseText);
	                    if (data.error != '0') {
	                        pop.error(data.errmsg);
	                        return;
	                    }
	                    data = data.data;
	                    // _this.prev().html(iconPlus + '上传成功');
	                    var _thisImg = _this.prev().prev(),
	                        _thisMainInput = _this.parent().next();
	                    _thisImg.attr('src', data.url).addClass('active');
	                    _thisMainInput.val(data.id);
	                    pop.error(_thisMainInput.next().text() + '上传成功！');
	                } else {
	                    // _this.prev().html(iconPlus + '重新上传');
	                    pop.error('上传失败');
	                }
	            };

	            xhr.onerror = function () {
	                // 处理错误
	            };

	            xhr.upload.onprogress = function (e) {
	                // 上传进度
	                var percentComplete = ((e.loaded / e.total) || 0) * 100;
	            };

	            // 添加参数
	            rst.formData.append('fileLen', rst.fileLen);

	            // 触发上传
	            xhr.send(rst.formData);
	            /* ==================================================== */

	            return rst;
	        })

	        .catch(function (err) {
	            // 万一出错了，这里可以捕捉到错误信息
	            // 而且以上的then都不会执行
	            pop.error(err);
	        })

	        .always(function () {
	            // 不管是成功失败，这里都会执行
	        });
	});
	btnSubmit.on('click', function(e){
		var _this = $(this);
		if (_this.hasClass('disabled')) {
			return;
		}
		_this.addClass('disabled');
		var imgUploader = $(".img-uploader");
		for (var i = 0; i < imgUploader.length; i++) {
			var _this = imgUploader.eq(i);
			var imgVal = _this.find("input[type='hidden']").val();
			var imgName = _this.find("input[type='hidden']").attr('name');
		    // if (!imgVal && (imgName == 'chetou' || imgName == 'chewei' || imgName == 'yibiaopan' || imgName == 'zhongkong' || imgName == 'neishi')) {
		    //     pop.error(imgNameData[imgName] + "照片未上传");
		    //     btnSubmit.removeClass('disabled');
		    //     img_id = [];
		    //     return false;
		    // }
		    img_id[i] = {};
		    img_id[i][imgName] = imgVal;
		}
		console.log(img_id);
		pop.loading('处理中...');
		$.post('/chedai/salesman_carphotoajax', {
			'id': $('body').data('id'),
			'image': img_id
		}, function(data){
			if (data.error != 0) {
				pop.clear();
				pop.error(data.msg);
				_this.removeClass('disabled');
				return;
			}
			location.href = '/chedai/salesman_carinfoconfirm?id=' + data.res;
		}, 'json')
	});

	btnBack.on('click', function(event) {
		event.preventDefault();
		location.href = '/chedai/salesman_caradd?id=' + $('body').data('id');
	});
});
