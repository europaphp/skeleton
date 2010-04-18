<?php

/**
 * Designed to make arrays and objects interchangeable and their properties
 * and elements overloadable via special getter and setter methods.
 * 
 * 
 */
class Europa_Array implements Iterator, ArrayAccess, Countable
{
	/**
	 * The current position in the array.
	 * 
	 * @var int
	 */
	protected $_index = 0;
	
	/**
	 * The array.
	 * 
	 * @var array
	 */
	protected $_array = array();
	
	/**
	 * Setter that automatically maps the name to a magic setter method
	 * prefixed with "_set" which will perform any actions required for
	 * setting the property.
	 * 
	 * @param string $name  The name of the property.
	 * @param mixed  $value The value of the property.
	 * 
	 * @return void
	 */
	public function __set($name, $value)
	{
		$this->offsetSet($name, $value);
		
		return $this;
	}

	/**
	 * Getter that automatically maps the name to a magic getter method
	 * prefixed with "_get" which will perform any actions required for
	 * getting the property.
	 * 
	 * @param string $name  The name of the property.
	 * 
	 * @return mixed
	 */
	public function __get($name)
	{
		return $this->offsetGet($name);
	}
	
	/**
	 * Counts the array elements.
	 * 
	 * @return int
	 */
	public function count()
	{
		return count($this->_array);
	}
	
	/**
	 * Returns the current item.
	 * 
	 * @return mixed
	 */
	public function current()
	{
		return current($this->_array);
	}
	
	/**
	 * Returns the key of the current element.
	 * 
	 * @return mixed
	 */
	public function key()
	{
		return key($this->_array);
	}
	
	/**
	 * sets the next element in the array.
	 * 
	 * @return Europa_Array
	 */
	public function next()
	{
		next($this->_array);
		++$this->_index;
		
		return $this;
	}
	
	/**
	 * Rewinds the array.
	 * 
	 * @return Europa_Array
	 */
	public function rewind()
	{
		reset($this->_array);
		$this->_index = 0;
		
		return $this;
	}

	/**
	 * Returns whether or not the array can still be iterated over.
	 * 
	 * @return bool
	 */
	public function valid()
	{
		return $this->_index >= 0 && $this->_index < $this->count();
	}

	/**
	 * Returns whether or not the offset exists in the array.
	 * 
	 * @return bool
	 */
	public function offsetExists($offset)
	{
		return isset($this->_array[$offset]);
	}

	/**
	 * Returns the element at the given offset.
	 * 
	 * @param mixed $offset
	 * 
	 * @return mixed
	 */
	public function offsetGet($offset)
	{
		$method = Europa_String::create($offset)->camelCase();
		$method = '_get' . $method;
		
		if (method_exists($this, $method)) {
			return $this->$method();
		}
		
		return isset($this->_array[$offset])
		     ? $this->_array[$offset]
		     : false;
	}

	/**
	 * Sets the element at the given offset.
	 * 
	 * @param mixed $offset
	 * @param mixed $value
	 * 
	 * @return Europa_Array
	 */
	public function offsetSet($offset, $value)
	{
		if ($offset) {
			$method = Europa_String::create($offset)->camelCase();
			$method = '_set' . $method;
		
			if (method_exists($this, $method)) {
				$this->$method($value);
			} else {
				$this->_array[$offset] = $value;
			}
		} else {
			$this->_array[] = $value;
		}

		return $this;
	}

	/**
	 * Unsets the element at the given offset.
	 * 
	 * @param mixed $offset
	 * 
	 * @return Europa_Array
	 */
	public function offsetUnset($offset)
	{
		unset($this->_array[$offset]);
		
		return $this;
	}
}