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
	 * The key used to get the controller from the request params.
	 * 
	 * @var string
	 */
	protected $_controllerKey = 'controller';
	
	/**
	 * The callback to use for formatting the controller parameter.
	 * 
	 * @var mixed
	 */
	protected $_controllerFormatter = null;
	
	/**
	 * The params parsed out of the route and cascaded through the
	 * super-globals. Contains the default controller to use.
	 * 
	 * @var array
	 */
	protected $_params = array('controller' => 'index');
	
	/**
	 * The route that was matched.
	 *
	 * @var Europa_Route
	 */
	protected $_route;
	
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
	 * Contains the instances of all requests that are currently 
	 * dispatching in chronological order.
	 * 
	 * @var array
	 */
	private static $stack = array();
	
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
	 * Returns the specified request parameter.
	 * 
	 * @param string $name The name of the parameter.
	 * @return mixed
	 */
	public function __get($name)
	{
		return $this->getParam($name);
	}
	
	/**
	 * Sets the specified request parameter.
	 * 
	 * @param string $name The name of the parameter.
	 * @param mixed $value The value of the parameter.
	 * @return mixed
	 */
	public function __set($name, $value)
	{
		$this->setParam($name, $value);
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
			if ($params = $this->route($this->getRouteSubject())) {
				$this->setParams($params);
			}
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
		
		// call the controller and action it
		$controller = new $controller($this);
		$controller->action();
		
		// return the controller
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
		foreach ($this->_routes as $route) {
			if ($params = $route->query($subject)) {
				return $params;
			}
		}
		return false;
	}

	/**
	 * Sets a route.
	 * 
	 * @param string $name The name of the route.
	 * @param Europa_Request_Route $route The route to use.
	 * @return Europa_Request
	 */
	public function setRoute($name, Europa_Request_Route $route)
	{
		$this->_routes[$name] = $route;
		return $this;
	}

	/**
	 * Gets a specified route or the route which was matched.
	 * 
	 * @param string $name The name of the route to get.
	 * @return Europa_Request_Route
	 */
	public function getRoute($name = null)
	{
		if ($name) {
			if (isset($this->_routes[$name])) {
				return $this->_routes[$name];
			}
			return null;
		}
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
			if (isset($this->_params[$name])) {
				return $this->_params[$name];
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
			$this->_params[$name] = $value;
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
		return $this->_params;
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
		if ($this->_controllerFormatter) {
			return call_user_func($this->_controllerFormatter, $controller);
		}
		return Europa_String::create($controller)->toClass() . 'Controller';
	}
	
	/**
	 * Sets the formatter that should be used to format the controller class.
	 * 
	 * @param mixed $callback The callback for formatting the controller.
	 * @return Europa_Request
	 */
	public function setControllerFormatter($callback)
	{
		if (!is_callable($callback, true)) {
			throw new Europa_Request_Exception(
				'The specified controller formatter is not valid.',
				Europa_Request_Exception::INVALID_CONTROLLER_FORMATTER
			);
		}
		$this->_controllerFormatter = $callback;
		return $this;
	}
	
	/**
	 * Sets the controller parameter.
	 * 
	 * @param string $controller The controller to set.
	 * @return Europa_Request
	 */
	public function setController($controller)
	{
		return $this->setParam($this->getControllerKey(), $controller);
	}
	
	/**
	 * Returns the controller parameter. This is the value that is passed to the
	 * formatter.
	 * @return string
	 */
	public function getController()
	{
		return $this->getParam($this->getControllerKey());
	}
	
	/**
	 * Sets the controller key to use for retrieving it from the request.
	 * 
	 * @param string $key The key of the controller parameter.
	 * @return Europa_Request
	 */
	public function setControllerKey($newKey)
	{
		// retrieve the current key and controller
		$oldKey = $this->_controllerKey;
		$oldVal = $this->getParam($oldKey);
		
		// set the new key
		$this->_controllerKey = $newKey;
		
		// auto-set the new controller parameter to the old value
		return $this->setParam($newKey, $oldVal);
	}
	
	/**
	 * Retrieves the controller key.
	 * 
	 * @return string
	 */
	public function getControllerKey()
	{
		return $this->_controllerKey;
	}

	/**
	 * Sniffs the passed in method for any parameters existing in the request
	 * and returns the appropriate parameters, in the order which they were
	 * defined in the method. Useful for using in conjunction with
	 * call_user_func_array().
	 * 
	 * If a required parameters is not found, an exception is thrown.
	 * 
	 * @param ReflectionMethod $method The method to use.
	 * @param bool $caseSensitive Whether or not to be case-sensitive or not.
	 * @return array
	 */
	public function getParamsForMethod(ReflectionMethod $method, $caseSensitive = false)
	{
		$methodParams  = array();
		$requestParams = array();
		foreach ($this->getParams() as $name => $value) {
			$name = $caseSensitive ? strtolower($name) : $name;
			$requestParams[$name] = $value;
		}

		// automatically define the parameters that will be passed to the action
		foreach ($method->getParameters() as $param) {
			$pos  = $param->getPosition();
			$name = strtolower($param->getName());

			// apply named parameters
			if (array_key_exists($name, $requestParams)) {
				$methodParams[$pos] = $requestParams[$name];
			// set default values
			} elseif ($param->isOptional()) {
				$methodParams[$pos] = $param->getDefaultValue();
			// throw exceptions when required params aren't defined
			} else {
				throw new Europa_Request_Exception(
					"A required parameter for {$method->getName()} was not defined.",
					Europa_Request_Exception::REQUIRED_METHOD_ARGUMENT_NOT_DEFINED
				);
			}

			// cast the parameter if it is scalar
			if (is_scalar($methodParams[$pos])) {
				$methodParams[$pos] = Europa_String::create($methodParams[$pos])->cast();
			}
		}
		return $methodParams;
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