<?php

/**
 * The exception class for Europa_Request.
 * 
 * @category Exceptions
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart
 * @link     http://europaphp.org/license
 */
class Europa_Request_Exception extends Europa_Exception
{
	/**
	 * Thrown when the controller cannot be found.
	 * 
	 * @var int
	 */
	const CONTROLLER_NOT_FOUND = 1;
	
	/**
	 * Thrown when an invalid controller formatter is set.
	 * 
	 * @var int
	 */
	const INVALID_CONTROLLER_FORMATTER = 2;
	
	/**
	 * Thrown when a required method parameter isn't defined when mapping
	 * request parameters to method parameters.
	 * 
	 * @var int
	 */
	const REQUIRED_METHOD_ARGUMENT_NOT_DEFINED = 3;
}