<?php

// +----------------------------------------------------------------------
// | LvyeCMS 模版商店
// +----------------------------------------------------------------------
// | Copyright (c) 2012-2014 http://www.lvyecms.com, All rights reserved.
// +----------------------------------------------------------------------
// | Author: 旅烨集团 <web@alvye.cn>
// +----------------------------------------------------------------------


namespace Admin\Controller;

use Common\Controller\AdminBase;

class TemplateshopController extends AdminBase {

	protected function _initialize() {
        parent::_initialize();
        if (!isModuleInstall('Templates')) {
            $this->error('你还没有安装模版模块，无法使用模版商店！',U("Admin/Main/index"));
        }
    }

    //在线插件列表
    public function index() {

		if (IS_POST) {
            $this->redirect('index', $_POST);
        }
        $parameter = array(
            'page' => $_GET[C('VAR_PAGE')]? : 1,
            'paging' => 10,
        );
        $keyword = I('post.keyword', '', 'trim');
        $id = I('get.catid', '', 'trim');
        if (!empty($keyword) || !empty($id)) {
            $parameter['keyword'] = $keyword;
            $parameter['catid'] = $id;
            $this->assign('keyword', $keyword)
                ->assign('catid',$id);
        }

        if (IS_AJAX) {
            $data = $this->Cloud->data($parameter)->act('get.templates.list');
            if (false === $data) {
                exit($this->Cloud->getError());
            }
            $page = $this->page($data['total'], $data['paging']);
           	$this->assign('data', $data['data']);
            $this->assign('Page', $page->show());  
            $this->display('ajax'); 
            return true;
        }
        $this->assign('page', $parameter['page']);
        $this->display();
    }

     //在线栏目列表
    public function catList() {

    	 if (IS_AJAX) {
            $data = $this->Cloud->act('get.templates.Category');
            if (false === $data) {
                exit($this->Cloud->getError());
            }
            
           	$this->assign('data', $data['data']);
            $this->display('category');
            return true;
        }
    }
    //云端模版下载安装
    public function install() {
        $identification = I('get.identification', '', 'trim');
        if (empty($identification)) {
            $this->error('请选择需要安装的模版！');
        }
        $this->assign('stepUrl', U('public_step_1', array('identification' => $identification)));
        $this->assign('identification', $identification);
        $this->display();
    }
    //目录权限判断通过后获取下载地址进行插件下载
    public function public_step_1() {
        if (\Libs\System\RBAC::authenticate('install') !== true) {
            $this->errors('您没有该项权限！');
        }
        $identification = I('get.identification', '', 'trim');
        if (empty($identification)) {
            $this->error('请选择需要安装的模版！');
        }
        $cache = S('Cloud');
        if (!empty($cache)) {
            $this->error('已经有任务在执行，请稍后！');
        }
        //帐号权限检测
        if ($this->Cloud->competence() == false) {
            $this->errors($this->Cloud->getError());
        }
        //获取插件信息
        $data = $this->Cloud->data(array('identification' => $identification))->act('get.templates.info');

        if (false === $data) {
            $this->error($this->Cloud->getError());
        } else {
            S('Cloud', $data, 3600);
        }
        if (empty($data)) {
            $this->errors('获取不到需要安装的模版信息缓存！');
        }
        $path = TEMPLATE_PATH .  $data['identification'];
        
        //检查是否有同样的插件目录存在
        if (file_exists($path)) {
            $this->errors("目录：{$path} 已经存在，无法安装在同一目录！");
        }
        //获取下载地址
        $packageUrl = $this->Cloud->data(array('identification' => $identification))->act('get.templates.install.package.url');
        if (empty($packageUrl)) {
            $this->errors($this->Cloud->getError());
        }
        //开始下载
        if ($this->CloudDownload->storageFile($packageUrl) !== true) {
            $this->errors($this->CloudDownload->getError());
        }
        $this->success('文件下载完毕！', U('public_step_2', array('package' => $packageUrl)));
    }

    //移动目录到插件
    public function public_step_2() {
        if (\Libs\System\RBAC::authenticate('install') !== true) {
            $this->errors('您没有该项权限！');
        }
        $data = S('Cloud');
        if (empty($data)) {
            $this->errors('获取不到需要安装的插件信息缓存！');
        }
        $packageUrl = I('get.package');
        if (empty($packageUrl)) {
            $this->errors('package参数为空！');
        }
        //临时目录名
        $tmp = $this->CloudDownload->getTempFile($packageUrl);
        //插件安装目录
        $addonsPath = TEMPLATE_PATH .$data['identification'] . '/';
        //.  $data['identification'] . '/'
        if ($this->CloudDownload->movedFile($tmp, $addonsPath, $packageUrl) !== true) {
            $this->errors($this->CloudDownload->getError());
        }


        $staticPath = SITE_PATH."statics/";
        $a = $this->CloudDownload->movedFile($addonsPath."statics/", $staticPath, $addonsPath."statics/");
        $this->success('移动文件到插件目录中，等待安装！', U('public_step_3', array('identification' => $data['identification'])));
    }
     //安装插件
    public function public_step_3() {
        if (\Libs\System\RBAC::authenticate('install') !== true) {
            $this->errors('您没有该项权限！');
        }
        $identification = I('get.identification');
        S('Cloud', NULL);
        $this->success('插件安装成功！');
        // if (D('Addons/Addons')->installAddon($identification)) {
        //     //$this->success('插件安装成功！');
        // } else {
        //     $error = D('Addons/Addons')->getError();
        //     //删除目录
        //     LvyeCMS()->Dir->delDir(TEMPLATE_PATH .  $identification);
        //     $this->error($error ? $error : '插件安装失败！');
        // }
    }
    //获取模版使用说明
    public function public_explanation() {
        $identification = I('get.identification');
        if (empty($identification)) {
            $this->error('缺少参数！');
        }
        $parameter = array(
            'identification' => $identification
        );
        $data = $this->Cloud->data($parameter)->act('get.templates.explanation');

        if (false === $data) {
            $this->error($this->Cloud->getError());
        }
        $this->ajaxReturn(array('status' => true, 'identification' => $identification, 'data' => $data));
    }

    protected function errors($message = '', $jumpUrl = '', $ajax = false) {
        S('Cloud', NULL);
        $this->error($message, $jumpUrl, $ajax);
    }
}
