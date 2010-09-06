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
	
	public function init()
	{
		
	}
	
	public function preRender()
	{
		
	}
	
	public function postRender()
	{
		
	}
	
	/**
	 * Sets the request the controller should use.
	 * 
	 * @param Europa_Request $request The request to use.
	 * @return Europa_Controller
	 */
	public function setRequest(Europa_Request $request)
	{
		$this->_request = $request;
		return $this;
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