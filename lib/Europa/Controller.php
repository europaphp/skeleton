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
	 * The request used to dispatch to this controller.
	 * 
	 * @var Europa_Request
	 */
	private $_request;
	
	/**
	 * The view rendering the page.
	 * 
	 * @var Europa_View
	 */
	private $_view;
	
	/**
	 * Handles action dispatching.
	 * 
	 * @return mixed
	 */
	abstract public function action();
	
	/**
	 * Renders the set view.
	 * 
	 * @return string
	 */
	public function __toString()
	{
		if ($this->_view) {
			return $this->_view->__toString();
		}
		return '';
	}
	
	/**
	 * Constructs a new controller using the specified request.
	 * 
	 * @param Europa_Request $request The request to use.
	 * @return Europa_Controller
	 */
	public function __construct(Europa_Request $request)
	{
		$this->_request = $request;
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
	
	/**
	 * Returns the request being used.
	 * 
	 * @return Europa_Request
	 */
	public function getRequest()
	{
		return $this->_request;
	}
	
	/**
	 * Forwards the request to the specified controller.
	 * 
	 * @param string $to The controller to forward the request to.
	 * @return Europa_Controller
	 */
	public function forward($to)
	{
		$to = Europa_String::create($to)->toClass();
		$to = new $to($this->_request);
		$to->action();
		return $to;
	}
}