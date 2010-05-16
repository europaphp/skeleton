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
	 * A child of Europa_View_Abstract which represents the layout.
	 * 
	 * @var Europa_View
	 */
	protected $_layout = null;
	
	/**
	 * An child of Europa_View_Abstract which represents the view.
	 * 
	 * @var Europa_View
	 */
	protected $_view = null;
	
	/**
	 * Contains the instances of all requests that are currently 
	 * dispatching in chronological order.
	 * 
	 * @var array
	 */
	private static $_stack = array();
	
	/**
	 * Returns the string that the routes will match against.
	 * 
	 * @return string
	 */
	abstract public function getRouteSubject();
	
	/**
	 * Renders the layout and view or any combination of the two depending on
	 * if they are enabled/disabled.
	 * 
	 * @return string
	 */
	public function __toString()
	{
		$layout = $this->getLayout();
		$view   = $this->getView();
		
		// set default scripts if not set
		if ($layout && !$layout->getScript()) {
			$layout->setScript($this->getLayoutScript());
		}
		if ($view && !$view->getScript()) {
			$view->setScript($this->getViewScript());
		}
		
		// render
		if ($layout && $view) {
			return (string) $layout;
		} elseif ($view) {
			return (string) $view;
		}
		
		return '';
	}
	
	/**
	 * Dispatches the request to the appropriate controller/action combo. If
	 * route matching hasn't been done yet, it will be done.
	 * 
	 * @return Europa_Request
	 */
	public function dispatch()
	{
		// register the instance in the stack so it can be easily found
		self::$_stack[] = $this;
		
		/*
		 * If a route hasn't been matched yet, perform matching against the
		 * Europa request uri and set any matched parameters.
		 */
		if (!$this->getRoute()) {
			$params = $this->route();
			if ($params) {
				$this->setParams($params);
			}
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
						"Required request parameter \${$param->getName()} for {$controllerName}->{$actionName}() is"
						. ' not defined',
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
				"Action {$actionName} does not exist in {$this->getControllerClassName()} and it was not trapped in"
				. ' __call.',
				Europa_Request_Exception::ACTION_NOT_FOUND
			);
		}
		
		return $this;
	}
	
	/**
	 * Processes all routes. If a route is matched, the matched parameters are
	 * returned. If no match is found, false is returned.
	 * 
	 * @return bool|array
	 */
	public function route()
	{
		foreach ($this->_routes as $route) {
			$match = $route->match($this->getRouteSubject());
			if ($match) {
				$this->_route = $route;
				return $match;
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
	 * Sets the layout.
	 * 
	 * @param Europa_View $layout The layout to use.
	 * @return Europa_Request
	 */
	public function setLayout(Europa_View $layout = null)
	{
		$this->_layout = $layout;
		return $this;
	}

	/**
	 * Gets the set layout.
	 * 
	 * @return Europa_View_Abstract|null
	 */
	public function getLayout()
	{
		return $this->_layout;
	}

	/**
	 * Sets the view.
	 * 
	 * @param Europa_View $view The view to use.
	 * @return Europa_Request
	 */
	public function setView(Europa_View $view = null)
	{
		$this->_view = $view;
		return $this;
	}

	/**
	 * Gets the set view.
	 * 
	 * @return Europa_View_Abstract|null
	 */
	public function getView()
	{
		return $this->_view;
	}

	/**
	 * Returns the layout script to be set. By default this is mapped to the
	 * camel-cased name of the controller route parameter.
	 * 
	 * @return string
	 */
	public function getLayoutScript()
	{
		$controller = $this->getParam('controller');
		$controller = $controller ? $controller : 'index';
		return Europa_String::create($controller)
		     ->camelCase(true)
		     ->__toString();
	}

	/**
	 * Returns the view script to be set. By default this is mapped to the
	 * camel-cased name of the controller as the directory and the camel-cased
	 * action name as the file.
	 * 
	 * @return string
	 */
	public function getViewScript()
	{
		$action = $this->getParam('action');
		$action = $action ? $action : 'index';
		return $this->getLayoutScript()
		     . '/' 
		     . Europa_String::create($action)
		     ->camelCase(false)
		     ->__toString();
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