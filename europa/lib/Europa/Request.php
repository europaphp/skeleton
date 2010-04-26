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
	 * superglobals.
	 * 
	 * @var array
	 */
	protected $_params = array();
	
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
	 * Contains the instances of all requests that are currently 
	 * dispatching in chronological order.
	 * 
	 * @var array
	 */
	private static $_stack = array();
	
	/**
	 * Dispatches the request to the appropriate controller/action combo. If
	 * route matching hasn't been done yet, it will be done.
	 * 
	 * @return Europa_Request
	 */
	public function dispatch()
	{
		// register
		self::$_stack[] = $this;
		
		// if a route hasn't been matched yet, perform matching
		if (!$this->getRoute()) {
			$this->route();
		}
		
		// dynamic controllers and actions
		$controllerName       = $this->getControllerClassName();
		$actionName           = $this->getActionMethodName();
		$controllerReflection = new ReflectionClass($controllerName);
		$controller           = $controllerReflection->newInstanceArgs();
		
		// store result of init call
		if ($controllerReflection->hasMethod('init')) {
			$controller->init();
		}
		
		// set parameters
		if ($controllerReflection->hasMethod($actionName)) {
			$actionReflection = $controllerReflection->getMethod($actionName);
			$actionParams     = array();
			$requestParams    = array();
			
			// make request paramters case insensitive
			foreach ($this->_params as $name => $value) {
				$requestParams[strtolower($name)] = $value;
			}
			
			// automatically define the parameters that will be passed to the action
			foreach ($actionReflection->getParameters() as $paramIndex => $param) {
				$pos  = $param->getPosition();
				$name = strtolower($param->getName());
				
				// apply named parameters
				if (array_key_exists($name, $requestParams)) {
					$actionParams[$pos] = $requestParams[$name];
				// set default values
				} elseif ($param->isOptional()) {
					$actionParams[$pos] = $param->getDefaultValue();
				// throw exceptions when required params aren't defined
				} else {
					throw new Europa_Request_Exception(
						"Required request parameter ${$param->getName()} for {$this->getControllerClassName()}->{$actionName}() is"
						. 'not defined.',
						Europa_Request_Exception::REQUIRED_PARAMETER_NOT_DEFINED
					);
				}
				
				// cast the parameter if it is scalar
				if (is_scalar($actionParams[$pos])) {
					$actionParams[$pos] = Europa_String::create($actionParams[$pos])->cast();
				}
			}
			
			// the return value from the action determines the action taken on the view
			$actionReflection->invokeArgs(
				$controller, 
				$actionParams
			);
		} elseif ($controllerReflection->hasMethod('__call')) {
			$controller->$actionName();
		} else {
			throw new Europa_Request_Exception(
				"Action {$actionName} does not exist in {$this->getControllerClassName()} and it was not trapped in __call.",
				Europa_Request_Exception::ACTION_NOT_FOUND
			);
		}
		
		return $this;
	}
	
	/**
	 * Processes all routes. Upon matching, matched parameters are bound to the
	 * request and it returns true. If no match is found, it returns false.
	 * 
	 * @return bool
	 */
	public function route()
	{
		foreach ($this->_routes as $route) {
			$match = $route->match(self::getRequestUri());
			if ($match) {
				$this->_route = $route;
				$this->setParams($match);
				return true;
			}
		}
		return false;
	}

	/**
	 * Sets a route.
	 * 
	 * @param Europa_Request_Route|name $name The name of the route.
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
			return false;
		}
		return $this->_route;
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
	 * Sets a given parameter's value.
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
	 * @param array $params The params to set.
	 * @return Europa_Request
	 */
	public function setParams(array $params)
	{
		foreach ($params as $k => $v) {
			$this->setParam($k, $v);
		}
		return $this;
	}
	
	/**
	 * Returns the formatted controller name that should be instantiated.
	 * 
	 * @return string
	 */
	public function getControllerClassName()
	{
		$controller = $this->getParam('controller', 'index');
		return Europa_String::create($controller)
		       ->camelCase(true)
		       ->__toString()
		     . 'Controller';
	}
	
	/**
	 * Returns the formatted action name that should be called.
	 * 
	 * @return string
	 */
	public function getActionMethodName()
	{
		$action = $this->getParam('action', 'index');
		return Europa_String::create($action)
		       ->camelCase()
		       ->__toString()
		     . 'Action';
	}
	
	/**
	 * Returns the Europa_Request instance that is currently dispatching.
	 * 
	 * @return mixed
	 */
	public static function getActiveInstance()
	{
		$len = count(self::$_stack);
		if ($len) {
			return self::$_stack[$len - 1];
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
		return self::$_stack;
	}
}