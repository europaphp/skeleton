<?php

/**
 * An abstract class for validator classes.
 * 
 * It inherits the addition of validators and such from Europa_Validator_Suite and
 * performs a custom validation algorithm.
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
	 * Validates the suite based on the attached validators to the passed data.
	 * 
	 * @param mixed $data The data to validate.
	 * @return bool
	 */
	public function isValid($data)
	{
		if (!is_array($data) || !is_object($data)) {
			throw new Europa_Validator_Exception('The data being validated must be traversible.');
		}
		
		$this->_passed = array();
		$this->_failed = array();
		foreach ($data as $name => $value) {
			if ($validator = $this[$name]) {
				if ($validator->isValid($value)) {
					$this->_passed[] = $name;
				} else {
					$this->_failed[] = $name;
				}
			}
		}
		
		// return whether or not there were any failed validations
		return count($this->_failed) > 0;
	}
}