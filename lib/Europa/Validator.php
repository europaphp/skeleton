<?php

abstract class Europa_Validator implements Europa_Validator_Validatable, ArrayAccess, Iterator, Countable
{
	private $_messages = array();
	
	public function count()
	{
		return count($this->_messages);
	}
	
	public function current()
	{
		return current($this->_messages);
	}
	
	public function key()
	{
		return key($this->_messages);
	}
	
	public function next()
	{
		next($this->_messages);
		return $this;
	}
	
	public function rewind()
	{
		reset($this->_messages);
		return $this;
	}
	
	public function valid()
	{
		return $this->offsetExists($this->key());
	}
	
	public function offsetSet($index, $value)
	{
		$this->_add($index, $value);
		return $this;
	}
	
	public function offsetGet($index)
	{
		if ($this->offsetExists($index)) {
			return $this->_messages[$index];
		}
		return null;
	}
	
	public function offsetExists($index)
	{
		return isset($this->_messages[$index]);
	}
	
	public function offsetUnset($index)
	{
		if ($this->offsetExists($index)) {
			unset($this->_messages[$index]);
		}
		return $this;
	}
	
	private function _add($index, Europa_Validator_Message $message)
	{
		if (is_null($index)) {
			$index = $this->count();
		}
		$this->_messages[$index] = $message;
		return $this;
	}
}