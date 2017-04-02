<?php
class WeixinModel {
	private $wx_appid;
	private $wx_appsecret;
	
	private $cache;
	
	public function __construct($appid='', $secert='') {
		if (!$appid || !$secert) {
			$this->wx_appid = Yaf_Application::app()->getConfig()->get("wechat")['appid'];
			$this->wx_appsecret = Yaf_Application::app()->getConfig()->get("wechat")['appsecret'];
		} else {
			$this->wx_appid = $appid;
			$this->wx_appsecret = $secert;
		}
		
		$this->cache = new Utils_Redis();
	}
	
	/**
	 * getJsApiDataSet
	 * 获取微信js接口所需要的JsApiTicket、noncestr、timestamp。
	 * @param $url string
	 * @access public
	 * @return string
	 */
	public  function getJsApiDataSet($url = ''){
		if(empty($url)){
			return false;
		}
		$randStr = 'zxcvbnmasdfghjklqwertyuiop1234567890QWERTYUIOPASDFGHJKLZXCVBNM';
		$jsApiTicket = $this->getJudgeJsApiTicket();
		if(!$jsApiTicket){
			return false;
		}
		$noncestr = '';
		for($i = 0;$i<=15;$i++){
			$noncestr .= substr($randStr, rand(0,60), 1);
		}
		$time = time();
		$string1 = "jsapi_ticket=".$jsApiTicket."&noncestr=".$noncestr."&timestamp=".$time."&url=".$url;
		$string = sha1($string1);
		$arr =array(
			'signature'      => $string,
			'noncestr'       => $noncestr,
			'timestamp'      => $time,
			'appId'          => $this->wx_appid,
			'url'            => $url,
		);
		return $arr;
	}
	
	/**
	 * getJudgeJsApiTicket
	 * 获取微信jsApi_ticket
	 * @access public
	 * @return string
	 */
	public function getJudgeJsApiTicket(){
		$ticketKey = 'WEIXINTICKETKEY:jsapiticket_'.$this->wx_appid;
		$jsApiTicket = $this->cache->get($ticketKey);
		if(!$jsApiTicket){
			$newJsApiTicket = $this->_getJsApiTicket();
			$this->cache->set($ticketKey, $newJsApiTicket, 3600);
			return $newJsApiTicket;
		}
		return $jsApiTicket;
	}
	
	/**
	 * getJsApiTicket
	 * 获取微信jsApi_ticket
	 * @access private
	 * @return string
	 */
	private function _getJsApiTicket(){
		$accessToken = $this->_getAccessToken();
		if(!$accessToken){
			return false;
		}
		$url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token='.$accessToken.'&type=jsapi';
		$str = file_get_contents($url);
		$str = json_decode($str, TRUE);
		return $str['ticket'];
	}
	
	/**
	 * getAccessToken
	 * 获取微信token
	 * @access private
	 * @return string
	 */
	private function _getAccessToken(){
		$tokenKey = 'WEIXINTICKETKEY:token_'.$this->wx_appid;
		$token = $this->cache->get($tokenKey);
		if(!$token){
			$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$this->wx_appid."&secret=".$this->wx_appsecret;
			$str = file_get_contents($url);
			$str = json_decode($str, true);
			$this->cache->set($tokenKey, $str['access_token'], 3600);
			return $str['access_token'];
		}
		return $token;
	}
	
	public function genInviteQR($code) {
		$key = 'WEIXIN_YDD_INVITE_QR_'.$this->wx_appid.'_'.$code;
		$ticket = $this->cache->get($key);
		
		if ($ticket) {
			return 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.urlencode($ticket);
		}
		
		$req = new HttpRequest();
		$req->timeout = 20;
		$req->url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.urlencode($this->_getAccessToken());
		//七天有效
		$req->body = '{"expire_seconds": 604800, "action_name": "QR_SCENE", "action_info": {"scene": {"scene_id": '.$code.'}}}';
		
		try {
			$res = $req->post();
		} catch (Exception $e) {
			return FALSE;
		}
		if($res->statusCode != '200' ){
			return FALSE;
		}
		$result = json_decode($res->body, TRUE);
		if (!$result || !isset($result['ticket'])) {
			return FALSE;
		}
		
		$ticket = $result['ticket'];
		$this->cache->set($key, $ticket, 604800);
		return 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.urlencode($ticket);
	}
}