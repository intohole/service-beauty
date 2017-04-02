<?php
class Ui_Common_Chedai_Navcdnation extends Ui_Base {
	public function __construct($currentNav = '') {
            parent::__construct();
            $this->assign("currentNav", $currentNav);
            $this->display('common/chedai/nav_cdnation.phtml');
	}
}