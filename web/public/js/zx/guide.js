
// JavaScript Document
//点击打开功能
var autoSize = function () {
    var autoTrun;
    var openPopup = document.getElementById("popupbox");
    var back = document.getElementById("back");
    openPopup.style.display = "block";
    var popupWidth = openPopup.offsetWidth;
    var popupHeight = openPopup.offsetHeight;
    back.style.display = "block";
    //获取被卷去的高度
    var scrollToHeight = document.body.scrollTop ? document.body.scrollTop : document.documentElement.scrollTop;
    //当前浏览器的高度
    var clientHeights = document.documentElement.clientHeight ? document.documentElement.clientHeight : document.body.clientHeight;
    var clientWidths = document.body.offsetWidth;//当前浏览器的宽度
//	back.style.height = clientHeights + "px";
    back.style.height = scrollToHeight+clientHeights + "px";
    openPopup.style.top = (clientHeights - popupHeight) / 2 + scrollToHeight + "px";
    openPopup.style.left = (clientWidths - popupWidth) / 2 + "px";
};
var openDiv = function () {
    Layer1.style.display = "";
    //Layer1.style.display = 'none';
    autoTrun = window.setInterval(autoSize, 10);
};
var clearAuto = function () {
    window.clearInterval(autoTrun);
};
//点击关闭功能
function closeDiv() {
    var openPopup = document.getElementById("popupbox");
    var back = document.getElementById("back");
    openPopup.style.display = "none";
    back.style.display = "none";
    Layer1.style.display = "none";
    clearAuto();
}


function openG() {
    window.open("/credit/setSafetyLevel", "mainFrame");
    closeDiv();
}

function iFrameHeight() {
    var ifm = document.getElementById("mainFrame");
    var ifm2 = document.getElementById("leftFrame");
    var subWeb = document.frames ? document.frames["mainFrame"].document : ifm.contentDocument;
    if (ifm != null && subWeb != null) {
        var iheight = subWeb.body.scrollHeight;
        //alert(subWeb.body.scrollHeight);
        //if(iheight>700){
        //alert(iheight);
        ifm.height = iheight;
        ifm2.height = iheight;
        //}
    }
}