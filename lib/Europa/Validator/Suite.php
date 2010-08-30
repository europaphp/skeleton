<?php

/**
 * Validates more than one validator against a value.
 * 
 * @category Validation
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart
 * @link     http://europaphp.org/license
 */
class Europa_Validator_Suite implements Europa_Validator_Validatable, ArrayAccess, Iterator, Countable
{
	/**
	 * Contains the indexes of the failed validators.
	 * 
	 * @var array
	 */
	protected $_errors = array();
	
	/**
	 * Contains all validators attached to the suite.
	 * 
	 * @var array
	 */
	protected $_validators = array();
	
	/**
	 * Validates and returns whether or not the validator passed validation.
	 * 
	 * @param mixed $value The value to validate.
	 * @return bool
	 */
	public function isValid($value)
	{
		$this->_errors = array();
		foreach ($this as $index => $validator) {
			if (!$validator->isValid($value)) {
				$this->_errors[] = $index;
			}
		}
		return !$this->hasErrors();
	}
	
	/**
	 * Returns whether or not the suite has errors.
	 * 
	 * @return bool
	 */
	public function hasErrors()
	{
		return $this->countErrors() > 0;
	}
	
	/**
	 * Returns the number of errors.
	 * 
	 * @return int
	 */
	public function countErrors()
	{
		return count($this->_errors);
	}
	
	/**
	 * Returns all error messages.
	 * 
	 * @return array
	 */
	public function getMessages()
	{
		$messages = array();
		foreach ($this->_errors as $index) {
			foreach ($this[$index]->getMessages() as $message) {
				$messages[] = $message;
			}
		}
		return $messages;
	}
	
	/**
	 * Returns the number of validators on the suite.
	 * 
	 * @return int
	 */
	public function count()
	{
		return count($this->_validators);
	}
	
	/**
	 * Returns the current validator in the iteration.
	 * 
	 * @return Europa_Validator_Validatable
	 */
	public function current()
	{
		return current($this->_validators);
	}
	
	/**
	 * Returns the index of the current validator.
	 * 
	 * @return int|string
	 */
	public function key()
	{
		return key($this->_validators);
	}
	
	/**
	 * Moves the internal pointer foward to the next validator in the suite.
	 * 
	 * @return Europa_Validator_Validatable
	 */
	public function next()
	{
		next($this->_validators);
		return $this;
	}
	
	/**
	 * Resets the internal pointer to the beginning of bound validators.
	 * 
	 * @return Europa_Validator_Validatable
	 */
	public function rewind()
	{
		reset($this->_validators);
		return $this;
	}
	
	/**
	 * Returns whether or not it is ok to continue iteration over the validators.
	 * 
	 * @return bool
	 */
	public function valid()
	{
		return $this->offsetExists($this->key());
	}
	
	/**
	 * Sets the specified validator.
	 * 
	 * @param int|string $index
	 * @param Europa_Validator_Validatable $value
	 * @return Europa_Validator_Validatable
	 */
	public function offsetSet($index, $value)
	{
		$this->_add($index, $value);
		return $this;
	}
	
	/**
	 * Returns the specified validator.
	 * 
	 * @param int|string $index
	 * @return Europa_Validator_Validatable
	 */
	public function offsetGet($index)
	{
		if ($this->offsetExists($index)) {
			return $this->_validators[$index];
		}
		return null;
	}
	
	/**
	 * Returns whether or not the specified validator exists.
	 * 
	 * @param int|string $index
	 * @return bool
	 */
	public function offsetExists($index)
	{
		return isset($this->_validators[$index]);
	}
	
	/**
	 * Unsets the specified validator if it exists.
	 * 
	 * @param int|string $index
	 * @return bool
	 */
	public function offsetUnset($index)
	{
		// remove the validator
		if ($this->offsetExists($index)) {
			unset($this->_validators[$index]);
		}
		
		// remove any errors associated to the valdiator
		if (isset($this->_errors[$index])) {
			unset($this->_errors[$index]);
		}
		
		return $this;
	}
	
	/**
	 * Adds the specified validator to the suite.
	 * 
	 * @param int|string $index
	 * @param Europa_Validator_Validatable
	 * @return Europa_Validator_Validatable
	 */
	protected function _add($index, Europa_Validator_Validatable $validator)
	{
		if (is_null($index)) {
			$index = $this->count();
		}
		$this->_validators[$index] = $validator;
		return $this;
	}
}