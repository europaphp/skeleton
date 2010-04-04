<?php

/**
 * @author Trey Shugart
 */

/**
 * The heart of EuropaPHP. This is where it all starts and ends.
 * 
 * @package Europa
 */
class Europa_Request
{
	/**
	 * A child of Europa_View_Abstract which represents the layout.
	 * 
	 * @var $layout
	 */
	protected $_layout;
	
	/**
	 * An child of Europa_View_Abstract which represents the view.
	 * 
	 * @var $view
	 */
	protected $_view;
	
	/**
	 * After dispatching, this will contain the route that was used to reach
	 * the  current page. This can be set before dispatching to force a 
	 * route to be taken.
	 * 
	 * @var $route
	 */
	protected $_route = null;
	
	/**
	 * All routes are set to this property. A route must be an instance of
	 * Europa_Request_Route_Abstract.
	 * 
	 * @var $routes
	 */
	protected $_routes = array();
	
	/**
	 * Contains the instances of all requests that are currently 
	 * dispatching in chronological order.
	 * 
	 * @var $stack
	 */
	private static $_stack = null;
	
	/**
	 * Constructs a new request and sets defaults.
	 * 
	 * @return Europa_Request
	 */
	final public function __construct()
	{
		// retrieve class names
		$layoutClassName = $this->_getLayoutClassName();
		$viewClassName   = $this->_getViewClassName();

		// initialize layout and viewÃ¥
		$this->_layout = new $layoutClassName();
		$this->_view   = new $viewClassName();
	}
	
	/**
	 * Fires dispatching.
	 * 
	 * @param bool $register Whether or not to register this instance in
	 * the stack.
	 * @return Europa_Request
	 */
	final public function dispatch($register = true)
	{
		// we add this dispatch instance to the stack if it is to be registered
		if ($register) {
			self::$_stack[] = $this;
		}
		
		// if a route is pre-defined, auto-match to define params
		if ($this->_route) {
			$this->_route->match(self::getRequestUri());
		}
		
		// if the route wasn't already set, find one and set it
		if (!$this->_route) {
			foreach ($this->_routes as $name => $route) {
				if ($route->match(self::getRequestUri())) {
					$this->_route = $route;
					
					break;
				}
			}
		}
		
		// if a route still wasn't found, provide a default
		if (!$this->_route) {
			$this->_route = $this->_getDefaultRoute();
			$this->_route->match(self::getRequestUri());
		}
		
		// set the controller and action names, and the layout and view
		$controllerName = $this->_getControllerClassName();
		$actionName     = $this->_getActionMethodName();
		
		// reverse engineer the controller
		$controllerReflection = new ReflectionClass($controllerName);
		
		// instantiate the controller
		$controllerInstance = $controllerReflection->newInstanceArgs();
		
		// call the init method, like __construct, but set properties are now available
		if ($controllerReflection->hasMethod('init')) {
			// the return value of the layout determines the action taken on the layout
			$initResult = $controllerInstance->init();
			
			// if init() returns false, the layout is disabled
			if ($initResult === false) {
				$this->_layout = null;
			// otherwise it is assumed to be an array of properties for the layout
			} else {
				foreach ((array) $initResult as $k => $v) {
					$this->_layout->$k = $v;
				}
			}
		}
		
		// generate values for the parameters in the action
		// named parameters will be set to their corresponding names as defined
		// in the action non-named parameters will be set according to their 
		// index required parameters must be set, or an exception will be thrown
		if ($controllerReflection->hasMethod($actionName)) {
			$actionReflection = $controllerReflection->getMethod($actionName);
			$actionParams     = array();
			$routeParams      = array();
			
			// make route paramters case insensitive
			foreach ($this->_route->getParams() as $name => $value) {
				$routeParams[strtolower($name)] = $value;
			}
			
			// automatically define the parameters that will be passed to the 
			// action
			foreach ($actionReflection->getParameters() as $paramIndex => $param) {
				$pos  = $param->getPosition();
				$name = strtolower($param->getName());
				
				// apply named parameters
				if (array_key_exists($name, $routeParams)) {
					$actionParams[$pos] = $routeParams[$name];
				// set default values
				} elseif ($param->isOptional()) {
					$actionParams[$pos] = $param->getDefaultValue();
				// throw exceptions when required params aren't defined
				} else {
					throw new Europa_Request_Exception(
						'Required request parameter <strong>$'
						. $name 
						. '</strong> for <strong>' 
						. $controllerName
						. '->' 
						. $actionName
						. '()</strong> is not defined.',
						Europa_Request_Exception::REQUIRED_PARAMETER_NOT_DEFINED
					);
				}
				
				// cast the parameter
				$actionParams[$pos] = Europa_String::create($actionParams[$pos])->cast();
			}
			
			// the return value from the action determines the action taken on 
			// the view
			$actionResult = $actionReflection->invokeArgs(
				$controllerInstance, 
				$actionParams
			);
			
			// returning false in the action terminates the view
			if ($actionResult === false) {
				$this->_view = null;
			// otherwise it is assumed to be an array of properties to apply to
			// the view
			} else {
				foreach ((array) $actionResult as $k => $v) {
					$this->_view->$k = $v;
				}
			}
		} elseif ($controllerReflection->hasMethod('__call')) {
			$controllerInstance->$actionName();
		} else {
			throw new Europa_Request_Exception(
				'Action <strong>' 
				. $actionName
				. '</strong> does not exist in <strong>' 
				. $controllerName
				. '</strong> and it was not trapped in <strong>__call</strong>.',
				Europa_Request_Exception::ACTION_NOT_FOUND
			);
		}
		
		// call a pre-rendering hook if it exists
		if ($controllerReflection->hasMethod('preRender')) {
			$controllerInstance->preRender();
		}
		
		// set the default layout script name if it hasn't been set yet
		if ($this->_layout && !$this->_layout->getScript()) {
			$this->_layout->setScript($this->_getLayoutScriptName());
		}

		// set the default view script name if it hasn't been set yet
		if ($this->_view && !$this->_view->getScript()) {
			$this->_view->setScript($this->_getViewScriptName());
		}
		
		// layout ouput assumes the view is output in it
		if ($this->_layout) {
			echo $this->_layout->__toString();
		// if the layout is disabled, we render the view
		} elseif ($this->_view) {
			echo $this->_view->__toString();
		}
		
		// call a post-rendering hook if it exists
		if ($controllerReflection->hasMethod('postRender')) {
			$controllerInstance->postRender();
		}
		
		// now we remove it from the dispatch stack if it is registered
		if ($register) {
			unset(self::$_stack[count(self::$_stack) - 1]);
		}
	}
	
	/**
	 * Sets the layout.
	 * 
	 * @param Europa_View_Abstract $layout The layout to use.
	 * @return Europa_Request
	 */
	final public function setLayout(Europa_View_Abstract $layout = null)
	{
		$this->_layout = $layout;
		
		return $this;
	}
	
	/**
	 * Gets the set layout.
	 * 
	 * @return Europa_View_Abstract|null
	 */
	final public function getLayout()
	{
		return $this->_layout;
	}
	
	/**
	 * Sets the view.
	 * 
	 * @param Europa_View_Abstract $view The view to use.
	 * @return Europa_Request
	 */
	final public function setView(Europa_View_Abstract $view = null)
	{
		$this->_view = $view;
		
		return $this;
	}
	
	/**
	 * Gets the set view.
	 * 
	 * @return Europa_View_Abstract|null
	 */
	final public function getView()
	{
		return $this->_view;
	}
	
	/**
	 * Sets a route.
	 * 
	 * @param Europa_Request_Route_Abstract|name $name The name of the route,
	 * or instance of Europa_Request_Route_Abstract.
	 * @param Europa_Request_Route_Abstract $route The route to use, if not
	 * explicity setting through the $name argument.
	 * @return Europa_Request
	 */
	final public function setRoute($name, Europa_Request_Route_Abstract $route = null)
	{
		if ($name instanceof Europa_Request_Route_Abstract) {
			$this->_route = $name;
		} else {
			$this->_routes[$name] = $route;
		}
		
		return $this;
	}
	
	/**
	 * Gets a specified route or the route which was matched.
	 * 
	 * @param string $name The name of the route to get.
	 * @return Europa_Request_Route_Abstract
	 */
	final public function getRoute($name = null)
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
	 * Provides a default Europa_Request_Route_Abstract if no route is matched
	 * during dispatching.
	 * 
	 * @return Europa_Request_Route_Abstract
	 */
	protected function _getDefaultRoute()
	{
		return new Europa_Request_Route_Regex(
			'.*',
			null,
			'?controller=:controller&action=:action'
		);
	}
	
	/**
	 * Returns the formatted controller name that should be instantiated.
	 * 
	 * @return string
	 */
	protected function _getControllerClassName()
	{
		$controller = $this->_route->getParam('controller', 'index');
		
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
	protected function _getActionMethodName()
	{
		$action = $this->_route->getParam('action', 'index');
		
		return Europa_String::create($action)
		       ->camelCase()
		       ->__toString()
		     . 'Action';
	}

	/**
	 * Returns the name of the class to be used for the layout.
	 *
	 * @return string
	 */
	protected function _getLayoutClassName()
	{
		return 'Europa_View_Php';
	}

	/**
	 * Returns the name of the class to be used for the view.
	 *
	 * @return string
	 */
	protected function _getViewClassName()
	{
		return 'Europa_View_Php';
	}
	
	/**
	 * Returns the layout script to be set. By default this is mapped to the
	 * camel-cased name of the controller route parameter.
	 * 
	 * @return string
	 */
	protected function _getLayoutScriptName()
	{
		$controller = $this->_route->getParam('controller', 'index');
		
		return Europa_String::create($controller)->camelCase(false);
	}
	
	/**
	 * Returns the view script to be set. By default this is mapped to the
	 * camel-cased name of the controller as the directory and the camel-cased
	 * action name as the file.
	 * 
	 * @return string
	 */
	protected function _getViewScriptName()
	{
		$route      = $this->getRoute();
		$controller = $route->getParam('controller', 'index');
		$action     = $route->getParam('action', 'index');
		
		return Europa_String::create($controller)->camelCase(false)
		     . '/' 
		     . Europa_String::create($action)->camelCase(false);
	}
	
	/**
	 * Returns the Europa root URI in relation to the file that dispatched
	 * the controller.
	 * 
	 * If running from CLI, '.' will be returned.
	 *
	 * @return string
	 */
	final public static function getRootUri()
	{
		static $rootUri;

		if (!isset($rootUri)) {
			$rootUri = trim(dirname($_SERVER['PHP_SELF']), '/');
		}

		return $rootUri;
	}

	/**
	 * Returns the Europa request URI in relation to the file that dispatched
	 * the controller.
	 * 
	 * If the running from CLI, then false will be returned.
	 *
	 * @return string
	 */
	final public static function getRequestUri()
	{
		static $requestUri;

		if (!isset($requestUri)) {
			// remove the root uri from the request uri to get the relative
			// request uri for the framework
			$requestUri = isset($_SERVER['HTTP_X_REWRITE_URL'])
			            ? $_SERVER['HTTP_X_REWRITE_URL']
				        : $_SERVER['REQUEST_URI'];
			$requestUri = trim($requestUri, '/');
			$requestUri = substr($requestUri, strlen(self::getRootUri()));
			$requestUri = trim($requestUri, '/');
		}

		return $requestUri;
	}
	
	/**
	 * Returns all of the request headers as an array.
	 * 
	 * The header names are formatted to appear as normal, not all uppercase
	 * as in the $_SERVER super-global.
	 * 
	 * @return array
	 */
	final public static function getRequestHeaders()
	{
		static $server;
		
		if (!isset($server)) {
			foreach ($_SERVER as $name => $value) {
				if (substr($name, 0, 5) === 'HTTP_') {
					$name = substr($name, 5);
					$name = strtolower($name);
					$name = str_replace('_', ' ', $name);
					$name = ucwords($name);
					$name = str_replace(' ', '-', $name);
					
					$server[$name] = $value;
				}
			}
		}
		
		return $server;
	}
	
	/**
	 * Returns the value of a single request header or null if not found.
	 * 
	 * @param string $name The name of the request header to retrieve.
	 * @return string
	 */
	final public static function getRequestHeader($name)
	{
		$headers = self::getRequestHeaders();
		
		if (isset($headers[$name])) {
			return $headers[$name];
		}
		
		return null;
	}
	
	/**
	 * Returns the Europa_Request instance that is currently dispatching.
	 * 
	 * @return mixed
	 */
	final public static function getActiveInstance()
	{
		$len = count(self::$_stack);
		
		// if there are dispatched instances, then return the latest one
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
	final public static function getStack()
	{
		return self::$_stack;
	}
}