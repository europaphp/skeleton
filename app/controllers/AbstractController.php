<?php

/**
 * An example of controller abstraction that sets up the views.
 * 
 * @category Controllers
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2010 Trey Shugart http://europaphp.org/license
 */
abstract class AbstractController extends Europa_Controller
{
    /**
     * Sets up the views.
     * 
     * @return void
     */
	public function init()
	{
		$view = str_replace('Controller', 'View', get_class($this));
		$view = str_replace('_', '/', $view);
		$this->setView(
			new Europa_View_Layout(
				new Europa_View_Php('DefaultLayout'),
				new Europa_View_Php($view)
			)
		);
	}
}