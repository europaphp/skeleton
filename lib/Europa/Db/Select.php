<?php

/**
 * @author Trey Shugart
 */

/**
 * A class that allows a select statement to be built and manipulated in an
 * object oriented manner. It can also be iterated over and accessed like an
 * array.
 * 
 * NOTE: The statement is implicitly executed when iterated over or accessed
 * like an array. Basically, whenever Europa_Db_Statement->offsetGet() is
 * invoked, then the statement is executed and cached until it needs to be
 * executed again.
 * 
 * @package Europa
 * @subpackage Db
 */
class Europa_Db_Select implements Iterator, ArrayAccess
{
	/**
	 * The constant that defines AND conditional concatenation.
	 */
	const WHERE_AND = 'AND';
	
	/**
	 * The constant that defines OR conditional concatenation.
	 */
	const WHERE_OR = 'OR';
	
	/**
	 * The join type INNER used when using Europa_Db_Select->join().
	 */
	const JOIN_INNER = 'INNER';
	
	/**
	 * The join type LEFT used when using Europa_Db_Select->join().
	 */
	const JOIN_LEFT = 'LEFT';
	
	/**
	 * The order direction ASC when using Europa_Db_Select->orderBy().
	 */
	const ORDER_ASC = 'ASC';
	
	/**
	 * The order direction DESC when using Europa_Db_Select->orderBy().
	 */
	const ORDER_DESC = 'DESC';
	
	/**
	 * A reference to the connected Europa_Db.
	 * 
	 * @var Europa_Db
	 */
	protected $_db;
	
	/**
	 * Holds column information.
	 * 
	 * @var array
	 */
	protected $_columns = array();
	
	/**
	 * Holds table information.
	 * 
	 * @var array
	 */
	protected $_tables = array();
	
	/**
	 * Holds where clause information.
	 * 
	 * @var array
	 */
	protected $_clauses = array();
	
	/**
	 * Holds all join information.
	 * 
	 * @var array
	 */
	protected $_joins = array();
	
	/**
	 * Contains grouping information.
	 * 
	 * @var array
	 */
	protected $_groups = array();
	
	/**
	 * Contains ordering information.
	 * 
	 * @var array
	 */
	protected $_orders = array();
	
	/**
	 * Holds the direction that the result should be ordered, if ordering.
	 * 
	 * @var string
	 */
	protected $_orderDirection = 'ASC';
	
	/**
	 * Contains information about the limit clause.
	 * 
	 * @var array
	 */
	protected $_limit = array();
	
	/**
	 * The current position in the set.
	 *
	 * @var int
	 */
	protected $_index = 0;

	/**
	 * The class that will be instantiated and filled.
	 *
	 * @var string
	 */
	protected $_class;
	
	/**
	 * The cached array.
	 * 
	 * @var array
	 */
	protected $_cache;
	
	/**
	 * The number of items to keep cached.
	 * 
	 * @var int
	 */
	protected $_cacheLimit = 1000;
	
	/**
	 * Contains parameters to be bound, if any.
	 * 
	 * @var array
	 */
	protected $_params = array();
	
	/**
	 * Constructs a new Europa_Db_Select object.
	 * 
	 * @return Europa_Db_Select
	 */
	public function __construct(PDO $db)
	{
		// set the database reference
		$this->_db = $db;
	}
	
	/**
	 * Parses out the statement into a query string.
	 * 
	 * @return string
	 */
	public function __toString()
	{
		$queries  = array();
		$queries[] = 'SELECT';
		$queries[] = $this->getPartColumns();
		$queries[] = $this->getPartFrom();
		$queries[] = $this->getPartJoin();
		$queries[] = $this->getPartWhere();
		$queries[] = $this->getPartGroup();
		$queries[] = $this->getPartOrder();
		$queries[] = $this->getPartLimit();
		
		// cleanup the query syntax
		foreach ($queries as $k => &$query) {
			// any extra whitespace
			$query = trim($query);
			
			// if it's an empty line, remove it
			if (!$query) {
				unset($queries[$k]);
			}
		}
		
		// join by lines
		return implode(PHP_EOL, $queries) . ';';
	}

	/**
	 * Sets which columns will be selected in the statement.
	 * 
	 * @param string|array $columns
	 * @return Europa_Db_Select
	 */
	public function columns($columns)
	{
		foreach ((array) $columns as $name => $ref) {
			if (!is_string($name)) {
				$name = $ref;
				$ref  = null;
			}
			
			$this->_columns[] = array($name, $ref);
		}
		
		return $this;
	}

	/**
	 * Adds the tables to be selected from to the statement.
	 * 
	 * @param string|array $tables
	 * @return Europa_Db_Select
	 */
	public function from($tables)
	{
		foreach ((array) $tables as $name => $ref) {
			if (!is_string($name)) {
				$name = $ref;
				$ref  = null;
			}
			
			$this->_tables[] = array($name, $ref);
		}
		
		return $this;
	}
	
	/**
	 * Adds a [*] JOIN to the statement.
	 * 
	 * @param string $join
	 * @param mixed $params
	 * @param string $type
	 * @return Europa_Db_Select
	 */
	public function join($join, $params = array(), $type = 'INNER')
	{
		$this->_joins[] = array($type, $join);
		
		$this->setParams($params);
		
		return $this;
	}
	
	/**
	 * Adds an INNER JOIN to the statement.
	 * 
	 * @param string $join
	 * @param mixed $params
	 */
	public function innerJoin($join, $params = array())
	{
		return $this->join($join, $params, self::JOIN_INNER);
	}
	
	/**
	 * Adds a LEFT JOIN to the statement.
	 * 
	 * @param string $join
	 * @param mixed $params
	 * @return Europa_Db_Select
	 */
	public function leftJoin($join, $params = array())
	{
		return $this->join($join, $params, self::JOIN_LEFT);
	}
	
	/**
	 * Adds a condition to the statement.
	 * 
	 * @param string $clause
	 * @param mixed $params
	 * @param string $concat
	 * @return Europa_Db_Select
	 */
	public function where($clause, $params = array(), $concat = self::WHERE_AND)
	{
		$this->_clauses[] = array($concat, $clause);
		
		$this->setParams($params);
		
		return $this;
	}
	
	/**
	 * Adds an OR clause to the statement.
	 * 
	 * @param string $clause
	 * @param mixed $params
	 * @return Europa_Db_Select
	 */
	public function orWhere($clause, $params = array())
	{
		// just send it to the where method with an OR concatenator
		return $this->where($clause, $params, self::WHERE_OR);
	}
	
	/**
	 * Specifies the grouping of the result set.
	 * 
	 * @param string|array $tables
	 * @return Europa_Db_Select
	 */
	public function groupBy($tables)
	{
		$this->_groups = array_merge($this->_groups, (array) $tables);
		
		return $this;
	}
	
	/**
	 * Orders the statement based on a table and a direction.
	 * 
	 * @param string|array $tables
	 * @param string $direction
	 * @return Europa_Db_Select
	 */
	public function orderBy($tables, $direction = 'ASC')
	{
		$this->_orders         = array_merge($this->_orders, (array) $tables);
		$this->_orderDirection = $direction;
		
		return $this;
	}

	/**
	 * Defines the limits imposed on the statement.
	 * 
	 * @return Europa_Db_Select
	 */
	public function limit($start, $end = null)
	{
		// resetting the limit
		if ($start === null || $start === false) {
			$this->_limit = array();
			
			return $this;
		}
		
		// if no end is supplied, reverse the arguments
		if (!$end) {
			$end   = $start;
			$start = 0;
		}
		
		// set the limit information
		$this->_limit = array($start, $end);
		
		return $this;
	}

	/**
	 * Effectively paginates the statement. Sets the page and number
	 * per page that should be returned in the result set.
	 * 
	 * @param int $page
	 * @param int $perPage
	 * @return Europa_Db_Select
	 */
	public function page($page = 1, $perPage = 10)
	{
		return $this->_limit(($perPage * $page) - $perPage, $perPage);
	}

	/**
	 * Returns the column part of the statement.
	 * 
	 * @return string
	 */
	public function getPartColumns()
	{
		$columns = array();
		foreach ($this->_columns as $k => $col) {
			$columns[$k] = $col[0];
			
			// if there is a column reference, use it
			if ($col[1]) {
				$columns[$k] .= ' AS ' . $col[1];
			}
		}
		$query = implode(', ', $columns);
		unset($columns);
		
		return $query;
	}

	/**
	 * Returns the FROM part of the statement.
	 * 
	 * @return string
	 */
	public function getPartFrom()
	{
		$tables = array();
		foreach ($this->_tables as $k => $table) {
			$tables[$k] = $table[0];
			
			// if there is a table reference, use it
			if ($table[1]) {
				$tables[$k] .= ' AS ' . $table[1];
			}
		}
		$query = implode(', ', $tables);
		unset($tables);
		
		return 'FROM ' . $query;
	}

	/**
	 * Returns the [*] JOIN part of the statement.
	 * 
	 * @return string
	 */
	public function getPartJoin()
	{
		$joins = array();
		
		foreach ($this->_joins as $k => $join) {
			$type = $join[0];
			$join = $join[1];
			
			// build the join
			$joins[] = $type . ' JOIN ' . $join;
		}
		
		return implode(PHP_EOL, $joins);
	}

	/**
	 * Returns the WHERE part of the statement.
	 * 
	 * @return string
	 */
	public function getPartWhere()
	{
		if (!$this->_clauses) {
			return null;
		}
		
		$query = '';
		
		foreach ($this->_clauses as $k => $clause) {
			if ($k != 0) {
				$query .= ' ' . $clause[0];
			}
			
			$query .= ' ' . $clause[1];
		}
		
		return 'WHERE ' . $query;
	}

	/**
	 * Returns the GROUP BY part of the statement.
	 * 
	 * @return string
	 */
	public function getPartGroup()
	{
		if (!$this->_groups) {
			return null;
		}
		
		return 'GROUP BY ' . implode(', ', $this->_groups);
	}

	/**
	 * Returns the ORDER BY part of the statement.
	 * 
	 * @return string
	 */
	public function getPartOrder()
	{
		if (!$this->_orders) {
			return null;
		}
		
		// normalize
		$this->_orderDirection = strtoupper($this->_orderDirection);
		
		// force correct syntax
		if ($this->_orderDirection != self::ORDER_ASC && $this->_orderDirection != self::ORDER_DESC) {
			$this->_orderDirection = self::ORDER_ASC;
		}
		
		return 'ORDER BY ' . implode(', ', $this->_orders) . ' ' . $this->_orderDirection;
	}

	/**
	 * Returns the LIMIT part of the statement.
	 * 
	 * @return string
	 */
	public function getPartLimit()
	{
		if (!$this->_limit) {
			return null;
		}
		
		return 'LIMIT ' . implode(', ', $this->_limit);
	}

	/**
	 * Retrieves the parameters that are bound to the statement.
	 * 
	 * @return array
	 */
	public function getParams()
	{
		return $this->_params;
	}

	/**
	 * Returns the number of records in the current set.
	 *
	 * @return int
	 */
	public function count()
	{
		$count = 0;
		$stmt  = $this->_db->prepare($this->__toString());
		
		// execute the prepared statement
		$stmt->execute($this->getParams());
		
		while ($stmt->fetch()) {
			++$count;
		}
		
		return $count;
	}

	/**
	 * Returns the record that resides at the current index.
	 *
	 * @return Europa_Db_Record
	 */
	public function current()
	{
		return $this->offsetGet($this->_index);
	}

	/**
	 * Returns the key of the current record.
	 *
	 * @return mixed
	 */
	public function key()
	{
		return $this->_index;
	}

	/**
	 * Returns whether the offset at the specified index exists or not.
	 *
	 * @param mixed $index
	 * @return bool
	 */
	public function offsetExists($index)
	{
		return (bool) $this->offsetGet($index);
	}

	/**
	 * Returns a specific record without affecting the position at which the
	 * index is currently at.
	 * 
	 * A cache is retrieved and kept at the set cache limit. The cache is
	 * kept at the limit too keep a balance between memory usage and the
	 * number of queries being run. This is so that for very large result sets
	 * say in excess of 10,000 records, you can set your limit to 1000. This
	 * means that a cache of 1,000 records is kept to iterate over. If the
	 * cache fails to contain the selected item, the query is re-executed
	 * with a different limit to check for more rows that would contain that
	 * item. If that item isn't found, then false is returned.
	 *
	 * @return array|Europa_Db_Record
	 */
	public function offsetGet($index)
	{
		$class = $this->_class;

		// if the cache can't be found, attempt to get it
		if (!isset($this->_cache[$index])) {
			// add a limit clause to the
			$select = clone $this;
			
			// add a new limit clause
			$select->limit($index, $index + $this->_cacheLimit);
			
			// prepare a new statement from the current state
			$stmt = $this->_db->prepare($select->__toString());
			
			// if it is unable to be executed, return false
			if (!$stmt || !$stmt->execute($this->getParams())) {
				return false;
			}
			
			// reset the cache
			$this->_cache = array();
			
			// the starting index for the cache
			$newIndex = $index;
			
			// build the new cache array
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$this->_cache[$newIndex] = $row;
				
				++$newIndex;
			}
			
			// close the cursor
			$stmt->closeCursor();
			
			// clear the new statement
			unset($stmt);
			
			// if the cache is still empty, return false
			if (!$this->_cache) {
				return false;
			}
		}

		if ($class) {
			return new $class($this->_cache[$index]);
		}
		
		return $this->_cache[$index];
	}

	/**
	 * Currently a result set is read only, therefore, setting an element at 
	 * a particular index DOES NOT work.
	 *
	 * @param mixed $index
	 * @param mixed $value
	 * @return void
	 */
	public function offsetSet($index, $value)
	{

	}

	/**
	 * Unsets an item from the cache if it exists.
	 *
	 * @param mixed $index
	 * @return void
	 */
	public function offsetUnset($index)
	{
		if (isset($this->_cache[$index])) {
			unset($this->_cache[$index]);
		}
	}

	/**
	 * Moves forward one record.
	 *
	 * @return void
	 */
	public function next()
	{
		++$this->_index;
	}

	/**
	 * Moves back one record.
	 *
	 * @return void
	 */
	public function rewind()
	{
		// clear the cache
		$this->_cache = array();

		// reset the index
		$this->_index = 0;
	}

	/**
	 * Whether or not it is still valid to iterate through the set.
	 *
	 * @return bool
	 */
	public function valid()
	{
		return $this->_index < $this->count();
	}

	/**
	 * Sets a limit on the size of the result set cache.
	 * 
	 * @param int $limit
	 * @return Europa_Db_Select
	 */
	public function setCacheLimit($limit)
	{
		$this->_cacheLimit = (int) $limit;

		return $this;
	}

	/**
	 * Sets the class that should be instantiated when retrieving
	 * an element from the result set.
	 * 
	 * @param string $className
	 * @return Europa_Db_Select
	 */
	public function setClass($className)
	{
		$this->_class = $className;

		return $this;
	}

	/**
	 * Sets a set of parameters. Used internally for adding parameters
	 * to the select statement.
	 * 
	 * @param array $params
	 * @return Europa_Db_Select;
	 */
	protected function setParams($params)
	{
		$this->_params = array_merge($this->_params, (array) $params);
		
		return $this;
	}
}