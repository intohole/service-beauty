<?php if (!defined('SHUIPF_VERSION')) exit(); ?>
<style type="text/css">
  .tt{
    float: left;
    padding: 10px;
  }

</style>
  <div class="table_list">
    
      <volist name="data" id="vo">
          <div class="tt">
            <img src="{$vo.thumb}" width="240" height="260">
            <h3>{$vo.title}</h3>
            <?php
              $path = TEMPLATE_PATH.$vo['identification'];
              if (is_dir($path)) {
                  echo "已安装";
              }else{
                  $op = '<a href="'.U('install',array('identification'=>$vo['identification']?:$vo['identification'])).'" class="btn btn_submit mr5 Js_install">安装</a>';
                  echo $op;
              }
              if($vo['price']){
                echo "<br /><font color=\"#FF0000\">价格：".$vo['price']." 元</font>";
            }
      ?>
          </div>
      </volist>
   
  </div>
  <div class="center pages">{$Page}</div>