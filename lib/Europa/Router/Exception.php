<?php

/**
 * The exception class for Europa_Router.
 * 
 * @category Exceptions
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart
 * @link     http://europaphp.org/license
 */
class Europa_Router_Exception extends Europa_Exception
{
	/**
	 * Thrown when no route is matched.
	 * 
	 * @var int
	 */
	const NO_MATCH = 1;
}