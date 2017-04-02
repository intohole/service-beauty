			var statussub = 0;
			var btnSubmit = $('.submit');
			btnSubmit.on('click', function(e){
			    e.preventDefault(); 
				var _this = $(this);
				var name = $.trim($('#showCityPicker').val());
				if (!name) {
					pop.error('请选择所在城市');
					return;
				};
				var addr = $.trim($('#addr').text());
				if (!addr) {
					pop.error('请选择房屋地址');
					return;
				};

				var forests = $.trim($('#input').val());
				if (!forests) {
					pop.error('面积不能为空');
					return;
				}
				var forest = parseInt($.trim($('#input').val()));
				if (forest >= 10000) {
					pop.error('面积不能超过10000');
					return;
				}else if(forest < 0){
					pop.error('面积不能低于0');
					return;
				}
				var showfunded = $.trim($('#showfunded').val());
				if (!showfunded) {
					pop.error('请选择房产种类');
					return;
				};
				var showplaning = $.trim($('#showplaning').val());
				if (!showplaning) {
					pop.error('请选择规划用途');
					return;
				};
				var shownetworkr = $.trim($('#shownetworkr').val());
				if (!shownetworkr) {
					pop.error('请选择服务网店');
					return;
				};
				if(statussub == 1){
					return;
				}
				statussub = 1;
				pop.loading('提交中...');
				var city = $("#showCityPicker").attr("data-t");
				var province = $("#showCityPicker").attr("data-i");
				var area = $("#showCityPicker").attr("data-b");
				/* var xq = $(".xiaoqu li").attr("data-t");
				var ld = $(".loudong li").attr("data-i");
				var dy = $(".danyuan li").attr("data-house"); */
				
				var dy = $("#allid").attr('house_id');
				var ld = $("#allid").attr('build_id');
				var xq = $("#allid").attr('construction_id');
				
				
				var reportorfunder = $("#reportorfunder").val();
				var service_id = $("#area_id").val();
				
			    $.post('/report/getQuickHouseinfoAjax',{
			    		name : name,
			    		addr : addr,
			    		forests : forests,
			    		showfunded : showfunded,
			    		showplaning : showplaning,
			    		shownetworkr : shownetworkr,
			    		city_id : city,
			    		provinceid : province,
			    		area_id : area,
			    		xq : xq,
			    		ld : ld,
			    		dy : dy,
						type:reportorfunder,
						service_id:service_id

			    	}, function(data){
			    		pop.clear();
						statussub = 0;
			    		if(data.error == 1){
			    			window.location.href='/report/evaluatsuccess?id='+data.data.id+'&type='+data.data.type;
			    		}else if(data.error == 0){
			    			window.location.href='/report/evaluatfailure?id='+data.data.id+'&reportorfunder='+reportorfunder+'&area_id='+service_id;
			    		}else if(data.error == 2){
			    			pop.error(data.data);
			    		}else if(data.error == 3){
			    			$('.fast-box').css('display','block');
			    		}
			    		return;
			    	},
				'json')
			});
			

			$('.yzm-cmt2').on('click', function(e){
			    e.preventDefault(); 
				var _this = $(this);
				var name = $.trim($('#showCityPicker').val());
				if (!name) {
					pop.error('请选择所在城市');
					return;
				};
				var addr = $.trim($('#addr').text());
				if (!addr) {
					pop.error('请选择房屋地址');
					return;
				};

				var forests = $.trim($('#input').val());
				if (!forests) {
					pop.error('面积不能为空');
					return;
				}
				var forest = parseInt($.trim($('#input').val()));
				if (forest >= 10000) {
					pop.error('面积不能超过10000');
					return;
				}else if(forest < 0){
					pop.error('面积不能低于0');
					return;
				}
				var showfunded = $.trim($('#showfunded').val());
				if (!showfunded) {
					pop.error('请选择房产种类');
					return;
				};
				var showplaning = $.trim($('#showplaning').val());
				if (!showplaning) {
					pop.error('请选择规划用途');
					return;
				};
				var shownetworkr = $.trim($('#shownetworkr').val());
				if (!shownetworkr) {
					pop.error('请选择服务网店');
					return;
				};
				if(statussub == 1){
					return;
				}
				statussub = 1;
				pop.loading('提交中...');
				var city = $("#showCityPicker").attr("data-t");
				var province = $("#showCityPicker").attr("data-i");
				var area = $("#showCityPicker").attr("data-b");
				/* var xq = $(".xiaoqu li").attr("data-t");
				var ld = $(".loudong li").attr("data-i");
				var dy = $(".danyuan li").attr("data-house"); */
				
				var dy = $("#allid").attr('house_id');
				var ld = $("#allid").attr('build_id');
				var xq = $("#allid").attr('construction_id');
				
				
				var reportorfunder = $("#reportorfunder").val();
				var service_id = $("#area_id").val();
				var code = $("input[name=code]").val();
				
			    $.post('/report/getQuickHouseinfoAjax',{
			    		name : name,
			    		addr : addr,
			    		forests : forests,
			    		showfunded : showfunded,
			    		showplaning : showplaning,
			    		shownetworkr : shownetworkr,
			    		city_id : city,
			    		provinceid : province,
			    		area_id : area,
			    		xq : xq,
			    		ld : ld,
			    		dy : dy,
						type:reportorfunder,
						service_id:service_id,
						code : code
			    	}, function(data){
			    		pop.clear();
						statussub = 0;
			    		if(data.error == 1){
			    			window.location.href='/report/evaluatsuccess?id='+data.data.id+'&type='+data.data.type;
			    		}else if(data.error == 0){
			    			window.location.href='/report/evaluatfailure?id='+data.data.id+'&reportorfunder='+reportorfunder+'&area_id='+service_id;
			    		}else if(data.error == 2){
			    			pop.error(data.data);
			    		}
			    		return;
			    	},
				'json')
			});

			var html = [];
			$('.dizhi').css('display','none');
			$('.addr_area').click(function(){
				html = [];
				//判断所在地址是否为空
				if(!$.trim($('#showCityPicker').val())){
					pop.error('请选择所在城市');
					return;
				}

				$('.pingfang').css('display','none');
				$('.dizhi').css('display','block');

			});

			// $('.xiaoqu ul').on('click','li',function(){
				
			// 	html.push($(this).text());
			// });

			// $('.loudong ul').on('click','li',function(){
				
			// 	html.push($(this).text());
			// });
			var test = [];
			$('.danyuan ul').on('click','li',function(){
				$('.danyuan').css('display','none');
				$('.pingfang').css('display','block');
				$('.dizhi').css('display','none');
				$('.xiaoqu').css('display','block');
				
				test.push($(this).text());
				console.log(test.join(' '));
				$('#addr').text(test.join(' '));
				
				var house_id = $(this).attr('data-house');
				var build_id = $(this).attr('data-i');
				var construction_id = $(this).attr('data-t');
				$("#allid").attr('house_id',house_id);
				$("#allid").attr('build_id',build_id);
				$("#allid").attr('construction_id',construction_id);
				
			});


			// 小区请求
			$('.xiaoqu .wbk span').click(function(){
				xiaoquselect(0);
				
			});
			
			var villagevaluekey = "";
			var villagekey = "";
			$(document).on('keyup', '#village', function () {
				villagekey = $(this).val().replace(/\ +/g,"");
				//alert(villagekey);
				if(villagekey != villagevaluekey && villagekey !=""){
					xiaoquselect(1);
				}
				villagevaluekey = villagekey;
			});
			
			
			//小区搜索
			function xiaoquselect(a){
				
				var val = $("#showCityPicker").attr("data-t");
				var village=$("#village").val().replace(/\ +/g,"");
				if(village == "" || village ==null){
					pop.error("输入值不能为空！");
					return;
				}
				if(a==0){
					pop.loading('正在查询...');
				}
				
				$.ajax({
			    	url:'getCityKeyAjax',
			    	type:'post',
			    	data:{
			    		val:val,
			    		village:village
			    	},
			    	dataType:"json",
			    	success : function(data){
			    		pop.clear();
			    		if(data.error == 1){
			    			var data = data.data;
				    		var list=$(".xiaoqu .bd ul");
				    		var html="";
				    		for(row in data){
				    			html+="<li data-t="+data[row].construction_id+"><a  href='javascript:;'>"+data[row].constructionname+"</a></li>";
				    		}
				    		list.html(html);
				    	}else if(data.error == 0){
			    			pop.error(data.data);
			    		}else if(data.error == 2){
			    			pop.error('重新搜索...');
			    		}
			    	}
			    })
			}
			
			//楼栋号请求
			$('.loudong .wbk span').click(function(){
				loudongselect(1);
			});
            
			var village_loudongvaluekey = "";
			var village_loudongkey = "";
			$(document).on('keyup', '#village_loudong', function () {
				village_loudongkey = $(this).val().replace(/\ +/g,"");
				if(village_loudongkey != village_loudongvaluekey && village_loudongkey != ''){
					loudongselect(0);
				}
				village_loudongvaluekey = village_loudongkey;
				
			});
			
			function loudongselect(a){
				//alert(2);
				var val = $("#showCityPicker").attr("data-t");
				var village=$("#village_loudong").val().replace(/\ +/g,"");
				if(village == "" || village ==null){
					pop.error("输入值不能为空！");
					return;
				}
				if(a==0){
					pop.loading('正在查询...');
				}
				$.ajax({
			    	url:'getCityBuildLessAjax',
			    	type:'post',
			    	data:{
			    		name:'build',
			    		village:village
			    	},
			    	dataType:"json",
			    	success : function(data){
			    		pop.clear();
			    		if(data.error == 1){
			    			var data = data.data;
				    		var list=$(".loudong .bd ul");
				    		var html="";
				    		for(row in data){
				    			html+="<li data-val="+val+" data-name="+data[row].buildname+" data-i="+data[row].build_id+" data-t="+data[row].construction_id+"><a  href='javascript:;'>"+data[row].buildname+"</a></li>";
				    		}
				    		list.html(html);
			    		}else if(data.error == 0){
			    			pop.error(data.data);
			    		}else if(data.error == 2){
			    			pop.error('重新搜索...');
			    		}
			    	}
			    })
			}
			
			//小区搜索
			$('.xiaoqu .bd ul').on("click","li",function(){
				//alert("xiaoqu bd");
				var val = $("#showCityPicker").attr("data-t");
				var list=$(this).attr('data-t');
				var html=$(this).find('a').html();
				var constructionname = $(this).find('a').html();
				//$("#allid").attr('construction_id',list);
				pop.loading('正在查询...');
				$.ajax({
			    	url:'getCityBuildAjax',
			    	type:'post',
			    	data:{
			    		val:val,
			    		construction_id:list,
			    		constructionname:html
			    	},
			    	dataType:"json",
			    	success : function(data){
			    		pop.clear();
			    		if(data.error == 1){
			    			var data = data.data;
				    		var list=$(".loudong .bd ul");
				    		var html="";
				    		for(row in data){
				    			html+="<li data-val="+val+" data-name="+data[row].buildname+" data-i="+data[row].build_id+" data-t="+data[row].construction_id+"><a  href='javascript:;'>"+data[row].buildname+"</a></li>";
				    		}
				    		list.html(html);
					    	$('.xiaoqu').css('display','none');
							$('.loudong').css('display','block');
							test = [];
							test.push(constructionname);
							console.log(test);
			    		}else if(data.error == 0){
			    			pop.error(data.data);
			    		}else if(data.error == 2){
			    			pop.error('重新搜索...');
			    		}
			    	}
			    })
			});

			//房间号搜索
			$('.danyuan .wbk span').click(function(){
				//alert("danyuan");
				danyuanselect(0);
			});
			
			var village_danyuanvaluekey = "";
			var village_danyuankey = "";
			$(document).on('keyup', '#village_danyuan', function () {
				village_danyuankey = $(this).val().replace(/\ +/g,"");
				if(village_danyuanvaluekey != village_danyuankey && village_danyuankey !=""){
					danyuanselect(1);
				}
				village_danyuanvaluekey = village_danyuankey;
				
			});
			
			function danyuanselect(a){
				//alert(3);
				var village=$("#village_danyuan").val().replace(/\ +/g,"");
				if(village == "" || village ==null){
					pop.error("输入值不能为空！");
					return;
				}
				
				if(a==0){
					pop.loading('正在查询...');
				}
				$.ajax({
			    	url:'getCityBuildLessAjax',
			    	type:'post',
			    	data:{
			    		name:'house',
			    		village:village
			    	},
			    	dataType:"json",
			    	success : function(data){
			    		pop.clear();
			    		if(data.error == 1){
			    			var data = data.data;
				    		var list=$(".danyuan .bd ul");
				    		var html="";
				    		for(row in data){
				    			html+="<li data-house="+data[row].house_id+" data-i="+data[row].build_id+" data-t="+data[row].construction_id+"><a  href='javascript:;'>"+data[row].house_name+"</a></li>";
				    		}
				    		list.html(html);
				    	}else if(data.error == 0){
			    			pop.error(data.data);
			    		}else if(data.error == 2){
			    			pop.error('重新搜索...');
			    		}
			    	}
			    })
			}
			
			$('.loudong .bd ul').on("click","li",function(){
				//alert("loudong bd");
				var val = $(this).attr("data-val");
				var construction_id=$(this).attr('data-t');
				var build_id = $(this).attr('data-i');
				var html=$(this).find('a').html();
				var buildname = $(this).attr('data-name');
				//$("#allid").attr('build_id',build_id);
				pop.loading('正在查询...');
				$.ajax({
			    	url:'getCityBuildHouseAjax',
			    	type:'post',
			    	data:{
			    		val:val,
			    		construction_id:construction_id,
			    		build_id:build_id,
			    		buildname : buildname
			    	},
			    	dataType:"json",
			    	success : function(data){
			    		pop.clear();
			    		if(data.error == 1){
			    			var data = data.data;
				    		var list=$(".danyuan .bd ul");
				    		var html="";
				    		for(row in data){
				    			html+="<li data-house="+data[row].house_id+" data-i="+data[row].build_id+" data-t="+data[row].construction_id+"><a  href='javascript:;'>"+data[row].house_name+"</a></li>";
				    		}
				    		list.html(html);
				    		$('.loudong').css('display','none');
							$('.danyuan').css('display','block');
							test.push(buildname);
							console.log(test);
				    	}else if(data.error == 0){
			    			pop.error(data.data);
			    		}else if(data.error == 2){
			    			pop.error('重新搜索...');
			    		}
			    	}
			    })
			})
			
			function test(){
				alert(1111);
			}
