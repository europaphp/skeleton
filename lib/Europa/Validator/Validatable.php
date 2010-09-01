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
	 * Performs validation on the specified value.
	 * 
	 * @param mixed $value The value to validate.
	 * @return Europa_Validator_Validatable
	 */
	public function validate($value);
	
	/** 
	 * Tells whether the last validation was successful or not.
	 * 
	 * @param mixed $value The value to validate.
	 * @return bool
	 */
	public function isValid();
	
	/**
	 * Returns the messages associated to the validatable object.
	 * 
	 * @return array
	 */
	public function getMessages();
	
	/**
	 * Fails validation.
	 * 
	 * @return Europa_Validator_Validatable
	 */
	public function fail();
	
	/**
	 * Passes validation.
	 * 
	 * @return Europa_Validator_Validatable
	 */
	public function pass();
}