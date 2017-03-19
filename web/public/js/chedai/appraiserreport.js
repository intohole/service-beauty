var btnSubmit = $('#btn-submit'),
    form = $('form'),
    keyName = {
        // 'procedures': '手续、规费情况',
        // 'config': '配置情况',
        // 'static': '静态检测',
        // 'dynamic': '动态检查',
        'synthesis': '综合评定',
        'assessable': '该车价值评估'
    };

btnSubmit.on('click', function(){
    var _this = $(this),
        formDatasObj = {},
        formDatasArr = form.serializeArray();//获取指定section中的表单的键值
    $.each(formDatasArr, function() {
        var __this = this;
        formDatasObj[__this.name] = __this.value;//拼装为对象
    });
    for (var i in formDatasObj) {
        if (!formDatasObj[i]) {
            pop.error(keyName[i] + '不能为空');
            return false;
        }
    }
    if (_this.hasClass('disabled')) {
        return false;
    };
    _this.addClass('disabled');
    pop.loading('处理中...');
    formDatasObj.id = $('body').data('id');
	$.post('/chedai/appraiser_reportajax',
        formDatasObj,
        function(data, textStatus, xhr) {
            pop.clear();
            if (data.error != 0) {
                pop.error(data.msg);
                _this.removeClass('disabled');
                return;
            }
            pop.error('提交成功');
            location.href = '/chedai/appraiser_list';
    }, 'json');
})
