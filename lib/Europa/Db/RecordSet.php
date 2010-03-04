<?php

class Europa_Db_RecordSet extends Europa_Db_Select implements Iterator, ArrayAccess
{
	protected $select;
	
	/**
	 * The current position in the set.
	 *
	 * @var int
	 */
	protected $index = 0;

	/**
	 * The class that will be instantiated and filled.
	 *
	 * @var string
	 */
	protected $class;
	
	/**
	 * The cached array.
	 * 
	 * @var array
	 */
	protected $cache;
	
	/**
	 * The number of items to keep cached.
	 * 
	 * @var int
	 */
	protected $cacheLimit = 100;
	
	public function __construct(Europa_Db_Select $select)
	{
		$this->select = $select;
	}

	/**
	 * Returns the number of records in the current set.
	 *
	 * @return int
	 */
	public function count()
	{
		return 10;
	}

	/**
	 * Returns the record that resides at the current index.
	 *
	 * @return Europa_Db_Record
	 */
	public function current()
	{
		return $this->offsetGet($this->index);
	}

	/**
	 * Returns the key of the current record.
	 *
	 * @return mixed
	 */
	public function key()
	{
		return $this->index;
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
	 * @return Europa_Db_Record
	 */
	public function offsetGet($index)
	{
		$class = $this->class;
		
		// if the cache can't be found, attempt to get it
		if (!isset($this->cache[$index + 1])) {
			$select = $this->select->limit($index + 1, $index + 1 + $this->cacheLimit);
			die(var_dump($select->prepare()));
			$this->cache = $select->prepare()->fetchAll(PDO::FETCH_ASSOC);
			
			die(var_dump($this->cache));
		}
		
		return $this->cache[$index + 1];
	}

	/**
	 * Add description.
	 *
	 * @param mixed $index
	 * @param Europa_Db_Record|object|array $value
	 * @return void
	 */
	public function offsetSet($index, $value)
	{
		
	}

	/**
	 * Currently doesn't do anything since you can't remove something from the
	 * PDOStatement result set.
	 *
	 * @param mixed $index
	 * @return void
	 */
	public function offsetUnset($index)
	{
		
	}

	/**
	 * Moves forward one record.
	 *
	 * @return void
	 */
	public function next()
	{
		++$this->index;
	}

	/**
	 * Moves back one record.
	 *
	 * @return void
	 */
	public function rewind()
	{
		// clear the cache
		$this->cache = array();
		
		--$this->index;
	}

	/**
	 * Whether or not it is still valid to iterate through the set.
	 *
	 * @return bool
	 */
	public function valid()
	{
		return $this->index < $this->count();
	}
	
	public function setCacheLimit($limit)
	{
		$this->cacheLimit = $limit;
		
		return $this;
	}
	
	public function setClass($className)
	{
		$this->class = $className;
		
		return $this;
	}
}