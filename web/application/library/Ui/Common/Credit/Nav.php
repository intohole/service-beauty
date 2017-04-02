<?php
class Ui_Common_Credit_Nav extends Ui_Base {
	public function __construct($currentNav = '') {
		parent::__construct();
    	$this->assign("currentNav", $currentNav);
		$this->display('common/credit/nav.phtml');
	}
}