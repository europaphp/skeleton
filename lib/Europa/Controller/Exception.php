<?php

/**
 * An example of an abstract controller to act as a base class for all
 * controllers.
 * 
 * @category Controllers
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2010 Trey Shugart http://europaphp.org/license
 */
namespace Europa\Controller
{
	class Exception extends \Europa\Exception
	{
	    /**
	     * Thrown when an action is not found by default in
	     * Europa_Controller_Action.
	     * 
	     * @var int
	     */
	    const ACTION_NOT_FOUND = 1;
	}
}