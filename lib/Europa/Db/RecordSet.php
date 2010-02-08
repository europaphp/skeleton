<?php

class Europa_Db_RecordSet implements Iterator, ArrayAccess
{
	protected
		/**
		 * The current position in the set.
		 *
		 * @var int
		 */
		$index = 0,

		/**
		 * The executed statement containing the records.
		 *
		 * @var PDOStatement
		 */
		$stmt,

		/**
		 * The class that will be instantiated and filled.
		 *
		 * @var string
		 */
		$class;

	/**
	 * Constructs the record set and sets required properties.
	 *
	 * @param PDOStatement $stmt The executed statement to use.
	 * @param string $class The class to instantiate and fill with a record.
	 * @param Europa_Db_RecordSet
	 */
	public function __construct(PDOStatement $stmt, $class = null)
	{
		$this->stmt  = $stmt;
		$this->class = $class;
	}

	/**
	 * When the final instance of the record set is cleaned up,
	 * then close the cursor on the statement.
	 * 
	 * @return void
	 */
	public function __destruct()
	{
		$this->stmt->closeCursor();
	}

	/**
	 * Returns the number of records in the current set.
	 *
	 * @return int
	 */
	public function count()
	{
		return $this->stmt->rowCount();
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
		$row   = $this->stmt->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_ABS, $index);

		if ($class) {
			return new $class($row);
		}
		
		return $row;
	}

	/**
	 * Fills the record at the given index with the value. The value can be a
	 * record instance, object or array. Keep in mind, that this auto-commits
	 * the changes to the database.
	 *
	 * @param mixed $index
	 * @param Europa_Db_Record|object|array $value
	 * @return void
	 */
	public function offsetSet($index, $value)
	{
		if (!$value instanceof $this->class || !is_array($value) || !is_object($value)) {
			return;
		}

		$this->offsetGet($index)->fill($value)->save();
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
		--$this->index;
	}

	/**
	 * Whether or not it is still valid to iterate through the set.
	 *
	 * @return bool
	 */
	public function valid()
	{
		return $this->count() && $this->index < ($this->count() - 1);
	}
}