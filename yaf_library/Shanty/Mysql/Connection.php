<?php

class Shanty_Mysql_Connection {
	protected $_wirte_db = null;
	protected $_read_db = null;
	
	public function __construct($dbConfigName = NULL) {
		$config = Yaf_Application::app ()->getConfig ();
		$servers = array();
		if($config->mysql->servers){
			$servers = $config->mysql->servers->toArray();
		}
		
        $masterDbConfig = array();
        $slaveDbConfig = array();
        if($dbConfigName === NULL){
            $masterDbConfig = $servers['master'][0];
            $slaveDbConfig = $servers['slave'];
        }
        else{
            if(array_key_exists($dbConfigName, $servers)){
                $masterDbConfig = $servers[$dbConfigName]['master'][0];
                $slaveDbConfig = $servers[$dbConfigName]['slave'];
            }
            else{
                //公共数据库配置
                $globalConfigFile = ini_get('yaf.library').'/conf/global.db.conf.ini';
                if(file_exists($globalConfigFile)){
                    $globalConfig = new Yaf_Config_Ini($globalConfigFile);
                    $globalConfigArray = $globalConfig->toArray();
                    $serversGlobal = $globalConfigArray[YAF_ENVIRON]['mysql']['servers'];
                    if(array_key_exists($dbConfigName, $serversGlobal)){
                        $masterDbConfig = $serversGlobal[$dbConfigName]['master'][0];
                        $slaveDbConfig = $serversGlobal[$dbConfigName]['slave'];
                    }                
                }
            }
        }
		$this->_write_db = Zend_Db::factory('PDO_MYSQL', $masterDbConfig);
		$this->_write_db->query("set names utf8");
		$this->_read_db = Zend_Db::factory('PDO_MYSQL', $this->setConnection($slaveDbConfig));
		$this->_read_db->query("set names utf8");
	}

	private function setConnection($configs) {
		if (empty($configs)) {
			throw new Exception("No mysql[master or slave] config find!");
		}
		$count = count($configs);
		$no =  intval(rand(0, 1000) % $count);

		return $configs[$no];
	}
	
	public function writeDb() {
		return $this->_write_db;
	}
	
	public function readDb() {
		return $this->_read_db;
	}
	
	public static function getWriteDb($dbConfigName = NULL) {
		$obj = new Shanty_Mysql_Connection($dbConfigName);
	
		return $obj->writeDb();
	}
	
	public static function getReadDb($dbConfigName = NULL) {
		$obj = new Shanty_Mysql_Connection($dbConfigName);
		return $obj->readDb();
	}
}
