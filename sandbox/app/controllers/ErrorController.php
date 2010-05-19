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
class ErrorController extends AbstractController
{
	/**
	 * Traps any actions called on the error controller and sends it to the
	 * "not found" action.
	 * 
	 * @param string $name The name of the error action called
	 * @param array $args The request parameters passed.
	 * @return void
	 */
	public function __call($name, $args)
	{
		$this->notFoundAction();
		$this->_view->setScript('Error/notFound');
	}
	
	/**
	 * The action that gets called whenever something isn't found.
	 * 
	 * @return void
	 */
	public function notFoundAction()
	{
		
	}
}