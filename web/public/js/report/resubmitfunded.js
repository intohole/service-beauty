var form = $('#report'),
	inputFile = $('.input-file'),
	btnSubmit = $('#btn-submit'),
	moreHouseImg = $('.more-house-img'),
	listHouse = $('#list-house');

inputFile.on('change', function () {
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
        	_this.prev().text('上传中...');
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
		            _this.prev().html(iconPlus + '上传成功');
			    	var _thisImg = _this.prev().prev(),
		                _thisMainInput = _this.parent().next();
			    	_thisImg.attr('src', data.url);
		        	_this.parent().next().val(data.id);
		        	if (!_this.hasClass('more-img')) {
		        		return;
		        	}
		            var listHouseLi = listHouse.find('li'),
		                _thisItem = _this.parents('li');
		            _this.removeClass('more-img');
		    		_thisItem.insertBefore(listHouseLi.eq(listHouseLi.length - 1));
		            pop.error(_thisItem.find('p').text() + '上传成功！');
		            moreHouseImg.find('li').length == 0 && moreHouseImg.hide() && listHouseLi.eq(listHouseLi.length - 1).remove();
                } else {
                	_this.prev().html(iconPlus + '重新上传');
                    pop.error('上传失败')
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
    e.preventDefault(); 
	var _this = $(this);
	if (_this.hasClass('disabled')) {
		return;
	};
	_this.addClass('disabled');
	
	var money = $('input[name="tail_money"]').val();
	var old_money = $('input[name="old_money"]').val();
	if(!money){
		pop.error('请填写预借金额');
		btnSubmit.removeClass('disabled');
		return false;
	};
	
	if(money == old_money){
		pop.error('预借金额没有修改,请重新填写');
		btnSubmit.removeClass('disabled');
		return false;
	}
	
	submitReport();
})

function submitReport(){
	form.attr('action','/report/resubmitFundedAjax').attr('method','post').submit();
}

$('#btn-more-img').click(function(){
	moreHouseImg.show();
})
$('#btn-more-img-confirm').click(function(){
	location.hash.indexOf('#more-img') > -1 && history.go(-1);
	moreHouseImg.hide();
})

function closeMoreImgLayer(){
	moreHouseImg.hide();
}