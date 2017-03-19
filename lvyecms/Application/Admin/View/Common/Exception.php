<?php
if (C('LAYOUT_ON')) {
    echo '{__NOLAYOUT__}';
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta content="text/html; charset=utf-8" http-equiv="Content-Type">
    <title>系统发生错误</title>
<style type="text/css">
* {
	margin: 0px;
	padding: 0px;
	text-decoration: none;
}
body,h1,h2,h3,h4,h5,h6,hr,p,blockquote,dl,dt,dd,ul,ol,li,pre,form,fieldset,legend,button,input,textarea,th,td{margin:0;padding:0;word-wrap:break-word}
body,html,input{font:12px/1.5 tahoma,arial,\5b8b\4f53,sans-serif;}
table{border-collapse:collapse;border-spacing:0;}img{border:none}
.c {
	margin: 0px;
	padding: 0px;
	clear: both;
}
#main {
	width: 1000px;
	margin-top: 0px;
	margin-right: auto;
	margin-bottom: 0px;
	margin-left: auto;
	padding-top: 20px;
	padding-right: 0px;
	padding-bottom: 0px;
	padding-left: 0px;
}
h1 {
	line-height: 28px;
	font-size: 16px;
	padding-left: 8px;
}
.td {
	padding-left: 8px;
	font-size: 14px;
}
.td1 {
	font-size: 14px;
	padding-left: 8px;
}
.td3 {
	font-size: 12px;
	padding: 8px;
}
.td6 {
	font-size: 12px;
	padding-top: 3px;
	padding-right: 8px;
	padding-bottom: 3px;
	padding-left: 8px;
}
h2 {
	font-size: 14px;
	line-height: 28px;
	padding-left: 8px;
}
.b_1 {
	line-height: 28px;
	font-size: 14px;
	padding: 8px;
}
.b_1 a img {
	padding-right: 5px;
	padding-left: 5px;
}
#box fieldset h6 {
	font-size: 14px;
	color: #333;
}
fieldset {
	padding-bottom: 10px;
	padding-left: 10px;
	padding-right: 10px;
}
legend {
	margin-bottom: 10px;
}

 a {
	color: #333;
}
 a:hover {
	color: #F60;
}
</style>
</head>
<body>
<div id="main">
  <div id="top">
        	<div class="t1">
        	  <h2>错误位置：</h2>
   	  </div>
    <table width="100%" border="1" cellspacing="0" cellpadding="0" bordercolor="#aead81">
      <tr>
        <td><h1>message</h1></td>
        <td><h1>file</h1></td>
        <td><h1>line</h1></td>
      </tr>
      <?php if (isset($e['file'])) { ?>
    	    <tr>
    	      <td height="28" bgcolor="#ffffcc" class="td"><?php echo $e['message']; ?></td>
    	      <td bgcolor="#ffffcc" class="td"><?php echo $e['file']; ?></td>
    	      <td bgcolor="#ffffcc" class="td"><?php echo $e['line']; ?></td>
        </tr>
      <?php } ?>
<?php if (isset($e['trace'])) { ?>
	<tr>
    	      <td colspan="3" bgcolor="#ffffcc" class="td1"><h2>TRACE：</h2><?php echo nl2br($e['trace']); ?></td>
      </tr>
<?php } ?>
    </table>
    <div class="c"></div>
   	</div>
    <div id="box">
   	  <div class="b_1">
        	<a title="上海旅烨网络科技有限公司" href="http://www.lvyecms.com" target="_blank">LvyeCMS</a><?php echo SHUIPF_VERSION ?> - <?php echo SHUIPF_BUILD ?>，尊敬的用户：为了让我们LyeCms更完善，你可以通过加入官方QQ群：49219815<a target="_blank" href="http://shang.qq.com/wpa/qunwpa?idkey=6e8bd0ac2dc2cc9dd8a294618ceabcea2c7916bef77cde75229c041fefb4ac41"><img border="0" src="http://pub.idqqimg.com/wpa/images/group.png" alt="LvyeCms Fans" title="LvyeCms Fans"></a>把错误信息反馈于我们。
   	    <div class="c"></div>
       	</div>
    <fieldset>
	<legend><h6>我们还为您提供：LvyeCms品牌扶持计划</h6></legend><table width="100%" border="1" align="left" cellpadding="0" cellspacing="0" bordercolor="#919880">
  <tr>
    <td width="28" align="right" bgcolor="#e6e6e6">1：</td>
    <td width="200" class="td3"><a href="http://ppfc.lvyecms.com/" target="_blank">LvyeCms免授权费</a></td>
    <td width="28" align="right" bgcolor="#e6e6e6">2：</td>
    <td width="260" class="td3"><a href="http://ppfc.lvyecms.com/" target="_blank">旅烨云空间主机免费使用</a></td>
    <td rowspan="3" align="left" valign="top" bgcolor="#E6E6E6"><table width="100%" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td class="td6">品牌扶持联系方式：</td>
      </tr>
      <tr>
        <td class="td6">QQ：2853375580&nbsp;&nbsp;<a target="_blank" href="http://wpa.qq.com/msgrd?v=3&uin=2853375580&site=qq&menu=yes"><img border="0" src="http://wpa.qq.com/pa?p=2:2853375580:51" alt="点击这里给我发消息" title="点击这里给我发消息"/></a></td>
      </tr>
      <tr>
        <td class="td6">品牌扶持合作QQ群：472718399&nbsp;&nbsp;<a target="_blank" href="http://shang.qq.com/wpa/qunwpa?idkey=b7df8a2c2019784e8f5e86b18c24a2abb689760f09fa706f9484c8d0287a847c"><img border="0" src="http://pub.idqqimg.com/wpa/images/group.png" alt="LvyeCms品牌扶持" title="LvyeCms品牌扶持"></a><br></td>
      </tr>
    </table></td>
  </tr>
  <tr>
    <td align="right" bgcolor="#e6e6e6">3：</td>
    <td class="td3"><a href="http://ppfc.lvyecms.com/" target="_blank">LvyeCms全年度开发项目及技术支持</a></td>
    <td align="right" bgcolor="#e6e6e6">4：</td>
    <td class="td3"><a href="http://ppfc.lvyecms.com/" target="_blank">LvyeCms模块、插件、模板免费使用</a></td>
    </tr>
  <tr>
    <td align="right" bgcolor="#e6e6e6">5：</td>
    <td class="td3"><a href="http://ppfc.lvyecms.com/" target="_blank">官方各平台的品牌宣传展示</a></td>
    <td align="right" bgcolor="#e6e6e6">6：</td>
    <td class="td3"><a href="http://ppfc.lvyecms.com/" target="_blank">技术开发人员定向合作派遣</a></td>
    </tr>
</table>
        <div class="c"></div>
    </fieldset>
    <div class="c"></div>
    </div>
        <div id="foot"></div>
        <div class="c"></div>
</div>
</body>
</html>