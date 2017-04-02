<?php
/**
 * 参数说明：
 *  允许的所有php类型：boolean integer float(double) string array object(stdClass)
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
class SdkSample {
	// 项目域名
	const PROJECT = '';
	// 服务端地址
	const LOCATION = '';
	// 密钥
	const KEY = '';
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
		$soapVar = new SoapVar ( $obj, SOAP_ENC_OBJECT, 'proving_user', SOAP_SERVER_DOMAIN );
		$header = new SoapHeader ( self::LOCATION, '__auth', $soapVar, true, SOAP_ACTOR_NEXT );
		$this->client->__setSoapHeaders ( array (
				$header
		) );
	}
	
	/**
	 * 调用服务端TestService的test($args)方法
	 * @param array $args
	 */
	function test(array $args){
		return $this->client->Test('test',$args);
	}
}