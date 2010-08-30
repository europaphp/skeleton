<?php

class Europa_Validator_Suite implements Europa_Validator_Validatable, ArrayAccess, Iterator, Countable
{
	protected $_failed = array();
	
	protected $_passed = array();
	
	protected $_validators = array();
	
	public function isValid($value)
	{
		$this->_passed = array();
		$this->_failed = array();
		foreach ($this as $index => $validator) {
			if ($validator->isValid($value)) {
				$this->_passed[] = $index;
			} else {
				$this->_failed[] = $index;
			}
		}
		return count($this->_failed) === 0;
	}
	
	/**
	 * Returns the validators that failed.
	 * 
	 * @return array
	 */
	public function getFailed()
	{
		$failed = array();
		foreach ($this->_failed as $index) {
			$failed[$index] = $this[$index];
		}
		return $failed;
	}
	
	/**
	 * Returns the validators that passed.
	 * 
	 * @return array
	 */
	public function getPassed()
	{
		$passed = array();
		foreach ($this->_passed as $index) {
			$passed[$index] = $this[$index];
		}
		return $passed;
	}
	
	public function count()
	{
		return count($this->_validators);
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
		$this->_add($index, $value);
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
	
	private function _add($index, Europa_Validator_Validatable $validator)
	{
		if (is_null($index)) {
			$index = $this->count();
		}
		$this->_validators[$index] = $validator;
		return $this;
	}
}