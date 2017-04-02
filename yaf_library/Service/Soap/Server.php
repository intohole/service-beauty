<?php
/**
 *	SOAP服务端，需要project.ini文件，格式见yaf_sample项目
 	Yaf_Registry::get('server_project')  服务端的name和code
	Yaf_Registry::get('client_project')  sdk传过来的对应的project的name和code
 * @author xiepeng@joyport.com
 */
class Service_Soap_Server {
	private $authResult;
	/**
	 * 启动soap服务端
	 *
	 * @throws ErrorException
	 */
	public static final function start($className = null) {
		static $started = false;
		if (false === $started) {
			$server = new SoapServer ( null, array (
					'uri' => 'SDK'
			) );
			if (! isset ( $className )) {
				$className = __CLASS__;
			}
			$server->setClass ( $className );
			$server->handle ();
			$started = true;
		} else {
			throw new ErrorException ( 'server has been started already' );
		}
	}

	/**
	 * 调用用户方法
	 *
	 * @param unknown $modelName
	 * @param unknown $args
	 * @throws ErrorException
	 * @return SoapParam
	 */
	public final function __call($serviceName, $args) {
		$res = new stdClass ();
		$res->errorCode = 0;
		$res->errorMsg = null;
		$res->value = null;
		$start = microtime ( true );
		try {
			if (true === $this->authResult) {
				if ($serviceName == '__desc') {
					$res->value = $this->__desc ();
				} else {
					$methodName = array_shift ( $args );
					$file = Yaf_Application::app ()->getConfig ()->application->directory . '/service/' . ucfirst ( $serviceName ) . '.php';
					require_once $file;
					$className = $serviceName . 'Service';
					$res->value = call_user_func_array ( array (
							new $className (),
							$methodName
					), $args );
				}
			} else {
				if (isset ( $this->authResult )) {
					throw $this->authResult;
				} else {
					throw new ErrorException ( 'auth needed' );
				}
			}
		} catch ( Exception $e ) {
			$err = $this->catchException ( $e );
			$res->errorMsg = $err [1];
			$res->errorCode = $err [0];
		}
		$res->timecost = round ( microtime ( true ) - $start, 6 );
		return new SoapParam ( $res, 'res' );
	}

	/**
	 * 返回数组,0=errorCode,1=errorMsg
	 *
	 * @param Exception $e
	 */
	protected function catchException(Exception $e) {
		return array (
				$e->getCode (),
				$e->getMessage ()
		);
	}

	/**
	 * 用于测试通信是否正常
	 */
	private function __desc() {
		return true;
	}

	/**
	 * 验证，验证结果保存在$this->authResult
	 *
	 * @param stdClass $object
	 */
	public final function __auth($object) {
		if (! is_object ( $object )) {
			$this->authResult = new ErrorException ( 'auth object is invalid' );
			return;
		}
		if (empty ( $object->project )) {
			$this->authResult = new ErrorException ( 'project name can\'t be empty' );
			return;
		}
		if (! is_numeric ( $object->time ) || 10 != strlen ( ( string ) $object->time )) {
			$this->authResult = new ErrorException ( 'time is invalid, time=' . $object->time );
			return;
		}
		$file = dirname ( Yaf_Application::app ()->getAppDirectory () ) . '/conf/project.ini';
		if (! is_file ( $file )) {
			$this->authResult == new ErrorException ( 'project.ini not found' );
			return;
		}
		$config = (new Yaf_Config_Ini ( $file ))->toArray ();
		$key = null;
		foreach ( $config ['project'] ['access'] as $v ) {
			if ($v ['url'] == $object->project) {
				$client = $v;
				break;
			}
		}
		if (isset ( $client )) {
			$key = $client ['key'];
			$timeout = $client ['timeout'];
		}
		if (! isset ( $key )) {
			$this->authResult = new ErrorException ( 'project is invalid, maybe not registered' );
			return;
		}
		if (! is_numeric ( $timeout )) {
			$this->authResult = new ErrorException ( 'config timeout is invalid, timeout=' . $timeout );
			return;
		}
		if (empty ( $key )) {
			$this->authResult = new ErrorException ( 'config key can\'t be empty' );
			return;
		}
		// 验证key
		// def1d610d4
		if ($object->time < time () - $timeout) {
			$this->authResult = new ErrorException ( "request time out" );
			return;
		}
		if ($object->sign != md5 ( $object->project . $object->time . $key )) {
			$this->authResult = new ErrorException ( "sign error" );
			return;
		}
		/*
		 * 注册全局变量
		 */
		Yaf_Registry::set ( "server_project", array (
				'name' => $config ['project'] ['server'] ['name'],
				'code' => $config ['project'] ['server'] ['code']
		) );
		Yaf_Registry::set ( "client_project", array (
				'name' => $client ['name'],
				'code' => $client ['code']
		) );
		$this->authResult = true;
	}
}
