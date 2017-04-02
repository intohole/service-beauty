var page=1;
var report_yue = "";
$(function(){
	$(".report-fasthouse").click();
	return;
	$.ajax({			//默然进入页面发送ajax，请求快评信息
		url:'/report/getQuickHouseListAjax',
		type:"post",
		dataType:"json",
		data:{
			houselist:0,
			page:page
		},
		success:function(data){
			if(data.error == 1){
				var data = data.data;
				var listdata = listquick(data,page);
				alert(listdata);
				return;
				$(".report-list").html(listdata);
			}else{
				$(".btn-more").addClass("disabled").html("暂无数据");
			}
			
			// $(this).removeClass("disabled")
		}
	})
})

/* var names = {
			null:'录单',
			'':'录单',
			1:'录单',
			2:'录单信息审核', 
			3:'初审', 
			4:'复审' ,
			5:'确认报送', 
			6:'权证',
			7:'抵押专员',
			8:'确认放款', 
			9:'财务', 
			10:'归档', 
			11:'保险公司初审',
			12:'保险公司复审',
			13:'审核不通过',
			14:'预审',
			15:'取回公证',
			16:'抵押取回',
			17:'分配下户人员',
			18:'分配初审',
			19:'下户未通过',
        
		} */
		
var names = {
			
			1:'报单填写',
			2:'线上评估', 
			3:'线下评估', 
			4:'审核材料' ,
			5:'办理权证', 
			6:'财务放款',
			7:'放款完成',
			
        
		}

function gotoService(_type,_reportorfounder,_service_id,_id){
	$(".submit-page").hide();
	if(_type == 0){
		if(_reportorfounder == 0){
			window.location.href='/report/submitHouse?type='+_reportorfounder+'&area_id='+_service_id;
		}else if(_reportorfounder == 1){ 
			window.location.href='/report/submitFunded?type='+_reportorfounder+'&area_id='+_service_id;
		}
	}else if(_type == 1){
		window.location.href='/report/details?type='+_reportorfounder+'&area_id='+_service_id+'&id='+_id;
		/* if(_reportorfounder == 0){
			window.location.href='/report/details?type='+_reportorfounder+'&area_id='+_service_id;
		}else if(_reportorfounder == 1){ 
			window.location.href='/report/submifunder?type='+_reportorfounder+'&area_id='+_service_id;
		} */
	}else{
		$(".submit-page").show();
	}
	
}
function gotoHouseInfo(_id){
	window.location.href='/report/detailroom?id='+_id;
	
}
var list = "";
function listquick(info,_page){
	var len = info.length;
	if(_page==1){
		list = "";
		report_yue = "";
	}
	for(var i=0;i < len; i++){
		var skip = "";
		var coler = "";
		if(info[i].returnstatus ==1){
			if(info[i].type == 0){//跳转至房抵

				skip = '<span onclick = "gotoService(0,0,'+info[i].service_id+','+info[i].id+')">急速报单</span>';

			}else{//跳转至垫资

				skip = '<span onclick = "gotoService(0,1,'+info[i].service_id+','+info[i].id+')">急速报单</span>';

			}
		}else{//失败
			if(info[i].status>=4){
				skip = '<span style="padding:.25rem .8rem;" onclick="gotoService(2)">联系客服</span>';
			}else{
				if(info[i].type == 0){//跳转至房抵
					skip = '<span onclick = "gotoService(1,0,'+info[i].service_id+','+info[i].id+')">重新评房</span>';
				}else{//跳转至垫资
					skip = '<span onclick = "gotoService(1,1,'+info[i].service_id+','+info[i].id+')">重新评房</span>';
				}
			}
			
			
			coler = 'class = "report-flase-color"'
		} 
		if(report_yue != info[i].month_item){
			list +='<div class="extra-text"><strong>'+info[i].month_item+'</strong></div>';
		}
		list += '<a class="clearfix" href="#">'+ 
					'<ul>'+
						'<span onclick = "gotoHouseInfo('+info[i].id+')"><li class="report-list-user-head">'+
							'<span '+coler+'>'+info[i].getname+'</span>'+
							'<p>申请时间：'+info[i].gettime+'</p>'+
						'</li>'+              
						'<li class="report-list-addr">'+                    
							'<p>房屋地址：</p>'+                   
							'<h4>'+info[i].housename+'</h4>'+               
						'</li></span>'+                
						'<li class="report-list-user-amount">'+  
							'<span class="btn-default report-user-amount">'+skip+'</span>'+                     
							'<p>房产估值金额：<strong> '+info[i].amount+'万元</strong></p>'+                    
							'<p>预估可贷金额：<strong style="color:#fc5e61"> '+info[i].fact_amount+'万元</strong></p>'+ 
									   
						'</li>'+
					'</ul>'+       
				'</a>';
		report_yue = info[i].month_item;
	}
	$(".report-list").html(list);
	
}



function restart(_category,_app_id){
	if(_category == 0){
		window.location.href='/report/submitHouse?app_id='+_app_id;
	}else if(_category == 1){
		window.location.href='/report/submitFunded?app_id='+_app_id;
	}
	
}

function listprecise(info,_page,_category){
	var len = info.length;
	if(_page==1){
		list = "";
		report_yue = "";
	}
	var img = "/image/ydd.jpg";
	var restart = "";
	var coler = "";
	for(var i=0;i < len; i++){
		if(report_yue != info[i].month_item){
			list +='<div class="extra-text"><strong>'+info[i].month_item+'</strong></div>';
		}
		if(info[i].restart == 1){
			restart = '<span class="btn-default report-agin" onclick ="restart('+_category+','+info[i].app_id+')">重新发起</span>';
		}else{
			restart = "";
		}
		if(info[i].img.url){
			img = info[i].img.url;
		}else{
			img = "/image/ydd.jpg";
		}
		/* if(info[i].title_status == 1){
			coler = 'class ="report-valuation-color"';
		}else if(info[i].title_status == 2){
			//coler = 'class ="report-valuation-color"';
		}else if(info[i].title_status == 3){
			coler = 'class ="report-flase-color"';
			//coler = 'report-flase-color';
		} */
		// /report/detailpage/report_id/'+info[i].report_id+'/type/'+info[i].fundtype+'
		list += '<a class="clearfix special_arror" > '+
					'<span onclick = gotodetailpage('+info[i].report_id+','+info[i].fundtype+')>'+
					'<div class="report-list-user-head">'+
						//'<span '+coler+'>'+info[i].title+'</span>'+
						'<p>申请时间：'+info[i].gettime+'</p>'+
					'</div>'+               
					'<div class="user-img">'+                    
						'<img src="'+img+'">'+                    
						'<strong>'+info[i].borrower_name+'</strong>'+                
					'</div>'+                
					'<div class="report-info">'+                    
						'<p>申贷金额：<strong>'+info[i].borrower_money+'万</strong></p>'+                    
						'<p>抵押类型：<strong>'+info[i].house_title+'</strong></p>'+                
						'<p>报单进度：<strong>'+names[info[i].mflow]+'</strong></p>'+                
					'</div>'+
					'</span>'+
					'<div class="report-info-agin">'+restart+'</div>'+
				'</a>';
		report_yue = info[i].month_item;
	}
	$(".report-list").html(list);
}

function gotodetailpage(report_id,fundtype){
	window.location.href='/report/detailpage/report_id/'+report_id+'/type/'+fundtype;
}

var btnmore=$(".btn-more");
var submitnav=$(".submitreport-head"),
	submithouse=$(".report-submithouse-page"),
	fasthouse=$(".report-fasthouse-page");
var navlist=submitnav.find("li");
navlist.on("click",function(){
	page=1;
	var _this=$(this);
	if(_this.hasClass("disabled")){
		return false;
	}
	_this.addClass("disabled");
	_this.addClass("active").siblings().removeClass("active");
	var houselist=_this.index(),
	 	list=_this.parent().siblings(".report-submithouse-page").children(".report-submithouse-lei").children(".active").index(),
		index=_this.parent().siblings(".report-submithouse-page").children(".report-submithouse-state").children(".active").index(),
		data={};
	if(houselist==1){
		submithouse.show();
		fasthouse.hide();
		data={
			houselist:1,		//精准评房记录
			category:list,		//房抵/垫资
			state:index,		//抵押类型
			page:page
		};
	}else{
		submithouse.hide();
		fasthouse.show();
		data={
			houselist:0,
			page:page
		}
	}
	$.ajax({       			//快速评房记录
		url:'/report/getQuickHouseListAjax',
		type:"post",
		dataType:"json",
		data:data,
		success:function(data){
			// $(this).removeClass("disabled")
			if(data.error == 1){
				$(".list-empty").hide();
				var data = data.data;
				var listdata = "";
				if(houselist == 1){
					listprecise(data,page,list);
				}else{
					listquick(data,page);
				}
				if(data.length == 5){
					$(".btn-more").removeClass("disabled").html("点击加载更多");
				}else{
					$(".btn-more").addClass("disabled").html("已加载完成");
				}
				
			}else{
				if(houselist == 1){
					$(".list-empty").show();
					$(".btn-more").addClass("disabled").html("");
					$(".report-list").html("");
				}else{
					$(".btn-more").addClass("disabled").html("暂无数据");
					$(".report-list").html("");
				}
			}
			_this.removeClass("disabled");
		}
	})
	
})

var category=$(".report-submithouse-lei").find("li");
var state=$(".report-submithouse-state").find("li");
category.on("click",function(){
	page=1;
	// if($(this).hasClass("disabled")){
	// 	return false;
	// }
	// $(this).addClass("disabled");
	$(this).addClass("active").siblings().removeClass("active");
	var index=$(this).parent().next().children(".active").index();
	var list=$(this).index();
	$.ajax({       			//精准评房选取房抵/垫资时，默认类型为全部
		url:'/report/getQuickHouseListAjax',
		type:"post",
		dataType:"json",
		data:{
			houselist:1,		//精准评房
			category:list,		//房抵/垫资
			state:index	,		//查找的具体类型
			page:page
		},
		success:function(data){
			if(data.error == 1){
				$(".list-empty").hide();
				var data = data.data;
				listprecise(data,page,list);
				if(data.length == 5){
					$(".btn-more").removeClass("disabled").html("点击加载更多");
				}else{
					$(".btn-more").addClass("disabled").html("已加载完成");
				}
			}else{
				$(".list-empty").show();
				$(".btn-more").addClass("disabled").html("");
				$(".report-list").html("");
			}
			// $(this).removeClass("disabled")
		}
	})
})
state.on("click",function(){
	page=1;
	$(this).addClass("active").siblings().removeClass("active");
	var list=$(this).parent().prev().children(".active").index();
	var index=$(this).index();
	$.ajax({       			//精准评房选取房抵/垫资时，默认类型为全部
		url:'/report/getQuickHouseListAjax',
		type:"post",
		dataType:"json",
		data:{
			houselist:1,		//精准评房
			category:list,		//房抵/垫资
			state:index	,		//查找的具体类型
			page:page
		},
		success:function(data){
			// $(this).removeClass("disabled")
			if(data.error == 1){
				$(".list-empty").hide();
				var data = data.data;
				listprecise(data,page,list);
				if(data.length == 5){
					$(".btn-more").removeClass("disabled").html("点击加载更多");
				}else{
					$(".btn-more").addClass("disabled").html("已加载完成");
				}
			}else{
				$(".list-empty").show();
				$(".btn-more").addClass("disabled").html("");
				$(".report-list").html("");
			}
		}
	})
})

var search=$(".report-fasthouse-search>span"),
	input=$(".report-fasthouse-search>input");
var value=" ";
search.on("click",function(){
	page=1;
	value=input.val();
	if(value==""){
		pop.error("请输入房屋地址关键字进行搜索！");
		return false
	}
	$.ajax({        			//关键词搜索
		url:'/report/getQuickHouseListAjax',
		type:"post",
		dataType:"json",
		data:{
			houselist:0,
			value:value,
			page:page
		},
		success:function(data){
			// $(this).removeClass("disabled")
			if(data.error == 1){
				var data = data.data;
				listquick(data,page);
				if(data.length == 5){
					$(".btn-more").removeClass("disabled").html("点击加载更多");
				}else{
					$(".btn-more").addClass("disabled").html("已加载完成");
				}
			}else{
				$(".btn-more").addClass("disabled").html("暂无数据");
				$(".report-list").html("");
			}	
		}
	})
	return value;
})

// 点击加载更多
btnmore.on("click",function(){
	if ($(this).hasClass('disabled')) {
        return false; 
    };
    btnmore.addClass('disabled').html('<div class="more-loading"><i class="bounce1"></i><i class="bounce2"></i><i class="bounce3"></i></div>');
    page++;
	var data={};
	var houselist=$('.submitreport-head').children(".active").index(),
		list=$(".report-submithouse-lei").children(".active").index(),
	 	index=$(".report-submithouse-state").children(".active").index();
	if(houselist==1){
		data={
			houselist:1,		//精准评房
			category:list,		//房抵/垫资
			state:index	,		//查找的具体类型
			page:page 			//当前查找页
		}
	}else{
		data={
			houselist:0,		//快速评房
			value:value,
			page:page 			//当前查找页
		}
	}
	$.ajax({
		url:'/report/getQuickHouseListAjax',
		type:"post",
		dataType:"json",
		data:data,
		success:function(data){
			if (data.error == 1) {
				var data = data.data;
				if(houselist == 1){
					listprecise(data,page,list);
				}else{
					listdata = listquick(data,page);
				}
				
				if(data.length == 5){
					$(".btn-more").removeClass("disabled").html("点击加载更多");
				}else{
					$(".btn-more").addClass("disabled").html("已加载完成");
				}
        	}else{
				$(".btn-more").addClass("disabled").html("已加载完成");
				return false;
	            pop.error('暂无更多数据');
	            btnmore.removeClass('disabled').html('点击获取更多');
	            return false;
        	};
		}
	})
})
$(".submit-page").on("click","a",function(){
        if($(this).hasClass("false")){
            $(".submit-page").hide();
        }
    })