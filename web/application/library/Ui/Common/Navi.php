<?php
class Ui_Common_Navi extends Ui_Base {
	public function __construct($navi=[]) {
		parent::__construct();
		$this->assign('navi', $navi);
		$this->display('common/navi.phtml');
	}
}