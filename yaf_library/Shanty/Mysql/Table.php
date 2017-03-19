<?php
class Shanty_Mysql_Table extends Zend_Db_Table_Abstract implements IteratorAggregate, ArrayAccess  {
	protected $_data;
	protected $_cleanData;
	
	protected $_requirements = array();
	protected $_filters = array();
	protected $_validators = array();
	
    protected $_dbConfigName;
	protected $_primary;
	
	protected $_write_db = false;
	
	public function getIterator() {
		return new ArrayIterator((array) $this->_data);
	}
	
	public function offsetExists ($offset) {
		return isset($this->_data[$offset]);
	}
	public function offsetGet ($offset)  {
		return isset($this->_data[$offset]) ? $this->_data[$offset] : null;
	}
	public function offsetSet ($offset,  $value) {
		$this->_data[$offset] = $value;
	}
	public function offsetUnset ($offset) {
		unset($this->_data[$offset]);
	}
	
	public function __construct($db = null, $write_db = false) {
		if ($db == null) {
			$db = Shanty_Mysql_Connection::getReadDb($this->_dbConfigName);
		}
		
		$this->_write_db = $write_db;
		
		$this->_requirements = static::makeRequirementsTidy($this->_requirements);
		if (!is_array($this->_primary)) {
			$this->_primary = array($this->_primary);
		}
		
		parent::__construct(array("db"=>$db));
	}
	
	public function __get($columnName) {
		return $this->_data[$columnName];
	}
	
	public function __set($columnName, $value) {
		$this->setProperty($columnName, $value);
	}

	public  function getWriteDb(){
		$db = Shanty_Mysql_Connection::getWriteDb($this->_dbConfigName);
		return $db;
	}

	public function find() {
		if (func_num_args() < 1) {
			throw new Exception("function find(\$id) \$id must be needed!");
		}
		
		
		$args = func_get_args();
		$id = $args[0];
		$row = parent::find($id)->current();
		if (!empty($row)) {
			$this->_data = $row->toArray();
			$this->_cleanData = $this->_data;
		}
		
		return $this;
	}
	
	public function save() {
		$exportData = $this->export();
		if (!empty($this->_cleanData)) {
			$config['stored'] = true;
			$config['data'] = $this->_cleanData;
			$config['table'] = $this;
			$row = new Zend_Db_Table_Row($config);
			$row->setFromArray($exportData);
			
		}
		else {
			$row = $this->createRow($exportData);
		}
		//读写分离、主从选择
		$table = new $this(Shanty_Mysql_Connection::getWriteDb($this->_dbConfigName), true);
		$row->setTable($table);
		return $row->save();
	}
	
	public function export($skipRequired = false) {
		$exportData = $this->_cleanData;

		foreach ($this->_data as $property => $value) {
			// If property has been deleted
			if (is_null($value)) {
				unset($exportData[$property]);
				continue;
			}
			
			if (!in_array($property, $this->_primary) && (!array_key_exists($property, $this->_requirements))) {
				throw new Shanty_Mongo_Exception("Property '{$property}' is not allowed.");
			}
			
			$exportData[$property] = $value;
		}
		
		if (!$skipRequired) {

			// make sure required properties are not empty
			$requiredProperties = $this->getPropertiesWithRequirement('Required');
			foreach ($requiredProperties as $property) {
				if (!isset($exportData[$property]) || (is_array($exportData[$property]) && empty($exportData[$property]))) {
					throw new Shanty_Mongo_Exception("Property '{$property}' must not be null.");
				}
			}
		}

		return $exportData;
	}
	
	public function getPropertiesWithRequirement($requirement)
	{
		$properties = array();
	
		foreach ($this->_requirements as $property => $requirementList) {
			if (strpos($property, '.') > 0) continue;
	
			if (array_key_exists($requirement, $requirementList)) {
				$properties[] = $property;
			}
		}
	
		return $properties;
	}
	
	
	public function setProperty($columnName, $value)
	{
	
		$validators = $this->getValidators($columnName);
	
		// Throw exception if value is not valid
		if (!is_null($value) && !$validators->isValid($value)) {
			$messages=$validators->getMessages();
			foreach($messages as $k=>$v){
				$messages[$k]=$columnName.' is invalid. '.$v;
			}
			throw new Shanty_Mongo_Exception(implode($messages, "\n"));
		}
	
		// Unset property
		if (is_null($value)) {
			$this->_data[$columnName] = null;
			return;
		}
	
		// Filter value
		$value = $this->getFilters($columnName)->filter($value);
	
		$this->_data[$columnName] = $value;
	}
	
	public function getValidators($property)
	{
		$this->loadRequirements($property);
		return $this->_validators[$property];
	}
	
	public function getFilters($property) {
		$this->loadRequirements($property);
		return $this->_filters[$property];
	}
	
	public function loadRequirements($property)
	{
		if (isset($this->_validators[$property]) || isset($this->_filters[$property])) {
			return true;
		}
	
		$validators = new Zend_Validate;
		$filters = new Zend_Filter;
	
		if (!isset($this->_requirements[$property])) {
			$this->_filters[$property] = $filters;
			$this->_validators[$property] = $validators;
			return false;
		}
		
		//var_dump($this->_requirements[$property]);
		foreach ($this->_requirements[$property] as $requirement => $options) {
			$req = Shanty_Mysql::retrieveRequirement($requirement, $options);
			if ($req instanceof Zend_Validate_Interface) {
				$validators->addValidator($req);
			} else if ($req instanceof Zend_Filter_Interface) {
				$filters->addFilter($req);
			}
		}
		$this->_filters[$property] = $filters;
		$this->_validators[$property] = $validators;
		return false;
	}
    
    public function getDbObject(){
        return new Shanty_Mysql_Db($this->_dbConfigName);
    }
	
	public static function makeRequirementsTidy(array $requirements) {
		foreach ($requirements as $property => $requirementList) {
			if (!is_array($requirementList)) {
				$requirements[$property] = array($requirementList);
			}
	
			$newRequirementList = array();
			foreach ($requirements[$property] as $key => $requirement) {
				if (is_numeric($key)) $newRequirementList[$requirement] = null;
				else $newRequirementList[$key] = $requirement;
			}
				
			$requirements[$property] = $newRequirementList;
		}
			
		return $requirements;
	}

	public function fetchPage($where = null, $order = null, $count = 20, $offset = null) {
		if (!($where instanceof Zend_Db_Table_Select)) {
            $select = $this->select();

            if ($where !== null) {
                $this->_where($select, $where);
            }

        } else {
            $select = $where;
        }
		$select->from($this->info(self::NAME), array(new Zend_Db_Expr('COUNT(1) as count')));
		$data = $this->_fetch($select);
		$total = $data[0]["count"];

        $list = parent::fetchAll($where, $order, $count, $offset);
		$list = $list->toArray();

		return array("count"=>$total, "list"=>$list);
	}
	
	public function insert(array $data) {
		if (!$this->_write_db) {
			//trigger_error("禁止直接使用Shanty_Mysql_Table::insert方法; 写入请使用save方法。 除非你已了解这么做的影响和后果", E_USER_WARNING);
		}
		
		$db = Shanty_Mysql_Connection::getWriteDb($this->_dbConfigName);
		self::_setupAdapter($db);
	
		return parent::insert($data);
	}
	
	public function update(array $data, $where) {
		if (!$this->_write_db) {
			//trigger_error("禁止直接使用Shanty_Mysql_Table::update方法; 更新请使用save方法。 除非你已了解这么做的影响和后果", E_USER_WARNING);
		}
		
		$db = Shanty_Mysql_Connection::getWriteDb($this->_dbConfigName);
		self::_setupAdapter($db);
		
		return parent::update($data, $where);
	}
	
	public function delete($where) {
		$db = Shanty_Mysql_Connection::getWriteDb($this->_dbConfigName);
		self::_setupAdapter($db);
		
		return parent::delete($where);
	}
	
	public function getAdapter() {
		//trigger_error("禁止直接调用getAdapter获取db对象执行insert、update、query方法，除非你已了解这么做的影响和后果。如需关闭该提示，请关闭全局错误通知。或关闭E_USER_NOTICE", E_USER_NOTICE);
		
		return parent::getAdapter();
	}
}
