<?php

/**
 * 
 */

/**
 * 
 */
class Europa_Db_Select
{
	const
		WHERE_AND = 'AND',
		
		WHERE_OR = 'OR',
		
		JOIN_INNER = 'INNER',
		
		JOIN_LEFT = 'LEFT',
		
		ORDER_ASC = 'ASC',
		
		ORDER_DESC = 'DESC';
	
	protected
		/**
		 * A reference to the connected Europa_Db.
		 * 
		 * @var Europa_Db
		 */
		$db,
		
		/**
		 * Holds column information.
		 * 
		 * @var array
		 */
		$columns = array(),
		
		/**
		 * Holds table information.
		 * 
		 * @var array
		 */
		$tables = array(),
		
		/**
		 * Holds where clause information.
		 * 
		 * @var array
		 */
		$clauses = array(),
		
		/**
		 * Holds all join information.
		 * 
		 * @var array
		 */
		$joins = array(),
		
		/**
		 * Contains grouping information.
		 * 
		 * @var array
		 */
		$groups = array(),
		
		/**
		 * Contains ordering information.
		 * 
		 * @var array
		 */
		$orders = array(),
		
		/**
		 * Holds the direction that the result should be ordered, if ordering.
		 * 
		 * @var string
		 */
		$orderDirection = 'ASC',
		
		/**
		 * Contains parameters to be bound, if any.
		 * 
		 * @var array
		 */
		$params = array();
	
	public function __construct(Europa_Db $db, $columns = array())
	{
		// set the database reference
		$this->db = $db;
		
		// add any columns if they were passed
		$this->columns($columns);
	}
	
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
	
	public function columns($columns)
	{
		foreach ((array) $columns as $name => $ref) {
			if (!is_string($name)) {
				$name = $ref;
				$ref  = null;
			}
			
			$this->columns[] = array($name, $ref);
		}
		
		return $this;
	}
	
	public function from($tables)
	{
		foreach ((array) $tables as $name => $ref) {
			if (!is_string($name)) {
				$name = $ref;
				$ref  = null;
			}
			
			$this->tables[] = array($name, $ref);
		}
		
		return $this;
	}
	
	public function join($join, $params = array(), $type = 'INNER')
	{
		$this->joins[] = array($type, $join);
		
		$this->setParams($params);
		
		return $this;
	}
	
	public function innerJoin($join, $params = array())
	{
		return $this->join($join, $params, self::JOIN_INNER);
	}
	
	public function leftJoin($join, $params = array())
	{
		return $this->join($join, $params, self::JOIN_LEFT);
	}
	
	public function where($clause, $params = array(), $concat = self::WHERE_AND)
	{
		$this->clauses[] = array($concat, $clause);
		
		$this->setParams($params);
		
		return $this;
	}
	
	public function orWhere($clause, $params = array())
	{
		// just send it to the where method with an OR concatenator
		return $this->where($clause, $params, self::WHERE_OR);
	}
	
	public function groupBy($tables)
	{
		$this->groups = array_merge($this->groups, (array) $tables);
		
		return $this;
	}
	
	public function orderBy($tables, $direction = 'ASC')
	{
		$this->orders         = array_merge($this->orders, (array) $tables);
		$this->orderDirection = $direction;
		
		return $this;
	}
	
	public function limit($start, $end = null)
	{
		// resetting the limit
		if ($start === null || $start === false) {
			$this->limit = array();
			
			return $this;
		}
		
		// if no end is supplied, reverse the arguments
		if (!$end) {
			$end   = $start;
			$start = 0;
		}
		
		// set the limit information
		$this->limit = array($start, $end);
		
		return $this;
	}
	
	public function getPartColumns()
	{
		$columns = array();
		foreach ($this->columns as $k => $col) {
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
	
	public function getPartFrom()
	{
		$tables = array();
		foreach ($this->tables as $k => $table) {
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
	
	public function getPartJoin()
	{
		$joins = array();
		
		foreach ($this->joins as $k => $join) {
			$type = $join[0];
			$join = $join[1];
			
			// build the join
			$joins[] = $type . ' JOIN ' . $join;
		}
		
		return implode(PHP_EOL, $joins);
	}
	
	public function getPartWhere()
	{
		if (!$this->clauses) {
			return null;
		}
		
		$query = '';
		
		foreach ($this->clauses as $k => $clause) {
			if ($k != 0) {
				$query .= ' ' . $clause[0];
			}
			
			$query .= ' ' . $clause[1];
		}
		
		return 'WHERE ' . $query;
	}
	
	public function getPartGroup()
	{
		if (!$this->groups) {
			return null;
		}
		
		return 'GROUP BY ' . implode(', ', $this->groups);
	}
	
	public function getPartOrder()
	{
		if (!$this->orders) {
			return null;
		}
		
		// normalize
		$this->orderDirection = strtoupper($this->orderDirection);
		
		// force correct syntax
		if ($this->orderDirection != self::ORDER_ASC && $this->orderDirection != self::ORDER_DESC) {
			$this->orderDirection = self::ORDER_ASC;
		}
		
		return 'ORDER BY ' . implode(', ', $this->orders) . ' ' . $this->orderDirection;
	}
	
	public function getPartLimit()
	{
		if (!$this->limit) {
			return null;
		}
		
		return 'LIMIT ' . implode(', ', $this->limit);
	}
	
	public function setParams($params)
	{
		$this->params = array_merge($this->params, (array) $params);
	}
	
	public function getParams()
	{
		return $this->params;
	}
	
	/**
	 * Allows the select statement to be executed directly.
	 * 
	 * @return PDOStatement|false
	 */
	public function execute()
	{
		return $this->db->query($this);
	}
	
	/**
	 * Allows direct fecthing of the first returned row.
	 * 
	 * @return Europa_Db_Record|false
	 */
	public function fetchOne()
	{
		return $this->db->fetchOne($this);
	}
	
	/**
	 * Allows direct fetching of all of the results.
	 * 
	 * @return Europa_Db_RecordSet|false
	 */
	public function fetchAll()
	{
		return $this->db->fetchAll($this);
	}
}