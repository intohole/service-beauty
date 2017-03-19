<?php
define ('BACK_AUTH_NAME','backadminyinuoauthv10');
class Session_AdminYnUser { 
	
	static $obj;
	
	private $uid;
	
	private $username;
	
	private $chineseName;

	public $auth_name = BACK_AUTH_NAME;
	
	private $login_url = 'http://admin.jyxl.com.cn/login';

	public $domain = 'admin.jyxl.com.cn';
	
	private function  __construct($login_url = null, $domain = null){
		if(empty($this->login_url)) {
			$host = $_SERVER["HTTP_HOST"];
			$this->login_url = "http://{$host}/login";
		}
		if (empty ( $_COOKIE [$this->auth_name] )) {
			return;
		}

		list ( $uid, $username, $ua, $tm, $chineseName ) = @$this->decodeAuth ($_COOKIE [$this->auth_name]);

		//ua检验
		if (empty ( $uid ) || $ua !== md5($_SERVER ['HTTP_USER_AGENT'])) {
			return;
		}

		//TODO:过期时间检验
		$this->uid = $uid;
		$this->username = $username;
		$this->chineseName = $chineseName;
	
	}
	
	static public function instance(){
		if(self::$obj)
			return self::$obj;
		else{
			self::$obj = new Session_AdminYnUser();
		}
		return self::$obj;
	}
	
	/**
	 * 用户是否登陆
	 * */
	public function isLogin(){
		if(! empty($this->uid))
			return true; 
		else
			return false;
	}

	/**
	 * 
	 * 跳转到登录页面
	 * @param unknown_type $forward
	 * @param unknown_type $exit
	 */
	public function requireLogin($forward = '', $exit = true){
		if(! $this->isLogin()){
			if($forward === null){
				header("location: " . $this->login_url);	
			}else{
				if(empty($forward)){
					$forward = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
				}
				$forward = urlencode($forward);
				header("location: ". $this->login_url . "?forward=$forward");
			}
			if($exit){
				exit;
			}
				
		}
	}

	/**
	 * 
	 * 设置登录状态
	 * @param unknown_type $uid
	 * @param unknown_type $username
	 * @param unknown_type $ua
	 * @param unknown_type $outtime
	 */
	public function setLogin($uid, $username, $ua = null,$outtime = null, $chineseName = null){
		if(empty($ua)){
			$ua = $_SERVER['HTTP_USER_AGENT'];
		}	
		$str = $this->encodeAuth($uid, $username, $ua, $chineseName);
		setcookie($this->auth_name,urlencode($str),$outtime,'/',$this->domain);
	}

	/**
	 * 用户退出
	  */
	public function setLogout(){
		setcookie($this->auth_name,'',-1,'/',$this->domain);
	}
	
	public function __get($key){
		if('uid' == $key){
			return $this->uid;
		}elseif ('username' == $key) {
			return $this->username;
		}elseif ('chineseName' == $key) {
			return $this->chineseName;
		}
		return ;
	}
	
	public  function getUid(){
		return $this->uid;
	}	
	
	public function getUserName(){
		return $this->username;
	}	
	
	public function getChineseName(){
		return $this->chineseName;
	}	

	/**
	 * 生成加密的登陆cookie
	 */
	private function  encodeAuth($uid,$username,$ua,$chineseName=null){
		$tm = time();
		$ua = md5($ua);
		$info = "$uid\t$username\t$ua\t$tm\t$chineseName";
		$des = new Session_DES();
		$str = $des->encrypt($info);
		return $str;
	}

	/**
	 * 解析加密cookie 
	 */
	private function decodeAuth($str){
		$des = new Session_DES();
		$info = explode("\t",@$des->decrypt($str));
		if(is_array($info)){
			return $info;
		}else{
			return array();
		}
	}
	
	public function auth($controller,$action){
		if(!in_array($controller,$conArr)){
			return false;
		}
		if(!in_array($action,$actArr)){
			return false;
		}
		return true;
	}
}
