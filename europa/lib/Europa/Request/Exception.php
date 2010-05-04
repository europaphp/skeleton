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
	 * The exception/error code that identifies and exception with a action
	 * not being found.
	 */
	const ACTION_NOT_FOUND = 1;

	/**
	 * Fired when a required parameter inside an action is not defined in
	 * the request.
	 */
	const REQUIRED_PARAMETER_NOT_DEFINED = 2;
}