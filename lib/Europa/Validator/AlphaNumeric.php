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
class Europa_Validator_AlphaNumeric implements Europa_Validator_Validatable
{
	/**
	 * Checks to make sure the value is alpha-numeric
	 * 
	 * @return bool
	 */
	public function isValid($value)
	{
		return preg_match('/[a-zA-Z0-9]/', $value);
	}
}