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
abstract class Europa_Controller_Basic
{
	/**
	 * The request used to dispatch to this controller.
	 * 
	 * @var Europa_Request
	 */
	private $_request;
	
	/**
	 * Renders the set view.
	 * 
	 * @return string
	 */
	abstract public function __toString();
	
	/**
	 * Handles action dispatching.
	 * 
	 * @return mixed
	 */
	abstract public function action();
	
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
	 * Sets a parameter.
	 * 
	 * @param string $name The name of the parameter.
	 * @param mixed $value The value of the parameter.
	 * @return void
	 */
	public function __set($name, $value)
	{
		$this->_request->setParam($name, $value);
	}
	
	/**
	 * Gets a parameter.
	 * 
	 * @param string $name The name of the parameter.
	 * @return void
	 */
	public function __get($name)
	{
		return $this->_request->getParam($name);
	}
	
	/**
	 * Sets the request object.
	 * 
	 * @param Europa_Request $request The request to use.
	 * @return Europa_Controller
	 */
	protected function _setRequest(Europa_Request $request)
	{
		$this->_request = $request;
		return $this;
	}
	
	/**
	 * Returns the request being used.
	 * 
	 * @return Europa_Request
	 */
	protected function _getRequest()
	{
		return $this->_request;
	}
	
	/**
	 * Forwards the current request to the specified controller and returns the
	 * new controller.
	 * 
	 * @param string $controller The controller to forward to.
	 * @return Europa_Controller
	 */
	protected function _forward($controller)
	{
		return $this->_request->setController($controller)->dispatch();
	}
}