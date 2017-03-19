<?php if (!defined('SHUIPF_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body>
<div class="wrap J_check_wrap" id="loadhtml">
  <Admintemplate file="Common/Nav"/>
  <div class="h_a">说明</div>
  <div class="prompt_text">
    <ul>
      <li>模版管理！</li>
      <li>官网地址：<font color="#FF0000">http://www.lvyecms.com</font>，<a href="http://www.lvyecms.com" target="_blank">立即前往</a>！</li>
    </ul>
  </div>
  <div class="h_a">分类</div>
  <ul id="catList" style="height:40px; line-height:40px;">
    <li style="float:left;padding-left:10px;"><a href="{:U('index')}">全部</a></li>
    <div class="loading" id="fenlei">loading...</div>
  </ul>
  <div class="h_a">搜索</div>
  <div class="search_type  mb10">
    <form action="{:U('index')}" method="post">
      <div class="mb10">
        <div class="mb10"> <span class="mr20"> 模版名称：
          <input type="text" class="input length_2" name="keyword" style="width:200px;" value="{$keyword}" placeholder="请输入关键字...">
          <input type="submit" value="搜索" class="btn" />
          </span> </div>
      </div>
    </form>
  </div>
  <div class="loading" id="nlist">loading...</div>
</div>
<script src="{$config_siteurl}statics/js/common.js"></script>
<script>
$(function(){
  $.get('{:U("catList")}',{r:Math.random()},function(category){
      $('#catList').append(category);
      $('#nlist').hide();
  });
});
$(function(){
  $.get('{:U("index")}',{catid: '{$catid}',page:'{$page}',keyword:'{$keyword}',r:Math.random() },function(data){
    $('#loadhtml').append(data);
    $('#fenlei').hide();
  });
});

</script>
</body>
</html>