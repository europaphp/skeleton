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
class Europa_Validator_AlphaNumeric extends Europa_Validator
{
	/**
	 * Checks to make sure the value is alpha-numeric
	 * 
	 * @param mixed $value The value to validate.
	 * @return Europa_Validator_AlphaNumeric
	 */
	public function validate($value)
	{
		if (preg_match('/^[a-zA-Z0-9]*$/', $value)) {
			$this->pass();
		} else {
			$this->fail();
		}
		return $this;
	}
}