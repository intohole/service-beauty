<?php
//
// UI 视图模块分离
// Idea from UIModule of facebook's tornado
//
// 创建Demo:
//  APP_PATH/application/library/Ui/Test.php
//  APP_PATH/ui/test.phtml
// 在view模版里使用:
//  new Ui_Test();
class Ui_Base{

    protected $_tplDir;
    protected $_view;

    public function __construct(){
        $this->_makeView();
    }

    final protected function _makeView(){
        if($this->_view){
            return $this->_view;
        }
        $this->_tplDir = APP_PATH . '/application/views/';
        $this->_view = Yaf_Dispatcher::getInstance()->initView($this->_tplDir);
        return $this->_view;
    }

    final public function __call($name, array $args){
        if(in_array($name, array('render', 'display'))){
            $args[0] = $this->_tplDir . $args[0];
        }
        return call_user_func_array(array($this->_view, $name), $args);
    }
}
