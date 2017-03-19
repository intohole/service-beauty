$(function(){
	var listBox=$('#listBox'),
		cancelCheck=$('#cancelCheck'),
		trueCheck=$('#trueCheck'),
		checkHtml='',
		kehuinfo=$('#kehuinfo'),
		carinfo=$('#carinfo'),
		page=1,
		flag=1,
		businessBox=$('#businessBox'),
		more=$('#wrap-more').find('a'),
		kehuid=0,
		carid=0,
		kehuid1=0,
		carid1=0,
		addsub=$('#addsub'),
		amount=$("input[name='amount']"),
		rate=$("input[name='rate']"),
		marriageName = ['未婚', '已婚', ''],
		textarea=$(".textarea");

	$(document.body).on('click','.business-add a',function(){
		businessBox.removeClass('dpn');
		more.html('<div class="more-loading"><i class="bounce1"></i><i class="bounce2"></i><i class="bounce3"></i></div>');
		flag=Number($(this).attr('data-flag'));
		getlist('/chedai/salesman_loanaddajax',flag);
	});

	more.on('click',function(){
		if($(this).hasClass('disabled')){
			return;
		}
		getlist('/chedai/salesman_loanaddajax',flag);
	});

	function getlist(url,flag){
		$.ajax({
			url:url,
			type:'POST',
			dataType:'json',
			data:{
				page:page,
				sel_type:flag
			},success:function(data){

				if(data.error==0){
					page++;
					var len=data.res.length,
						html='';
					for(var i=0;i<len;i++){
						
			            if(flag==1){
			            	html+='<div class="dpb wrap-list-block"><a class="item check-item" data-id="'+data.res[i].id+'" href="javascript:;"><strong class="check-item-icon "></strong>\
						<p class="clearfix"><span>客户编号：<b>'+data.res[i].id+'</b></span><span>录入时间：<b>'+data.res[i].created+'</b></span></p>';
			            	html+='<p class="clearfix"><span>客户姓名：<b>'+data.res[i].name+'</b></span><span>客户年龄：<b>'+data.res[i].age+'</b></span></p>\
			            	<p class="clearfix"><span>婚姻状况：<b>'+marriageName[data.res[i].marriage]+'</b></span><span>客户手机：<b>'+data.res[i].phone+'</b></span></p>\
			            	</a></div>';
			            }else{
			            	html+='<div class="dpb wrap-list-block"><a class="item check-item" data-id="'+data.res[i].id+'" href="javascript:;"><strong class="check-item-icon "></strong>\
						<p class="clearfix"><span>车辆编号：<b>'+data.res[i].id+'</b></span><span>录入时间：<b>'+data.res[i].created+'</b></span></p>';
			            	if(!data.res[i].estimate_total|| data.res[i].estimate_total==null || data.res[i].estimate_total==0 || data.res[i].estimate_total==0.00){
			            		var price='待评估';
			            	}else{
			            		var price=data.res[i].estimate_total;
			            	}
			            	html+='<p class="clearfix"><span>车辆品牌：<b>'+data.res[i].brand+'</b></span><span>车辆车系：<b>'+data.res[i].series+'</b></span></p>\
			            	<p class="clearfix"><span>车辆型号：<b>'+data.res[i].version+'</b></span></p>\
			            	<p class="clearfix"><span>车架号：<b>'+data.res[i].frame+'</b></span></p>\
			            	<p class="clearfix"><span>评估价值：<b>'+price+'</b></span></p>\
			            	</a></div>';
			            }
					}
			        listBox.append(html);
			        more.html('点击获取更多');
				}else if(data.error==101){
					more.html('无更多数据').addClass('disabled');
				}
			}
		});
	}

	$(document.body).on('click','.check-item',function(){
		var that=$(this);
		listBox.find('.check-item').removeClass('check-item-checked');
		that.addClass('check-item-checked');
		checkHtml=that.parent().clone();
		checkHtml.eq(0).children().removeClass('check-item check-item-checked').addClass('item-link').attr('data-flag',flag);
		if(flag==1){
			kehuid=Number(that.attr('data-id'));
		}else{
			carid=Number(that.attr('data-id'));
		}
	});

	cancelCheck.on('click',function(){
		checkHtml='';
		listBox.html('');
		page=1;
		businessBox.addClass('dpn');
		more.removeClass('disabled');
	});
	trueCheck.on('click',function(){
		if(checkHtml){
			if(flag==1){
				kehuinfo.html(checkHtml);
			}else{
				carinfo.html(checkHtml);
			}
			listBox.html('');
			page=1;
		}
		if(kehuid!=kehuid1){
			kehuid1=kehuid;
		}
		if(carid!=carid1){
			carid1=carid;
		}
		console.log(carid1,kehuid1,kehuid,carid);
		if(kehuid1!=0 && carid1!=0){
			$('#bus-add-input').removeClass('dpn');
		}
		more.removeClass('disabled');
		businessBox.addClass('dpn');
		checkHtml='';
	});

	addsub.on('click',function(){
		if(kehuid1==0||carid1==0){
			return;
		}
		if(amount.val()==''){
			pop.error('请输入贷款额度');
			return;
		}
		if(rate.val()==''){
			pop.error('请输入贷款利率');
			return;
		}
		if(textarea.val==""){
			pop.error('请输入业务意见');
			return;
		}
		if($(this).hasClass('disabled')){
			return;
		}
		$(this).addClass('disabled');
		$.ajax({
			url:'/chedai/salesman_loansubmitAjax',
			type:'POST',
			dataType:'json',
			data:{
				customer: kehuid1,
				car: carid1,
				amount: amount.val(),
				rate: rate.val(),
				deadline: $('#loanaddTime').val(),
				pingushi: $('#loanaddAppraiser').val(),
				opinion:textarea.val()
			},success:function(data){
				if(data.error==0){
					location.href='/chedai/salesman_loanlist';
				}else{
					pop.error(data.msg);
				}
				addsub.removeClass('disabled');
			}
		});

	});

})