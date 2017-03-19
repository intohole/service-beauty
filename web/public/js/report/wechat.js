/*
@   Name：微信分享公共组件
@   Author：cuiwenchao
@
@   wechat({
@       shareDatas : {
@          title: 'string',//分享的标题
@          desc: 'string',//分享的描述
@          shareUrl: 'url',//分享出去的链接，为空则分享出去当前页的链接
@          imgUrl: 'url',//分享的图标链接，为空则图标为银巴克LOGO
@          goToUrl: 'url',//分享后跳转的链接，为空则不跳转
@          from: 'url'//统计分享来源的ajax接口url，无则留空
@       },
@       getLocation : function(res){}//获取地理位置后的回调方法
@   });
@
*/
function wechat(option){
    var callback = function (json) {
        var data = json.msg;
        wx.config({
            debug: false,
            appId: data.appId,
            timestamp: data.timestamp,
            nonceStr: data.noncestr,
            signature: data.signature,
            jsApiList: [
                'onMenuShareTimeline',
                'onMenuShareAppMessage',
                'onMenuShareQQ',
                'onMenuShareWeibo',
                'getLocation'
            ]
        });
        wx.ready(function () {
            console.log(option.shareDatas)
            if (option.shareDatas) {
                var sData = {
                    title: option.shareDatas.title,
                    desc: option.shareDatas.desc,
                    link: option.shareDatas.shareUrl ? option.shareDatas.shareUrl : window.location.href, // 分享链接
                    imgUrl: option.shareDatas.imgUrl ? option.shareDatas.imgUrl : 'https://mmbiz.qlogo.cn/mmbiz/nzhlVibVGiaaLIY4ia1Vq7Vibs4v4JszQy933QiczTQvRL5jibIzZ8CWkhibdAaPLJCQ1W7FXmA97HUGnxroNbUUs6XrQ/0?wx_fmt=png',
                    success: function () {
                        if (option.shareDatas.from) {
                            $.post(option.shareDatas.from)
                        };
                        //alert('已分享');         
                        // 用户确认分享后执行的回调函数
                        if(option.shareDatas.goToUrl){
                            window.location.href = option.shareDatas.goToUrl;
                        }
                    }
                };
                wx.onMenuShareAppMessage(sData);
                wx.onMenuShareTimeline(sData);
                wx.onMenuShareQQ(sData);
                wx.onMenuShareWeibo(sData);
            };
            if (option.getLocation) {
                wx.getLocation({
                    type: 'wgs84', // 默认为wgs84的gps坐标，如果要返回直接给openLocation用的火星坐标，可传入'gcj02'
                    success: function (res) {
                        // var latitude = res.latitude; // 纬度，浮点数，范围为90 ~ -90
                        // var longitude = res.longitude ; // 经度，浮点数，范围为180 ~ -180。
                        // var speed = res.speed; // 速度，以米/每秒计
                        // var accuracy = res.accuracy; // 位置精度
                        console.log(1)
                        typeof(option.getLocation) === 'function' && option.getLocation(res, 1);//1成功0失败
                        console.log(2)
                    },
                    cancel: function (res) {
                        typeof(option.getLocation) === 'function' && option.getLocation(res, 0);
                        //用户拒绝授权获取地理位置
                    }
                });
            }
        });
        wx.error(function (res) {
            //alert(res.errMsg);
        });
    };
    var getConfigUrl = "/weixin/share/?url=" + encodeURIComponent(window.location.href).replace(/'/g, "%27").replace(/"/g, "%22");
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
    