/*
@   Name：微信分享公共组件
@   Author：vincent-tsui
@   Note: 执行share()方法，并传入option即可
@   param title 分享的标题
@   param desc 分享的描述
@   param shareUrl 分享出去的链接，为空则分享出去当前页的链接
@   param imgUrl 分享的图标链接，为空则图标为银巴克LOGO
@   param goToUrl 分享后跳转的链接，为空则不跳转
@   param from 统计分享来源的ajax接口url，无则留空
@
@   share({
@       title: 'string',
@       desc: 'string',
@       shareUrl: 'url',
@       imgUrl: 'url',
@       goToUrl: 'url',
@       from: 'url'
@   });
@
*/
var initConfig = {
    getConfigPath : '/weixin/share',
    defaultImgUrl : 'https://mmbiz.qlogo.cn/mmbiz/VEuFzgBFcyeXZwN4pEkJ8xKEtxlK5mXA8H97DGy98ibFeaW2DV2FjD4hfhUiasNvev98JqXJXF73phPRX4sJ1qtg/0?wx_fmt=jpeg',
    secondObject : 'msg',
    appIdKey : 'appId',
    timestampKey : 'timestamp',
    nonceStrKey : 'noncestr',
    signatureKey : 'signature',
};
function share(option){
    function encode(url) {
        return encodeURIComponent(url).replace(/'/g, "%27").replace(/"/g, "%22");
    }
    var getConfigUrl = initConfig.getConfigPath + '?url=' + encode(window.location.href),
        shareUrl = option.shareUrl ? option.shareUrl : window.location.href,
        imgUrl = option.imgUrl ? option.imgUrl : initConfig.defaultImgUrl;
    var callback = function (data) {
        data = data[initConfig.secondObject] || data;
        wx.config({
            debug: false,
            appId: data[initConfig.appIdKey],
            timestamp: data[initConfig.timestampKey],
            nonceStr: data[initConfig.nonceStrKey],
            signature: data[initConfig.signatureKey],
            jsApiList: ['onMenuShareTimeline', 'onMenuShareAppMessage', 'onMenuShareQQ', 'onMenuShareWeibo']
        });
        wx.ready(function () {
            var shareData = {
                title: option.title,
                desc: option.desc,
                link: shareUrl, // 分享链接
                imgUrl: imgUrl,
                success: function () {
                    if (option.from) {
                        $.post(option.from)
                    };
                    //alert('已分享');         
                    // 用户确认分享后执行的回调函数
                    if(option.goToUrl){
                        window.location.href = option.goToUrl;
                    }
                }
            };
            wx.onMenuShareAppMessage(shareData);
            wx.onMenuShareTimeline(shareData);
            wx.onMenuShareQQ(shareData);
            wx.onMenuShareWeibo(shareData);
        });
        wx.error(function (res) {
            //alert(res.errMsg);
        });
    };
    $.ajax({
        url: getConfigUrl,
        dataType: "json",
        success: function (response) {
            callback && callback(response);
        },
        error: function (xhr, type) {
            console.log('xhr:');
            console.log(xhr);
            console.log('type:');
            console.log(type);
            console.log("网络错误")
        }
    })
}
    