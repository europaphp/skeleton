<?php

/**
 * @author Trey Shugart
 */

/**
 * @package Europa
 * @subpackage Db
 */
abstract class Europa_Db_Record implements ArrayAccess
{
	/**
	 * Contains relationships that have been accessed.
	 * 
	 * @var $relationships
	 */
	private $_relationships = array();
	
	/**
	 * Returns an array of elements. The key is the name of
	 * the column and the value is it's default value.
	 * 
	 * @return array
	 */
	abstract public function getColumns();
	
	/**
	 * Returns the name of the primary key.
	 * 
	 * @return string
	 */
	abstract public function getPrimaryKeyName();
	
	/**
	 * Returns the name of the foreign key.
	 * 
	 * @return string
	 */
	abstract public function getForeignKeyName();
	
	/**
	 * Returns the table name for the current record instance.
	 * 
	 * @return string
	 */
	abstract public function getTableName();
	
	/**
	 * Returns an instance of Europa_Db to be used with this
	 * record instance.
	 * 
	 * @return Europa_Db
	 */
	abstract protected function getDb();
	
	/**
	 * Constructs a record and fills any values that are passed.
	 * 
	 * Properties on the class are cascaded from defaults, to database values
	 * (if set to load) then to the specific values passed in.
	 * 
	 * @param array $keyVals Key/value pairs to fill the object with.
	 * @return Europa_Db_Record
	 */
	public function __construct($keyVals = null)
	{
		$pk = $this->getPrimaryKeyName();
		
		// assume a primary key is passed
		$this->$pk = $keyVals;
		
		// check priamry key validity
		if ($this->hasPrimaryKey()) {
			$this->load();
		} else {
			// initialize defaults
			$this->fill($this->getColumns());
			
			// override with passed values
			$this->fill($keyVals);
		}
	}
	
	/**
	 * Used to retrieve either specific column based on the valid
	 * columns for the table or a specific relationship based on the
	 * conventions set for relationships.
	 * 
	 * In order for relationships to work, they must be returned in an
	 * array defined by Europa_Db_Record->getRelationships().
	 * 
	 * @param string $name The name of the variable being retrieved.
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
	 * @param mixed $value
	 * @return void
	 */
	public function __set($name, $value)
	{
		// first check columns
		if ($this->hasColumn($name)) {
			$this->$name = $value;
		// then relationships
		} elseif ($this->hasRelationship($name)) {
			$this->setRelationship($name, $value);
		}
	}
	
	/**
	 * Checks to see if a particular property is set.
	 * 
	 * @return bool
	 */
	public function __isset($name)
	{
		return isset($this->$name) || isset($this->_relationships[$name]);
	}
	
	/**
	 * Unsets a particular property.
	 * 
	 * @return bool
	 */
	public function __unset($name)
	{
		if ($this->hasColumn($name)) {
			unset($this->$name);
		} elseif ($this->hasRelationship($name)) {
			unset($this->_relationships[$name]);
		}
	}
	
	/**
	 * Used for ArrayAccess. Checks for the existence of a particular offset.
	 * 
	 * @return bool
	 */
	public function offsetExists($index)
	{
		return isset($this->$index);
	}
	
	/**
	 * Used for ArrayAccess. Gets a particular offset.
	 * 
	 * @return mixed
	 */
	public function offsetGet($index)
	{
		return $this->$index;
	}
	
	/**
	 * Used for ArrayAccess. Sets a particular offset value.
	 * 
	 * @return void
	 */
	public function offsetSet($index, $value)
	{
		$this->$index = $value;
	}
	
	/**
	 * Used for ArrayAccess. Unsets a particular offset.
	 * 
	 * @return void
	 */
	public function offsetUnset($index)
	{
		unset($this->$index);
	}
	
	/**
	 * Fills all columns and relationships. Utilizes __get and __set and
	 * effectively sets valid columns or relationships. If a valid key/value
	 * pair isn't passed, then it just returns.
	 * 
	 * @param object|array $keyVals
	 * @return Europa_Db_Record
	 */
	final public function fill($keyVals)
	{
		// check for valid key/value pairs
		if (!is_array($keyVals) && !is_object($keyVals)) {
			return $this;
		}
		
		// fill columns
		foreach ($keyVals as $name => $value) {
			$this->$name = $value;
		}
		
		return $this;
	}
	
	/**
	 * Loads the field values from the database based based on the passed
	 * primary key.
	 * 
	 * @return bool Whether the load was successful or not.
	 */
	final public function load($pkValue = null)
	{
		// the primary key name
		$pk = $this->getPrimaryKeyName();
		
		// check to see if the primary key is set yet, if not, set it to
		// the passed value
		if (!$this->hasPrimaryKey()) {
			$this->$pk = $pkValue;
		}
		
		// if it's still not set, then return false
		if (!$this->hasPrimaryKey()) {
			return false;
		}
		
		// fetch the row
		$select = $this->find()->where(':id = ' . $pk, array(':id' => $this->$pk));
		
		// if successful, fill the current object with it's values
		if ($select[0]) {
			$this->fill($select[0]);
			
			return true;
		}
		
		return false;
	}
	
	/**
	 * Automates saving for a record.
	 * 
	 * Intelligently decides whether to update or to insert a record based on 
	 * if all of the primary keys are set. If all are set, it updates, if not 
	 * all are set then an insert takes place. Returns true on success or false 
	 * on failure.
	 * 
	 * @return boolean
	 */
	public function save()
	{
		$db     = $this->getDb();
		$pk     = $this->getPrimaryKeyName();
		$insert = false;
		
		// will hold save-able values
		$saveableValues = array();
		
		// build save-able values
		foreach ($this->getColumns() as $name => $defValue) {
			$saveableValues[$name] = $this->$name;
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
			// build the UPDATE query
			$query = 'UPDATE ' . $this->getTableName() . ' SET';
			$parts = array();
			
			// build the SET values
			foreach ($saveableValues as $name => $value) {
				// add the set name/value to the query
				$parts[] = $name . ' = :' . $name;
				
				// build the parameters
				$params[':' . $name]  = $value;
			}
			
			$query .= ' ' . implode(', ', $parts);
			$query .= '	WHERE ' . $pk . ' = :id';
		} else {
			/*
			 * Unset the primary key if it is not properly set. We check here 
			 * since some models might require a custom primary key to be 
			 * generated before it is inserted.
			 */
			if (!$this->hasPrimaryKey()) {
				unset($this->$pk);
			}
			
			$insert = true;
			$cols   = array_keys($saveableValues);
			
			// build the INSERT query
			$query = 'INSERT INTO ' . $this->getTableName()
			       . '(' . implode(', ', $cols) . ')'
			       . 'VALUES'
			       . '(:' . implode(', :', $cols) . ')';
			
			// build the parameters
			foreach ($saveableValues as $name => $value) {
				$params[':' . $name]  = $value;
			}
		}
		
		// execute
		$stmt   = $db->prepare($query);
		$result = $stmt->execute($params);
		
		// if we have inserted, set the last insert id
		if ($result && $insert) {
			$this->$pk = $db->lastInsertId();
		}
		
		// true || false
		return $result;
	}
	
	/**
	 * Deletes the current record and returns true if successful or false on 
	 * failure.
	 * 
	 * @return boolean
	 */
	public function delete()
	{
		$db = $this->getDb();
		
		// if the primary key is set then we can delete it
		if ($this->hasPrimaryKey()) {
			$pk   = $this->getPrimaryKeyName();
			$stmt = $db->prepare(
				'
					DELETE 
					FROM
						' . $this->getTableName() . '
					WHERE
						' . $pk . ' = :id
				;'
			);
			
			if ($stmt->execute(array(':id' => $this->$pk))) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Counts the total number of items in the table.
	 * 
	 * @return int
	 */
	public function count()
	{
		return $this->find()->count();
	}
	
	/**
	 * Fetches a record set of records.
	 * 
	 * @return Europa_Db_RecordSet|false
	 */
	public function find(Europa_Db_Select $select = null)
	{
		if (!$select) {
			$select = $this->getDb()->select('*');
		}
		
		$select->from($this->getTableName());
		$select->setClass(get_class($this));
		
		return $select;
	}
	
	/**
	 * Returns whether the particular record exists or not base on the the
	 * primary key that is set.
	 * 
	 * @return boolean
	 */
	final public function exists()
	{
		$pk   = $this->getPrimaryKeyName();
		$stmt = $this->getDb()->prepare(
			'SELECT 
				* 
			FROM 
				' . $this->getTableName() . '
			WHERE
				' . $pk . ' = :id'
		);
		
		$stmt->execute(array(':id' => $this->$pk));
		
		$exists = (bool) $stmt->fetch();
		
		unset($stmt);
		
		return $exists;
	}
	
	/**
	 * Returns whether the specified column exists.
	 * 
	 * Column names are specified as the array's keys. This can be overridden to
	 * provide any specialized validation if necessary.
	 * 
	 * @return boolean
	 */
	final public function hasColumn($name)
	{
		return array_key_exists($name, $this->getColumns());
	}
	
	/**
	 * Checks to see whether the primary key is valid.
	 * 
	 * This can be overridden to do custom validation on the primary key. This 
	 * is suitable in situations where a primary key may not be an integer for 
	 * some tables. However, the primary key must still be unique and as a 
	 * result, only requires one. It is not a design limitation or flaw to 
	 * support only one primary key, but a choice in using Europa_Db_Record.
	 * 
	 * @return bool
	 */
	public function hasPrimaryKey()
	{
		$pk = $this->getPrimaryKeyName();
		
		return isset($this->$pk) && is_numeric($this->$pk) && (int) $this->$pk > 0;
	}
	
	/**
	 * Returns the names of all valid relationships for the given record.
	 * 
	 * @return array
	 */
	protected function getRelationships()
	{
		return array();
	}
	
	/**
	 * Returns whether or not the current record has the specified relationship.
	 * 
	 * If a string key is set, $name will be compared to that. Otherwise only
	 * the value will be compared. If the relationship exists, the array value
	 * is returned.
	 * 
	 * @return bool|string
	 */
	final protected function hasRelationship($name)
	{
		return array_key_exists($name, $this->getRelationships());
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
	 * Since relationships are cached, they will not be retrieved/loaded more
	 * than once on the same object, but will not be cached for other objects.
	 * To re-load the data on the relationshp, just call the load method on it.
	 * 
	 * @param string $name The name of the relationship to retrieve. This is the
	 *                     name of the property that will be used to access the
	 *                     relationship and is formatted with
	 *                     Europa_Db_Record->getRelationshipClassName().
	 * @return Europa_Db_Record|Europa_Db_RecordSet
	 */
	final protected function getRelationship($name)
	{
		// if the current relationship is not valid, return false
		if (!$this->hasRelationship($name)) {
			return null;
		}
		
		// if the relationshp already exists, return it
		if (isset($this->_relationships[$name])) {
			return $this->_relationships[$name];
		}
		
		// get a list of relationships
		$rels = $this->getRelationships();
		
		// get relationship naming conventions
		$className = $rels[$name];
		
		// instantiate the relationship of the class
		$class = new $className;
		
		// key relationships
		$thisLocalKey    = $this->getPrimaryKeyName();
		$thisForeignKey  = $this->getForeignKeyName();
		$classLocalKey   = $class->getPrimaryKeyName();
		$classForeignKey = $class->getForeignKeyName();
		
		// check to see if the foreign key exists
		if (!$this->hasColumn($thisLocalKey) || !$class->hasColumn($classLocalKey)) {
			unset($class);
			
			return null;
		}
		
		/*
		 * A has-many relationship is defined by the relationship class having
		 * a column by the name of the current class' foreign key name.
		 */
		if ($class->hasColumn($thisForeignKey)) {
			$select = $class->getDb()
			                ->select()
			                ->from($class->getTableName()) 
			                ->where($thisForeignKey . ' = ?', $this->$thisLocalKey)
			                ->setClass($class);
			
			$this->_relationships[$name] = $select;
		// one to one relationship
		} elseif ($this->hasColumn($classForeignKey)) {
			// load the data if the key is set
			$class->load($this->$classForeignKey);
			
			// define the property
			$this->_relationships[$name] = $class;
		}
		
		// and return it
		return $this->_relationships[$name];
	}
	
	/**
	 * Sets a relationship to the passed in value. If the relationships does not
	 * exist, nothing happens.
	 * 
	 * @param string $name
	 * @param mixed $value
	 * @return Europa_Db_Record
	 */
	final protected function setRelationship($name, $value)
	{
		$rel = $this->getRelationship($name);
		
		if (!$rel) {
			return $this;
		}
		
		// the relationship will only be set/filled if it is valid record
		if ($value instanceof Europa_Db_Record) {
			$value = (array) $value;
			
			// check to see if the relationship value is a has-many
			if (
				isset($value[0])
				&& (
					is_array($value[0])
					|| is_object($value[0])
					|| $value[0] instanceof Europa_Db_Record
				)
			) {
				$records = array();
				
				foreach ($values[0] as $filler) {
					$records[] = $this->getRelationship($name)->fill($filler);
				}
				
				$this->_relationships[$name] = new Europa_Db_RecordSet($records);
			} else {
				$rel->fill($value);
			}
		} elseif ($value instanceof Europa_Db_RecordSet) {
			$this->$name = $value;
		}
		
		return $this;
	}
}