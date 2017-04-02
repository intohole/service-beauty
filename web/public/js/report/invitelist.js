var page = 0,
    btnMore = $('.btn-more'),
    inviteList = $('.invite-list'),
    inviteListEmpty = $('.list-empty'),
    awardCategoryName = ['好友报单奖励', '首次报单', '首次报单', '好友报单奖励', '好友报单奖励', '成交奖励', '首次报单', '好友报单奖励'],
    userNameTpye = ['invite_user_name', 'user_name', 'user_name', 'invite_user_name', 'invite_user_name', 'user_name', 'user_name', 'invite_user_name'],
    userPhoneTpye = ['invite_user_phone', 'user_phone', 'user_phone', 'invite_user_phone', 'invite_user_phone', 'user_phone', 'user_phone', 'invite_user_phone'];

getMore();

btnMore.click(function(){
    getMore();
})

function getMore(){
    if (btnMore.hasClass('disabled')) {
        return false;
    };
    btnMore.addClass('disabled').html('<div class="more-loading"><i class="bounce1"></i><i class="bounce2"></i><i class="bounce3"></i></div>');
    page++;
    $.post('/report/inviteListAjax', {
        page    : page
    }, 
    function(data) {
        console.log(data)
        if (data.error != 0) {
            if (page != 1) {
                pop.error('暂无更多数据');
                btnMore.removeClass('disabled').html('点击获取更多');
            }else{
                inviteListEmpty.show();
                btnMore.hide();
                $('.award-tip').hide();
            };
            page--;
            return false;
        }else{
            inviteList.append(htmlFactory(data));
            btnMore.removeClass('disabled').html('点击获取更多');
        };
    }, 'json');
}

function htmlFactory(data){
    var contents = [];
        data = data.msg;
    for (var j in data) {
        contents.push('<div class="extra-text"><strong>'+ j + '</strong></div><ul>');
        var dayDatas = data[j];
        for (var i = 0; i < data[j].length; i++){
            var imgUrl = dayDatas[i].img_url ? dayDatas[i].img_url : '/image/ydd.jpg',
                awardCategory = dayDatas[i].award_category;
            contents.push(
            '<li class="clearfix">\
                <img src="' + imgUrl + '">\
                <div class="user-info">\
                    <strong>' + dayDatas[i][userNameTpye[awardCategory - 1]] + '</strong>\
                    <span>' + dayDatas[i][userPhoneTpye[awardCategory - 1]] + '</span>\
                </div>\
                <div class="user-value">\
                    <b>+' + parseInt(dayDatas[i].award_money) + '元</b>\
                    <small>' + awardCategoryName[awardCategory - 1] + '</small>\
                </div>\
            </li>'
            )
        };
        contents.push('</ul>');
    };
    return contents.join('');
}
