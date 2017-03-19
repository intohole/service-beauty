<?php
class Ui_Common_Footer extends Ui_Base {
	public function __construct() {
		parent::__construct();
		$this->display('common/footer.phtml');
	}
}