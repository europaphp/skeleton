<?php

/**
 * Provides a base implementation for routes.
 * 
 * @category Routing
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart
 * @link     http://europaphp.org/license
 */
abstract class Europa_Request_Route
{
	/**
	 * The expression used to match the route.
	 * 
	 * @var string
	 */
	protected $_expression = null;
	
	/**
	 * Contains an associative array of the parameters that were parsed out
	 * of the request from the route definition.
	 * 
	 * @var array
	 */
	protected $_params = array();
	
	/**
	 * A way of retrieving a uri representing the route.
	 * 
	 * @return string
	 */
	abstract public function getUri();
	
	/**
	 * An algorithm for matching the passed $uri to the expression
	 * set on the route. Returns an array of matched parameters or
	 * false on failure.
	 * 
	 * @param string $uri The uri to match against the route.
	 * @return array|bool
	 */
	abstract public function match($uri);
	
	/**
	 * Constructs a new route and sets the passed in expression.
	 * 
	 * @param string $expression The expression to use when matching the route.
	 * @return Europa_Request_Route_Abstract
	 */
	public function __construct($expression)
	{
		$this->_expression = (string) $expression;
	}
	
	/**
	 * Binds a parameter to the route.
	 * 
	 * @param string $name The name of the parameter to set.
	 * @param mixed $value The value of the parameter.
	 * @return Europa_Request_Route_Abstract
	 */
	final public function setParam($name, $value)
	{
		$this->_params[$name] = $value;
		
		return $this;
	}
	
	/**
	 * Returns the specified parameter. If the parameter isn't found, then the
	 * default value is returned.
	 * 
	 * @param string $name The name of the parameter to get.
	 * @param mixed $defaultValue A value to return if the parameter is not
	 * defined.
	 * @return mixed
	 */
	final public function getParam($name, $defaultValue = null)
	{
		$params = $this->getParams();
		
		if (array_key_exists($name, $params)) {
			return $params[$name];
		}
		
		return $defaultValue;
	}
	
	/**
	 * Returns all parameters cascading to route params, get, then post.
	 * 
	 * @return array
	 */
	final public function getParams()
	{
		static $params;
		
		if (!isset($params)) {
			$params = array_merge($this->_params, $_GET, $_POST);
		}
		
		return $params;
	}
}