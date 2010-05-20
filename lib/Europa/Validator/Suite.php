<?php

/**
 * An abstract class for validator classes.
 * 
 * @category Validation
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart
 * @link     http://europaphp.org/license
 */
class Europa_Validator_Suite implements Europa_Validator_Validatable
{
	/**
	 * 
	 */
	protected $_errors = array();
	
	/**
	 * 
	 */
	protected $_validators = array();
	
	/**
	 * 
	 */
	public function validate(array $data)
	{
		$valid = true;
		foreach ($this->_validators as $name => $group) {
			$value = null;
			if (isset($data[$name])) {
				$value = $data[$name];
			}
			foreach ($group as $validator) {
				if (!$validator->validate($value)) {
					$valid = false;
					$this->_errors[] = $validator;
				}
			}
		}
		return $valid;
	}
	
	/**
	 * 
	 */
	public function addValidator($name, Europa_Validator_Validatable $validator)
	{
		if (!isset($this->_validators[$name])) {
			$this->_validators[$name] = array();
		}
		$this->_validators[$name][] = $validator;
		return $this;
	}
	
	/**
	 *
	 */
	public function getValidators()
	{
		return $this->_validators;
	}
	
	/**
	 * 
	 */
	public function getErrors()
	{
		return $this->_errors;
	}
	
	/**
	 * 
	 */
	public function getErrorMessages()
	{
		$messages = array();
		foreach ($this->getErrors() as $error) {
			foreach ($error->getErrorMessages() as $message) {
				$messages[] = $message;
			}
		}
		return $messages;
	}
}