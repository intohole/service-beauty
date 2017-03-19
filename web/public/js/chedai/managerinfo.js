var btnAgree = $('#btn-agree'),
    body = $('body'),
    creditimg = [],
    valueimg1 = [],
    bankimg = [],
    courtimg = [];
    illegalimg=[];
    launch=$("input[name=launch]").val();
btnAgree.on('click', function(){
    var _this = $(this),
        level = body.data('level'),
        submitDatas = {};
    if (_this.hasClass('disabled')) {
        return false;
    };
    _this.addClass('disabled');
    if (level == 1) {
        if (courtimg.length==0 && launch == 1) {
            pop.error('法院网查询截图未上传');
            _this.removeClass('disabled');
            return;
        }
        var oCourt = $('input[name=court]:checked');
        if (oCourt.length == 0) {
            pop.error('请选择法院信息审核结果是否正常');
            _this.removeClass('disabled');
            return;
        }
        var court = $.trim(oCourt.val());
        var courtreason = $.trim($('textarea[name=courtreason]').val());
        if (court == '0' && !courtreason) {
            pop.error('请填写法院信息审核结果不正常的原因');
            _this.removeClass('disabled');
            return;
        }
        if (valueimg1==0 && launch == 1) {
            pop.error('评估价值信息审核结果未上传');
            _this.removeClass('disabled');
            return;
        }
        var credit = $('input[name=credit]:checked').val();
        var creditreason = $.trim($('textarea[name=creditreason]').val());
        var monthin = $.trim($('input[name=monthin]').val());
        var monthout = $.trim($('input[name=monthout]').val());
        var confirmreason = $.trim($('textarea[name=confirmreason]').val());
        var confirm = $('input[name=confirm]:checked').val();
        //var confirm = [];
        var  courtimg_ajax = courtimg.join(",");
        var  valueimg1_ajax = valueimg1.join(",");     //评估价值信息审核结果
        var  creditimg_ajax = creditimg.join(",");
        var  bankimg_ajax = bankimg.join(",");
        var  illegalimg_ajax = illegalimg.join(",");
            submitDatas = {
            courtimg : courtimg_ajax,  //法院网查询截图
            illegalimg : illegalimg_ajax,      //违章结果截图
            court: court,
            courtreason: courtreason,
            valueimg1: valueimg1_ajax,     //评估价值信息审核结果
            creditimg : creditimg_ajax,    //征信报告
            credit: credit,
            creditreason: creditreason,
            bankimg : bankimg_ajax, //流水单
            monthin: monthin,
            monthout: monthout,
            confirmreason: confirmreason,
            confirm: confirm,
            id: body.data('id')
        }
    }else if(level == 2){
        var result = $('input[name=result]:checked').val();
        if (!result) {
            pop.error('法律信息审核结果未选择');
            _this.removeClass('disabled');
            return;
        }
        submitDatas = {
            result: result,
            id: body.data('id')
        }
    }else{
        submitDatas = {
            id: body.data('id')
        }
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
            location.href = '/chedai/manager_agree?id=' + body.data('id') + '&auditid=' + data.res;
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
    var that=this.files,length=that.length;
    _this.addClass('disabled');
    // this.files[0] 是用户选择的文件
    for(var i=0;i<length;i++){
         lrz(this.files[i], {width: 1024, fieldName: 'upload_img'})
            .then(function (rst) {
                // onstart
                //_this.prev().text('上传中...');
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
                            _this.removeClass('disabled');
                            return;
                        }
                        data = data.data;
                        //_this.prev().html(iconPlus + '上传成功');
                        var _thisImg = _this.prev().prev(),
                            _thisMain = _this.parent().next();
                            _thisMainInput = _this.parent().parent().parent();
                        // _thisImg.attr('src', data.url).addClass('active');
                        // _thisMain.val(data.id);
                        _this.removeClass('disabled');
                        pop.error('上传成功！');
                        var image_perfor=_this.parent().next();
                        var state = null;
                        if(_thisMainInput.data("role")=='creditimg'){           //征信报告
                            image_perfor.attr("name",'creditimg');
                            state = 'creditimg';
                            creditimg.push(data.id);
                        }else if(_thisMainInput.data("role")=="bankimg"){
                            image_perfor.attr("name",'bankimg');
                            state = 'bankimg';
                            console.log(state);
                            bankimg.push(data.id);
                        }else if(_thisMainInput.data("role")=="courtimg"){      //法院网查询截图
                            image_perfor.attr("name",'courtimg');
                            state = 'courtimg';
                            courtimg.push(data.id);
                        }else if(_thisMainInput.data("role")=="valueimg1"){
                            image_perfor.attr("name",'valueimg1');
                            state = 'valueimg1';
                            valueimg1.push(data.id);
                        }else if(_thisMainInput.data("role")=="illegal"){
                            image_perfor.attr("name",'illegal');
                            state = 'illegal';
                            illegalimg.push(data.id);
                        };
                        var uploader = "<div class='img-uploader'>\
                                            <div class='img-uploader-box img-uploader-box-wait'>\
                                            <img src="+data.url+"  class='active'>\
                                            <div class='img-uploader-btn'>\
                                            </div>\
                                            <input class='input-file' type='file' multiple style='display:none'>\
                                            </div>\
                                            <input type='hidden' name="+state+" value="+data.id+">\
                                        </div>";
                        // _this.prev().removeClass("img-uploader-btn-ap").addClass("img-uploader-btn");
                        // _this.css("display","none");
                        _this.parent().parent().before(uploader);
                        _this.removeClass('disabled');
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
    }
   
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
    if(input.attr("name")=="creditimg"){
        s=val.toString();
        n=creditimg.indexOf(s);
        creditimg.splice(n,1);
    }else if(input.attr("name")=="bankimg"){
        s=val.toString();
        n=bankimg.indexOf(val);
        bankimg.splice(n,1);
    }else if(input.attr("name")=="courtimg"){
        s=val.toString();
        n=courtimg.indexOf(val);
        courtimg.splice(n,1);
    }else if(input.attr("name")=="valueimg1"){
        s=val.toString();
        n=valueimg1.indexOf(val);
        valueimg1.splice(n,1);
    }else if(input.attr("name")=="illegalimg"){
        s=val.toString();
        n=illegalimg.indexOf(val);
        illegalimg.splice(n,1);
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
