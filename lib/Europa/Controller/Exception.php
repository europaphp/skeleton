<?php

/**
 * An example of an abstract controller to act as a base class for all
 * controllers.
 * 
 * @category  Controllers
 * @package   Europa
 * @author    Trey Shugart <treshugart@gmail.com>
 * @copyright (c) 2010 Trey Shugart
 * @link      http://europaphp.org/license
 */
class Europa_Controller_Exception extends Europa_Exception
{
	/**
	 * Thrown when an action is not found by default in
	 * Europa_Controller_Action.
	 * 
	 * @var int
	 */
	const ACTION_NOT_FOUND = 1;
}