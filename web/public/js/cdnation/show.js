var btnSubmit = $('#btn-submit'),
    body = $('body'),
    courtimg = [];
    borrowimg = [],
    carimg = [],
    btnSubmit.on('click', function(){
    var _this = $(this),
        submitDatas = {};
    if (_this.hasClass('disabled')) {
        return false;
    };
    _this.addClass('disabled');
    //var courtimg = $.trim($('input[name=courtimg]').val());
    //if (!courtimg) {
    var renfa = $('#renfa').val();
    if(!renfa){
        pop.error('人法未填写');
        _this.removeClass('disabled');
         return;
    }
    var renfa = $('#weizhang').val();
    if(!weizhang){
        pop.error('违章未填写');
        _this.removeClass('disabled');
        return;
    }
    // var courtimg = $.trim($('input[name=courtimg]').val());
    console.log(courtimg)
    if (courtimg.length==0) {
        pop.error('人法查询未上传照片');
        _this.removeClass('disabled');
        return;
    }
    // var borrowimg = $.trim($('input[name=borrowimg]').val());
    if (borrowimg.length==0) {
        pop.error('借款人违章查询未上传照片');
        _this.removeClass('disabled');
        return;
    }
    // var carimg = $.trim($('input[name=carimg]').val());
    if (carimg.length==0) {
        pop.error('车辆线上评估值查询未上传照片');
        _this.removeClass('disabled');
        return;
    }
    var  courtimg_ajax = courtimg.join(",");
    var  borrowimg_ajax = borrowimg.join(",");
    var  carimg_ajax = carimg.join(",");
    console.log(carimg)
    submitDatas = {
        courtimg : courtimg_ajax,  //法院网查询截图
        borrowimg: borrowimg_ajax,     //评估价值信息审核结果
        carimg : carimg_ajax,    //征信报告
        renfa: renfa,
        weizhang : weizhang, //流水单
    }
    
    pop.loading('处理中...');
    $.post('/chedai/manager_infoajax',
        submitDatas,
        function(data, textStatus, xhr) {
            pop.clear();
            if (data.error != 0) {
                pop.error(data.msg);
                _this.removeClass('disabled');
                return;
            }
            pop.error('提交成功');
            // location.href = '/chedai/manager_agree?id=' + body.data('id') + '&auditid=' + data.res;
        }, 'json');
})

$(document).on('change', '.input-file',function () {
    var _this = $(this),
        //iconPlus = '<i class="icon-plus"></i> ',
        input_uploader="";
        uploader = "<div class='img-uploader'>\
                        <div class='img-uploader-box img-uploader-box-wait'>\
                        <img src=''  class='active'>\
                        <div class='img-uploader-btn-ap'>\
                        </div>\
                        <input class='input-file' type='file'>\
                        </div>\
                        <input type='hidden' name='' value=''>\
                </div>";
    if (_this.hasClass('disabled')) {
        return;
    }
    _this.addClass('disabled');
    // this.files[0] 是用户选择的文件
    lrz(this.files[0], {width: 1024, fieldName: 'upload_img'})
        .then(function (rst) {
            // onstart
            //_this.prev().text('上传中...');
            var img = new Image();
            
                _this.prev().prev().attr('src',rst.base64)
            
            return rst;
        })

        .then(function (rst) {
            // upload
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '/cdnation/uploadFileAjax');

            xhr.onload = function () {
                if (xhr.status === 200) {
                    var data = $.parseJSON(xhr.responseText);
                    if (data.error != '0') {
                        pop.error(data.errmsg);
                        _this.removeClass('disabled');
                        return;
                    }
                    data = data.data;
                    //_this.prev().html(iconPlus + '上传成功');
                    var _thisImg = _this.prev().prev(),
                        _thisMain = _this.parent().next();
                        _thisMainInput = _this.parent().parent().parent();
                    _thisImg.attr('src', data.url).addClass('active');
                    _thisMain.val(data.id);
                    _this.removeClass('disabled');
                    pop.error('上传成功！');
                    var image_perfor=_this.parent().next();
                    
                    if(_thisMainInput.data("role")=='borrowimg'){           //征信报告
                        image_perfor.attr("name",'borrowimg');
                        borrowimg.push(data.id);
                    }else if(_thisMainInput.data("role")=="courtimg"){      //法院网查询截图
                        image_perfor.attr("name",'courtimg');
                        courtimg.push(data.id);
                    }else if(_thisMainInput.data("role")=="carimg"){
                        image_perfor.attr("name",'carimg');
                        carimg.push(data.id);
                    };

                    _this.prev().removeClass("img-uploader-btn-ap").addClass("img-uploader-btn");
                    _this.css("display","none");
                    _this.parent().parent().after(uploader);
                } else {
                    _this.removeClass('disabled');
                    //_this.prev().html(iconPlus + '重新上传');
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

var manager_img="";
$(".wrap-reply-wait").on('click',"img",function(){
    $(".managerinfo_image_big").fadeIn();
    $(".managerinfo_image_box").children("img").attr("src",$(this).attr("src"));
    manager_img=$(this).parent().parent();
})
$(".managerinfo_image_box").on("click",function(e){
    $(this).parent().fadeOut();
})
$(".managerinfo_image_box_c").on("click",function(){
    $(".managerinfo_image_box_que").fadeIn();
})
$(".managerinfo_image_box_que_submit").on("click",function(){
    $(this).parent().parent().css("display","none");
    var input=manager_img.children("input");
    var val=manager_img.children(".img-uploader-box-wait").next("input").val();
    manager_img.remove();
   

   var n="",s="";
    if(input.attr("name")=="borrowimg"){
        s=val.toString();
        n=borrowimg.indexOf(s);
        borrowimg.splice(n,1);
    }else if(input.attr("name")=="courtimg"){
        s=val.toString();
        n=courtimg.indexOf(val);
        courtimg.splice(n,1);
        console.log(courtimg);
    }else if(input.attr("name")=="carimg"){
        s=val.toString();
        n=carimg.indexOf(val);
        carimg.splice(n,1);
    };

    $(".managerinfo_image_box_que").css("display","none");
    //var src=$(this).parent().siblings(".managerinfo_image_box").children("img").attr("scr");
})
$(".managerinfo_image_box_que_bottom").on("click",function(){
    $(".managerinfo_image_box_que").css("display","none");
})
$('.reply-more-btn').on('click', function(event) {
    $(this).next().fadeIn();
    $(this).remove();
});
