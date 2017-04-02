<?php
class Ui_Common_Header extends Ui_Base {
	public function __construct() {
		parent::__construct();
		$this->display('common/header.phtml');
	}
}