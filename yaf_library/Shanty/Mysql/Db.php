<?php

class Shanty_Mysql_Db {
	protected $db;
	protected $dbConfigName = null;

	public function __construct($dbConfigName = NULL) {
		$this->dbConfigName = $dbConfigName;
	}

	public function init() {
		if ($this->db == null) {
			$this->db = Shanty_Mysql_Connection::getReadDb($this->dbConfigName);
		}
	}

	public function select() {
		$this->init();
		return $this->db->select();
	}

	public function fetchAll($select) {
		$this->init();
		if ($select instanceof Zend_Db_Select) {
			return $this->db->fetchAll($select->__toString()); 
		}

		return $this->db->fetchAll($select);
	}

	public function fetchRow($select) {
		$this->init();
		if ($select instanceof Zend_Db_Select) {
			return $this->db->fetchRow($select->__toString());
		}

		return $this->db->fetchRow($select);
	}

	public function fetchOne($select) {
		$this->init();
		if ($select instanceof Zend_Db_Select) {
			return $this->db->fetchOne($select->__toString());
		}

		return $this->db->fetchOne($select);
	}
	
	public function getDb(){
		$this->init();
		return $this->db;
	}
}
