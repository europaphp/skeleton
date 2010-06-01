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
	 * Holds the request that was used to get to this controller.
	 * 
	 * @var Europa_Request
	 */
	protected $_request;
	
	/**
	 * Constructs a new view and sets the request.
	 * 
	 * @param Europa_Request $request The request object.
	 * @return Europa_Controller
	 */
	public function __construct(Europa_Request $request)
	{
		// set the request
		$this->_request = $request;
	}
	
	/**
	 * Renders the set view.
	 * 
	 * @return string
	 */
	abstract public function __toString();
	
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
}