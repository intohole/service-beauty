<?php
/**
 * 所有在Bootstrap类中, 以_init开头的方法, 都会被Yaf调用,
 * 这些方法, 都接受一个参数:Yaf_Dispatcher $dispatcher
 * 调用的次序, 和申明的次序相同
 */
class Bootstrap extends Yaf_Bootstrap_Abstract {

    private $_config;

    public function _initConfig() {
        // 关闭自动加载模板
        //Yaf_Dispatcher::getInstance()->autoRender(FALSE);

        //把配置保存起来
        $arrConfig = Yaf_Application::app()->getConfig();
        Yaf_Registry::set('config', $arrConfig);
        define('VIEW_DIR', APP_PATH. "/application/views/");

		//AJAX请求
		//错误标识 error=>1 错误.
		define('AJAX_RESULT_ERROR', 1);
		//error=>0请求成功
		define('AJAX_RESULT_NO_ERROR', 0);
		//参数缺失 error=>2
		define ('AJAX_MISS_PARAM', 2);
		//结果为空 error=>3
		define ('AJAX_NO_RESULT', 3);
		//非法请求
		define ('AJAX_ILLEGAL_REQUEST', 4);
		//超过长度
		define ('AJAX_EXCEED_LENGTH', 5);
		//验证码不正确
		define('AJAX_VALIDATE_CODE_ERROR', 6);
		//上传错误
		define('AJAX_UPLOAD_ERROR', 7);
		//还款日期错误
		define('AJAX_DATE_ERROR', 8);
    }

    public function _initDebug() {
        if(array_key_exists('debug', Yaf_Registry::get("config")->get('application')->toArray()) && Yaf_Registry::get('config')->get('application')->get('debug') == 1) {
            error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
            ini_set("display_errors", 1);
        }
        else {
            ini_set("display_errors", 0);
        }
    }

    /*
     * initIncludePath is only required because zend components have a shit load
     * of
     * include_once calls everywhere. Other libraries could probably just use
     * the autoloader (see _initNamespaces below).
     */
    public function _initIncludePath() {
        
        set_include_path(get_include_path(). PATH_SEPARATOR. Yaf_Registry::get("config")->get("application")->get('library'));
        
    }
    
    public function _initLibrary(){
        Yaf_Loader::getInstance()->registerLocalNamespace([
            'Session',
            'Utils',
            'Ui'
        ]);
        
        Session_SessionHandlerSetter::run();
    }

	public function _initPlugin(Yaf_Dispatcher $dispatcher) {
        //注册一个插件
        $adminLogin = new AdminLoginPlugin();
        $dispatcher->registerPlugin($adminLogin);
        
    }

}
