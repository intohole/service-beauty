<?php

class Cache_Memcache {
	private $cache;
	private $namespace;

	public function __construct($time=null) {
		$config = Yaf_Application::app ()->getConfig ();
		$servers = $config->memcache->servers->toArray();

		$backendOptions = array(
			'servers' => $servers,
			'compression' => false
		);
		if(empty($time)){
			$time = $config->memcache->lifetime;
		}
		$frontendOptions = array(
			'lifeTime' => $time,
			'automatic_serialization' => true
		);

		$this->cache = Zend_Cache::factory('Core', 'Memcached', $frontendOptions, $backendOptions);
		$this->namespace = $config->memcache->namespace;
	}
	
	public function setNameSpace($namespace) {
		$this->namespace = $namespace;
	}

    private function _getKey($key){
        if($this->namespace){
            return $this->namespace . "_". $key;
        }
        return $key;
    }

	public function save($key, $value) {
		$key = $this->_getKey($key);
		return $this->cache->save($value, $key);
	}

	public function load($key) {
        $key = $this->_getKey($key);
		return $this->cache->load($key);
	}

	public function remove($key) {
        $key = $this->_getKey($key);
		return $this->cache->remove($key);
	}
}
