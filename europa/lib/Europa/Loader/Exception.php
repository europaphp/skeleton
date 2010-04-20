<?php

/**
 * Loader exception class.
 * 
 * @category   Exception
 * @package    Europa
 * @subpackage Loader
 * @license    (c) 2010 Trey Shugart <treshugart@gmail.com>
 * @link       http://europaphp.org/license
 */
class Europa_Loader_Exception extends Europa_Exception
{
	/**
	 * Thrown when added path cannot be resolved.
	 * 
	 * @var int
	 */
	const INVALID_PATH = 1;
}