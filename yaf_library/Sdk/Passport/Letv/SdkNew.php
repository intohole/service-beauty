<?php
/**
 * 参数说明：
 *  所有array $id参数的键可以是uid,username,mobile,email,nickname，例如$id=array('username'=>'ares')
 *  null值和空字符串和0是三种不同的值！
 *
 * 返回值说明：
 * 	所有返回值都是一个stdClass，value属性是调用结果，如果errorCode大于零表示调用没有成功运行。
 * 	stdClass Object
 *	(
 *	    [errorCode] => 0
 *	    [errorMessage] => null
 *	    [value] => null
 *		[timecost] => 0
 *	)
 *
 * @author xiepeng@joyport.com
 */
class Sdk_Passport_Letv_SdkNew {
	// 项目名称，建议用域名
	const PROJECT = 'letv.ledu.com';
	// 服务端地址
	const LOCATION = 'http://passport.data.service.ledu.com:8080/api';
	// 密钥
	const KEY = 'G!NK2fEMZa';
	private $client;
	public function __construct() {
		$this->client = new SoapClient ( null, array (
				"location" => self::LOCATION,
				"uri" => 'SDK'
		) );
		$time = time ();
		$obj = new stdClass ();
		$obj->project = self::PROJECT;
		$obj->time = $time;
		$obj->sign = md5 ( self::PROJECT . $time . self::KEY );
		$soapVar = new SoapVar ( $obj, SOAP_ENC_OBJECT, 'proving_user' );
		$header = new SoapHeader ( self::LOCATION, '__auth', $soapVar, true, SOAP_ACTOR_NEXT );
		$this->client->__setSoapHeaders ( array (
				$header
		) );
	}

	/**
	 * 用于单例模式
	 *
	 * @return self
	 */
	public function getInstance() {
		if (! isset ( self::$instance )) {
			self::$instance = new self ();
		}
		return self::$instance;
	}

	/**
	 * 新用户注册
	 * from,from_uid,from_nickname,username,password,nickname,icon,email,mobile,icon,sex,nick_name,realname,idcard,vip_level,reg_ip
	 *
	 * @param array $args
	 * @return number
	 *         uid
	 */
	function reg(array $args) {
		return $this->client->User ( 'reg', $args );
	}

	/**
	 * 更新用户信息
	 *
	 * @param array $args
	 * @param array $id
	 * @return bool
	 */
	function update(array $id, array $args) {
		return $this->client->User ( 'update', array_merge ( $args, $id ) );
	}

	/**
	 * 更新用户密码
	 *
	 * @param array $id
	 * @param unknown $old
	 * @param unknown $new
	 */
	function updatePassword(array $id, $old, $new) {
		$id ['old'] = $old;
		$id ['new'] = $new;
		return $this->client->User ( 'updatePassword', $id );
	}

	/**
	 * 读取用户信息
	 *
	 * @param array $id
	 * @param array $fields
	 * @return array
	 */
	function info(array $id, array $fields = null) {
		if (isset ( $fields )) {
			$id ['fields'] = $fields;
		}
		$res = $this->client->User ( 'info', $id );
		if (is_array ( $res->value )) {
			$value = array ();
			foreach ( array_keys ( $res->value ) as $v ) {
				foreach ( $res->value [$v] as $k1 => $v1 ) {
					$value [$k1] = $v1;
				}
			}
			$res->value = $value;
		}
		return $res;
	}

	/**
	 * 邮箱是否存在并且已经激活
	 *
	 * @param unknown $email
	 * @return bool
	 */
	function emailExists($email) {
		$res = $this->client->User ( 'info', array (
				'email' => $email,
				'fields' => array (
						'email',
						'email_active_time'
				)
		) );
		$value = false;
		if (is_array ( $res->value )) {
			if (isset ( $res->value ['email'] )) {
				$res->value = array_pop ( $res->value ['email'] );
			} else {
				$res->value = array ();
			}
			if (array_key_exists ( 'email', $res->value ) && array_key_exists ( 'email_active_time', $res->value ) && 0 < $res->value ['email_active_time']) {
				$value = true;
			}
		}
		$res->value = $value;
		return $res;
	}

	/**
	 * 手机是否存在
	 *
	 * @param unknown $email
	 * @return bool
	 */
	function mobileExists($mobile) {
		$res = $this->client->User ( 'info', array (
				'mobile' => $mobile
		) );
		$value = false;
		if (is_array ( $res->value )) {
			if (array_key_exists ( 'mobile', $res->value ) && count ( $res->value ['mobile'] ) > 0) {
				$value = true;
			}
		}
		$res->value = $value;
		return $res;
	}
	/**
	 * 昵称是否存在
	 *
	 * @param unknown $email
	 * @return bool
	 */
	function nicknameExists($nickname) {
		$res = $this->client->User ( 'info', array (
				'nickname' => $nickname,
				'fields' => array (
						'nickname'
				)
		) );
		$value = false;
		if (is_array ( $res->value )) {
			if (array_key_exists ( 'nickname', $res->value ) && count ( $res->value ['nickname'] ) > 0) {
				$value = true;
			}
		}
		$res->value = $value;
		return $res;
	}
	/**
	 * 用户名是否存在
	 *
	 * @param unknown $email
	 * @return bool
	 */
	function usernameExists($username) {
		$res = $this->client->User ( 'info', array (
				'username' => $username,
				'fields' => array (
						'username'
				)
		) );
		$value = false;
		if (is_array ( $res->value )) {
			if (array_key_exists ( 'username', $res->value ) && count ( $res->value ['username'] ) > 0) {
				$value = true;
			}
		}
		$res->value = $value;
		return $res;
	}

	/**
	 * 批量获取uid
	 *
	 * @param array $id
	 * @return array
	 */
	function getUid(array $id) {
		$res = $this->client->User ( 'info', array_merge ( $id, array (
				'field' => 'uid'
		) ) );
		$res = $this->client->User ( 'info', $id );
		if (is_array ( $res->value )) {
			$value = array ();
			foreach ( array_keys ( $res->value ) as $v ) {
				$value = array_merge ( array_keys ( $res->value [$v] ) );
			}
			$res->value = array_unique ( $value );
		}
		return $res;
	}

	/**
	 * 登录
	 *
	 * @param unknown $id
	 *        	nickname不能登录
	 * @param unknown $password
	 * @return bool
	 */
	function login(array $id, $password) {
		$args = $id;
		$args ['password'] = $password;
		return $this->client->User ( 'login', $args );
	}

	/**
	 * 玩某个游戏
	 *
	 * @param array $id
	 *        	只支持uid
	 * @param array $args
	 *        	game_id
	 *        	game_name
	 *        	server_id
	 *        	server_name
	 *        	server_sn
	 *        	login_ip
	 */
	function play(array $id, array $args) {
		return $this->client->User ( "play", array_merge ( $args, $id ) );
	}

	/**
	 * 查询玩家玩过的游戏
	 *
	 * @param unknown $uid
	 */
	function playedGame($uid) {
		return $this->client->User ( "playedGame", array (
				'uid' => $uid
		) );
	}

	/**
	 * 查询玩家玩过的游戏
	 *
	 * @param unknown $uid
	 */
	function playedServer($uid, $gameId = null, $limit = 10) {
		return $this->client->User ( "playedServer", array (
				'uid' => $uid,
				'game_id' => $gameId,
				'limit' => $limit
		) );
	}

	/**
	 * action区分大小写,例如User.info
	 *
	 * @param unknown $action
	 *        	用.分隔
	 * @param unknown $args
	 */
	function call($action, $args) {
		$action = explode ( '.', $action );
		return $this->client->$action [0] ( $action [1], $args );
	}

	/**
	 * 用于检测服务器通信
	 */
	function __desc() {
		$class = __CLASS__;
		$project = self::PROJECT;
		$key = self::KEY;
		$server = self::LOCATION;
		$tips = "当前测试信息：\n当前类名  : {$class}\n客户端项目: {$project}\n会话密钥  : {$key}\n服务器入口: {$server}\n";
		echo "<pre>$tips";
		$res = $this->client->__desc ();
		if ($res->errorCode === 0) {
			echo "服务器接口接求成功。服务器信息：" . $res->value;
		} else {
			echo "服务器接口接求失败。服务器信息：" . $res->errorMsg;
			var_dump ( $res );
		}
	}
}
