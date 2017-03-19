var data_t='',
	data_f="",
	finish="",
	unfinish="",
	btnmore=$(".btn-more");
var html=[];
var arr=[];
var app_on=$(".application-on");
var app_over=$(".application-over");
$(function(){
		var role=$("input[name=role_id]").val();
		
		$.ajax({
			url:"/m/detailsAjax",
			type:"get",
			dataType:"json",
			data:{
				role_id:role
			},
			success:function(data){
                if(!data.finished){
                    data.finished={};
                }
                if(!data.unfinished){
                    data.unfinished={}
                }
				finish=data.finished;
				unfinish=data.unfinished;
				role_id=data.role_id;
				// var status=["第一笔放款","回款","第二笔放款","完成"];

				if(data.finished.length>=4){

					for(var i=0;i<4;i++){
						var unit="";
						var name="";
						if(data.finished[i].type==null || data.finished[i].type==1){
							unit="月";
							name="房抵"
						}else{
							unit="日";
							name="垫资"
						}
						html.push('<a href="/m/detailpage?app_id='+data.finished[i].id+'&&role_id='+data.role_id+'&&add_f='+ 1 +'" href="javaScript:;" class="application-over-group-wait">\
										<i>	</i>\
										<span>'+name+'</span>\
										<div class="group-content">\
											<p>\
												<span>'+data.finished[i].borrower+' <b>|</b> '+data.finished[i].loans_amount+'万元</span>\
											</p>\
											<p>\
												<span>借款期限：<b>'+data.finished[i].loans_life+'</b>'+unit+'</b></span>\
											</p>\
											<p>\
												<span>放款时间：<b>'+data.finished[i].loans_time+'</b></span>\
											</p>\
											<p>\
												<span>放款申请人：<b>'+data.finished[i].notarizer+'</b></span>\
											</p>\
										</div>\
								</a>');
					}
					finish.splice(0,4);
				}else if(finish.length<4 && finish.length>0){
					for(var i=0;i<data.finished.length;i++){
						var unit="";
						var name="";
						if(data.finished[i].type==null || data.finished[i].type==1){
							unit="月";
							name="房抵"
						}else{
							unit="日";
							name="垫资"
						}
						html.push('<a href="/m/detailpage?app_id='+data.finished[i].id+'&&role_id='+data.role_id+'&&add_f='+ 1 +'" href="javaScript:;" class="application-over-group-wait">\
										<i>	</i>\
										<span>'+name+'</span>\
										<div class="group-content">\
											<p>\
												<span>'+data.finished[i].borrower+' <b>|</b> '+data.finished[i].loans_amount+'万元</span>\
											</p>\
											<p>\
												<span>借款期限：<b>'+data.finished[i].loans_life+'</b>'+unit+'</b></span>\
											</p>\
											<p>\
												<span>放款时间：<b>'+data.finished[i].loans_time+'</b></span>\
											</p>\
											<p>\
												<span>放款申请人：<b>'+data.finished[i].notarizer+'</b></span>\
											</p>\
										</div>\
								</a>');
					}
					finish.splice(0,data.finished.length);
				}else{
					$(".btn-defule").show();
					$(".btn-more").hide();
				}
				app_over.html(html);
				if(data.unfinished.length>=4){
					for(var j=0;j<4;j++){
						var unit="";
						var name="";
						if(data.unfinished[j].type==null || data.unfinished[j].type==1){
							unit="月";
							name="房抵"
						}else{
							unit="日";
							name="垫资"
						}
						arr.push('<a  href="/m/detailpage?app_id='+data.unfinished[j].id+'&&role_id='+data.role_id+'&&add_f='+ 2 +'" class="application-over-group-wait">\
										<i>	</i>\
										<span>'+name+'</span>\
										<div class="group-content">\
											<p>\
												<span>'+data.unfinished[j].borrower+' <b>|</b> '+data.unfinished[j].loans_amount+'万元</span>\
											</p>\
											<p>\
												<span>借款期限：<b>'+data.unfinished[j].loans_life+'</b>'+unit+'</b></span>\
											</p>\
											<p>\
												<span>放款时间：<b>'+data.unfinished[j].loans_time+'</b></span>\
											</p>\
											<p>\
												<span>放款申请人：<b>'+data.unfinished[j].notarizer+'</b></span>\
											</p>\
										</div>\
								</a>');
					}
					data.unfinished.splice(0,4);
				}else if(unfinish.length<4 && unfinish.length>0){
					for(var j=0;j<data.unfinished.length;j++){
						var unit="";
						var name="";
						if(data.unfinished[j].type==null || data.unfinished[j].type==1){
							unit="月";
							name="房抵"
						}else{
							unit="日";
							name="垫资"
						}
						arr.push('<a  href="/m/detailpage?app_id='+data.unfinished[j].id+'&&role_id='+data.role_id+'&&add_f='+ 2 +'" class="application-over-group-wait">\
										<i>	</i>\
										<span>'+name+'</span>\
										<div class="group-content">\
											<p>\
												<span>'+data.unfinished[j].borrower+' <b>|</b> '+data.unfinished[j].loans_amount+'万元</span>\
											</p>\
											<p>\
												<span>借款期限：<b>'+data.unfinished[j].loans_life+'</b>'+unit+'</b></span>\
											</p>\
											<p>\
												<span>放款时间：<b>'+data.unfinished[j].loans_time+'</b></span>\
											</p>\
											<p>\
												<span>放款申请人：<b>'+data.unfinished[j].notarizer+'</b></span>\
											</p>\
										</div>\
								</a>')
					}
					unfinish.splice(0,unfinish.length);
				}else{
					$(".btn-defule").show();
					$(".btn-more").hide();
				}
				app_on.html(arr);
			}

		})
	})
	btnmore.on("click",function(){
		if($(this).hasClass("disabled")){
			return false;
		}
		$(this).addClass("disabled");
		if($(".application-list-on").hasClass("active")){
			if(unfinish.length>=4){
					for(var j=0;j<4;j++){
						var unit="";
						var name="";
						if(unfinish[j].type==null || unfinish[j].type==1){
							unit="月";
							name="房抵"
						}else{
							unit="日";
							name="垫资"
						}
						arr.push('<a  href="/m/detailpage?app_id='+unfinish[j].id+'&&role_id ='+role_id+'&&add_f='+ 2 +'" class="application-over-group-wait">\
										<i>	</i>\
										<span>'+name+'</span>\
										<div class="group-content">\
											<p>\
												<span>'+unfinish[j].borrower+' <b>|</b> '+unfinish[j].loans_amount+'万元</span>\
											</p>\
											<p>\
												<span>借款期限：<b>'+unfinish[j].loans_life+'</b>'+unit+'</b></span>\
											</p>\
											<p>\
												<span>放款时间：<b>'+unfinish[j].loans_time+'</b></span>\
											</p>\
											<p>\
												<span>放款申请人：<b>'+unfinish[j].notarizer+'</b></span>\
											</p>\
										</div>\
								</a>')
					}
					unfinish.splice(0,4);
				}else if(unfinish.length==0){
					pop.error("没有更多数据！");
				}else{
					for(var j=0;j<unfinish.length;j++){
						var unit="";
						var name="";
						if(unfinish[j].type==null || unfinish[j].type==1){
							unit="月";
							name="房抵"
						}else{
							unit="日";
							name="垫资"
						}
						arr.push('<a  href="/m/detailpage?app_id='+unfinish[j].id+'&&role_id ='+role_id+'&&add_f='+ 2 +'" class="application-over-group-wait">\
										<i>	</i>\
										<span>'+name+'</span>\
										<div class="group-content">\
											<p>\
												<span>'+unfinish[j].borrower+' <b>|</b> '+unfinish[j].loans_amount+'万元</span>\
											</p>\
											<p>\
												<span>借款期限：<b>'+unfinish[j].loans_life+'</b>'+unit+'</b></span>\
											</p>\
											<p>\
												<span>放款时间：<b>'+unfinish[j].loans_time+'</b></span>\
											</p>\
											<p>\
												<span>放款申请人：<b>'+unfinish[j].notarizer+'</b></span>\
											</p>\
										</div>\
								</a>')
					}
					unfinish.splice(0,unfinish.length);
				}
				$(this).removeClass("disabled");
				app_on.html(arr);
		}else if($(".application-list-over").hasClass("active")){
			if(finish.length>=4){
					for(var i=0;i<4;i++){
						var unit="";
						var name="";
						if(finish[i].type==null || finish[i].type==1){
							unit="月";
							name="房抵"
						}else{
							unit="日";
							name="垫资"
						}
						html.push('<a href="/m/detailpage?app_id='+finish[i].id+'&&role_id ='+role_id+'&&add_f='+ 1 +'" href="javaScript:;" class="application-over-group-wait">\
										<i>	</i>\
										<span>'+name+'</span>\
										<div class="group-content">\
											<p>\
												<span>'+finish[i].borrower+' <b>|</b> '+finish[i].loans_amount+'万元</span>\
											</p>\
											<p>\
												<span>借款期限：<b>'+finish[i].loans_life+'</b>个月</b></span>\
											</p>\
											<p>\
												<span>放款时间：<b>'+finish[i].loans_time+'</b></span>\
											</p>\
											<p>\
												<span>放款申请人：<b>'+finish[i].notarizer+'</b></span>\
											</p>\
										</div>\
								</a>');
					}
					finish.splice(0,4);
				}else if(finish.length==0){
					pop.error("没有更多数据！");
				}else{
					for(var i=0;i<finish.length;i++){
						var unit="";
						var name="";
						if(finish[i].type==null || finish[i].type==1){
							unit="月";
							name="房抵"
						}else{
							unit="日";
							name="垫资"
						}
						html.push('<a href="/m/detailpage?app_id='+finish[i].id+'&&role_id='+role_id+'&&add_f='+ 1 +'" href="javaScript:;" class="application-over-group-wait">\
										<i>	</i>\
										<span>'+name+'</span>\
										<div class="group-content">\
											<p>\
												<span>'+finish[i].borrower+' <b>|</b> '+finish[i].loans_amount+'万元</span>\
											</p>\
											<p>\
												<span>借款期限：<b>'+finish[i].loans_life+'</b>个月</b></span>\
											</p>\
											<p>\
												<span>放款时间：<b>'+finish[i].loans_time+'</b></span>\
											</p>\
											<p>\
												<span>放款申请人：<b>'+finish[i].notarizer+'</b></span>\
											</p>\
										</div>\
								</a>');
					}
					finish.splice(0,finish.length);
				}
				$(this).removeClass("disabled");
				app_over.html(html);
		}
	})
	$(".application-head").on("click","li",function(){
		$(this).addClass("active").siblings("li").removeClass("active");
		var data=$(this).attr("data-t");
		$("."+data).show();
		var datas=$(this).siblings("li").attr("data-t");
		$("."+datas).hide();
	})
	$(".application-on").on("click",".application-approval",function(event){
		// event.preventDefault();
		if (event && event.preventDefault) {
        	event.preventDefault();
 		}else{ 
        	window.event.returnValue = false;
 		};
		$(".submit-page").show();
		data_t=$(this).parents().attr('data-t');
		data_f=$(this).parents().attr("data-f");
	})
	$(".submit-page").on("click","span",function(){
        if($(this).hasClass("false")){
           	location.href="/m/rejected?app_id="+data_t;
        }else{
            $.ajax({
                url:"/m/submitdetails",
                type:"get",
                dataType:"json",
                data:{
                   app_id:data_t,
                   flower:data_f
                },
                success:function(data){
		            pop.error(data.errmsg);
		            history.go(0);
		            
		        }
            })
        }
    })