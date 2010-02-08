<?php

/**
 * @package    Europa
 * @subpackage Db
 * @subpackage Statement
 */

/**
 * Provides methods for building an SQL query and only that. This class provides no other functionality
 * but can easily be extended to do so.
 */
class Europa_Db_Statement
{
	const
		/**
		 * The statement type of select.
		 * 
		 * @var string
		 */
		TYPE_SELECT = 'select',
		
		/**
		 * The statement type of insert.
		 * 
		 * @var string
		 */
		TYPE_INSERT = 'insert',
		
		/**
		 * The statement type of update.
		 * 
		 * @var string
		 */
		TYPE_UPDATE = 'update',
		
		/**
		 * The statement type of delete.
		 * 
		 * @var string
		 */
		TYPE_DELETE = 'delete',
		
		/**
		 * The condition concatenator 'AND'.
		 * 
		 * @var string
		 */
		CONDITION_CONCAT_AND = 'AND',
		
		/**
		 * The condition concatenator 'OR'.
		 * 
		 * @var string
		 */
		CONDITION_CONCAT_OR = 'OR';
	
	protected
		/**
		 * The type of query that is being constructed. Specified using one of the TYPE_ constants.
		 * 
		 * @var string
		 */
		$_type = null,
		
		/**
		 * The columns that will be used
		 * 
		 * @var array
		 */
		$_columns = array('*'),
		
		/**
		 * The tables that will be used.
		 * 
		 * @var string
		 */
		$_tables = array(),
		
		/**
		 * A string of conditions that will be used.
		 * 
		 * @var string
		 */
		$_conditions = null,
		
		/**
		 * The columns that will be used to group the results
		 * 
		 * @var array
		 */
		$_groupBy = array(),
		
		/**
		 * The columns that will be used to order the results.
		 * 
		 * @var string
		 */
		$_orderBy = array(),
		
		/**
		 * The direction or ordering.
		 * 
		 * @var string
		 */
		$_orderDirection = 'ASC',
		
		/**
		 * An array that holds the data for the limit clause.
		 * 
		 * @var array
		 */
		$_limit = array(),
		
		/**
		 * Contains as list of parameters bound (in order) to this statement.
		 * 
		 * @var array
		 */
		$_params = array();
	
	
	
	/**
	 * When invoked, it builds a query from the data gathered and returns it.
	 * 
	 * @return string
	 */
	public function __toString()
	{
		$query      = '';
		$columns    = $this->_escape($this->_columns);
		$tables     = $this->_escape($this->_tables);
		$columns    = $this->_escape($this->_columns);
		$conditions = $this->_conditions ? ' WHERE ' . $this->_conditions : '';
		$groupBy    = $this->_groupBy ? ' GROUP BY ' . implode(', ', $this->_escape($this->_groupBy)) . ' ' : '';
		$orderBy    = $this->_orderBy ? ' ORDER BY ' . implode(', ', $this->_escape($this->_orderBy)) . ' ' . $this->_orderDirection . ' ' : '';
		$limit      = '';
		
		// an array to hold a matching number of question marks as parameters bound
		$values = array();
		
		// prepare values
		for ($i = 0; $i < count($this->_params); $i++) {
			$values[] = '?';
		}
		
		// set the limit clause
		if ($this->_limit) {
			$limit = ' LIMIT ';
			
			if (isset($this->_limit[1])) {
				$limit .= $this->_limit[0] . ', ' . $this->_limit[1];
			} else {
				$limit .= $this->_limit[0];
			}
		}
		
		// build the query based on statement type for the most common types of queries
		switch ($this->_type) {
			// insert
			case self::TYPE_INSERT:
				$query = 'INSERT INTO '
				       . implode(', ', $tables)
				       . ' (' . implode(', ', $columns) . ')'
				       . ' VALUES (' . implode(', ', $values) . ')';
			break;
			
			// update
			case self::TYPE_UPDATE:
				$sets  = array();
				$query = 'UPDATE ' . implode(', ', $tables) . ' SET ';
				
				foreach ($columns as $col) 
					$sets[] = $col . ' = ?';
				
				$query .= implode(', ', $sets)
				       .  $conditions
				       .  $orderBy
				       .  $limit;
			break;
			
			// delete
			case self::TYPE_DELETE:
				$query = 'DELETE FROM '
				       . implode(', ', $tables)
				       . $conditions;
			break;
			
			// select, default
			case self::TYPE_SELECT:
			default:
				$query = 'SELECT '
				       . implode(', ', $columns)
				       . ' FROM '
				       . implode(', ', $tables)
				       . $conditions
				       . $groupBy
				       . $orderBy
				       . $limit;
		}
		
		// return a statement for preparation
		return $query;
	}
	
	
	
	/**
	 * Creates a new statement object and returns it.
	 * 
	 * @return Europa_Db_Statement
	 */
	static public function create()
	{
		return new self;
	}
	
	/**
	 * Retrieves the parameters that were set on this statement.
	 * 
	 * @return array Parameters bound to this statement.
	 */
	public function getParams()
	{
		return $this->_params;
	}
	
	/**
	 * Returns whether the statement is of the given type.
	 * 
	 * @param string $type Use Europa_Db_Statement::TYPE_[type].
	 * 
	 * @return boolean
	 */
	public function isType($type)
	{
		return $this->_type === $type;
	}
	
	/**
	 * Returns whether the statement is fetchable or not.
	 * 
	 * @return boolean
	 */
	public function isFetchable()
	{
		return $this->_type === self::TYPE_SELECT;
	}
	
	/**
	 * Sets the statement type.
	 * 
	 * @param $type One of the types. Use Europa_Db_Statement::TYPE_[desired type].
	 * 
	 * @return Europa_Db_Statement
	 */
	public function setType($type)
	{
		$this->_type = $type;
		
		return $this;
	}
	
	/**
	 * Sets the tables which should be used.
	 * 
	 * @param string|array $columns The tables to use.
	 * 
	 * @return Europa_Db_Statement
	 */
	public function setTables($tables)
	{
		// merge the existing tables with the new ones
		$this->_tables = array_merge($this->_tables, (array) $tables);
		
		return $this;
	}
	
	/**
	 * Sets the type of query to Europa_Db_Statement::TYPE_SELECT.
	 * 
	 * @param string|array $columns The columns to use.
	 * 
	 * @return Europa_Db_Statement
	 */
	public function select($columns = '*')
	{
		$this->_type    = self::TYPE_SELECT;
		$this->_columns = (array) $columns;
		
		return $this;
	}

	/**
	 * Sets the type of query to Europa_Db_Statement::TYPE_INSERT.
	 * 
	 * @param array $columns Key/value pairs of the $columnName => $columnValue.
	 * 
	 * @return Europa_Db_Statement
	 */
	public function insert($keyVals)
	{
		$this->_type    = self::TYPE_INSERT;
		$this->_columns = array_keys($keyVals);
		$this->_params  = array_values($keyVals);
		
		return $this;
	}

	/**
	 * Sets the type of query to Europa_Db_Statement::TYPE_UPDATE.
	 * 
	 * @param array $columns Key/value pairs of the $columnName => $columnValue.
	 * 
	 * @return Europa_Db_Statement
	 */
	public function update($keyVals)
	{
		$this->_type    = self::TYPE_UPDATE;
		$this->_columns = array_keys($keyVals);
		$this->_params  = array_values($keyVals);
		
		return $this;
	}

	/**
	 * Sets the type of query to Europa_Db_Statement::TYPE_DELETE.
	 * 
	 * @return Europa_Db_Statement
	 */
	public function delete()
	{
		$this->_type = self::TYPE_DELETE;
		
		return $this;
	}

	/**
	 * Adds one or more conditions. Allows the use of a concatenator. Concatenator defaults to Europa_Db_Statement::CONDITION_CONCAT_AND.
	 * 
	 * @param string|array $conditions Conditions to use.
	 * @param string       $andOr      The 'AND' or 'OR' condition concatenator. Should be specified using
	 * 
	 * @return Europa_Db_Statement
	 */
	public function where($conditions, $params = null, $andOr = null)
	{
		$this->_addParams($params);
		
		// normalize
		$andOr = $andOr === self::CONDITION_CONCAT_AND ? self::CONDITION_CONCAT_AND : self::CONDITION_CONCAT_OR;
		
		// all conditions are added to existing ones so if there is already conditions
		$pre = $this->_conditions ? ' ' . $andOr . ' ' : '';
		
		// add to the existing conditions
		$this->_conditions .= $pre . implode(' ' . $andOr . ' ', (array) $conditions);
		
		return $this;
	}

	/**
	 * Adds one or more conditions using the 'AND' concatenator.
	 * 
	 * @param string|array $conditions Conditions to use.
	 * 
	 * @return Europa_Db_Statement
	 */
	public function andWhere($conditions, $params = null)
	{
		$this->_addParams($params);
		
		return $this->where($conditions, null, self::CONDITION_CONCAT_AND);
	}

	/**
	 * Adds one or more conditions.
	 * 
	 * Adds one or more conditions using the 'OR' concatenator.
	 * 
	 * @return Europa_Db_Statement
	 */
	public function orWhere($conditions, $params = null)
	{
		$this->_addParams($params);
		
		return $this->where($conditions, null, self::CONDITION_CONCAT_OR);
	}

	/**
	 * Sets the group by clause.
	 * 
	 * @param string|array $columns A string or array of columns to use.
	 * 
	 * @return Europa_Db_Statement
	 */
	public function groupBy($columns)
	{
		// merge order by columns
		$this->_groupBy = array_merge($this->_groupBy, $columns);
		
		return $this;
	}

	/**
	 * Sets the order by clause.
	 * 
	 * @param string|array $columns A string or array of columns to use.
	 * 
	 * @return Europa_Db_Statement
	 */
	public function orderBy($columns, $orderDirection = 'ASC')
	{
		$this->_orderBy        = array_merge($this->_orderBy, (array) $columns);
		$this->_orderDirection = strtolower($orderDirection) == 'desc' ? 'DESC' : 'ASC';
		
		return $this;
	}

	/**
	 * Sets the limit clause.
	 * 
	 * @param string|integer $numPerPage The number of items per page.
	 * @param string|integer $page       The page to retrieve.
	 * 
	 * @return Europa_Db_Statement
	 */
	public function limit($numPerPage = 10, $page = null)
	{
		// force a number, otherwise 10
		if (!is_numeric($numPerPage)) {
			$numPerPage = 10;
		}
		
		// force a number, otherwise null
		if (!is_numeric($page)) {
			$page = null;
		}
		
		$this->_limit = $page
			? array(($numPerPage * $page) - $numPerPage, $numPerPage)
			: array($numPerPage);
		
		return $this;
	}
	
	
	
	/**
	 * Escapes the passed in value(s).
	 * 
	 * @param string|array The value(s) to escape.
	 * 
	 * @return string|array
	 */
	protected function _escape($these)
	{
		$these = (array) $these;
		
		foreach ($these as &$v) {
			$v = trim($v);
			
			if ($v !== '*') {
				$v = '`' . str_replace('`', '', $v) . '`';
			}
		}
		
		return $these;
	}
	
	/**
	 * Adds parameters to the statement for a prepared statement.
	 * 
	 * @param mixed $params The parameters to add. Can be scalar or an array.
	 * 
	 * @return Europa_Db_Statement
	 */
	protected function _addParams($params)
	{
		if ($params)
			foreach ((array) $params as $param)
				$this->_params[] = $param;
		
		return $this;
	}
}