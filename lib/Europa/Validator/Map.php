<?php

/**
 * Acts as a validation suite, but maps validators to input data.
 * 
 * @category Validation
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart
 * @link     http://europaphp.org/license
 */
class Europa_Validator_Map extends Europa_Validator_Suite
{
	/**
	 * Contains a mapping to the failed validators.
	 * 
	 * @var array
	 */
	private $_failed = array();
	
	/**
	 * Contains a mapping to the passed validators.
	 * 
	 * @var array
	 */
	private $_passed = array();
	
	/**
	 * Validates the suite based on the attached validators to the passed data.
	 * 
	 * @param mixed $data The data to validate.
	 * @return bool
	 */
	public function isValid($data)
	{
		if (!is_array($data)) {
			throw new Europa_Validator_Exception('The data being validated must be an array.');
		}
		
		$this->_passed = array();
		$this->_failed = array();
		foreach ($this as $name => $validator) {
			$value = isset($data[$name]) ? $data[$name] : null;
			if ($validator->isValid($value)) {
				$this->_passed[] = $name;
			} else {
				$this->_failed[] = $name;
			}
		}
		
		// return whether or not there were any failed validations
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
		foreach ($this->_failed as $name) {
			$failed[$name] = $this[$name];
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
		foreach ($this->_passed as $name) {
			$passed[$name] = $this[$name];
		}
		return $passed;
	}
}