<?php

/**
 *  微信模板消息推送
*/


class WxSmsModel {

    private $appid;
    private $appsecret;
	
	private $cache;

    public function __construct($appid='',$appsecret='') {
		if($appid && $appsecret){
			$this->appid = $appid;
			$this->appsecret = $appsecret;
		}else{
			$this->appid = Yaf_Application::app()->getConfig()->get("wechat")['appid'];
			$this->appsecret = Yaf_Application::app()->getConfig()->get("wechat")['appsecret'];
		}
		
		$this->cache = new Utils_Redis();
		$this->access_token = $this->getToken($this->appid,$this->appsecret);
		//var_dump($this->appid,$this->appsecret,$this->access_token);exit;
		
    }
	
	//发送模板消息
	public function sendMessage($touser,$template_id,$url,$data,$topcolor = '#7B68EE'){
		$template = array
		(
			'touser'=>$touser,
			'template_id' => $template_id,
            'url' => $url,
            'topcolor' => $topcolor,
            'data' => $data
		);
		//print_r($this->access_token);exit;
		$json_template = json_encode($template);
		$url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$this->access_token;
		//print_r($url);exit;
		$res = $this->request_post($url,urldecode($json_template));
		//print_r($res);exit;
		if($res['errcode'] == 0){
			return true;
		}else{
			return false;
		}
	}
	
	//https请求(POST)
    private function request_post($url = '',$param = '')
    {
        if (empty($url) || empty($param)) {
            return false;
        }
        $postUrl = $url;
        $curlPost = $param;
        $ch = curl_init(); //初始化curl
        curl_setopt($ch, CURLOPT_URL, $postUrl); //抓取指定网页
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_HEADER, 0); //设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1); //post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($ch); //运行curl
        curl_close($ch);
        return $data;
    }
	
	//https请求(GET)
	private function request_get($url = '')
	{
        if (empty($url)) {
            return false;
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);//对认证证书来源的检查，FALSE表示阻止对证书的合法性的检查。
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);//从证书中检查SSL加密算法是否存在
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
	}
	
	
	//获取access_token
	public function getToken($appid,$appsecret){
		$tokenKey = 'WEIXINTICKETKEY:token_'.$appid;
		$token = $this->cache->get($tokenKey);
		if(!$token){
			$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$appsecret;
            //$token = $this->request_get($url);
            //$token = json_decode(stripslashes($token));
            //$arr = json_decode(json_encode($token), true);
			$arr = file_get_contents($url);
			$arr = json_decode($arr, true);
            $this->cache->set($tokenKey, $arr['access_token'], 3600);
			return $arr['access_token'];
		}
		return $token;
	}
	
	//获取access_token
	/* public function getToken($appid,$appsecret){
		if($_SESSION['acctoken']){
			$access_token = $_SESSION['acctoken'];
		}else{
			$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$appsecret;
            $token = $this->request_get($url);
            $token = json_decode(stripslashes($token));
            $arr = json_decode(json_encode($token), true);
            $access_token = $arr['access_token'];
			$_SESSION['acctoken'] = $access_token;
		}
		return $access_token;
		
	} */
	
	/* //https请求(支持GET和POST)
	protected function http_request($url,$data=null){
		$curl = curl_init();
		curl_setopt($curl,CURLOPT_URL,$url);
		curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,FALSE);
		curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,FALSE);
		if(!empty($data)){
			curl_setopt($curl,CURLOPT_POST,1);
			curl_setopt($curl,CURLOPT_POSTFIELDS,$data);
		}
		curl_setopt($url,CURLOPT_RETURNTRANSFER,1);
		$output = curl_exec($curl);
		curl_close($curl);
		return $output;
	} */

}
