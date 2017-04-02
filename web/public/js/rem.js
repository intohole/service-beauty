/*
@   Name：根据设备宽度计算html标签的字号
@   Author：cuiwenchao
@   Note: 需要在zepro.js之后引入
@   Example: 640px宽的效果图，对应的HTML标签处的字号为640/16=40px，故1rem = 40px
@
*/
function resetFontSize(){
    $('html').css('font-size', $('body').width()/16);
}
resetFontSize();
$(window).resize(function(){
    resetFontSize();
});
