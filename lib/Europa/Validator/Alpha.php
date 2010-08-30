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
class Europa_Validator_Alpha extends Europa_Validator
{
	/**
	 * Checks to make sure the specified value is set.
	 * 
	 * @return bool
	 */
	public function isValid($value)
	{
		return (bool) preg_match('/^[a-zA-Z]*$/', $value);
	}
}