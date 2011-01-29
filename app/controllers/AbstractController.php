<?php

/**
 * An example of controller abstraction that sets up the views.
 * 
 * @category Controllers
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2010 Trey Shugart http://europaphp.org/license
 */
abstract class AbstractController extends \Europa\Controller
{
    /**
     * Sets up the views.
     * 
     * @return void
     */
	public function init()
	{
		$this->setView(
			new \Europa\View\Layout(
				new \Europa\View\Php('DefaultLayout'),
				new \Europa\View\Php(str_replace('Controller', 'View', get_class($this)))
			)
		);
	}
}