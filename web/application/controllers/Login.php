<?php
/**
 * 后台登录页
 *
 */
class LoginController extends Yaf_Controller_Abstract {


    private $_host;

    public function init(){
        $this->_adminUserModel = new AdminUserModel();
        $this->_loginUser =  Session_AdminFengkong::instance();
        $this->_host = Yaf_Application::app()->getConfig()->get("website")['host'];
    }

    public function indexAction(){
        return true;
    }

    public function loginAjaxAction(){
        $username = $this->getRequest()->getPost("username", "");
        $password = trim($this->getRequest()->getPost("password", ""));
        $code = trim($this->getRequest()->getPost("code", 0));
        if(empty($username) || empty($password) || empty($code)){
            $return['error'] = AJAX_RESULT_ERROR;
            $return['errorMsg'] = '参数缺失';
            echo json_encode($return);exit;
        }
        $key = $this->getRequest()->getCookie("admin_key","XXX");
        $fcache = Zend_Cache::factory("Core", "File");
        $verifycode = $fcache->load($key);
        $result = array();
        if(strtolower($verifycode) != strtolower($code)){
            $return['error'] = AJAX_RESULT_ERROR;
            $return['errorMsg'] = '验证码错误，请重新输入';
            echo json_encode($return);exit;
        }
        /*
                $rand = md5(time() . mt_rand(0,1000));
                $data['salt']= substr($rand, 6,6);
                $data['password'] = md5(md5($password).$data['salt']);
                var_dump($data);exit;
        */
        //TODO: 检查用户名密码
        $info = $this->_adminUserModel->findUser($username);
        //var_dump($password,$info['salt'],$username,md5(md5($password).$info['salt']),$info);exit;
        if (md5(md5($password).$info['salt']) == $info['passwd']) {
			if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== FALSE) {
				//判断该用户登录状态
				if($info['login_status'] == 2){
					//查找登录openid以前是否有关联,如果有,取消以前关联
					$beforeInfo = $this->_adminUserModel->checkBefore($_SESSION['openid']);
					if($beforeInfo){
						$befoIds = array();
						foreach($beforeInfo as $vall){
							$befoIds[] = $vall['id'];
						}	
					}
					
					if(in_array($info['id'],$befoIds)){
						foreach($befoIds as $k=>$v){
							if($v == $info['id']){
								unset($befoIds[$k]);
							}
						}       
					}
					
					//退出状态,修改登录状态和openid
					$saveDatas = array(
						'login_status' => 1,
						'openid' =>$_SESSION['openid'],
						'wx_avatar' =>$_SESSION['wx_avatar']
					);
					$save_resu = $this->_adminUserModel->mod($info['id'],$saveDatas);
					if($save_resu){
						if($befoIds){
							$this->_adminUserModel->emptyOpenid($befoIds);
						}
					}
				}elseif($info['login_status'] == 1){
					//登录状态,不需任何操作
				}
			}
            $this->_loginUser->setLogin($info['id'], $info['phone'], null, time()+3600*24*30, $info['realname']);
            $return['error'] = AJAX_RESULT_NO_ERROR;
            $return['errorMsg'] = '登录成功';
            echo json_encode($return);exit;
        }
        else {
            $return['error'] = AJAX_RESULT_ERROR;
            $return['errorMsg'] = '手机号或密码错误';
            echo json_encode($return);exit;
        }
    }

    public function codeAction() {
        $VerifyCode = new Idcode_Verify();
        $font = array(
            'space' => 0,
            'size' => 14,
            'left' => 5,
            'file' => '',
            'top' => ''
        );

        $key = @$_COOKIE['admin_key'] ? @$_COOKIE['admin_key'] : md5($_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR'] . rand());
        setcookie("admin_key", $key, time()+60 * 5, "/");

        $VerifyCode->setFont($font);
        $VerifyCode->generateCode();
        $verify_code = strtolower($VerifyCode->getVerifyCode());

        $fcache = Zend_Cache::factory("Core", "File");
        $fcache->save($verify_code, $key);

        $VerifyCode->paint();
        exit();

    }

    public function logoutAction(){
        $this->_loginUser->setLogout();
        header("Location: /login");
    }
	
	public function logingrantAction() {
		$code = $this->getRequest()->get('code');
		$stype = $this->getRequest()->get('stype', 0);
		$forname = $this->getRequest()->get('forname', '');
		$goback = urldecode($this->getRequest()->get('goback', ''));
        $appID = Yaf_Application::app()->getConfig()->get("wechat")['appid'];
        $appSecert = Yaf_Application::app()->getConfig()->get("wechat")['appsecret'];
		$this->login_url =  'http://'.Yaf_Application::app()->getConfig()->website->host.'/login';

		if(empty($forward)){
			$forward = 'http://'.$_SERVER['HTTP_HOST'].'/m';
		}
		$forward = urlencode($forward);
        if (!$code) {
			header("location: ". $this->login_url . "?forward=$forward");
            return FALSE;
        }

        $tokenurl = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$appID}&secret={$appSecert}&code={$code}&grant_type=authorization_code";
        $result = json_decode(file_get_contents($tokenurl), TRUE);
        if ($result && !isset($result['errcode'])) {
            $infourl = "https://api.weixin.qq.com/sns/userinfo?access_token={$result['access_token']}&openid={$result['openid']}&lang=zh_CN";
            $result = json_decode(file_get_contents($infourl), TRUE);

            if (isset($result['nickname'])) {
                $_SESSION['openid'] = $result['openid'];
                $_SESSION['wx_nickname'] = $result['nickname'];
                $_SESSION['wx_avatar'] = $result['headimgurl'];
	
				if($stype == 2){
					//根据openid去报单用户表中匹配
					$selRes = $this->_adminUserModel->getByOpenId($_SESSION['openid']);
					//print_r($selRes);exit;
					if($selRes){
						//匹配成功,设置cookie
						$this->_loginUser->setLogin($selRes['id'], $selRes['phone'], null, time() + 3600 * 24 * 30, $selRes['username']);
						if($goback){
							$goto = 'http://'.$_SERVER['HTTP_HOST'].$goback;
							header('Location: ' . $goto);exit;
						}else{
							$goto = 'http://'.$_SERVER['HTTP_HOST'].'/m';
							header('Location: ' . $goto);exit;
						}
						/* if($forname){
							if($forname == 'index'){
								$goto = 'http://'.$_SERVER['HTTP_HOST'].'/m';
								header('Location: ' . $goto);exit;
							}else{
								$goto = 'http://'.$_SERVER['HTTP_HOST'].'/m/'.$forname;
								header('Location: ' . $goto);exit;
							}
						}else{
							$goto = 'http://'.$_SERVER['HTTP_HOST'].'/m';
							header('Location: ' . $goto);exit;
						} */
						//return TRUE;
					}else{
						header("location: ". $this->login_url . "?forward=$forward");exit;
						//return FALSE;
					}
				}elseif($stype == 3){
					if($forname == 'login'){
						//header("Location: /login?forward=$forward");exit;
						header("location: ". $this->login_url . "?forward=$forward");exit;
						//header("location: ". $this->login_url . "?forward=$forward");exit;
					}elseif($forname == 'signout'){
						$goto = 'http://'.$_SERVER['HTTP_HOST'].'/m/'.$forname;
						header('Location: ' . $goto);exit;
					}
					return FALSE;
				}else{
					header("location: ". $this->login_url . "?forward=$forward");exit;
				}
				
                return FALSE;
            } else {
                header("location: ". $this->login_url . "?forward=$forward");
                return FALSE;
            }
        } else {
            header("location: ". $this->login_url . "?forward=$forward");
            return FALSE;
        }
        return FALSE;
	}
	
}
