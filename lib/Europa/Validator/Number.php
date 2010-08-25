<?php

/**
 * Validator for numbers.
 * 
 * @category Validation
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart
 * @link     http://europaphp.org/license
 */
class Europa_Validator_Number implements Europa_Validator_Validatable
{
	/**
	 * Checks to make sure the specified value is a number.
	 * 
	 * @return bool
	 */
	public function isValid($value)
	{
		return is_numeric($value);
	}
}