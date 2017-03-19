var steps = $('form'),
    inputAll = $('input'),
    submitDatas = {},
    oTitle = $('title'),
    unitSelect = $('#unit'),
    positionSelect = $('#position'),
    body = $('body'),
    editId = body.data('edit'),
    saveTime=null;
    title = ['基本信息', '信用记录', '职业及财产情况','', '客户信息确认'],
    level = ['F', 'E', 'D', 'C', 'B', 'A'],
    keyName = {
        'type': '业务类型',
        'username': '姓名',
        'sexual': '性别',
        'idcard': '身份证号',
        'birthday': '出生日期',
        'marry': '婚姻状况',
        'phone': '手机号码',
        'email': '常用邮箱',
        'education': '最高学历',
        'company': '工作单位',
        'registeraddress': '户籍地址',
        'household': '户口性质',
        'livingaddress': '暂住地址',
        'driving': '驾龄',
        'career': '职业类型',
        'agelimit': '现单位工作年限',
        'unit': '现单位性质',
        'position': '现单位岗位级别',
        'earning': '个人月收入状况',
        'house': '个人住房状况',
        'employee': '是否为我司员工',
        'client': '是否为我司客户',
        'credit': '信用记录',
        'litigation': '诉讼执行记录',
        'sin': '犯罪记录',
        'impression': '面谈主观印象',
        'idcardfront': '身份证正面照',
        'idcardback': '身份证背面照',
        'drivinglicence': '驾驶证照片'
    },
    keyName0 = {
        'type': '业务类型',
        'username': '姓名',
        'sexual': '性别',
        'idcard': '身份证号',
        'birthday': '出生日期',
        'phone': '手机号码',
        'email': '常用邮箱',
        'company': '工作单位',
        'registeraddress': '户籍地址',
        'livingaddress': '暂住地址',
        'earning': '个人月收入状况',
        'illegal': '违章记录',
        'litigation_car': '诉讼执行记录',
        'sin': '犯罪记录',
        'idcardfront': '身份证正面照',
        'idcardback': '身份证背面照',
        'drivinglicence': '驾驶证照片'
    },
    unitDatas = {
        '机关事业单位': ['厅局级以上', '处级', '科级', '一般干部', '其它'],
        '团体企业单位': ['正副总经理', '部门经理', '职员', '其它'],
        '一般企业单位': ['正副总经理', '部门经理', '职员', '其它'],
        '其它': ['其它']
    };

$('input[name=birthday]').mobiscroll().date({
    theme: 'mobiscroll',
    lang: 'zh',
    display: 'bottom',
    mode: 'scroller',
    dateOrder: 'yymmdd',
    dateFormat: 'yy/mm/dd',
    startYear: new Date().getFullYear() - 100,
    endYear: new Date().getFullYear(),
    minWidth: 100
});

//禁止空格 
// inputAll.on('input propertychange', function(){
//     var _this = $(this);
//     _this.val(_this.val().replace(/\s/g,""));
// })

function goTo(step, check){
    console.log(steps);
    if (step == 2 && check && !checkStep1()) {
        return;
    }
    if (step == 3 && check && !checkStep2()) {
        return;
    }
    if (step == 4 && check && !checkStep3()) {
        return;
    }else if (step == 4 && check && checkStep3()) {
        step=5;
        showConfirm(checkStep1(), checkStep2(), checkStep3());
    }
    if(step == 0 && check && !checkStep0()){
        step=4;
        return;
    }else if(step == 0 && check && checkStep0()){
        step=5;
        showConfirm_car(checkStep0());
    }
    steps.addClass('dpn').eq(step-1).removeClass('dpn');
    oTitle.text(title[step-1]);
    
}

function showConfirm(data1, data2, data3){
    steps.eq(4).prepend(confirmHtmlFactory('基本信息', data1) + confirmHtmlFactory('职业及财产情况', data2) + confirmHtmlFactory('信用记录', data3))
    submitDatas = $.extend({}, data1, data2, data3);
}
function showConfirm_car(data4){
    steps.eq(4).prepend(confirmHtmlFactory2('基本信息', data4) )
    submitDatas = $.extend({}, data4);
}
function confirmHtmlFactory(title, datas){
    var oImg = $('.xiamen img');
    var html = [];
    html.push('<div class="extra-text">' + title + '</div><ul class="wrap-list">');
    for (var i in datas) {
        if (i == 'idcardfront') 
 {           html.push('\
            <li><div class="list-item"><span class="list-item-key">身份证照片</span></div></li>\
            <li class="clearfix no-bt">\
                <div class="img-uploader">\
                    <div class="img-uploader-box">\
                        <img src="' + oImg.eq(0).attr('src') + '" alt="身份证正面照" data-src="" class="active">\
                    </div>\
                    <p class="tac">身份证正面照</p>\
                </div>\
                <div class="img-uploader">\
                    <div class="img-uploader-box">\
                        <img src="' + oImg.eq(1).attr('src') + '" alt="身份证背面照" data-src="" class="active">\
                    </div>\
                    <p class="tac">身份证背面照</p>\
                </div>\
                <div class="img-uploader">\
                    <div class="img-uploader-box">\
                        <img src="' + oImg.eq(2).attr('src') + '" alt="驾驶证照片" data-src="" class="active">\
                    </div>\
                    <p class="tac">驾驶证照片</p>\
                </div>\
            </li>\
            ')
        }else if(i == 'idcardback' || i == 'drivinglicence'){

        }else{
            html.push('<li><div class="list-item"><span class="list-item-key">' + keyName[i] + '</span>\
                <span class="list-item-value">' + datas[i] + '</span></div></li>');
        }
    }
    html.push('</ul>');
    return html.join('');
}

function confirmHtmlFactory2(title, datas){
    var oImg = $('.national img');
    console.log(oImg);
    var html=[];
    html.push('<div class="extra-text">' + title + '</div><ul class="wrap-list">');
    for(i in datas){
        if (i == 'idcardfront') {           
            html.push('<li><div class="list-item"><span class="list-item-key">身份证照片</span></div></li>\
                <li class="clearfix no-bt">\
                    <div class="img-uploader">\
                        <div class="img-uploader-box">\
                            <img src="' + oImg.eq(0).attr('src') + '" alt="身份证正面照" data-src="" class="active">\
                        </div>\
                        <p class="tac">身份证正面照</p>\
                    </div>\
                    <div class="img-uploader">\
                        <div class="img-uploader-box">\
                            <img src="' + oImg.eq(1).attr('src') + '" alt="身份证背面照" data-src="" class="active">\
                        </div>\
                        <p class="tac">身份证背面照</p>\
                    </div>\
                    <div class="img-uploader">\
                        <div class="img-uploader-box">\
                            <img src="' + oImg.eq(2).attr('src') + '" alt="驾驶证照片" data-src="" class="active">\
                        </div>\
                        <p class="tac">驾驶证照片</p>\
                    </div>\
                </li>\
            ')
        }else if(i == 'idcardback' || i == 'drivinglicence'){

        }else{
            html.push('<li><div class="list-item"><span class="list-item-key">' + keyName0[i] + '</span>\
                <span class="list-item-value">' + datas[i] + '</span></div></li>');
        }
    }
    html.push('</ul>');
    return html.join('');
}

function checkStep1(){
    var formDatasObj = {},
        formDatasArr = steps.eq(0).serializeArray();//获取指定section中的表单的键值
    $.each(formDatasArr, function() {
        var _this = this;
        if (_this.name == '') {
            return;
        }
        formDatasObj[_this.name] = _this.value;//拼装为对象
    });
    console.log(formDatasObj);
    for (var i in formDatasObj) {
        if (i == 'company' || i == 'registeraddress' || i == 'livingaddress' ) {
            continue;
        }
        if (!formDatasObj[i]) {
            pop.error(keyName[i] + '不能为空');
            return false;
        }
    }
    if(!IdCardValidate(formDatasObj.idcard)){
        pop.error('身份证号错误');
        return;
    }
    var phoneReg = /^1[3,4,5,7,8]\d{9}$/;
    if(!phoneReg.test(formDatasObj.phone)){
        pop.error('请输入正确的手机号');
        return false;
    }
    var emailReg = /^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-])+/;
    if(!emailReg.test(formDatasObj.email)){
        pop.error('邮箱地址是无效的');
        return;
    }
    return formDatasObj;
}

function checkStep2(){
    var formDatasObj = {},
        formDatasArr = steps.eq(1).serializeArray();//获取指定section中的表单的键值
    $.each(formDatasArr, function() {
        var _this = this;
        formDatasObj[_this.name] = _this.value;//拼装为对象
    });
    for (var i in formDatasObj) {
        if (!formDatasObj[i]) {
            pop.error(keyName[i] + '不能为空');
            return false;
        }
    }
    return formDatasObj;
}

function checkStep3(){
    var formDatasObj = {},
        formDatasArr = steps.eq(2).serializeArray();//获取指定section中的表单的键值
    $.each(formDatasArr, function() {
        var _this = this;
        formDatasObj[_this.name] = _this.value;//拼装为对象
    });
    for (var i in formDatasObj) {
        if (!formDatasObj[i]) {
            pop.error(keyName[i] + '不能为空');
            return false;
        }
    }
    return formDatasObj;
}

function checkStep0(){
    var formDatasObj = {},
        formDatasArr = steps.eq(3).serializeArray();//获取指定section中的表单的键值
    $.each(formDatasArr, function() {
        var _this = this;
        if (_this.name == '') {
            return;
        }
        formDatasObj[_this.name] = _this.value;//拼装为对象
    });
    console.log(formDatasObj);
    for (var i in formDatasObj) {
        if (i == 'company' || i == 'registeraddress' || i == 'livingaddress' || i== 'idcardfront' || i== 'idcardback' || i== 'drivinglicence') {
            continue;
        }
        if (!formDatasObj[i]) {
            pop.error(keyName0[i] + '不能为空');
            return false;
        }
    }
    if(!IdCardValidate(formDatasObj.idcard)){
        pop.error('身份证号错误');
        return;
    }
    var phoneReg = /^1[3,4,5,7,8]\d{9}$/;
    if(!phoneReg.test(formDatasObj.phone)){
        pop.error('请输入正确的手机号');
        return false;
    }
    var emailReg = /^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-])+/;
    if(!emailReg.test(formDatasObj.email)){
        pop.error('邮箱地址是无效的');
        return;
    }
    return formDatasObj;
}

$('#btn-cancel').on('click', function(){
    pop.confirm('温馨提示', '是否删除此次所有录入信息？', {btnName : ['取消', '确定'], className: 'animated fadeIn'}, function(res){
        if(res){
            pop.clear();
        }else{
            clearInterval(saveTime);
            $.ajax({
                url:'/chedai/clearUserRedisAjax',
                type:'POST',
                dataType:'json',
                success:function(data){
                    if(data.error==0||data.error==101){
                        location.href = '/chedai/salesman_customerlist';
                    }
                }
            });
        }
    });
});

$('#btn-submit').on('click', function(){
    var _this = $(this);
    if (_this.hasClass('disabled')) {
        return;
    }
    var launch=$("input[name=launch]").val();
    _this.addClass('disabled');
    pop.loading('处理中...');
    submitDatas.id = editId || '';
    $.post('/chedai/salesman_customeraddajax', submitDatas, function(data){
        pop.clear();
        if (data.error != 0) {
            pop.error(data.msg);
            _this.removeClass('disabled');
            return;
        }
        clearInterval(saveTime);
        if(launch==1){
            var oScoring = $('#scoring'),
            oScore = oScoring.find('.col-text-value');
            steps.addClass('dpn');
            oTitle.text('客户登记评分');
            oScoring.removeClass('dpn');
            oScore.eq(0).text(data.res.score + '分');
            oScore.eq(1).html('<strong>' + level[data.res.rank - 1] + '级</strong>');
        }else if(launch==2){
            location.href="/chedai/salesman_customerlist";
        }
    }, 'json')
})

unitSelect.on('change', function(event) {
    event.preventDefault();
    changePositionSelect();
});

function changePositionSelect(){
    var _this = unitSelect,
        html = [];
    var currentUnit = _this.children('option:selected').val();
    for (var i = 0; i < unitDatas[currentUnit].length; i++) {
        var isSelectedDom = i == 0 ? ' selected' : '';
        html.push('<option' + isSelectedDom + ' value="' + unitDatas[currentUnit][i] + '">' + unitDatas[currentUnit][i] + '</option>')
    }
    positionSelect.html(html.join(''));
}

if ( $('body').data('unit') ) {//编辑模式，根据单位性质改变职位select
    unitSelect.find('option').removeAttr('selected');
    unitSelect.val(body.data('unit'));
    changePositionSelect();
    positionSelect.find('option').removeAttr('selected');
    positionSelect.val(body.data('position'));
}

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



function getAlwaySaveDate(obj){
    var saveJson={};
    var saveArray=[];
    var formlen=obj.length;

    for(var i=0; i<formlen; i++){
        saveArray.push( obj[i].serializeArray() );
    }
    
    for(var j=0; j<saveArray.length; j++){
        for(var h=0; h<saveArray[j].length; h++){                
            if( saveArray[j][h].value!='' ){
                saveJson[saveArray[j][h].name] = saveArray[j][h].value;
            }
        }
    }

    return saveJson;

}



function alwaySave(obj){

    if(obj.length<1){
        return;
    }

    saveTime=setInterval(function(){
        $.ajax({
            url:'/chedai/realTimeStorageAjax',
            type:'POST',
            data:getAlwaySaveDate(obj),
            success:function(data){
                if(data.error==0){

                }
            }
        });
    },5000);
    
}

if(!editId){
    alwaySave( [steps.eq(0),steps.eq(1),steps.eq(2)] );
}
var idcardInput=$("input[name=idcard]");

function textId(v){
    if( IdCardValidate(v) ){
        var oBirth=getBirthday(v);
        $("input[name=birthday]").val(oBirth.y+'/'+oBirth.m+'/'+oBirth.d);
    }
}
textId( idcardInput.val() );

idcardInput.keyup(function(){
    var v=$(this).val();
    textId(v);
});

function getBirthday(id){
    if( IdCardValidate(id) ){
        var year = id.substring(6,10);
　　    var month = id.substring(10,12);
　　    var date= id.substring(12,14);
        return {y:year,m:month,d:date};
    }else{
        return false;
    }
}
