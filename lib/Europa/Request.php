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
abstract class Europa_Request
{
	/**
	 * The params parsed out of the route and cascaded through the
	 * super-globals.
	 * 
	 * @var array
	 */
	protected $params = array();
	
	/**
	 * The route that was matched.
	 *
	 * @var Europaroute
	 */
	protected $route;
	
	/**
	 * The array of routes to match.
	 * 
	 * @var array
	 */
	protected $routes = array();
	
	/**
	 * Contains the instances of all requests that are currently 
	 * dispatching in chronological order.
	 * 
	 * @var array
	 */
	private static $stack = array();
	
	/**
	 * Returns the string that the routes will match against.
	 * 
	 * @return string
	 */
	abstract public function getRouteSubject();
	
	/**
	 * Destructs the object and removes it's reference.
	 * 
	 * @return void
	 */
	public function __destruct()
	{
		// remove the dispatch from the stack
		array_pop(self::$stack);
	}
	
	/**
	 * Dispatches the request to the appropriate controller/action combo. If
	 * route matching hasn't been done yet, it will be done.
	 * 
	 * @return Europa_Controller
	 */
	public function dispatch()
	{
		// register the instance in the stack so it can be easily found
		self::$stack[] = $this;
		
		// route if it hasn't been done yet
		if (!$this->getRoute()) {
			$this->route($this->getRouteSubject());
		}
		
		// routing information
		$controller = $this->formatController($this->getController());
		
		// dispatch request
		if (!Europa_Loader::loadClass($controller)) {
			throw new Europa_Request_Exception(
				'Could not load controller ' . $controller . '.',
				Europa_Request_Exception::CONTROLLER_NOT_FOUND
			);
		}
		
		// call the controller
		$controller = new $controller;
		
		// return the action result
		return $controller;
	}
	
	/**
	 * Processes all routes. If a route is matched, the matched parameters are
	 * returned. If no match is found, false is returned.
	 * 
	 * @param string $subject The subject to route against.
	 * @return bool|array
	 */
	public function route($subject)
	{
		foreach ($this->routes as $route) {
			$params = $route->query($subject);
			if ($params) {
				$this->route = $route;
				$this->setParams($params);
				return true;
			}
		}
		return false;
	}

	/**
	 * Sets a route.
	 * 
	 * @param string $name The name of the route.
	 * @param Europa_Requestroute $route The route to use.
	 * @return Europa_Request
	 */
	public function setRoute($name, Europa_Requestroute $route)
	{
		$this->routes[$name] = $route;
		return $this;
	}

	/**
	 * Gets a specified route or the route which was matched.
	 * 
	 * @param string $name The name of the route to get.
	 * @return Europa_Requestroute
	 */
	public function getRoute($name = null)
	{
		if ($name) {
			if (isset($this->routes[$name])) {
				return $this->routes[$name];
			}
			return false;
		}
		return $this->route;
	}
	
	/**
	 * Returns a given parameter's value.
	 * 
	 * @param string $names The name or names of the parameters to search for.
	 * @param mixed $default The default value to return if the parameters
	 * aren't set.
	 * @return mixed
	 */
	public function getParam($names, $default = null)
	{
		if (!is_array($names)) {
			$names = array($names);
		}
		foreach ($names as $name) {
			if (isset($this->params[$name])) {
				return $this->params[$name];
			}
		}
		return $default;
	}
	
	/**
	 * Sets a given parameter's value. If multiple names are supplied, their
	 * values are set to the single passed value. This is useful for example
	 * for batch setting of default param values, or in CLI mode when you have
	 * a param '--my-param' which is also aliased as 'm'.
	 * 
	 * @param string $names The parameter name or names.
	 * @param mixed $value The parameter value.
	 * @return Europa_Request
	 */
	public function setParam($names, $value)
	{
		if (!is_array($names)) {
			$names = array($names);
		}
		foreach ($names as $name) {
			$this->params[$name] = $value;
		}
		return $this;
	}
	
	/**
	 * Returns all parameters set on the request.
	 * 
	 * @return array
	 */
	public function getParams()
	{
		return $this->params;
	}
	
	/**
	 * Binds multiple parameters to the request. Overrides any existing
	 * parameters with the same name.
	 * 
	 * @param mixed $params The params to set. Can be any iterable value.
	 * @return Europa_Request
	 */
	public function setParams($params)
	{
		if (is_array($params) || is_object($params)) {
			foreach ($params as $k => $v) {
				$this->setParam($k, $v);
			}
		}
		return $this;
	}
	
	/**
	 * Returns the formatted controller name that should be instantiated.
	 * 
	 * @param string $controller The controller param to format.
	 * @return string
	 */
	public function formatController($controller)
	{
		return Europa_String::create($controller)->toClass() . 'Controller';
	}
	
	/**
	 * Returns the controller parameter. This is the value that is passed to the
	 * formatter.
	 * @return string
	 */
	public function getController()
	{
		return $this->getParam('controller', 'index');
	}
	
	/**
	 * Returns the Europa_Request instance that is currently dispatching.
	 * 
	 * @return mixed
	 */
	public static function getActiveInstance()
	{
		$len = count(self::$stack);
		if ($len) {
			return self::$stack[$len - 1];
		}
		return null;
	}
	
	/**
	 * Returns all Europa_Request instances that are dispatching,
	 * in chronological order, as an array.
	 * 
	 * @return array
	 */
	public static function getStack()
	{
		return self::$stack;
	}
	
	/**
	 * Returns whether or not the request is a CLI request or not.
	 * 
	 * @return bool
	 */
	public static function isCli()
	{
		return defined('STDIN');
	}
}