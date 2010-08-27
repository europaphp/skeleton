<?php

/**
 * A router which binds a Europa_Request to multiple Europa_Route's.
 * 
 * @category Request
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart
 * @link     http://europaphp.org/license
 */
class Europa_Router implements Iterator, ArrayAccess, Countable
{
	/**
	 * The request that is to be routed.
	 * 
	 * @var Europa_Request
	 */
	protected $_request;
	
	/**
	 * The array of routes to match.
	 * 
	 * @var array
	 */
	protected $_routes = array();
	
	/**
	 * The subject that each route is queried against.
	 * 
	 * @var string
	 */
	protected $_subject;
	
	/**
	 * Constructs a new request router.
	 * 
	 * @return Europa_Router
	 */
	public function __construct(Europa_Request $request)
	{
		$this->_request = $request;
		$this->_subject = $request->__toString();
	}
	
	/**
	 * Processes all routes. If a route is matched, it is set, params are set
	 * and it returns true. If a route is not matched, the route is set to
	 * false and it returns false.
	 * 
	 * @param string $subject The subject to route against.
	 * @return bool
	 */
	public function dispatch()
	{
		foreach ($this as $route) {
			$params = $route->query($this->getSubject());
			if ($params !== false) {
				return $this->_request->setParams($params)->dispatch();
			}
		}
		throw new Europa_Router_Exception(
			'Unable to match route.',
			Europa_Router_Exception::NO_MATCH
		);
	}

	/**
	 * Sets a route.
	 * 
	 * @param string $name The name of the route.
	 * @param Europa_Route $route The route to use.
	 * @return Europa_Request
	 */
	public function setRoute($name, Europa_Route $route)
	{
		$this->_routes[$name] = $route;
		return $this;
	}

	/**
	 * Gets a specified route.
	 * 
	 * @param string $name The name of the route to get.
	 * @return Europa_Route|null
	 */
	public function getRoute($name)
	{
		if (isset($this->_routes[$name])) {
			return $this->_routes[$name];
		}
		return null;
	}
	
	/**
	 * Returns the subject that is queried against each route.
	 * 
	 * @return string
	 */
	public function getSubject()
	{
		return $this->_subject;
	}
	
	/**
	 * Sets the subject that is queried against each route.
	 * 
	 * @return Europa_Route
	 */
	public function setSubject($subject)
	{
		$this->_subject = (string) $subject;
		return $this;
	}
	
	/**
	 * Returns the number of routes bound to the router.
	 * 
	 * @return int
	 */
	public function count()
	{
		return count($this->_routes);
	}
	
	/**
	 * Returns the current route in the iteration.
	 * 
	 * @return Europa_Route
	 */
	public function current()
	{
		return current($this->_routes);
	}
	
	public function key()
	{
		return key($this->_routes);
	}
	
	public function next()
	{
		next($this->_routes);
	}
	
	public function rewind()
	{
		reset($this->_routes);
	}
	
	public function valid()
	{
		return (bool) $this->current();
	}
	
	public function offsetGet($offset)
	{
		return $this->getRoute($offset);
	}
	
	public function offsetSet($offset, $route)
	{
		$this->setRoute($offset, $route);
	}
	
	public function offsetExists($offset)
	{
		return isset($this->_routes);
	}
	
	public function offsetUnset($offset)
	{
		if (isset($this->_routes[$offset])) {
			unset($this->_routes[$offset]);
		}
	}
}