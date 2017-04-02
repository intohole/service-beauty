<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE HTML>
<html>
<head>
	<meta charset="UTF-8">
<meta content="<?php echo C('WEB_SITE_KEYWORD');?>" name="keywords"/>
<meta content="<?php echo C('WEB_SITE_DESCRIPTION');?>" name="description"/>
<link rel="shortcut icon" href="<?php echo SITE_URL;?>/favicon.ico">
<title><?php echo empty($page_title) ? C('WEB_SITE_TITLE') : $page_title; ?></title>
<link href="/Public/static/font-awesome/css/font-awesome.min.css?v=<?php echo SITE_VERSION;?>" rel="stylesheet">
<link href="/Public/Home/css/base.css?v=<?php echo SITE_VERSION;?>" rel="stylesheet">
<link href="/Public/Home/css/module.css?v=<?php echo SITE_VERSION;?>" rel="stylesheet">
<link href="/Public/Home/css/weiphp.css?v=<?php echo SITE_VERSION;?>" rel="stylesheet">
<link href="/Public/static/emoji.css?v=<?php echo SITE_VERSION;?>" rel="stylesheet">
<!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
<!--[if lt IE 9]>
<script src="/Public/static/bootstrap/js/html5shiv.js?v=<?php echo SITE_VERSION;?>"></script>
<![endif]-->

<!--[if lt IE 9]>
<script type="text/javascript" src="/Public/static/jquery-1.10.2.min.js"></script>
<![endif]-->
<!--[if gte IE 9]><!-->
<script type="text/javascript" src="/Public/static/jquery-2.0.3.min.js"></script>
<!--<![endif]-->
<script type="text/javascript" src="/Public/static/bootstrap/js/bootstrap.min.js"></script>
<script type="text/javascript" src="/Public/static/uploadify/jquery.uploadify.min.js"></script>
<script type="text/javascript" src="/Public/static/zclip/ZeroClipboard.min.js?v=<?php echo SITE_VERSION;?>"></script>
<script type="text/javascript" src="/Public/Home/js/dialog.js?v=<?php echo SITE_VERSION;?>"></script>
<script type="text/javascript" src="/Public/Home/js/admin_common.js?v=<?php echo SITE_VERSION;?>"></script>
<script type="text/javascript" src="/Public/Home/js/admin_image.js?v=<?php echo SITE_VERSION;?>"></script>
<script type="text/javascript" src="/Public/static/masonry/masonry.pkgd.min.js"></script>
<script type="text/javascript" src="/Public/static/jquery.dragsort-0.5.2.min.js"></script> 
<script type="text/javascript">
var  IMG_PATH = "/Public/Home/images";
var  STATIC = "/Public/static";
var  ROOT = "";
var  UPLOAD_PICTURE = "<?php echo U('home/File/uploadPicture',array('session_id'=>session_id()));?>";
var  UPLOAD_FILE = "<?php echo U('File/upload',array('session_id'=>session_id()));?>";
var  UPLOAD_DIALOG_URL = "<?php echo U('home/File/uploadDialog',array('session_id'=>session_id()));?>";
</script>
<!-- 页面header钩子，一般用于加载插件CSS文件和代码 -->
<?php echo hook('pageHeader');?>

</head>
<body id="login_body">
	
	<!-- 主体 -->
	
<!-- 头部 -->
<div class="login_header">
    <div class="small_wrap">
        <a href="/" title="乐摇"><img class="logo" src="/Public/Home/images/logo.png"/></a>
        <div class="nav_r">
           <a href="<?php echo U('/Home/index/lead');?>">接入指引</a>
        </div>
    </div>
</div>

<section class="reg_box small_wrap about_wrap">
	
    <div class="about_content">
    	
        <p>
        	<strong>注册账号</strong>
        </p>
        <P>
        	填写正确的用户名、密码、邮箱，以后将使用该用户名和密码登录<?php echo C('WEB_SITE_TITLE');?>系统：
            <br/>
            <img src="/Public/Home/images/about/help/ly_lead_001.png"/>
        </P>
        <p>
        	<strong>在微信公众号后台</strong>
        </p>
        <P>
        	请按照图示填写正确的信息：
            <br/>
            <img src="/Public/Home/images/about/help/ly_lead_0021.png"/>
        </P>
        <p>
        	<strong>如果选择使用自有的公众号，则需要按流程填写授权信息</strong>
        </p>
        <P>
        	请按照图示填写正确的公众号信息：
            <br/>
            <img src="/Public/Home/images/about/help/ly_lead_002.png"/>
        </P>
        
        <p>
        	<strong>同时在微信后台进行域名授权配置</strong>
        </p>
        <P>
            配置域名授权：在开发者中心，功能列表里配置，配置授权域名如下图<br/>
            <img src="/Public/Home/images/about/help/ly_lead_003.png"/>
            配置JS接口安全域名，在公众号设置-功能配置里面配置，配置JS安全域名如下图<br/>
            <img src="/Public/Home/images/about/help/ly_lead_004.png"/>
        </P>
        <p>
        	<strong>在微信公众号后台配置授权信息</strong>
        </p>
        <P>
        	 <strong>请在公众平台开发者中心里的服务器配置录入以下参数</strong>
             <br/>
             <?php if(!empty($id)): ?>URL(服务器地址)：<span style="color: #FF0000"><?php echo U('home/weixin/index',array('id'=>$id));?></span><br>
              	Token(令牌)：<span style="color: #F00"><?php echo SYSTEM_TOKEN;?></span><br>
              <?php else: ?>
                你还没有登录乐摇系统<?php endif; ?>
              EncodingAESKey(消息加解密密钥)：点击随机生成得到密钥，不需要自己填写<br>
              消息加解密方式： 根据自己的需要选择其中一种
             
        </P>
        <P>
        	 <strong>填写公众号授权信息</strong><br/>
            <img src="/Public/Home/images/about/help/ly_lead_0022.png"/>
        </P>
        <P>
        	<strong>提交成功！刷新页面进入后台！</strong>
        </P>
    </div>
    
    
</section>

	<!-- /主体 -->

	<!-- 底部 -->
	<div class="wrap bottom" style="background:none">
    <p class="copyright">本系统由<a href="http://weiphp.cn" target="_blank">WeiPHP</a>强力驱动</p>
</div>

	<!-- /底部 -->
    

</body>
</html>