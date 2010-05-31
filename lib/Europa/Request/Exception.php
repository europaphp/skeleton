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
}