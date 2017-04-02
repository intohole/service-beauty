/*

 @Name：pop 移动端弹层组件
 @Author：崔文超
 @Date：2015-06-26
        
 */

window.pop = {
/*alert提示框
 *@param title 提示的标题
 *@param desc 提示的描述
 *@param btnNum 按钮的数量，假如为1，则无视e2，t2，l2参数
 *@param e1 第一个按钮的类型，0为关闭类型，1为链接类型
 *@param t1 第一个按钮的显示文字
 *@param l1 第一个按钮的链接，如果该按钮为关闭类型，此处填0，链接类型填写该按钮链接
 *@param e2 第二个按钮的类型，0为关闭类型，1为链接类型
 *@param t2 第二个按钮的显示文字
 *@param l2 第二个按钮的链接，如果该按钮为关闭类型，此处填0，链接类型填写该按钮链接
 */
    alert: function(title, desc, btnNum, e1, t1, l1, e2, t2, l2) {

        /*initialize main dom*/
        var _body = $('body');
        var popShadow = $('<div class="shadow"></div>');
        var popWrap = $('<div class="pop-wrap"></div>');
        var popContent = $('<div class="pop-content"></div>')
            .html('<p class="pop-title">' + title + '</p><p class="pop-describe">' + desc + '</p>')
            .appendTo(popWrap);

        var popButtonBox = $('<div class="pop-button-box"></div>');
        var typeClass = (btnNum == 1) ? 'pop-button-single' : 'pop-button-double'

        var btnNum = parseInt(btnNum);//get button's number
        var buttonData = [[e1, t1, l1], [e2, t2, l2]];//save data to array

        /*loop append buttons to pop-button-box*/
        for (var i = 0; i < btnNum; i++) {
            if(parseInt(buttonData[i][0]) == 0){
                popButtonBox.append(closeDom(buttonData[i][1]));
            }else{
                popButtonBox.append(linkDom(buttonData[i][1], buttonData[i][2]));
            }
        };

        /*create the close type dom
         *@return dom
         */
        function closeDom (text) {
            return $('<span class="pop-button pop-button-close ' + typeClass + '">' + text + '</span>').bind('click', closeFun);
        }

        /*create the link type dom
         *@return dom
         */
        function linkDom (text, link) {
            return $('<a class="pop-button pop-button-close ' + typeClass + '" href="' + link + '">' + text + '</a>');
        }

        /*close pop function*/
        function closeFun () {
            popShadow.remove();
            popWrap.remove();
        }

        /*append pop to document*/
        popShadow.appendTo(_body).fadeIn('fast');
        popWrap.append(popButtonBox).appendTo(_body).fadeIn('fast');
        popOffset(popWrap);
    },

    /*确认提示
     *@param title 确认提示标题
     *@param desc 确认提示描述
     *@param fn 确认后回调函数
    */
    confirm: function(title, desc, fn) {

        var _body = $('body');
        var popShadow = $('<div class="shadow"></div>');
        var popWrap = $('<div class="pop-wrap"></div>');
        var popContent = $('<div class="pop-content"></div>')
            .html('<p class="pop-title">' + title + '</p><p class="pop-describe">' + desc + '</p>')
            .appendTo(popWrap);

        var popButtonBox = $('<div class="pop-button-box"></div>');

        var closeBtnDom = $('<span class="pop-button pop-button-close pop-button-double">取消</span>')
            .on('click', function(){
                popShadow.remove();
                popWrap.remove();
            })
            .appendTo(popButtonBox);

        var isFn = typeof fn === 'function';
        if (isFn) {
            var fnBtnDom = $('<span class="pop-button pop-button-close pop-button-double">确定</span>')
                .on('click', fn)
                .on('click', function(){
                    popShadow.remove();
                    popWrap.remove();
                })
                .appendTo(popButtonBox);
        };

        popButtonBox.appendTo(popWrap);

        /*append pop to document*/
        popShadow.appendTo(_body).fadeIn('fast');
        popWrap.appendTo(_body).fadeIn('fast');
        popOffset(popWrap);
    },

    /*错误信息提示
     *@param text 错误信息文本
     *@param sec 错误信息显示时间，单位毫秒，选填，不填则默认3秒
    */
    error: function (text, sec, fn) {
        var _body = $('body');
        var popError = $('<div class="pop-wrap-error">' + text + '</div>');
        $('.pop-wrap-error').remove();
        popError.appendTo(_body).fadeIn('fast');
        popOffsetX(popError);
        sec = sec ? sec : 3000;
        setTimeout(function() {
            popError.fadeOut('fast',function() {
                popError.remove();
            });
            if (typeof fn == 'function') fn()
        },sec);
    },

    /*loading信息提示
     *@param text 读取中提示信息
    */
    loading: function (text) {
        var _body = $('body');
        var popLoading = $('<div class="pop-loading"></div>');
        var popContent = $('<p>' + text + '</p>');
        var popLoadingEffect = $('<div class="pop-loading-effect"><i class="bounce1"></i><i class="bounce2"></i><i class="bounce3"></i><i class="bounce4"></i><i class="bounce5"></i><i class="bounce6"></i></div>');
        
        popLoading.append(popContent).append(popLoadingEffect).appendTo(_body).fadeIn('fast');
        popOffset(popLoading);
    },

    /*tips信息提示
     *@param text 提示信息，强调部分请用strong标签包裹
    */
    tips: function () {
        var _body = $('body');
        var allTips = $('.icon-question-circle');//选取页面中所有问号图标
        allTips.each(function(index, el) {//遍历所有问号图标
            var _this = $(this);
            var text = _this.data('pop-tips');//获取当前tip的文案
            _this.on('touchstart', function(){//为此图标绑定事件
                $('.pop-tips').remove();
                var popTips = $('<div class="pop-tips"></div>');
                var popContent = $('<p><span>' + text + '</span></p>');
                popTips.append(popContent).appendTo(_body);
                var arrThisOffset = getElementOffset(_this);//获得此图标的距离页面顶端的距离和左边的距离
                var arrTipOffset = [popTips.width(),popTips.height()]//获得tip的宽高
                var tipFinalOffset = [
                    arrThisOffset[0] - (arrTipOffset[0]/2) + (_this.width()/2),//图标上偏移距离-tip高度的一般
                    arrThisOffset[1] - arrTipOffset[1] - 10//图标左偏移距离-tip宽度的一般+图标宽度的一半
                ];
                popTips.css({
                    'left': tipFinalOffset[0],
                    'top': tipFinalOffset[1] - 5
                });
                popTips.fadeIn('fast').animate({'top': tipFinalOffset[1],'opacity':1}, 200);
                setTimeout(function(){
                    popTips.remove();
                }, 3000)
            })
        });
    },

    /*
     *清除所有pop弹层
    */
    remove: function () {
        $('.shadow, .pop-wrap, .pop-wrap-error, .pop-loading').remove();
    }

};
/*
 *修正元素的定位，使之绝对定位于页面中间
 *@param e 目标元素
*/
function popOffset (e){
    var popHeight = e.height();
    var popWidth = e.outerWidth();
    e.css({'margin-top': -popHeight / 2, 'margin-left': -popWidth / 2});
}
/*
 *修正元素的X轴定位，使之X轴绝对定位于页面中间
 *@param e 目标元素
*/
function popOffsetX (e){
    var popWidth = e.outerWidth();
    e.css({'margin-left': -popWidth / 2});
}
/*
 *返回元素距离页面顶端的距离和左边的距离
 *@param e 目标元素
 *@return array [左边距，顶边距]
*/
function getElementOffset(e){
    return [e.offset().left,e.offset().top];
}
//渲染时初始化
$(function(){
    if ($('.icon-question-circle').length > 0) {//按需加载
        pop.tips();
    };
})