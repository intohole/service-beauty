<?php

class APP_Controller_Abstract extends Yaf_Controller_Abstract {
	public function getParam($param, $default = null) {
		$value = $this->getRequest()->getParam($param);
		if (isset($value)) {
			$value = strip_tags(trim($value));
			return $value;
		}
		
		if (is_array($_REQUEST[$param])) {
			foreach ($_REQUEST[$param] as $key => $sub_value) {
				$value[$key] = strip_tags(trim($sub_value));
			}
			return $value;
			//throw new Exception("value(Array) is not supported by METHOD:getParam");
		}
		
		$value = trim($_REQUEST[$param]);
		
		$value = strip_tags($value);
		
		if ($value == null && $default != null) {
			$value = $default;
		}
		return $value;
	}
	
	public function getPrams() {
		if (empty($_REQUEST)) {
			return false;
		}
		
		foreach ($_REQUEST as $key => $value) {
			$params[$key] = $this->getParam($key);
		}
		
		return $params;
	}
	
	public function getHtml() {
		
	}
}
