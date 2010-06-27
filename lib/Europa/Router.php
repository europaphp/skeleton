<?php

/**
 * The heart of EuropaPHP. This is where it all starts and ends.
 * 
 * @category Request
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart
 * @link     http://europaphp.org/license
 */
class Europa_Router
{
	/**
	 * The parameters that were matched after routing.
	 * 
	 * @var array
	 */
	protected $_params = array();
	
	/**
	 * The route that was matched.
	 *
	 * @var Europa_Route
	 */
	protected $_route = null;
	
	/**
	 * The array of routes to match.
	 * 
	 * @var array
	 */
	protected $_routes = array();
	
	/**
	 * The string to use for route matching.
	 * 
	 * @var string
	 */
	protected $_routeSubject = null;
	
	/**
	 * Processes all routes. If a route is matched, it is set, params are set
	 * and it returns true. If a route is not matched, the route is set to
	 * false and it returns false.
	 * 
	 * @param string $subject The subject to route against.
	 * @return bool
	 */
	public function route($subject)
	{
		foreach ($this->_routes as $route) {
			if ($params = $route->query($subject)) {
				$this->_route  = $route;
				$this->_params = $params;
				return true;
			}
		}
		$this->_route = false;
		return false;
	}

	/**
	 * Sets a route.
	 * 
	 * @param string $name The name of the route.
	 * @param Europa_Request_Route $route The route to use.
	 * @return Europa_Request
	 */
	public function setRoute($name, Europa_Router_Route $route)
	{
		$this->_routes[$name] = $route;
		return $this;
	}

	/**
	 * Gets a specified route.
	 * 
	 * @param string $name The name of the route to get.
	 * @return Europa_Request_Route
	 */
	public function getRoute($name)
	{
		if (isset($this->_routes[$name])) {
			return $this->_routes[$name];
		}
		return null;
	}
	
	/**
	 * Returns whether or not the request has a route to take.
	 * 
	 * @return bool
	 */
	public function hasRoute()
	{
		return $this->_route instanceof Europa_Router_Route;
	}
	
	/**
	 * Returns whether or not the current router has routed.
	 * 
	 * @return bool
	 */
	public function hasRouted()
	{
		return !is_null($this->_route);
	}
	
	/**
	 * Returns the route that was matched during routing.
	 * 
	 * @return null|false|Europa_Request_Route
	 */
	public function getMatchedRoute()
	{
		return $this->_route;
	}
	
	/**
	 * Sets the string to use for route matching.
	 * 
	 * @param string $subject The subject to use for route matching.
	 * @return Europa_Request
	 */
	public function setRouteSubject($subject)
	{
		$this->_routeSubject = $subject;
		return $this;
	}
	
	/**
	 * Returns the string that the routes will match against.
	 * 
	 * @return string
	 */
	public function getRouteSubject()
	{
		return $this->_routeSubject;
	}
	
	/**
	 * Returns all parameters that were matched during routing.
	 * 
	 * @return array
	 */
	public function getParams()
	{
		return $this->_params;
	}
}