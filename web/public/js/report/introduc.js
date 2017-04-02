// var bussinessDates=[
//     {
//         title: '垫资',
//         text:[
//                     ['垫资利率',
//                         '促销期内，日息万分之五，按日计息，高出全返（于新银行放款后立即支付返还）;',
//                         '促销期至5月31日（以客户签约为准），促销期后恢复至0.5%/10天。'],
//                     ['贷款服务费',
//                         '全免，无垫资需求也为同行免费办理贷款。'],
//                     ['其它费用',
//                         '公证费千三，第三方公司千三（不包发票）'],
//                     ['款项用途',
//                         '用于同一银行或不同银行产品之间的倒贷垫资业务（房产证原件被原贷款银行收押的产品暂无法垫资）；',
//                         '抵贷不一可做；',
//                         '企业垫资、交易类垫资暂不受理。'],
//                     ['房 龄',
//                         '1991年以后，25年以内。'],
//                     ['区 域',
//                         '北京城六区、通州、亦庄、黄村、回龙观、天通苑、顺义。'],
//                     ['房屋类型',
//                         '70年产权且无需补办手续即可正常上市的个人名下普通住宅；',
//                         '央产、军产暂不受理；',
//                         '公寓、别墅、商业等项目暂不受理；',
//                         '公司房产暂不受理。'],
//                     ['借款额度',
//                         '不高于原银行贷款尾款，单笔不超过500万；',
//                         '若高于限额，超出部分垫资利率另议。'],
//                     ['借款期限',
//                         '不超过3个月，年底可放宽至4个月，可随时提前还款。'],
//                     ['付息方式',
//                         '可每10天一付息、每月付息或后付息（新银行放款后，甲方扣除借款金额及利息，再将余款支付至借款人指定账户）。'],
//                     ['借款人年龄',
//                         '借款人及配偶年龄在18岁（不含）-60岁（不含）之间；',
//                         '在抵贷不一情况下，产权所有人及配偶年龄也需在18岁（不含）-60岁（不含）之间。'],
//                     ['借款人国籍',
//                         '中国国籍（除港澳台）。'],
//                     ['借款人职业',
//                         '公、检、法、军人不做。'],
//                     ['所需材料',
//                         '房产证原件、身份证、户口薄、结婚证或离婚证（财产分割协议）、征信报告、银行流水。'],
//                     ['合作流程',
//                         '银代代微信公众号提交基础材料（房产证、身份证、预借金额、联系方式）；',
//                         '办理抵押登记，见受理单当日放款；',
//                         '确认原银行还款；',
//                         '核实新银行批贷；',
//                         '办理借款公证手续；',
//                         '原银行还款当日放款垫资；',
//                         '新银行放款后一个工作日内返费。'
//                     ]
//                 ]
//     },
//     {
//         title: '房抵',
//         text:[
//             ['质押利率',
//                 '促销期内，评估值七成以内，征信流水良好，一抵、二抵、转单统统月息0.73%，高出全返！月月返！促销期至4月30日;',
//                 '评估值7-8.5成或征信不良，一抵、二抵、转单月息上浮至1.2%，且有一定否单率。'],
//             ['借款期限',
//                 '1-12个月。'],
//             ['其它费用',
//                 '全免！12个月内随借随还，无任何展期费、服务费、手续费。'],
//             ['房龄',
//                 '1991年以后，25年以内。'],
//             ['区域',
//                 '北京城六区、通州、亦庄、黄村、回龙观、天通苑、顺义。'],
//             ['房屋类型',
//                 '70年产权且无需补办手续即可正常上市的个人名下普通住宅；',
//                 '央产、军产暂不受理；',
//                 '公寓、别墅、商业等项目暂不受理；',
//                 '公司房产暂不受理。'],
//             ['借款额度',
//                 '单笔不超过500万；',
//                 '若高于限额，全额加息0.5%。'],
//             ['借款人年龄',
//                 '借款人、产权共有人及配偶年龄在18岁（不含）-60岁（不含）之间。'],
//             ['借款人国籍',
//                 '中国国籍（除港澳台）。'],
//             ['借款人职业',
//                 '公、检、法、军人不做。'],
//             ['所需材料',
//                 '房产证、身份证、户口薄、结婚证或离婚证（财产分割协议）、征信报告（可代打，150元/份）、银行流水（可拍照）。'],
//             ['合作流程',
//                 '银代代微信公众号提交基础材料（房产证、身份证、预借金额、联系方式）；',
//                 '上门核实借款人的征信报告、银行流水、房屋照片、户口本、结婚证等材料；',
//                 '办理借款公证手续；',
//                 '办理房屋抵押手续；',
//                 '14:00之前见受理单当日放款，14:00之后见受理单，下个工作日放款。']
//         ]

//     }
// ];
renderPage(getQueryString('action'));

function renderPage(action){
        // var html_dianzi='';
        // html_dianzi+='<ul class="group group_header">\
        //             <li class="font_color">日息：</li>\
        //             <li class="font_color"> <div class="month_width"><span class="month_color">0.05</span>\
        //                  <span class="color_persent">%<pan></div><b class="font_position">按日计息，高出全返!</b> </li>\
        //             <li class="color_gray">免费代办贷款</li>\
        //         </ul>';
        // var html_fangdi='';
        // html_fangdi+='<ul class="group group_header">\
        //             <li class="font_color">月息：</li>\
        //             <li class="font_color"> <div class="or_width"><span class="month_color color_or">0.73</span>\
        //                  <span class="color_persent color_or">%<pan></div><b class="font_position">一低、二抵、转单通通<font>0.73%</font></b> </li>\
        //             <li class="color_gray">30分钟内反馈初审结果 | 当天放贷</li>\
        //         </ul>';
    var datasIndex = getDatas(action);
    if(datasIndex==1){
        $(".header_fangdi").css("display","block");
        // $('.header_fangdi_f').append(html_fangdi);
    }else if(datasIndex==0){
        $(".header_dianzi").css("display","block");
    //     $('.header_dianzi_d').append(html_dianzi);
        
    }

    // $('title').text('业务介绍-北京' + bussinessDates[datasIndex].title);
    // var html='';
    // var len=bussinessDates[datasIndex].text;
    // for(var i=0;i<len.length;i++){
    //     var html_li='';
    //     for(var j= 1;j<len[i].length;j++){
    //         // if(len[i][j].indexOf("children")>0){
    //         //     len[i][j]=len[i][j].replace("children","");
    //         //     len[i][j]+=' <b class="special">1、1-3个月：一抵优质客户0.66%，二抵、转单0.88%；</b>\
    //         //                 <b class="special"> 2、4-6月：一抵1.08%，二抵1.28%;</b>'
    //         // };
    //         if(len[i].length==2){
    //             html_li+='<li class="team">\
    //                        <p class="content">'+len[i][j]+'</p>\
    //                   </li>';
    //         }else{
    //             html_li+='<li class="team">\
    //                        <span class="circle">'+(j)+'</span>\
    //                        <p class="content">'+len[i][j]+'</p>\
    //                   </li>';
    //         }
    //     }
    // }
    //     // $('title').text('业务介绍-北京' + bussinessDates[datasIndex].title);
    //     // var html='';
    //     // var len=bussinessDates[datasIndex].text;
    //     // for(var i=0;i<len.length;i++){
    //     //     var html_li='';
    //     //     for(var j= 1;j<len[i].length;j++){
    //     //         if(len[i][j].indexOf("children")>0){
    //     //             len[i][j]=len[i][j].replace("children","");
    //     //             len[i][j]+=' <b class="special">1、1-3个月：一抵优质客户0.66%，二抵、转单0.88%；</b>\
    //     //                         <b class="special"> 2、4-6月：一抵1.08%，二抵1.28%;</b>'
    //     //         };
    //     //         if(len[i].length==2){
    //     //             html_li+='<li class="team">\
    //     //                        <p class="content">'+len[i][j]+'</p>\
    //     //                   </li>';
    //     //         }else{
    //     //             html_li+='<li class="team">\
    //     //                        <span class="circle">'+(j)+'</span>\
    //     //                        <p class="content">'+len[i][j]+'</p>\
    //     //                   </li>';
    //     //         }

                
    //     //     }
    //     //     html+='<div class="group">\
    //     //             <h2 class="title">'+len[i][0]+'</h2>\
    //     //             <ul class="group_team">'+html_li+'</ul>\
    //     //            </div>';
    //     // };
    //     // $(".container").append(html);

    //         // $('title').text('业务介绍-北京' + bussinessDates[datasIndex].title);
    //         // var html='';
    //         // var len=bussinessDates[datasIndex].text;
    //         // for(var i=0;i<len.length;i++){
    //         //     var html_li='';
    //         //     for(var j= 1;j<len[i].length;j++){
    //         //         if(len[i][j].indexOf("children")>0){
    //         //             len[i][j]=len[i][j].replace("children","");
    //         //             len[i][j]+=' <b class="special">1、1-3个月：一抵优质客户0.66%，二抵、转单0.88%；</b>\
    //         //                         <b class="special"> 2、4-6月：一抵1.08%，二抵1.28%;</b>'
    //         //         };
    //         //         if(len[i].length==2){
    //         //             html_li+='<li class="team">\
    //         //                        <p class="content">'+len[i][j]+'</p>\
    //         //                   </li>';
    //         //         }else{
    //         //             html_li+='<li class="team">\
    //         //                        <span class="circle">'+(j)+'</span>\
    //         //                        <p class="content">'+len[i][j]+'</p>\
    //         //                   </li>';
    //         //         }
                    
    //         //     }
    //         //     html+='<div class="group">\
    //         //             <h2 class="title">'+len[i][0]+'</h2>\
    //         //             <ul class="group_team">'+html_li+'</ul>\
    //         //            </div>';
    //         // };
    //         // $(".container").append(html);
}

function getDatas(action){
    switch(action){
        case 'fangdi':
            return 1;
            break;
        case 'dianzi':
            return 0;
            break;
        default:
            return 1;
    }
}

function getQueryString(name){
    var r = window.location.search.substr(1).slice(7,13);
    if(r!=null){ return r};
}