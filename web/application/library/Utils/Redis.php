<?php
/**
 * redis处理的二次封装
 * 
 */
class Utils_Redis{

    private $_redis;
    
    private $_config;
    
    public function __construct() {        
        $this->_config = Yaf_Application::app()->getConfig()->get("redis");
        if(empty($this->_config)){
            throw new Exception("email config can not be empty!");
        }
		if ($this->_config['servers']['host'] == '')  {
			$this->_config['servers']['host'] = '127.0.0.1'; 
		} 
        if ($this->_config['servers']['port'] == ''){
			$this->_config['servers']['port'] = '6379';  		
		} 
		$this->_redis = new Redis();  
		$this->_redis->connect($this->_config['servers']['host'], $this->_config['servers']['port']);  
		//$this->_redis->pconnect($this->_config['servers']['host'], $this->_config['servers']['port']);
		$this->_redis->auth($this->_config['servers']['password']);
	}

	/** 
     * 设置值 
     * @param string $key KEY名称 
     * @param string|array $value 获取得到的数据 
     * @param int $timeOut 时间 
     */  
	public function set($key, $value, $timeOut = 0) {   
		$value = json_encode($value, TRUE);  
		$retRes = $this->_redis->set($key, $value);  
		if ($timeOut > 0) $this->_redis->setTimeout($key, $timeOut);  
		return $retRes;  
	}

    /**
     * 设置db
     * @param int $deIndex db值
     */
    public function select($deIndex) {
        $deIndex = (int)$deIndex;
        $retRes = $this->_redis->select($deIndex);
        return $retRes;
    }

    /**
	* 通过KEY获取数据 
	* @param string $key KEY名称 
	*/  
	public function get($key) {  
		$result = $this->_redis->get($key);  
		return json_decode($result, TRUE);  
	}  
    
	/** 
     * 删除一条数据 
     * @param string $key KEY名称 
     */  
    public function delete($key) {  
        return $this->_redis->delete($key);  
    }  
      
    /** 
     * 清空数据 
     */  
    public function flushAll() {  
        return $this->_redis->flushAll();  
    }  

	/** 
     * 数据入队列 
     * @param string $key KEY名称 
     * @param string|array $value 获取得到的数据 
     * @param bool $right 是否从右边开始入 
     */  
    public function push($key, $value ,$right = true) {  
        $value = json_encode($value);  
        return $right ? $this->_redis->rPush($key, $value) : $this->redis->lPush($key, $value);  
    }  
      
    /** 
     * 数据出队列 
     * @param string $key KEY名称 
     * @param bool $left 是否从左边开始出数据 
     */  
    public function pop($key , $left = true) {  
        $val = $left ? $this->_redis->lPop($key) : $this->redis->rPop($key);  
        return json_decode($val);  
    }  

	/** 
     * 数据自增 
     * @param string $key KEY名称 
     */  
    public function increment($key) {  
        return $this->_redis->incr($key);  
    }  
  
    /** 
     * 数据自减 
     * @param string $key KEY名称 
     */  
    public function decrement($key) {  
        return $this->_redis->decr($key);  
    }  

	/**
	 * setTranction   
	 * 执行事务添加值
	 * @param string $key 
	 * @param int $count 
	 * @access public
	 * @return boolean
	 */
	public function setTranction($key, $count){
		$this->_redis->watch($key);
		return $this->_redis->multi()->set($key, $count)->exec();
	}

	/**
	 * getTranction   
	 * 执行事务获取
	 * @param string $key 
	 * @access public
	 * @return boolean
	 */
	public function getTranction($key){
		$this->_redis->watch($key);
		return $this->_redis->multi()->get($key)->exec();
	}
	
	/**
	 * 指定步长增加
	 * @param string $key
	 * @param int $count
	 * @return int
	 */
	public function incrBy($key, $count) {
		return $this->_redis->incrBy($key, $count);
	}
	
	/**
	 * 指定步长减少
	 * @param string $key
	 * @param int $count
	 * @return int
	 */
	public function decrBy($key, $count) {
		return $this->_redis->decrBy($key, $count);
	}

	/**
	 * decrByTranction   
	 * 执行事务减去某个值
	 * @param string $key 
	 * @param int $count 
	 * @access public
	 * @return array
	 */
	public function decrByTranction($key, $count){
		$this->_redis->watch($key);
		return $this->_redis->multi()->decrBy($key, $count)->exec();
	}

	/**
	 * incrByTranction 
	 * 执行事务，增加某个值 
	 * @param string $key 
	 * @param int $count 
	 * @access public
	 * @return array
	 */
	public function incrByTranction($key, $count){
		$this->_redis->watch($key);
		return $this->_redis->multi()->incrBy($key, $count)->exec();
	}
        
        /**
	 * incrByFloat 
	 * 执行事务，增加某个值，float型运算
	 * @param string $key 
	 * @param int $count 
	 * @access public
	 * @return array
	 */
	public function incrByFloat($key, $count){
		$this->_redis->watch($key);
		return $this->_redis->multi()->incrByFloat($key, $count)->exec();
	}

	/** 
     * key是否存在，存在返回ture 
     * @param string $key KEY名称 
     */  
    public function exists($key) {  
        return $this->_redis->exists($key);  
    }  

	/**
	 * setnx 
	 * 当没有值时设置一个值
	 * @param string $key 
	 * @param mixed $value 
	 *
	 */
	public function setnx($key, $value){
		return $this->_redis->setnx($key, $value);
	}	
	
	/** 
     * 返回redis对象 
     * redis有非常多的操作方法，我们只封装了一部分 
     * 拿着这个对象就可以直接调用redis自身方法 
     */  
    public function redis() {  
        return $this->_redis;  
    }  

}
