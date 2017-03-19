var page = 0,
    btnMore = $('.btn-more'),
    reportList = $('.report-list'),
    reportListEmpty = $('.list-empty'),
    selectType = $('.choose-type select'),
    selectStatus = $('.choose-status select'),
    mortgageType = ['一抵', '二抵', '转单','一抵转单','二抵转单', '垫资'],
    currentType = selectType.find('option:selected').val(),
    currentStatus = 0,
    statusName = [
        ['全部', '录单', '录单信息审核', '初审', '复审' , '确认报送', '公证专员', '抵押专员', '确认放款', '财务', '归档', '保险公司初审', '保险公司复审', '审核不通过', '预审', '取回公证', '抵押取回','下户未通过','重新发起'],
        ['全部', '待预审', '预审通过', '预审未通过', '初审通过', '初审未通过', '待下户', '已放款', '反息中', '已逾期'],
        ['全部', '待审', '通过', '驳回', '重新发起']
    ];

selectStatus.html(selectHtml(statusName[currentType - 1]));

changeStatus(currentType, currentStatus);

btnMore.click(function(){
    getMoreReport(currentType, currentStatus);
});

selectStatus.change(function(){
    changeStatus(selectType.find('option:selected').val(), selectStatus.find('option:selected').val());
})

selectType.change(function(){
    selectStatus.html(selectHtml(statusName[selectType.find('option:selected').val() - 1]));
    changeStatus(selectType.find('option:selected').val(), selectStatus.find('option:selected').val());
})

function getMoreReport(type, status){
    if (btnMore.hasClass('disabled')) {
        return false; 
    };
    btnMore.addClass('disabled').html('<div class="more-loading"><i class="bounce1"></i><i class="bounce2"></i><i class="bounce3"></i></div>');
    page++;
    $.post('/report/getReportListAjax', {
        status  : status,
        type    : type,
        page    : page
    }, 
    function(data) {
        if (data.error != 0) {
            pop.error('暂无更多数据');
            btnMore.removeClass('disabled').html('点击获取更多');
            page--;
            return false;
        }else{
            reportList.append(htmlFactory(data));
            btnMore.removeClass('disabled').html('点击获取更多');
        };
    }, 'json');
}

function changeStatus(type, status){
    if (btnMore.hasClass('disabled')) {
        return false; 
    };
    btnMore.addClass('disabled');
    page = 1;
    $.post('/report/getReportListAjax', {
        status  : status,
        type    : type,
        page    : page
    }, 
    function(data) {
        if (data.error != 0) {
            reportList.html('')
            reportListEmpty.show();
            btnMore.removeClass('disabled').hide().html('点击获取更多');
            return false;
        }else{
            currentStatus = status;
            currentType = type;
            reportListEmpty.hide();
            reportList.html('').append(htmlFactory(data));
            btnMore.show().removeClass('disabled').html('点击获取更多');
        };
    }, 'json');
}

function htmlFactory(data){
    var contents = [];
        data = data.msg;
    for (var j in data) {
        contents.push('<div class="extra-text"><strong>'+ formatDate(j) + '</strong></div>');
        var monthDatas = data[j];
        for (var i = 0; i < data[j].length; i++){
            var imgUrl = monthDatas[i].img_url || '/image/ydd.jpg';
            var sta=selectType.find('option:selected').val();
            var status=statusName[selectType.find('option:selected').val() - 1][monthDatas[i].report_deal_status];
            if(sta==1 && status=="重新发起"){
                contents.push(
                    '<a class="clearfix" href="javascript:;">\
                        <div class="user-img">\
                            <img src="' + imgUrl + '">\
                            <strong>' + (monthDatas[i].borrower_name || '') + '</strong>\
                        </div>\
                        <div class="report-info">\
                            <p>申贷金额：<strong>' + parseInt(monthDatas[i].borrower_money) + '万</strong></p>\
                            <p>抵押类型：<strong>' + mortgageType[monthDatas[i].house_type - 1] + '</strong></p>\
                        </div>\
                        <div class="report-info">\
                            <p style="width: 4rem;border-radius: .2rem;color:#fff;height: 1.5rem;background-color: red;" class="report-house" data-add='+monthDatas[i].app_id+'  class="report-status-' + monthDatas[i].report_deal_status + ' tac">' + statusName[selectType.find('option:selected').val() - 1][monthDatas[i].report_deal_status] + '</p>\
                            <p >' + monthDatas[i].create_time + '</p>\
                        </div>\
                </a>'
                )
            }else if(sta==3 && status=="重新发起"){
                contents.push(
                    '<a class="clearfix" href="javascript:;">\
                        <div class="user-img">\
                            <img src="' + imgUrl + '">\
                    <strong>' + (monthDatas[i].borrower_name || '') + '</strong>\
                </div>\
                <div class="report-info">\
                    <p>申贷金额：<strong>' + parseInt(monthDatas[i].borrower_money) + '万</strong></p>\
                    <p>抵押类型：<strong>' + mortgageType[monthDatas[i].house_type - 1] + '</strong></p>\
                </div>\
                <div class="report-info">\
                    <p style="width: 4rem;border-radius: .2rem;color:#fff;height: 1.5rem;background-color: red;" class="report-funded" data-add='+monthDatas[i].app_id+'  class="report-status-' + monthDatas[i].report_deal_status + ' tac">' + statusName[selectType.find('option:selected').val() - 1][monthDatas[i].report_deal_status] + '</p>\
                    <p >' + monthDatas[i].create_time + '</p>\
                </div>\
            </a>'
                )
            }else{
                contents.push(
                    '<a class="clearfix" href="javascript:;">\
                        <div class="user-img">\
                            <img src="' + imgUrl + '">\
                    <strong>' + (monthDatas[i].borrower_name || '') + '</strong>\
                </div>\
                <div class="report-info">\
                    <p>申贷金额：<strong>' + parseInt(monthDatas[i].borrower_money) + '万</strong></p>\
                    <p>抵押类型：<strong>' + mortgageType[monthDatas[i].house_type - 1] + '</strong></p>\
                </div>\
                <div class="report-info">\
                    <p class="report-status-' + monthDatas[i].report_deal_status + ' tac">' + statusName[selectType.find('option:selected').val() - 1][monthDatas[i].report_deal_status] + '</p>\
                    <p>' + monthDatas[i].create_time + '</p>\
                </div>\
            </a>'
                )
            }


        };
    };
    return contents.join('');
}

function selectHtml(data){
    var html = [];
    for (var i = 0; i < data.length; i++) {
        var isSelect = i == 0 ? 'selected' : '';
        html.push('<option value="' + i + '" ' + isSelect + '>' + data[i] + '</option>')
    };
    return html.join('');
}

function formatDate(string){
    var dateString = string.replace(/\-/, "年");
    dateString  += '月';
    return dateString;
}

$(".report-list").on("click",".report-house",function(){
    var id=$(this).attr("data-add");
    location.href="/report/submitHouse?app_id="+id+"";
})
$(".report-list").on("click",".report-funded",function(){
    var id=$(this).attr("data-add");
    location.href="/report/submitfunded?app_id="+id+"";
})