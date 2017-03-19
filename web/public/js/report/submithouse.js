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
        	// _this.prev().text('上传中...');
            return rst;
        })

        .then(function (rst) {
            // upload
            _this.removeClass('disabled');
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
$(function(){
	$(".housetype_change").on("change","input",function(){
		var n=$(this).val();
		var add_html='<li class="single_add clearfix" >\
				            <label class="label-normal">原银行尾款</label>\
				            <input class="input-normal" type="tel" maxlength="6" placeholder="填写原银行尾款" name="payment">\
				            <div class="form-extra">\
				                万元\
				            </div>\
				        </li>';
		console.log(n);
		if($(".single_add")){
			$(".single_add").remove();
		}
		if(n==2||n==5){
			$(".housetype_change").after(add_html);
		}
	})
})

btnSubmit.on('click', function(e){
    e.preventDefault(); 
	var _this = $(this);
	if (_this.hasClass('disabled')) {
		return;
	};
	_this.addClass('disabled');
	var name = $('input[name="name"]').val().trim();
	if(area_id !=3){
		if(!name){
			pop.error('请填写借款人姓名');
			btnSubmit.removeClass('disabled');
			return false;
		}
	}
	var money = $('input[name="money"]').val().trim();
	if(!money){
		pop.error('请填写申贷金额');
		btnSubmit.removeClass('disabled');
		return false;
	}
	
	/* var term = $('input[name="term"]').val().trim();
	if(!term){
		pop.error('请填写申贷期限');
		btnSubmit.removeClass('disabled');
		return false;
	};
	
	if(term<0 || term>255){
		pop.error('申贷期限填写0-255之间的数字');
		btnSubmit.removeClass('disabled');
		return false;
	}; */
	
	var houseType = $('.radio-group input:radio:checked').val().trim();
	if(!houseType){
		pop.error('请选择房产抵押类型');
		btnSubmit.removeClass('disabled');
		return false;
	}
	if(houseType==2||houseType==5){
		var payment = $('input[name="payment"]').val().trim();
		if(!payment){
			pop.error('请填写原银行尾款');
			btnSubmit.removeClass('disabled');
			return false;
		}
	}
	var idCardFrontVal = $('input[name="idcardfront"]').val().trim();
	if(area_id !=3){
		if (!idCardFrontVal) {
			pop.error('身份证正面照未上传');
			btnSubmit.removeClass('disabled');
			return false;
		};
	}
	var idCardBackVal = $('input[name="idcardback"]').val().trim();
	if(area_id !=3){
		if (!idCardBackVal) {
			pop.error('身份证反面照未上传');
			btnSubmit.removeClass('disabled');
			return false;
		};
	}
	var houseVal = $('input[name="house"]').val().trim();
	var houseFirstVal = $('input[name="housefirst"]').val().trim();
	var houseTableVal = $('input[name="housetable"]').val().trim();
	var housePlanVal = $('input[name="houseplan"]').val().trim();
	if (!houseVal && !houseFirstVal && !houseTableVal && !housePlanVal) {
		pop.error('房产证照片至少上传一张');
		btnSubmit.removeClass('disabled');
		return false;
	};
	submitReport();
})

function submitReport(){
	form.attr('action','/report/submitHouseAjax').attr('method','post').submit();
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