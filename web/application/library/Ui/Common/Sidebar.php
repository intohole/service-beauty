<?php
class Ui_Common_Sidebar extends Ui_Base {
	public function __construct($menus=[]) {
		parent::__construct();

        $userId = Session_AdminFengkong::instance()->getUid();
        $menus = (new RoleModel())->getUserMenus($userId);
		$this->assign('menus', $menus);
		$this->display('common/sidebar.phtml');
	}
}