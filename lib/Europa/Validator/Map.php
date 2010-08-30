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
		$this->_errors = array();
		foreach ($this as $index => $validator) {
			$value = isset($data[$index]) ? $data[$index] : null;
			if (!$validator->isValid($value)) {
				$this->_errors[] = $index;
			}
		}
		return !$this->hasErrors();
	}
}