<?php

/**
 * The base controller for all controller classes.
 * 
 * @category  Controllers
 * @package   Europa
 * @author    Trey Shugart <treshugart@gmail.com>
 * @copyright (c) 2010 Trey Shugart
 * @link      http://europaphp.org/license
 */
abstract class Europa_Controller
{
	/**
	 * Renders the layout and view or any combination of the two depending on
	 * if they are enabled/disabled.
	 * 
	 * @return string
	 */
	public function __toString()
	{
		$view = substr(get_class($this), 0, -strlen('Controller'));
		$view = Europa_String::create($view)->toClass();
		$view = $view . 'View';
		return new $view;
	}
}