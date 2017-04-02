<?php
/**
 * session handler 设置方法
 */
class Session_SessionHandlerSetter {
	static public function run() {
		$config = Yaf_Application::app()->getConfig()->get("sessRedis");
		if ($config) {
			$session_redis = new Redis;
			try {
				$session_redis->connect($config['host'], $config['port'], $config['timeout']);
				if ($config['auth']) {
					$session_redis->auth($config['auth']);
				}
				$session_redis->select($config['db']);
				
				$redisSessHandler = new Session_RedisSessionHandler($session_redis);
				
				session_set_save_handler(
					array($redisSessHandler, 'open'),
					array($redisSessHandler, 'close'),
					array($redisSessHandler, 'read'),
					array($redisSessHandler, 'write'),
					array($redisSessHandler, 'destroy'),
					array($redisSessHandler, 'gc')
				);
			} catch (Exception $e) {
			}
		}
		
		session_name('YBKID');
		session_start();
	}
}