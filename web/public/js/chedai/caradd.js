$(function(){

$('input[name=check-day]').mobiscroll().date({
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
            bid && $('.carName').attr('bid', bid);
            sid && $('.carName').attr('sid', sid);
            specId && $('.carName').attr('specId', specId);
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
        carName = $('.carName');
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
        var categoryShowObj = $(".categoryShow");
            $.ajax({
                type: 'GET',
                dataType: 'json',
                url: '/chedai/salesman_getcarbrandlistajax',
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
        $('.categoryShow li').on('click', function () {
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
                url: '/chedai/salesman_getcarserieslistajax',
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
                        $('.seriesShow').html(str);
                        //绑定点击车系事件
                        $('.seriesShow li').on('click', function () {
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
                url: '/chedai/salesman_getcarstylelistajax',
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
                        $('.specShow').html(str).show();
                        //绑定车型点击事件
                        $('.specShow').find('input[type=checkbox]').on('change', function () {
                            var specid=$(this).attr('specid');
                            var specName=$(this).attr('specname');
                            ithis.selectSpecValue(specid, specName);
                            // $(this).parent().removeClass('aside-close').addClass('aside-close');
                        });
                        $('.specShow .drawer').find('i').on('click', function () {
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
var registration='';
var launch=$("input[name=launch]").val();
inputFile.on('change', function (rst) {
    var _this = $(this);
    var self=this;
        // iconPlus = '<i class="icon-plus"></i> ';
    if (_this.hasClass('disabled')) {
        return;
    }
    _this.addClass('disabled');
    // this.files[0] 是用户选择的文件
    var that=self.files;
    var langth=that.length;
    for(var i=0;i<langth;i++){
      lrz(that[i], {width: 1024, fieldName: 'upload_img'})

        .then(function (rst) {
            // onstart
            // _this.prev().text('上传中...');
            return rst;
        })

        .then(function (rst) {
            // upload
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '/report/uploadFileAjax');

            xhr.onload = function () {
                if (xhr.status === 200) {
                    var data = $.parseJSON(xhr.responseText);
                    if (data.error != '0') {
                        pop.error(data.errmsg);
                        return;
                    }
                    data = data.data;
                    var _thisImg = _this.prev().prev();
                    if(_thisImg.attr('alt')=='登记证' && launch==2){
                        var li_form=$("#moreImg");
                        var html='<li class="img-uploader">\
                                      <div class="img-uploader-box wrap-reply-wait">\
                                          <img class="active" src="'+data.url+'" alt="登记证" data-src="">\
                                          <div class="img-uploader-btn">\
                                              <!-- <i class="icon-plus"></i> 请上传 -->\
                                          </div>\
                                      </div>\
                                      <input type="hidden" name="car-page-2" value="'+data.id+'">\
                                  </li>';
                        li_form.before(html);
                        if(li_form.prev().hasClass("img-uploader")){
                          li_form.css("clear","none");
                        }
                        // pop.error(_thisMainInput.next().text() + '上传成功！');
                        pop.error('上传成功！');
                        var car_page = $('.national input[name="car-page-2"]');
                        for(var j=0,arr=[];j<car_page.length;j++){
                          arr.push($(car_page[j]).val());
                          registration=arr.join(",")
                        }
                        _this.removeClass('disabled');
                    }else{
                        _thisMainInput = _this.parent().next();
                        _thisImg.attr('src', data.url).addClass('active');
                        _thisMainInput.val(data.id);
                        pop.error(_thisMainInput.next().text() + '上传成功！');
                        _this.removeClass('disabled');
                        
                    }
                    
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
    }
});

btnSubmit.on('click', function(e){
    e.preventDefault(); 
    var _this = $(this);
    if (_this.hasClass('disabled')) {
        return;
    };
    _this.addClass('disabled');
    var car_status = $(".xiamen select[name='type'] option:selected").val();
    if(!car_status){
        pop.error('请选择车辆状况');
        btnSubmit.removeClass('disabled');
        return false;
    }
    var carNameBid = $('.xiamen .carName').attr('bid');
    if(!carNameBid){
        pop.error('请选择车辆品牌');
        btnSubmit.removeClass('disabled');
        return false;
    }
    var carNameSid = $('.xiamen .carName').attr('sid');
    if(!carNameSid){
        pop.error('请选择车系');
        btnSubmit.removeClass('disabled');
        return false;
    }
    var carNameSpecid = $('.xiamen .carName').attr('specid');
    if(!carNameSpecid){
        pop.error('请选择车辆型号');
        btnSubmit.removeClass('disabled');
        return false;
    }
    var car_orgin = $(".xiamen select[name='orgin'] option:selected").val();
    // if(!car_orgin){
    //     pop.error('请选择车辆产地');
    //     btnSubmit.removeClass('disabled');
    //     return false;
    // }
    var car_gear = $(".xiamen select[name='gear'] option:selected").val();
    if(!car_gear){
        pop.error('请选择车档');
        btnSubmit.removeClass('disabled');
        return false;
    }
    var car_color = $(".xiamen select[name='color'] option:selected").val();
    if(!car_color){
        pop.error('请选择车辆颜色');
        btnSubmit.removeClass('disabled');
        return false;
    }
    var kilo = $('.xiamen input[name="kilo"]').val();
    if(!kilo){
        pop.error('请填写行驶里程');
        btnSubmit.removeClass('disabled');
        return false;
    }
    var litre = $('.xiamen input[name="litre"]').val();
    if(!litre){
        pop.error('请填写排量');
        btnSubmit.removeClass('disabled');
        return false;
    }
    var dealvalue = $('.xiamen input[name="dealvalue"]').val();
    if(!dealvalue){
        pop.error('请填写成交价值');
        btnSubmit.removeClass('disabled');
        return false;
    }
    var check_day = $('.xiamen input[name="check-day"]').val();
    if (!check_day) {
        pop.error('请填写上牌日期');
        btnSubmit.removeClass('disabled');
        return false;
    };
    var car_source = $('.xiamen input[name="car-source"]').val();
    if (!car_source) {
        pop.error('请填写车辆来源');
        btnSubmit.removeClass('disabled');
        return false;
    };
    var car_numb1 = $('.xiamen input[name="car-numb1"]').val();
    if (!car_numb1) {
        pop.error('请填写车架号');
        btnSubmit.removeClass('disabled');
        return false;
    };
    var car_numb2 = $('.xiamen input[name="car-numb2"]').val();
    if (!car_numb2) {
        pop.error('请填写发动机号');
        btnSubmit.removeClass('disabled');
        return false;
    };
    var car_page_1 = $('.xiamen input[name="car-page-1"]').val();
    if(!car_page_1){
        pop.error('请上传行驶证照片');
        btnSubmit.removeClass('disabled');
        return false;
    }
    car_page_2 = $('.xiamen input[name="car-page-2"]').val();
    if(!car_page_2){
        pop.error('请上传登记证照片');
        btnSubmit.removeClass('disabled');
        return false;
    }
    var car_page_3 = $('.xiamen input[name="car-page-3"]').val();
    if(!car_page_3){
        pop.error('请上传购车发票照片');
        btnSubmit.removeClass('disabled');
        return false;
    }
    var submitDatas = {
        'car_status' :car_status,
        'carNameBid' :carNameBid,
        'carNameSid' :carNameSid,
        'carNameSpecid' :carNameSpecid,
        'car_orgin': car_orgin,
        'car_gear': car_gear,
        'car_color' :car_color,
        'car_kilo' :kilo,
        'litre': litre,
        'dealvalue': dealvalue,
        'check_day' :check_day,
        'car_source':car_source,
        'car_numb1' :car_numb1,
        'car_numb2' :car_numb2,
        'car_page_1' :car_page_1,
        'car_page_2' :car_page_2,
        'car_page_3' :car_page_3
    }
    submitReport(submitDatas);
})


$(document).on("click","#next-step",function(e){
  e.preventDefault(); 
    var _this = $(this);
    if (_this.hasClass('disabled')) {
        return;
    };
    _this.addClass('disabled');
    var carNameBid = $('.national .carName').attr('bid');
    if(!carNameBid){
        pop.error('请选择车辆品牌');
        $("#next-step").removeClass('disabled');
        return false;
    }
    var carNameSid = $('.national .carName').attr('sid');
    if(!carNameSid){
        pop.error('请选择车系');
        $("#next-step").removeClass('disabled');
        return false;
    }
    var carNameSpecid = $('.national .carName').attr('specid');
    if(!carNameSpecid){
        pop.error('请选择车辆型号');
        $("#next-step").removeClass('disabled');
        return false;
    }
    var car_color = $(".national select[name=color] option:selected").val();
    if(!car_color){
        pop.error('请选择车辆颜色');
        $("#next-step").removeClass('disabled');
        return false;
    }
    var kilo = $('.national input[name=kilo]').val();
    if(!kilo){
        pop.error('请填写行驶里程');
        $("#next-step").removeClass('disabled');
        return false;
    }
    var dealvalue = $('.national input[name="dealvalue"]').val();
    if(!dealvalue){
        pop.error('请填写成交价值');
        $("#next-step").removeClass('disabled');
        return false;
    }
    var check_day = $('.national input[name="check-day"]').val();
    if (!check_day) {
        pop.error('请填写上牌日期');
        $("#next-step").removeClass('disabled');
        return false;
    };
    var car_numb1 = $('.national input[name="car-numb1"]').val();
    if (!car_numb1) {
        pop.error('请填写车架号');
        $("#next-step").removeClass('disabled');
        return false;
    };
    var car_numb2 = $('.national input[name="car-numb2"]').val();
    if (!car_numb2) {
        pop.error('请填写发动机号');
        $("#next-step").removeClass('disabled');
        return false;
    };
    var car_page_1 = $('.national input[name="car-page-1"]').val();
    var car_page_3 = $('.national input[name="car-page-3"]').val();
    var submitDatas = {
        'carNameBid' :carNameBid,
        'carNameSid' :carNameSid,
        'carNameSpecid' :carNameSpecid,
        'car_color' :car_color,
        'car_kilo' :kilo,
        'dealvalue': dealvalue,
        'check_day' :check_day,
        'car_numb1' :car_numb1,
        'car_numb2' :car_numb2,
        'car_page_1' :car_page_1,
        'car_page_2' :registration,
        'car_page_3' :car_page_3
    }
    submitReport(submitDatas);
  })

//end
});
function submitReport(submitDatas){    
    pop.loading('处理中...');
    submitDatas.id = $('body').data('id');
    $.post("/chedai/salesman_caraddajax", submitDatas,function(data){
        pop.clear();
        if(data.error != 0){
            pop.error(data.msg);
            btnSubmit.removeClass('disabled')
            return;
        }
        pop.error("添加成功");
        location.href="/chedai/salesman_carphoto?submitid=" + data.res;
    },"json");
}

function getAlwaySaveDate(obj){
    var saveJson={};
    var saveArray=[];
    var formlen=obj.length;

    for(var i=0; i<formlen; i++){
        saveArray.push( obj[i].serializeArray() );
    }
    saveArray[0].length=17;
    
    for(var h=0; h<saveArray[0].length; h++){
        if( saveArray[0][h].value!='' ){
            saveJson[saveArray[0][h].name] = saveArray[0][h].value;
        }
    }    

    var carName=$('.carName'),
        carIndex=[
                    ['carNameBid','carNameSid','carNameSpecid'],
                    ['bid','sid','specid']
                 ];

    for(var l=0;l<carIndex[0].length; l++){
        if( carName.attr( carIndex[1][l] ) ){
            saveJson[carIndex[0][l]]=carName.attr( carIndex[1][l] );
        }
    }

    return saveJson;

}

function alwaySave(obj){

    if(obj.length<1){
        return;
    }

    setInterval(function(){
        $.ajax({
            url:'/chedai/realTimeStorageAjax',
            type:'POST',
            data:getAlwaySaveDate(obj),
            success:function(data){
                if(data.error==0){

                }
            }
        });
    },5000);
    
}
if( !$('body').data('id') ){
  alwaySave( [$('form').eq(0)] );
}



// 全国业务登记证上传多张，支持查看/删除
var manager_img="";
$(".national").on('click',".wrap-reply-wait img",function(){
    $(".managerinfo_image_big").fadeIn();
    $(".managerinfo_image_box").children("img").attr("src",$(this).attr("src"));
    manager_img=$(this).parent().parent();
})
$(".managerinfo_image_box").on("click",function(e){
    $(this).parent().fadeOut();
    $(".managerinfo_image_box_que").fadeOut();
})
$(".managerinfo_image_box_c").on("click",function(){
    $(".managerinfo_image_box_que").fadeIn();
})
$(".managerinfo_image_box_que_submit").on("click",function(){
    $(this).parent().parent().css("display","none");
    var input=manager_img.children("input");
    var val=manager_img.children(".img-uploader-box-wait").next("input").val();
    manager_img.remove();
    var n="",s="";
    if(input.attr("name")=="car_page_2"){
        s=val.toString();
        n=creditimg.indexOf(s);
        registration.splice(n,1);
    }
    $(".managerinfo_image_box_que").css("display","none");
    //var src=$(this).parent().siblings(".managerinfo_image_box").children("img").attr("scr");
})
$(".managerinfo_image_box_que_bottom").on("click",function(){
    $(".managerinfo_image_box_que").css("display","none");
})