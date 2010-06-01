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
abstract class AbstractController extends Europa_Controller
{
	/**
	 * Sets up the view scheme.
	 * 
	 * @return string
	 */
	public function __toString()
	{
		$view = $this->_request->getParam('controller', 'index');
		$view = Europa_String::create($view . 'View')->toClass();
		$view = new Europa_View_Php((string) $view);
		return (string) $view->extend(new Europa_View_Php('AbstractView'));
	}
}