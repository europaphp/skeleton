<?php

/**
 * A standard controller base class that implements a layout view system in
 * a one-controller-per-action environment.
 * 
 * @category  Controllers
 * @package   Europa
 * @author    Trey Shugart <treshugart@gmail.com>
 * @copyright (c) 2010 Trey Shugart
 * @link      http://europaphp.org/license
 */
abstract class Europa_Controller_Standard extends Europa_Controller
{
	/**
	 * The view rendering the page.
	 * 
	 * @var Europa_View
	 */
	private $_view;
	
	/**
	 * Constructs the controller and sets the request to use.
	 * 
	 * @param Europa_Request $request The request to use.
	 * @return Europa_Controller_Standard
	 */
	public function __construct($request)
	{
		parent::__construct($request);
		
		$controller = $request->getController();
		$controller = Europa_String::create($controller)->toClass()->replace('_', DIRECTORY_SEPARATOR);
		
		$this->_view = new Europa_View_Layout;
		$this->_view->setLayout(new Europa_View_Php('IndexLayout'));
		$this->_view->setView(new Europa_View_Php($controller . 'View'));
	}
	
	/**
	 * Renders the view.
	 * 
	 * @return string
	 */
	public function __toString()
	{
		return $this->_view->__toString();
	}
	
	/**
	 * Sets the view to use.
	 * 
	 * @param Europa_View $view The view to use.
	 * @return Europa_Controller_Standard
	 */
	public function setView(Europa_View $view = null)
	{
		$this->_view = $view;
		return $this;
	}
	
	/**
	 * Returns the view being used.
	 * 
	 * @return Europa_View
	 */
	public function getView()
	{
		return $this->_view;
	}
}