<?php
/**
 * 微信设置
 */
class WeixinController extends Yaf_Controller_Abstract {
	private $_req;
	
	public function init() {
		$this->_req = $this->getRequest();
	}
	
	public function YddAction() {
		$m = new YddWeixinModel();
		
		$echostr = $this->_req->get('echostr');
		if ($echostr) {
			$signature = $this->_req->get('signature');
			$timestamp = $this->_req->get('timestamp');
			$nonce = $this->_req->get('nonce');
			$m->valid($echostr, $signature, $timestamp, $nonce);
		} else {
			$m->handler();
		}
		return FALSE;
	}
	
	public function shareAction() {
		$url  = $this->getRequest()->get("url", "");
		if(!$url){
			Utils_Output::jsonResponse(101,"参数缺失");//参数缺失
		}

		$appid = Yaf_Application::app()->getConfig()->get("wechat")['appid'];
		$secert = Yaf_Application::app()->getConfig()->get("wechat")['appsecret'];
		$m = new WeixinModel($appid, $secert);
		$arr = $m->getJsApiDataSet($url);
		if($arr){
			Utils_Output::jsonResponse(0,'OK',$arr);exit;
		}
		Utils_Output::jsonResponse(102,'系统错误');//参数缺失
	}
	
	public function qrAction($code) {
		
		
		return FALSE;
	}
}