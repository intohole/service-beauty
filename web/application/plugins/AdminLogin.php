<?php
/**
 * @name SamplePlugin
 * @desc Yaf定义了如下的6个Hook,插件之间的执行顺序是先进先Call
 * @see http://www.php.net/manual/en/class.yaf-plugin-abstract.php
 * @author root
 */
class AdminLoginPlugin extends Yaf_Plugin_Abstract {

    public function routerStartup(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
    }

    public function routerShutdown(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
       
    }

    public function dispatchLoopStartup(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
    }

    public function preDispatch(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
        
        
        $controller = strtolower($request->getControllerName());
        $action = $request->getActionName();
        $module = $request->getModuleName();
        if(in_array($controller, array("login", "api","api_moblie","pop","userapi", 'wechatorder', 'weixin','Api','test','credit','activity'))){

                return;
        }
        if($controller == "chedai"){
            if(in_array($action,array('login','loginajax','logout','index'))){
                return;
            }
            $user = Session_AdminFengkong::instance();
            if (!$user->isLogin()) {
                header('Location:'.'http://'.$_SERVER['HTTP_HOST'].'/chedai/login');
                exit;
            }
        }

        
        if($controller == "report"){
            
        }
        else{
            $user = Session_AdminFengkong::instance();
            if (!$user->isLogin()) {
                if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== FALSE) {
                //if(1){
                    $forward = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
                    $forwa = explode('/',$forward);
                    if($forwa[4]){
                        $forname = $forwa[4];
                    }else{
                        $forname = 'index';
                    }

                    if($forwa[3] == 'm'){
                        //$_SESSION['openid'] = '';
                        //$user->siginByOpenid();
                        $goto = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
                        if($user->siginByOpenid($goto,$forname,$_SERVER['REQUEST_URI'])){
                            return;
                        }else{
                            $user->requireLogin('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
                            exit;
                        }
                    }
                }
                $user->requireLogin('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
                exit;
            }
           
        }


    }

    public function postDispatch(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
    }

    public function dispatchLoopShutdown(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
	
    }
}
