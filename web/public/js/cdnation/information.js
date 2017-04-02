$(function(){

$('#check-day').mobiscroll().date({
    theme: 'mobiscroll',
    lang: 'zh',
    display: 'bottom',
    mode: 'scroller',
    dateOrder: 'yymmdd',
    dateFormat: 'yy/mm/dd',
    startYear: new Date().getFullYear() - 100,
    endYear: new Date().getFullYear(),
    minWidth: 100
});
//禁止空格
// inputAll.on('input propertychange', function(){
//     var _this = $(this);
//     _this.val(_this.val().replace(/\s/g,""));
// })

// 选择车型  start
// 侧边栏滑动

(function ($, undefined) {
  $.mCfg = {
    $document: $(document),
    $win: $(window),
    $body: $("body"),
    $wrapper: $(".wrapper"),
    $mask: $(".mask"),
    hideClass: 'fn-hide',
    weiboAppKey: '1035238687'
  }
  var el = $("[data-role=aside]");
  /*
   * class aside 侧边栏动画
   * @params obj config {control:触发侧边栏的开关, aside:默认的侧边栏id, timer:动画持续时间}
   */
  function Aside(config) {
    config = $.extend({
      control:"[data-role=aside]",
      aside  : ".aside", //目标侧边栏
      timer: 300 //侧边栏滑动时间
    }, config);
    var self = this;
    self.cfg = config;
    self.control = config.control; //侧边栏开关
    self.$mask = $(".mask");
    self.$aside = $(self.cfg.aside);
    self.$curAside = $(self.cfg.aside);
    self.init();
  }

  /**
   * function init 初始化
   */

  Aside.prototype.init = function () {
    var self = this;
    //保存初始化aside的z-index值
    $.mCfg.$body.data("aside-zIndex", "10000");

    //默认侧边栏状态－关闭
    self.$aside.data("isClose", true);
    self.$mask.data("isClose", true);

    // console.log(self.$mask);
    //打开
    self.open();
    //关闭
    self.close();
  }
  /**
   * function tpl 默认侧边栏模板
   */
  Aside.prototype.tpl = function(className, title){
    var self = this;
    var tpl = "<aside class='aside "+className+"'>";
    // if(navSign !== false){
    tpl += "<nav class='nav-final'>";
    tpl += "<p>"+title+"</p>";
    tpl += "<a class='back' href='#'><i class='iconfont icon-arrow-left'></i></a>";
    tpl += "</nav>";
    tpl += "<section class='aside-main'>";
    tpl += "</section>";
    tpl += "</aside>";
    if(self.$mask.length <= 0) tpl += "<div class='mask fn-hide'></div>"
    return tpl;
  }

  /**
   * function open 打开侧边栏
   */
  Aside.prototype.open = function(){
    var self = this;

    //点击打开
    $.mCfg.$body.on("click", self.control, function(){
      var $this = $(this),
          dest = $this.data("relation"),
          url = $this.data("url"); //获取数据模版的接口
      self.$curAside = $(dest ? ("."+dest) : self.cfg.aside); //相关联的侧边栏

      //侧边栏标题
      var title = !!$this.data("title") === true ? $this.data("title") : $this.text();

      //判断侧边栏是否存在，不存在则使用模板
      if(self.$curAside.length <= 0){
        $.mCfg.$body.append(self.tpl(dest, title));
        // console.log($("."+dest));
        self.$curAside = $("."+dest);
        self.$aside = $(".aside");
        self.$aside.data("isClose", true);
        self.$mask = $(".mask");
        self.$mask.data("isClose", true);
      }else{
        self.$curAside = $(dest ? ("."+dest) : self.cfg.aside ); //相关联的侧边栏
      }

      //设置侧边栏标题
      var $title = self.$curAside.find(".nav-final p");
      $title.html(title);

      //隐藏页面滚动条
      $.mCfg.$body.css("overflow", "hidden");

      self.$mask.removeClass($.mCfg.hideClass);
      self.$curAside.removeClass($.mCfg.hideClass);

      //侧边栏滑动

      self.slide(self.$curAside, $this, url);

      //关闭页面滚动条
      $.mCfg.$wrapper.css({
        "overflow":"hidden"
      });
    })
  }

  /**
   * function side 滑动
   */
  Aside.prototype.slide = function($curAside, $cur, url){
    var self = this,
        isClose = $curAside.data("isClose");

    //同时加载数据
    var hisUrl = $curAside.data("his-url");
    if(url && (!hisUrl || (hisUrl && hisUrl != url))){
      self.ajax($curAside, $cur, url);
    }

    if(isClose){
      //蒙层渐显
      self.$mask.animate({
        opacity:1
      }, self.cfg.timer, "ease-in-out");

      var curIndex = parseInt($.mCfg.$body.data("aside-zIndex"));
      $curAside.css({zIndex:curIndex + 1});
      $curAside.animate({
        translate3d: "0, 0, 0"
      }, self.cfg.timer, "ease-in-out", function(){
        $curAside.data("isClose", false);
        $.mCfg.$body.data("aside-zIndex", curIndex+1);
        self.$mask.data("isClose", false).removeClass($.mCfg.hideClass);
      });
    }

  }

  /**
   * function close 关闭侧边栏
   */
  Aside.prototype.close = function(){
    var self = this;
    //点击蒙板，关闭侧边栏
    $.mCfg.$body.on("click", ".mask, .aside-close", function(e){
      self.closeAll();
    });
    //点击侧边栏的返回按钮，关闭当前的侧边栏
    $.mCfg.$body.on("click", ".aside .back, .aside-back", function(e){
      e.stopPropagation();
      e.preventDefault();
      var $curAside = $(this).parents(".aside");
      $curAside.animate({translate3d:"100%, 0, 0"}, self.cfg.timer, "ease-in-out",function(){
        //关闭侧边栏后，设置侧边栏状态为true（关闭）
        $curAside.data("isClose", true);
      });
      // setTimeout(function(){
      //判断如果只有一个侧边栏属于打开状态，则蒙层一起关闭
      var openCnt = self.$aside.length;
      self.$aside.each(function(){
        if($(this).data("isClose") == true) --openCnt;
      });

      if(openCnt === 1){
        self.$mask.data("isClose", true).animate({
          opacity:0
        }, self.cfg.timer, "ease-in-out",function(){
          self.$mask.addClass($.mCfg.hideClass);
        });
        //关闭页面滚动条
        $.mCfg.$body.attr("style", "");
        $.mCfg.$wrapper.attr("style", "");
      };
      // }, 0);

    });
  };

  Aside.prototype.closeAll = function(){
    var self = this;
    if(self.$mask.data("isClose") === false){
      $(".aside").each(function(){
        if($(this).data("isClose") === false){
          $(this).animate({translate3d:"100%, 0, 0"}, self.cfg.timer,"ease-in-out", function(){
            //关闭侧边栏后，设置侧边栏状态为true（关闭）
            $(".aside").data("isClose", true);
            var bid = sessionStorage.getItem("bid"),
                sid = sessionStorage.getItem("sid"),
                specId = sessionStorage.getItem("specId");
            bid && $('#carName').attr('bid', bid);
            sid && $('#carName').attr('sid', sid);
            specId && $('#carName').attr('specId', specId);
            sessionStorage.clear();
          });

          self.$mask.data("isClose", true).animate({
            opacity:0
          }, self.cfg.timer, "ease-in-out",function(){
            self.$mask.addClass($.mCfg.hideClass);
          });
        }
      })
    }

    $.mCfg.$body.attr("style", "");
    $.mCfg.$wrapper.attr("style", "");
  }

  /**
   * function ajax 异步请求数据
   */
  Aside.prototype.ajax = function($aside, $cur, url){
    var self = this;
    //判断是否有遮罩层
    $.ajax({
      type:"get",
      url: url,
      dataType: "text",
      success:function(data){
        if(data){
          var $asideMain = $aside.find(".aside-main");
          if($asideMain.length){
            $asideMain.html(data);
          }else{
            $aside.html(data);
          }
          $aside.data("his-url", url);
        }
      },
      error:function(XMLHttpRequest, textStatus, errorThrown){
        console.log("ajax error");
      }
    })
  }
  //侧边栏功能
  if(el.length > 0){
    $.aside = new Aside();
  }

}(Zepto))
// 侧边栏滑动end

// 加载车型数据

var PingGU = function () {
    var ithis = this,
        carName = $('#carName');
    ithis.selectedValue = [];
        // uarea = Uarea;
    // areaId = uarea.length > 6 ? uarea.split('|')[0] : 0;

    /* 初始化 */
    this.init = function () {
        carName.on('click', ithis.showBrand);
        //初始化添加submitInfoJson的cookie信息，使用户不必输入第二遍
        // var submitInfoJson = $.getCookie('submitInfoJson', '');
        // if (submitInfoJson.length > 0) {
        //     var subJson = eval($.parseJSON(submitInfoJson));
        //     carName.attr("specid", subJson.specid);
        //     carName.attr("sid", subJson.sid);
        //     carName.attr("bid", subJson.bid);
        //     $("#carName").text(subJson.carName);
        //     carName.removeClass('active').addClass('active');
        //     $("#mileage").val(subJson.mileage);
        // }
    };

    /* 显示品牌 */
    this.showBrand = function () {
        var bid = carName.attr('bid'),
            sid = carName.attr('sid'),
            specId = carName.attr('specId');
        bid && sessionStorage.setItem('bid', bid);
        sid && sessionStorage.setItem('sid', sid);
        specId && sessionStorage.setItem('specId', specId);
        var str = '';
        var categoryShowObj = $("#categoryShow");
            $.ajax({
                type: 'GET',
                dataType: 'json',
                url: '/cdnation/getcarbrandlistajax',
                success: function (data) {
                        var allHtml = '', letters = '', hotBrands = '', brands = '', tempHtml = '', tempObj = null;
                        /* 加载热门品牌 */
                        allHtml += '<h3>热门品牌</h3><ul class="list-brand column5">';
                        if (data.hotbrand.length > 0) {
                            tempHtml = ''; tempObj = data.hotbrand;
                            for (var i = 0, j = tempObj.length; i < j; i++) {                                
                                tempHtml += ' <li data-brandid="' + tempObj[i].id + '"><a href="javascript:void(0)" data-url="" data-relation="cartype-sub" data-role="aside"><img src="http://x.autoimg.cn/m/news/brand/' + tempObj[i].id + '.jpg">' + tempObj[i].v + '</a></li>';
                            }
                            tempHtml += '</ul>';
                            allHtml += tempHtml;
                        }
                        allHtml += '<h3>按字母选择品牌</h3>';
                        allHtml += '<div class="tab-btngroup column7 letter-group">';
                        /* 加载首选字母 */
                        if (data.letters.length > 0) {
                            tempObj = data.letters;
                            tempHtml = ''
                            for (var i = 0, j = tempObj.length; i < j; i++) {
                                if (tempObj[i].enable== 1) {                                    
                                    tempHtml += '<a href="#brand' + tempObj[i].letter + '" class="btn simple">' + tempObj[i].letter + '</a>';
                                } 
                            }
                            tempHtml += '</div>';
                        }
                        allHtml += tempHtml;
                        
                        /* 加载所有品牌 */
                        if (data.brands.length > 0) {
                            tempHtml = ''; tempObj = data.brands;
                            for (var i = 0, j = tempObj.length; i < j; i++) {                                
                                tempHtml += '<h3 id="brand' + tempObj[i].letter + '">' + tempObj[i].letter + '</h3>';
                                tempHtml += '<ul class="list-line  list-line-logo">';
                                for (var x = 0, y = tempObj[i].item.length; x < y; x++) {                                   
                                    tempHtml += '<li data-url="" data-relation="cartype-sub" data-role="aside" data-num="' + parseInt(x + 1, 10) + '" data-brandid="' + tempObj[i].item[x].id + '"><img src="http://x.autoimg.cn/m/news/brand/' + tempObj[i].item[x].id + '.jpg">' + tempObj[i].item[x].name + '</li>';
                                }
                                tempHtml += '</ul>';
                            }
                            allHtml += tempHtml + '</div>'; /* 与 <div class="w-main"> 相对应的DIV */
                        }

                        categoryShowObj.html(allHtml);
                        ithis.brandClick();/* 绑定品牌点击事件 */
                 
                }
            });
    };

    /* 品牌点击绑定事件 */
    var lastClickAClass = ''; /* 上一次点击品牌的类型 */
    this.brandClick = function () {
        $('#categoryShow li').on('click', function () {
            var brandid = $(this).data('brandid');  
            carName.attr('sid', 0);
            carName.attr('specid', 0);
            carName.attr('bid', brandid);
            ithis.selectedValue[0] = $(this).text();
            ithis.showSeries(brandid);
        });
    };
    /* 显示车系 */
    this.showSeries = function (bid) {
        if (bid > 0) {
            var str = '';
            $.ajax({
                type: 'GET',
                data: {
                    id : bid
                },
                dataType: 'json',
                url: '/cdnation/getcarserieslistajax',
                success: function (data) {
                    if (typeof (data.item) == 'undefined' || data.item.length < 1) {

                    } else {
                        for (var i = 0;i < 1; i++) {
                            str += '<ul class=\"list-line\">';
                            for (var j= 0; j < data.item.length; j++) {//循环获取该级别下的车系
                                str += '<li sid="' + data.item[j].sid + '" data-role="aside" data-relation="cartype-sub-sub" data-url=""><span>' + data.item[j].sname + '</span></li>';
                            }
                            str += '</ul>';
                        }
                        $('#seriesShow').html(str);
                        //绑定点击车系事件
                        $('#seriesShow li').on('click', function () {
                            var sid = $(this).attr('sid');
                            var seriesName= $(this).text();
                            ithis.showSpec(sid, seriesName);
                        });
                    }
                }
            });
        }
    };

    /* 显示车型 */
    this.showSpec = function (seriesId, seriesName) {
        /* 显示所有车型 */
        if (seriesId != 'undefined' && seriesId > 0 && seriesName != 'undefined') {
            carName.attr('specid', 0);
            carName.attr('sid', seriesId);
            ithis.selectedValue[1] = seriesName;
            $.ajax({
                type: 'GET',
                data:{
                    id : seriesId
                },
                dataType: 'json',
                url: '/cdnation/getcarstylelistajax',
                success: function (data) {
                    var str = '';
                    if (typeof (data[1]) == 'undefined' || data[1].length < 1) {
                        
                    } else {
                        $('#specTitle').text(seriesName);                        
                        for (var i = 0; i<1; i++) {
                            // str += '<label class="drawer radio tt full pl" data-role="drawer">' + data.yname;
                            // str += '    <i class="iconfont drawer-ctr popyear icon-arrow-up"></i>';
                            // str += '</label>';    
                            str += '<div class="drawer-sub" >';
                            for (var j = 0; j < data[1].length; j++) {
                                str += '<label class="radio-1 full pl">' + data[1][j].name + '<input type="checkbox" name="rad_spec" specname="'+data[1][j].name+'" specid="' + data[1][j].id + '" ></label>';                                
                            }
                            str += '</div>';
                        }                        
                        $('#specShow').html(str).show();
                        //绑定车型点击事件
                        $('#specShow').find('input[type=checkbox]').on('change', function () {
                            var specid=$(this).attr('specid');
                            var specName=$(this).attr('specname');
                            ithis.selectSpecValue(specid, specName);
                            // $(this).parent().removeClass('aside-close').addClass('aside-close');
                        });
                        $('#specShow .drawer').find('i').on('click', function () {
                            var ithis = $(this);
                            if (ithis.attr('class').indexOf('icon-arrow-up') > 0) {
                                ithis.removeClass('icon-arrow-up').addClass('icon-arrow-down');
                                ithis.parent().next().hide();
                            } else {
                                ithis.removeClass('icon-arrow-down').addClass('icon-arrow-up');
                                ithis.parent().next().show();
                            }
                        });
                    }
                }
            });
        }
    };

    /* 选择车型操作 */
    this.selectSpecValue = function (specId, specName) {
        if (specId > 0) {
            carName.attr('specId', specId);
            sessionStorage.clear();
            carName.removeClass('active').addClass('active');
            ithis.selectedValue[2] = specName;
            carName.html(ithis.selectedValue[0] + '<br><b>' + ithis.selectedValue[1] + '</b>' + ithis.selectedValue[2] + '<i class="iconfont icon-arrow-right"></i>')
                   .parent().addClass('choose-car-actice');
            $.aside.closeAll();
        }
    };

};

var pingGu = new PingGU();
pingGu.init();

// 加载车型数据end
// 选择车型  end
var form = $('#report'),
    inputFile = $('.input-file'),
    btnSubmit = $('#next-step2');
    

inputFile.on('change', function () {
    var _this = $(this);
        // iconPlus = '<i class="icon-plus"></i> ';
    if (_this.hasClass('disabled')) {
      
    }
    _this.addClass('disabled');
    // this.files[0] 是用户选择的文件
    lrz(this.files[0], {width: 1024, fieldName: 'upload_img'})
        .then(function (rst) {
            // onstart
            // _this.prev().text('上传中...');
            return rst;
        })

        .then(function (rst) {
            // upload
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '/cdnation/uploadFileAjax');

            xhr.onload = function () {
                if (xhr.status === 200) {
                    var data = $.parseJSON(xhr.responseText);
                    if (data.error != '0') {
                        pop.error(data.errmsg);
                        return;
                    }
                    data = data.data;
                    // _this.prev().html(iconPlus + '上传成功');
                    var _thisImg = _this.prev().prev(),
                        _thisMainInput = _this.parent().next();
                    _thisImg.attr('src', data.url).addClass('active');
                    _thisMainInput.val(data.id);
                    pop.error(_thisMainInput.next().text() + '上传成功！');
                } else {
                    // _this.prev().html(iconPlus + '重新上传');
                    pop.error('上传失败');
                }
            };

            xhr.onerror = function () {
                // 处理错误
            };

            xhr.upload.onprogress = function (e) {
                // 上传进度
                var percentComplete = ((e.loaded / e.total) || 0) * 100;
            };

            // 添加参数
            rst.formData.append('fileLen', rst.fileLen);

            // 触发上传
            xhr.send(rst.formData);
            /* ==================================================== */

            return rst;
        })

        .catch(function (err) {
            // 万一出错了，这里可以捕捉到错误信息
            // 而且以上的then都不会执行
            pop.error(err);
        })

        .always(function () {
            // 不管是成功失败，这里都会执行
        });
});

btnSubmit.on('click', function(e){
    e.preventDefault(); 
    var _this = $(this);
    if (_this.hasClass('disabled')) {
        return;
    };
    _this.addClass('disabled');
    var userName = $('#user').val();
    if(!userName){
        pop.error('请输入购买车用户姓名');
        btnSubmit.removeClass('disabled');
        return false;
    }
    var litigation_car = $('#litigation_car').val();
        litigation_car = litigation_car.replace(/\s/g,"");
    if(litigation_car==""){
        pop.error('请输入人法');
        btnSubmit.removeClass('disabled');
        return false;
    }
    if(isNaN(litigation_car)){
        pop.error('人法必须填写数字');
        btnSubmit.removeClass('disabled');
        return false;
    }
    var illegal = $('#illegal').val();
        illegal = illegal.replace(/\s/g,"");
    if(illegal==""){
        pop.error('请输入违章次数');
        btnSubmit.removeClass('disabled');
        return false;
    }
    if(isNaN(illegal)){
        pop.error('违章必须填写数字');
        btnSubmit.removeClass('disabled');
        return false;
    }
    // var idcard = $('#idcard').val();
    // if(!idcard){
    //     pop.error('请填写购车客户省份证号码');
    //     btnSubmit.removeClass('disabled');
    //     return false;
    // }
    var carNameBid = $('#carName').attr('bid');
    if(!carNameBid){
        pop.error('请选择车辆品牌');
        btnSubmit.removeClass('disabled');
        return false;
    }
    var carNameSid = $('#carName').attr('sid');
    if(!carNameSid){
        pop.error('请选择车系');
        btnSubmit.removeClass('disabled');
        return false;
    }
    var carNameSpecid = $('#carName').attr('specid');
    if(!carNameSpecid){
        pop.error('请选择车辆型号');
        btnSubmit.removeClass('disabled');
        return false;
    }
    
    // var kilo = $('input[name="kilo"]').val();
    // if(!kilo){
    //     pop.error('请填写行驶里程');
    //     btnSubmit.removeClass('disabled');
    //     return false;
    // }
    // var check_day = $('input[name="check-day"]').val();
    // if (!check_day) {
    //     pop.error('请填写上牌日期');
    //     btnSubmit.removeClass('disabled');
    //     return false;
    // };
    // var dealvalue = $('input[name="dealvalue"]').val();
    // if(!dealvalue){
    //     pop.error('请填写成交价值');
    //     btnSubmit.removeClass('disabled');
    //     return false;
    // }
    var car_page_1 = $('input[name="car-page-1"]').val();
    if (!car_page_1) {
        pop.error('身份证正面照未上传');
        btnSubmit.removeClass('disabled');
        return false;
    };
    var car_page_2 = $('input[name="car-page-2"]').val();
    if (!car_page_1) {
        pop.error('身份证反面照未上传');
        btnSubmit.removeClass('disabled');
        return false;
    };
    var car_page_3 = $('input[name="car-page-3"]').val();
    if (!car_page_1) {
        pop.error('客户行驶证照片未上传');
        btnSubmit.removeClass('disabled');
        return false;
    };
    var money = $('input[name="money"]').val();
    if (!money) {
        pop.error('请输入贷款金额');
        btnSubmit.removeClass('disabled');
        return false;
    };
    if(isNaN(money)){
        pop.error('贷款金额必须填写数字');
        btnSubmit.removeClass('disabled');
        return false;
    }
    if(money > 9999){
        pop.error('请输入正确贷款金额');
        btnSubmit.removeClass('disabled');
        return false;
    }
    var rates = $('input[name="rates"]').val();
    if (!rates) {
        pop.error('请输入贷款利率');
        btnSubmit.removeClass('disabled');
        return false;
    };
    if(isNaN(rates)){
        pop.error('贷款利率必须填写数字');
        btnSubmit.removeClass('disabled');
        return false;
    }
    if(rates > 20){
        pop.error('请输入正确贷款利率');
        btnSubmit.removeClass('disabled');
        return false;
    }
    var dataTimes = $("select[name='dataTime']")[0];
    dataTime = $(dataTimes).val();
    if(!dataTime){
        pop.error('请输入贷款期限');
        btnSubmit.removeClass('disabled');
        return false;
    }
    if(isNaN(dataTime)){
        pop.error('贷款期限必须填写数字');
        btnSubmit.removeClass('disabled');
        return false;
    }
    
    //var suggest = $('#suggest').html();
    var suggest = $('#suggest').val();
    if (!suggest) {
        pop.error('请输入业务员意见');
        btnSubmit.removeClass('disabled');
        return false;
    };
    
    var app_id = $('input[name="app_id"]').val();
    var submitDatas = {
        'app_id' :app_id,
        'userName' :userName,
        'litigation_car':litigation_car,
        'illegal':illegal,
        // 'idcard' :idcard,
        'carNameBid' :carNameBid,
        'carNameSid' :carNameSid,
        'carNameSpecid' :carNameSpecid,
        // 'kilo' :kilo,
        // 'dealvalue': dealvalue,
        // 'check_day': check_day,
        'car_page_1' :car_page_1,
        'car_page_2' :car_page_2,
        'car_page_3': car_page_3,
        'money': money,
        'rates' :rates,
        'dataTime':dataTime,
        'suggest':suggest
    }
    submitReport(submitDatas);
})



function submitReport(submitDatas){    
    pop.loading('处理中...');
    submitDatas.id = $('body').data('id');
    $.post("/cdnation/reportaddajax", submitDatas,function(data){
        pop.clear();
        if(data.error != 0){
            pop.error(data.msg);
            btnSubmit.removeClass('disabled')
            return;
        }
        pop.error("添加成功");
        setTimeout("location.href='/cdnation/index'",2000);
    },"json");
}

});

//补录信息js部分
var customeraddsub = $('#customeraddsub');
customeraddsub.on('click', function(e){
  e.preventDefault();
  // var _this = $(this);
  //   if (_this.hasClass('disabled')) {
  //       return;
  //   };
  //   _this.addClass('disabled');
    var userName = $('#name').val();
    if(!userName){
        pop.error('请输入购买车用户姓名');
        customeraddsub.removeClass('disabled');
        return false;
    }
    var litigation_car = $('#litigation_car').val();
        litigation_car = litigation_car.replace(/\s/g,"");
    if(litigation_car==""){
        pop.error('请输入人法');
        btnSubmit.removeClass('disabled');
        return false;
    }
    if(isNaN(litigation_car)){
        pop.error('人法必须填写数字');
        btnSubmit.removeClass('disabled');
        return false;
    }
    var illegal = $('#illegal').val();
        illegal = illegal.replace(/\s/g,"");
    if(illegal==""){
        pop.error('请输入违章次数');
        btnSubmit.removeClass('disabled');
        return false;
    }
    if(isNaN(illegal)){
        pop.error('违章必须填写数字');
        btnSubmit.removeClass('disabled');
        return false;
    }
    var gender = $('input[name="gender"]:checked').val();
    if(!gender){
        pop.error('请选择性别');
        customeraddsub.removeClass('disabled');
        return false;
    }
    var idcard = $('#idcard').val();
    if(!IdCardValidate(idcard)){
        pop.error('身份证号错误');
        return;
    }
    var birthday = $('#birthday').val();
    if(!birthday){
        pop.error('请填写出生日期');
        customeraddsub.removeClass('disabled');
        return false;
    }
    var phone = $('#phone').val();
    var phoneReg = /^1[3,4,5,7,8]\d{9}$/;
    if(!phoneReg.test(phone)){
        pop.error('请输入正确的手机号');
        return false;
    }
    var email = $('#email').val();
    var emailReg = /^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-])+/;
    if(!emailReg.test(email)){
        pop.error('邮箱地址是无效的');
        return;
    }
    var work_address = $('#work_address').val();
    if(!work_address){
        pop.error('请输入工作单位');
        customeraddsub.removeClass('disabled');
        return false;
    }
    var census_register = $('#census_register').val();
    if(!census_register){
        pop.error('请输入户籍地址');
        customeraddsub.removeClass('disabled');
        return false;
    }
    var temp_address = $('#temp_address').val();
    if(!temp_address){
        pop.error('请输入暂住地址');
        customeraddsub.removeClass('disabled');
        return false;
    }
    var income = $('#income option:selected').val();
    if(!income){
        pop.error('请选择个人月收入状况');
        customeraddsub.removeClass('disabled');
        return false;
    }
    
    var crime = $('#crime option:selected').val();
    if(!crime){
        pop.error('请选择犯罪记录');
        customeraddsub.removeClass('disabled');
        return false;
    }

    var edu = $('#edu option:selected').val();
    if(!edu){
        pop.error('请选择犯罪记录');
        customeraddsub.removeClass('disabled');
        return false;
    }

    var marriage = $('#marriage option:selected').val();
    if(!marriage){
        pop.error('请选择犯罪记录');
        customeraddsub.removeClass('disabled');
        return false;
    }
    
    var user_id = $('#user_id').val();
    var customerSubmitDatas = {
        'user_id' :user_id,
        'userName' :userName,
        'litigation_car':litigation_car,
        'illegal':illegal,
        'gender':gender,
        'idcard':idcard,
        'birthday':birthday,
        'phone':phone,
        'email':email,
        'work_address':work_address,
        'census_register':census_register,
        'temp_address':temp_address,
        'income':income,
        'crime':crime,
        'edu':edu,
        'marriage':marriage
    }
    customerSubmitReport(customerSubmitDatas);
})

var customerSubmitReport = function(customerSubmitDatas){
  pop.loading('处理中...');

    $.post('/cdnation/addCustomerInfoAjax', customerSubmitDatas, function(data){
        pop.clear();
        if(data.error != 0){
            pop.error(data.res);
            customeraddsub.removeClass('disabled')
            return;
        }
        pop.error("补录信息成功");
        setTimeout("location.href='/cdnation/index'",2000);
    }, 'json');
}
