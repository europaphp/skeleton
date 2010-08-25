<?php

class Europa_Validator_Suite implements Europa_Validator_Validatable, ArrayAccess, Iterator
{
	private $_validators = array();
	
	public function isValid($value)
	{
		foreach ($this as $validator) {
			if (!$validator->isValid($value)) {
				return false;
			}
		}
		return true;
	}
	
	public function current()
	{
		return current($this->_validators);
	}
	
	public function key()
	{
		return key($this->_validators);
	}
	
	public function next()
	{
		next($this->_validators);
		return $this;
	}
	
	public function rewind()
	{
		reset($this->_validators);
		return $this;
	}
	
	public function valid()
	{
		return $this->offsetExists($this->key());
	}
	
	public function offsetSet($index, $value)
	{
		$this->_add($value);
		return $this;
	}
	
	public function offsetGet($index)
	{
		if ($this->offsetExists($index)) {
			return $this->_validators[$index];
		}
		return null;
	}
	
	public function offsetExists($index)
	{
		return isset($this->_validators[$index]);
	}
	
	public function offsetUnset($index)
	{
		if ($this->offsetExists($index)) {
			unset($this->_validators[$index]);
		}
		return $this;
	}
	
	private function _add(Europa_Validator_Validatable $validator)
	{
		$this->_validators[] = $validator;
		return $this;
	}
}