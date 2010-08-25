<?php

/**
 * An interface for building custom validators.
 * 
 * @category Validation
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart
 * @link     http://europaphp.org/license
 */
interface Europa_Validator_Validatable
{
	/** 
	 * Validates the passed in value.
	 * 
	 * @param mixed $value The value to validate.
	 * @return bool
	 */
	public function isValid($value);
}