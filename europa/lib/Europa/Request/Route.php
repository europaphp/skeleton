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
}