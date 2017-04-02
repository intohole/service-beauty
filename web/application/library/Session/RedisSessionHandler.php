<?php
class Session_RedisSessionHandler {
	public $ttl = 1200;	//session过期时间20分钟
	protected $db;
	protected $prefix;

	public function __construct($db, $prefix='PHPSESSID:') {
		$this->db = $db;
		$this->prefix = $prefix;
	}

	/**
	 * session开始使用处理函数
	 * @param string $savePath
	 * @param string $sessionName
	 */
	public function open($savePath, $sessionName) {
	}

	/**
	 * session使用完之后收尾处理函数
	 */
	public function close() {
		$this->db = null;
		unset($this->db);
	}

	/**
	 * 从redis中获取session数据
	 * @param string $id
	 * @return string
	 */
	public function read($id) {
		$id = $this->prefix.$id;
		$data = $this->db->get($id);
		$this->db->expire($id, $this->ttl);	//session记录活动之后20分钟才过期
		return $data;
	}

	/**
	 * 向redis中写入session数据
	 * @param string $id
	 * @param string $data
	 */
	public function write($id, $data) {
		$id = $this->prefix . $id;
		$this->db->set($id, $data);
		$this->db->expire($id, $this->ttl);	//session记录活动之后20分钟才过期
	}

	/**
	 * 从redis中删除session数据
	 * @param string $id
	 */
	public function destroy($id) {
		$this->db->del($this->prefix.$id);
	}

	public function gc($maxLifetime) {
	}
}