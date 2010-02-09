<?php

/**
 * @package    Europa
 * @subpackage Db
 * @subpackage Record
 */

/**
 * 
 */
abstract class Europa_Db_Record implements ArrayAccess
{
	private
		/**
		 * Contains relationships that have been accessed.
		 * 
		 * @var $relationships
		 */
		$relationships = array();
	
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
	 * @param boolean $load Whether or not to auto-load the new instance.
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
		
		// handle arrays
		if (is_array($keyVals) && isset($keyVals[$pk])) {
			$this->$pk = $keyVals[$pk];
		}
		
		// handle objects
		if (is_object($keyVals) && isset($keyVals->$pk)) {
			$this->$pk = $keyVals->$pk;
		}
		
		// if loading, load then override with passed values
		if ($load && $this->isPrimaryKeySet()) {
			$this->load($this->$pk);
		}
		
		// now re-fill to override any loaded values with more specific ones
		if ($keyVals) {
			$this->fill($keyVals);
		}
	}
	
	/**
	 * Used to retrieve either specific column based on the valid
	 * columns for the table or a specific relationship based on the
	 * conventions set for relationships.
	 * 
	 * In order for relationships to work, they must be returned in an
	 * array defined by Europa_Db_Record->_getRelationships().
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
		// then relationships
		if ($this->hasRelationship($name)) {
			$this->setRelationship($name, $value);
			
			return;
		}
		
		$this->$name = $value;
	}
	
	/**
	 * Checks to see if a particular property is set.
	 * 
	 * @return bool
	 */
	public function __isset($name)
	{
		return isset($this->$name) || isset($this->relationships[$name]);
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
			
			return;
		}
		
		if ($this->hasRelationship($name)) {
			unset($this->relationships[$name]);
			
			return;
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
	public function offsetGet($index) {
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
	 * Fills the current record with values, overriding any existing ones.
	 * Columns that aren't specified will not be set. If a specified column
	 * doesn't exist, it will not be set.
	 * 
	 * @return Europa_Db_Record
	 */
	final public function fill($keyVals)
	{
		// if none were passed, just return
		if (!$keyVals) {
			return $this;
		}
		
		// only fill valid columns
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
		
		// set the primary key
		$this->$pk = $pkValue;
		
		// if the primary key isn't properly set, then we can't load
		if (!$this->isPrimaryKeySet()) {
			return false;
		}
		
		// fetch the row
		$res = $this->getDb()->fetchOne('
			SELECT 
				* 
			FROM 
				' . $this->getTableName() . '
			WHERE
				' . $pk . ' = :id
		', array(
			':id'    => $this->$pk
		));
		
		// if successful, fill the current object with it's values
		if ($res) {
			$this->fill($res);
			
			return true;
		}
		
		return false;
	}
	
	/**
	 * Automates saving for a record.
	 * 
	 * Intelligently deciedes whether to update or to insert a record based on 
	 * if all of the primary keys are set. If all are set, it updates, if not 
	 * all are set then an insert takes place. Returns true on succes or false 
	 * on failure.
	 * 
	 * @return boolean
	 */
	public function save($cascade = true)
	{
		$db     = $this->getDb();
		$pk     = $this->getPrimaryKeyName();
		$insert = false;
		
		// handle single relationship cascading
		if ($cascade) {
			foreach ($this->relationships as $rel) {
				if ($rel instanceof Europa_Db_Record) {
					$relLocalKey   = $rel->getPrimaryKeyName();
					$relForeignKey = $rel->getForeignKeyName();
					
					if (!$rel->$relLocalKey) {
						$rel->$relLocalKey = $this->$relForeignKey;
					}
					
					// and save it
					if ($rel->save($cascade)) {
						$this->$relForeignKey = $rel->$relLocalKey;
					}
				}
			}
		}
		
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
			$query = '
				UPDATE
					' . $this->getTableName() . '
				SET
			';
			
			$parts = array();
			
			// build the SET values
			foreach ($saveableValues as $name => $value) {
				// add the set name/value to the query
				$parts[] = $name . ' = :' . $name;
				
				// build the parameters
				$params[':' . $name]  = $value;
			}
			
			$query .= ' ' . implode(', ', $parts);
			$query .= '
				WHERE
					' . $pk . ' = :id
			';
		} else {
			/*
			 * Unset the primary key if it is not properly set. We check here 
			 * since some models might require a custom primary key to be 
			 * generated before it is inserted.
			 */
			if (!$this->isPrimaryKeySet()) {
				unset($this->$pk);
			}
			
			$insert = true;
			$cols   = array_keys($saveableValues);
			
			// build the INSERT query
			$query  = '
				INSERT
				INTO
					' . $this->getTableName() . '
				(' . implode(', ', $cols) . ')
				VALUES
				(:' . implode(', :', $cols) . ')
			';
			
			// build the parameters
			foreach ($saveableValues as $name => $value) {
				$params[':' . $name]  = $value;
			}
		}
		
		// execute
		$res = $db->query($query, $params) ? true : false;
		
		// if we have inserted, set the last insert id
		if ($res && $insert) {
			$this->$pk = $db->lastInsertId();
		}
		
		// handle has-many relationship cascading
		if ($cascade) {
			foreach ($this->relationships as $rel) {
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
					$rel->save($cascade);
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
	 * @param bool $cascade Whether or not to recursively delete relationships.
	 * @return boolean
	 */
	public function delete($cascade = true)
	{
		$db = $this->getDb();
		
		// if the primary key is set then we can delete it
		if ($this->isPrimaryKeySet()) {
			$pk    = $this->getPrimaryKeyName();
			$query = '
				DELETE 
				FROM
					' . $this->getTableName() . '
				WHERE
					' . $pk . ' = :id
			;';
			$params = array(
				':id'    => $this->$pk
			);
			
			// if successfully deleted
			if ($db->query($query, $params)) {
				// if cascading, delete all relationships
				if ($cascade) {
					foreach ($this->relationships as $relationshp) {
						$relationship->delete($cascade);
					}
				}
				
				// return true
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
		return $this->fetchAll()->count();
	}
	
	/**
	 * Like Europa_Db_Record::fetchAll(), but only fetches a single record.
	 * 
	 * @return mixed
	 */
	public function fetchOne(Europa_Db_Select $select = null)
	{
		// execute the query
		$res = $this->fetchAll();

		// return it if it was successful
		if ($res) {
			return $res->offsetGet(0);
		}
		
		return false;
	}
	
	/**
	 * Fetches a record set of records.
	 * 
	 * @return Europa_Db_RecordSet|false
	 */
	public function fetchAll(Europa_Db_Select $select = null)
	{
		$res = $this->getDb()->query('
			SELECT 
				* 
			FROM 
				' . $this->getTableName() . '
		;');
		
		// return a record set while populating records for each row
		return $res ? new Europa_Db_RecordSet($res, get_class($this)) : false;
	}
	
	/**
	 * Fetches a specific page number and orders it accordingly.
	 * 
	 * @return Europa_Db_RecordSet|false
	 */
	public function fetchPage($page = 1, $numPerPage = 10, $orderBy = null, $orderDirection = 'ASC', Europa_Db_Select $select = null)
	{
		if (!$select) {
			$select = $this->getDb()->select();
		}
		
		$table = $this->getTableName();
		
		// build/add-on-to the existing select query
		$select->columns($table . '.*')
		       ->from($table)
		       ->orderBy($table . '.' . $orderBy, $orderDirection)
		       ->limit(($numPerPage * $page) - $numPerPage, $numPerPage);
		
		// execute to be passed to Europa_Db_RecordSet if successful
		$res = $select->execute();
		
		// return
		return $res ? new Europa_Db_RecordSet($res, get_class($this)) : false;
	}
	
	/**
	 * Returns whether the particular record exists or not base on the the
	 * primary key that is set.
	 * 
	 * @return boolean
	 */
	final public function exists()
	{
		$pk     = $this->getPrimaryKeyName();
		$query  = '
			SELECT 
				* 
			FROM 
				' . $this->getTableName() . '
			WHERE
				' . $pk . ' = :id
		';
		
		$res = $this->getDb()->fetchAll($query, array(
			':id' => $this->$pk
		));
		
		return (bool) $res->count();
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
	public function isPrimaryKeySet()
	{
		$pk = $this->getPrimaryKeyName();
		
		return isset($this->$pk) && (int) $this->$pk > 0;
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
		if (isset($this->relationships[$name])) {
			return $this->relationships[$name];
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
			$this->relationships[$name] = $class->getDb()->fetchAll('
				SELECT
					*
				FROM
					' . $this->getTableName() . '
				WHERE
					' . $thisForeignKey . ' = :id
			;', array(
				':id' => $class->$thisForeignKey
			));
		} else {
			// load the data if the key is set
			if ($this->hasColumn($classForeignKey) && $this->$classForeignKey) {
				$class->load($this->$classForeignKey);
			}
			
			// define the property
			$this->relationships[$name] = $class;
		}
		
		// and return it
		return $this->relationships[$name];
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
			return;
		}
		
		// the relationship will only be set/filled if it is valid record
		if ($value instanceof Europa_Db_Record) {
			$value = (array) $value;
			
			// check to see if the relationship value is a has-many
			if (isset($value[0]) && (is_array($value[0]) || is_object($value[0] || $value[0] instanceof Europa_Db_Record))) {
				$records = array();
				
				foreach ($values[0] as $filler) {
					$records[] = $this->getRelationship($name)->fill($filler);
				}
				
				$this->relationships[$name] = new Europa_Db_RecordSet($records);
			} else {
				$rel->fill($value);
			}
		} elseif ($value instanceof Europa_Db_RecordSet) {
			$this->$name = $value;
		}
		
		return $this;
	}
}