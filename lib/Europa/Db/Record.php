<?php

/**
 * @package    Europa
 * @subpackage Db
 * @subpackage Record
 */

/**
 * 
 */
abstract class Europa_Db_Record
{
	private
		/**
		 * Contains a snapshot of the field values when the instance was first instantiated.
		 * 
		 * @var $_snapshot
		 */
		$_snapshot = array(),
		
		/**
		 * Contains relationships that have been accessed.
		 * 
		 * @var $_relationships
		 */
		$_relationships = array();
	
	
	
	/**
	 * Constructs a record and fills any values that are passed.
	 * 
	 * Properties on the class are cascaded from defaults, to database values
	 * (if set to load) then to the specific values passed in.
	 * 
	 * @param array   $keyVals An array of key/value pairs to fill the object 
	 *                         with.
	 * @param boolean $load    Whether or not to auto-call load() on the new 
	 *                         instance.
	 * 
	 * @return Europa_Db_Record
	 */
	public function __construct($keyVals = null, $load = true)
	{
		$pk = $this->getPrimaryKeyName();
		
		// if a scalar value is passed, assume it's a primary key
		if ($keyVals && !is_array($keyVals) && !is_object($keyVals)) {
			$keyVals = array($pk => $keyVals);
		}
		
		// defaults are ALWAYS set because db default values might be different
		// and this way it can be consistently set via the models
		$this->fill($this->getColumns());
		
		// if passed, set the primary key so it can be loaded
		if (isset($keyVals[$pk])) {
			$this->$pk = $keyVals[$pk];
		}
		
		// if loading, load then override with passed values
		if ($load && $this->isPrimaryKeySet()) {
			$this->load($this->$pk);
		}
		
		// now re-fill to override any loaded values with more specific ones
		$this->fill($keyVals);
	}
	
	/**
	 * Used to retrieve either specific column based on the valid
	 * columns for the table or a specific relationship based on the
	 * conventions set for relationships.
	 * 
	 * In order for relationships to work, they must be returned in an
	 * array defined by Europa_Db_Record->_getRelationshipNames().
	 * 
	 * @param string $name The name of the variable being retrieved.
	 * 
	 * @return mixed
	 */
	public function __get($name)
	{
		if (isset($this->$name)) {
			return $this->$name;
		}
		
		return $this->getRelationship($name);
	}
	
	/**
	 * Handles the setting of valid columns as well as relationships and
	 * valid relationship columns.
	 * 
	 * @param string $name
	 * @param mixed  $value
	 * 
	 * @return void
	 */
	public function __set($name, $value)
	{
		// if the column exists, set it's value
		if ($this->hasColumn($name)) {
			$this->$name = $value;
			
			return;
		}
		
		$this->setRelationship($name, $value);
	}
	
	/**
	 * Returns an array of $columnName => $columnValue key/value pairs for the 
	 * current record. 
	 * 
	 * This also transforms all relationships on the current instance. If a
	 * property is unset, then it won't be returned.
	 * 
	 * @param $recursive Whether or not to recursively transform all 
	 *                   relationships.
	 * 
	 * @return array
	 */
	public function toArray($recursive = true)
	{
		$arr = array();
		
		// the default value isn't used here, only the column index
		foreach ($this->getColumns() as $col => $defaultValue) {
			if (!array_key_exists($col, (array) $this)) {
				continue;
			}
			
			$arr[$col] = $this->$col;
		}
		
		if ($recursive) {
			foreach ($this->_relationships as $relationship) {
				$relationship->toArray($recursive);
			}
		}
		
		return $arr;
	}
	
	/**
	 * Fills the current record with values, overriding any existing ones.
	 * Columns that aren't specified will not be set. If a specified column
	 * doesn't exist, it will not be set.
	 * 
	 * @return Europa_Db_Record
	 */
	public function fill($keyVals, $cascade = true)
	{
		// normalize
		$keyVals = $keyVals instanceof Europa_Db_Record
		         ? $keyVals->toArray() 
		         : (array) $keyVals;
		
		// if none were passed, just return
		if (!$keyVals) {
			return $this;
		}
		
		// set each property, if one doesn't exist, a relationship will be
		// filled with the value
		foreach ($keyVals as $name => $value) {
			$this->$name = $value;
		}
		
		return $this;
	}
	
	/**
	 * Returns the object back to it's previous state.
	 * 
	 * @return Europa_Db_Record
	 */
	public function revertState()
	{
		foreach ($this->_snapshot as $name => $value) {
			$this->$name = $value;
		}
		
		return $this;
	}
	
	/**
	 * Commits the changes to the instance state so that it can be reverted if 
	 * necessary.
	 * 
	 * @return Europa_Db_Record
	 */
	public function commitState()
	{
		foreach ($this->_snapshot as $name => &$value) {
			$value = $this->$name;
		}
		
		return $this;
	}
	
	/**
	 * Loads the field values from the database based based on the passed
	 * primary key.
	 * 
	 * @return bool Whether the load was successful or not.
	 */
	public function load($pkValue = null)
	{
		$pk = $this->getPrimaryKeyName();
		
		$this->$pk = $pkValue;
		
		if (!$this->isPrimaryKeySet()) {
			return false;
		}
		
		$db   = $this->getDb();
		$stmt = $db->select()->setTables($this->getTableName());
		
		$stmt->andWhere($pk . ' = ?', $this->$pk);
		
		$res = $db->fetchOne($stmt);
		
		if ($res) {
			$this->fill($res);
			
			return true;
		}
		
		return false;
	}
	
	/**
	 * Automates saving for a record. Intelligently deciedes whether to update or to insert a record
	 * based on if all of the primary keys are set. If all are set, it updates, if not all are set
	 * then an insert takes place. Returns true on succes or false on failure.
	 * 
	 * @return boolean
	 */
	public function save($cascade = true)
	{
		$db     = $this->getDb();
		$pk     = $this->getPrimaryKeyName();
		$insert = false;
		
		// handle single relatinoship cascading
		if ($cascade) {
			foreach ($this->_relationships as $rel) {
				if ($rel instanceof Europa_Db_Record) {
					$relLocalKey   = $rel->getPrimaryKeyName();
					$relForeignKey = $rel->getForeignKeyName();
					
					if (!$rel->$relLocalKey) {
						$rel->$relLocalKey = $this->$relForeignKey;
					}
					
					// and save it
					if ($rel->save()) {
						$this->$relForeignKey = $rel->$relLocalKey;
					}
				}
			}
		}
		
		/*
		 * Check to see if the row exists before it is updated. This allows for 
		 * custom primary keys to be set before inserting and also allows the
		 * programmer to explicitly set the primary key of the record they want
		 * updated.
		 * 
		 * If the record doesn't exist based on it's primary key, then an insert
		 * is performed.
		 */
		if ($this->exists()) {
			$stmt = $db->update($this->toArray(false));
			
			$stmt->andWhere($pk . ' = ?', $this->$pk);
		} else {
			/*
			 * Unset the primary key if it is not properly set. We check here since
			 * some models might require a custom primary key to be generated
			 * before it is inserted.
			 */
			if (!$this->isPrimaryKeySet()) {
				unset($this->$pk);
			}
			
			$insert = true;
			$stmt   = $db->insert($this->toArray(false));
		}
		
		// update this table
		$stmt->setTables($this->getTableName());
		
		// execute
		$res = $db->query($stmt) ? true : false;
		
		// if we have inserted, set the last insert id
		if ($res && $insert) {
			$this->$pk = $db->lastInsertId();
		}
		
		// handle has-many relationship cascading
		if ($cascade) {
			foreach ($this->_relationships as $rel) {
				// must be an instance of Europa_Db_RecordSet
				if ($rel instanceof Europa_Db_RecordSet) {
					$thisForeignKey = $this->getForeignKeyName();
					
					/*
					 * Foreach record, make sure this primary key is set if it
					 * isn't already.
					 */
					foreach ($rel as $r) {
						// if the relationship key isn't set yet, set it
						if (!$r->$thisForeignKey) {
							$r->$thisForeignKey = $this->$pk;
						}
					}
					
					// save all relationships
					$rel->save($stmt);
				}
			}
		}
		
		// true || false
		return $res;
	}
	
	/**
	 * Deletes the current record and returns true if successful or false on 
	 * failure.
	 * 
	 * @return boolean
	 */
	public function delete($cascade = true)
	{
		$db = $this->getDb();
		
		if ($cascade) {
			foreach ($this->_relationships as $relationshp) {
				$relationship->delete($cascade);
			}
		}
		
		// if the primary key is set then we can delete it
		if ($this->isPrimaryKeySet()) {
			$stmt = $db->delete()->setTables($this->getTableName());
			$pk   = $this->getPrimaryKeyName();
			
			$stmt->andWhere($pk . ' = ?', $this->$pk)
			     ->limit(1);
			
			return $db->query($stmt) 
				? true 
				: false;
		}
		
		return false;
	}
	
	/**
	 * Returns a single value from the database and casts its value.
	 * 
	 * @param Europa_Db_Statement $stmt
	 * 
	 * @return mixed
	 */
	public function fetchValue(Europa_Db_Statement $stmt = null)
	{
		throw new Europa_Db_Exception(
			'Europa_Db_RecordSet->fetchValue() has'
			. 'not been implemented yet.'
		);
	}
	
	/**
	 * Like Europa_Db_Record::fetchAll(), but only fetches a single record.
	 * 
	 * @param Europa_Db_Statement $stmt The prepared statement to use for fetching.
	 * 
	 * @return mixed
	 */
	public function fetchOne(Europa_Db_Statement $stmt = null)
	{
		$res = $this->fetchAll($stmt);
		
		if ($res) {
			return $res->offsetGet(0);
		}
		
		return false;
	}
	
	/**
	 * Fetches an array of instances of the current table.
	 * 
	 * @param Europa_Db_Statement $stmt The prepared statement to use for 
	 *                                  fetching.
	 * 
	 * @return array|false
	 */
	public function fetchAll(Europa_Db_Statement $stmt = null)
	{
		// get the class, allows extending Europa_Db_Record
		$class = get_class($this);
		
		if (!$stmt) {
			$stmt = new Europa_Db_Statement;
		}
		
		// everything but the type and tables will be used
		$stmt->select()->setTables($this->getTableName());
		
		// will either return an array of results or false
		$res = $this->getDb()->fetchAll($stmt);
		
		// build a list of instances
		if ($res) {
			foreach ($res as &$row) {
				$row = new $class($row, false);
			}
		}
		
		// return a record set
		return $res ? new Europa_Db_RecordSet($res) : false;
	}
	
	/**
	 * Fetches a specific page number.
	 * 
	 * You can specify a particular number per page and as well as an instance 
	 * of Europa_Db_Statement to modify with the correct limit. No other changes
	 * will be made to the statement until it is passed to fetchAll to actually
	 * fetch the rows.
	 * 
	 * @return array|false
	 */
	public function fetchPage($page = 1, $numPerPage = 10, $orderBy = null, $groupBy = null, Europa_Db_Statement $stmt = null)
	{
		$stmt = $stmt
		      ? $stmt
		      : Europa_Db_Statement::create();
		
		$stmt->limit($numPerPage, $page);
		
		if ($orderBy) {
			$stmt->orderBy($orderBy);
		}
		
		if ($groupBy) {
			$stmt->orderBy($groupBy);
		}
		
		return $this->fetchAll($stmt);
	}
	
	/**
	 * Returns whether the particular record exists or not base on the the
	 * primary key that is set.
	 * 
	 * @return boolean
	 */
	public function exists()
	{
		if (!$this->isPrimaryKeySet()) {
			return false;
		}
		
		$pk   = $this->getPrimaryKeyName();
		$stmt = Europa_Db_Statement::create()
		        ->where($pk . ' = ?', $this->$pk);
		
		return (bool) $this->fetchOne($stmt);
	}

	/**
	 * Returns the Europa_Db instance that should be used for this table.
	 * 
	 * The database name is derived first from the default configuration and
	 * then by Europa_Db_Record->getDbName(). This can be overridden to
	 * specify a specific database instance for the record class to use.
	 * 
	 * The default naming convention for a class is:
	 * 
	 * [database name]_[table name]
	 * 
	 * @return Europa_Db
	 */
	public function getDb()
	{
		static $db;
		
		if (!isset($db)) {
			if (!Europa_Db::$defaultConfig['database']) {
				Europa_Db::$defaultConfig['database'] = $this->getDbName();
			}
			
			$db = new Europa_Db;
		}
		
		return $db;
	}
	
	/**
	 * Returns the database name.
	 * 
	 * The database name, by default, is generated from the first part
	 * of the class name; so up to where the first underscore is. So a
	 * class name of Database_Table will have the database name of
	 * "Database".
	 * 
	 * By default, this is only used in the default configuration and if
	 * the Europa_Db::$defaultConfig['database'] option isn't set. This
	 * allows the programmer to override this to specify a convention, or
	 * to use the configuration setting set a database name.
	 * 
	 * @return string
	 */
	public function getDbName()
	{
		static $dbName;
		
		if (!isset($dbName)) {
			$class  = get_class($this);
			$parts  = explode('_', $class);
			$dbName = $parts[0];
		}
		
		return $dbName;
	}
	
	/**
	 * Returns the table name.
	 * 
	 * By default this is parsed out of the class name.
	 * If the class name contains an underscore, the table name is the part
	 * after the first underscore. If no underscore is found, it is the whole
	 * class name.
	 * 
	 * @return string
	 */
	public function getTableName()
	{
		static $tableName;
		
		if (!isset($tableName)) {
			$class     = get_class($this);
			$parts     = explode('_', $class);
			$tableName = isset($parts[1])
			           ? $parts[1]
			           : $parts[0];
		}
		
		return $tableName;
	}
	
	/**
	 * Returns the columns in the table for this record as a numerically indexed
	 * array.
	 * 
	 * The column keys should be the column name and the values should be
	 * their default values. In the default implementation, the default
	 * values will always be null since the column names are sniffed without
	 * their default values.
	 * 
	 * @return array
	 */
	public function getColumns()
	{
		static $columns = array();
		
		if (!$columns) {
			$tempCols = $this->getDb()->fetchAll('SHOW COLUMNS FROM `' . $this->getTableName() . '`;');
			
			if ($tempCols) {
				foreach ($tempCols as $tempCol) {
					$columns[$tempCol['Field']] = null;
				}
			}
		}
		
		return $columns;
	}
	
	/**
	 * Returns whether the specified column exists.
	 * 
	 * Column names are specified as the array's keys. This can be
	 * overridden to provide any specialized validation if necessary.
	 * 
	 * @return boolean
	 */
	public function hasColumn($name)
	{
		return array_key_exists($name, $this->getColumns());
	}
	
	/**
	 * Returns the primary key name in the table for this record.
	 * 
	 * @param string $name The relationship name - if any - that is being
	 *                     accessed while retrieving the primary key.
	 * 
	 * @return string
	 */
	public function getPrimaryKeyName($name = null)
	{
		return 'id';
	}
	
	/**
	 * Returns the name of the foreign key for this table.
	 * 
	 * @param string $name The relationship name - if any - that is being
	 *                     accessed while retrieving the foreign key.
	 * 
	 * @return string
	 */
	public function getForeignKeyName($name = null)
	{
		return 'id' . $this->getTableName();
	}
	
	/**
	 * Checks to see whether the primary key is valid.
	 * 
	 * This can be overridden to do custom validation on the primary
	 * key. This is suitable in situations where a primary key may
	 * not be an integer for some tables. However, the primary key must
	 * still be unique and as a result, only requires one. It is not a
	 * design limitation or flaw to support only one primary key, but
	 * a choice in using Europa_Db_Record.
	 * 
	 * @return bool
	 */
	public function isPrimaryKeySet()
	{
		$pk = $this->getPrimaryKeyName();
		
		return isset($this->$pk) && (int) $this->$pk > 0;
	}
	
	/**
	 * Returns a relationship instance of either Europa_Db_Record for a has-one
	 * relationship or Europa_Db_RecordSet for a has-many relationship. 
	 * 
	 * If a relationship cannot be found an exception is thrown describing which
	 * relationship was unable to be found and why. Additionally, if no 
	 * relational keys are found, then an exception is thrown describing which 
	 * keys were not set.
	 * 
	 * @return Europa_Db_Record|Europa_Db_RecordSet
	 */
	public function getRelationship($name)
	{
		// if the relationshp already exists, return it
		if (isset($this->_relationships[$name])) {
			return $this->_relationships[$name];
		}
		
		// get relationship naming conventions
		$className = $this->_formatRelationshipClassName($name);
		
		// if the relationship class can't be found throw an exception.
		if (!Europa_Loader::loadClass($className)) {
			Europa_Db_Exception::trigger(
				'Either the relationship class <strong>'
				. $className
				. '</strong> could not be found for <strong>'
				. $name
				. '</strong> <em>or</em> <strong>'
				. get_class($this)
				. '->'
				. $name
				. '</strong> cannot be found for table <strong>'
				. $this->getTableName()
				. '</strong>.'
			);
		}
		
		// instantiate the relationship of the class
		$class           = new $className;
		$thisLocalKey    = $this->getPrimaryKeyName($name);
		$thisForeignKey  = $this->getForeignKeyName($name);
		$classLocalKey   = $class->getPrimaryKeyName($name);
		$classForeignKey = $class->getForeignKeyName($name);
		
		// check to see if the foreign key exists
		if (!$this->hasColumn($thisLocalKey) || !$class->hasColumn($classLocalKey)) {
			Europa_Db_Exception::trigger(
				'The relationship <strong>'
				. get_class($this) 
				. '->' 
				. $thisLocalKey
				. '</strong>'
				. ' could not be mapped to '
				. '<strong>'
				. $className
				. '->'
				. $classLocalKey
				. '</strong>.'
			);
		}
		
		/*
		 * A has-many relationship is defined by the relationship class having
		 * a column by the name of the current class' foreign key name.
		 */
		if ($class->hasColumn($thisForeignKey)) {
			$stmt = Europa_Db_Statement::create();
			
			// fetch all where this foreign key is in the relationship table
			$stmt->andWhere($thisForeignKey . ' = ?', $this->$thisLocalKey);
			
			$this->_relationship[$name] = $class->fetchAll();
		} else {
			// load the data if the key is set
			if ($this->hasColumn($classForeignKey) && $this->$classForeignKey) {
				$class->load($this->$classForeignKey);
			}
			
			// define the property
			$this->_relationships[$name] = $class;
		}
		
		// and return it
		return $this->_relationships[$name];
	}
	
	/**
	 * 
	 */
	public function setRelationship($name, $value)
	{
		$value = (array) $value;
		
		// check to see if the relationship value is a has-many
		if (isset($value[0]) && (is_array($value[0]) || is_object($value[0] || $value[0] instanceof Europa_Db_Record))) {
			$records = array();
			
			foreach ($values[0] as $filler) {
				$records = $this->getRelationship($name)->fill($filler);
			}
			
			$this->_relationships[$name] = new Europa_Db_RecordSet($records);
		} elseif ($value instanceof Europa_Db_Record) {
			$rel = $this->getRelationship($name);
			
			$rel->fill($value);
		}
	}
	
	/**
	 * Formats the passed name into a class name and returns it.
	 * 
	 * Europa_Loader::loadClass is called against this return value.
	 * If Europa_Loader::loadClass returns false, the relationship
	 * is not instantiated and an exception is thrown.
	 * 
	 * @return string
	 */
	protected function _formatRelationshipClassName($name)
	{
		$name      = ucfirst($name);
		$className = $this->getDbName()
		           . '_'
		           . $name;
		
		return $className;
	}
}