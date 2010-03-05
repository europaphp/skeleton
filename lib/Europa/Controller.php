<?php

/**
 * @author Trey Shugart
 */

/**
 * The heart of EuropaPHP. This is where it all starts and ends.
 * 
 * @package Europa
 */
class Europa_Controller
{	
	/**
	 * An instance or child of Europa_View which represents the layout.
	 * 
	 * @var $layout
	 */
	protected $layout;
	
	/**
	 * An instance or child of Europa_View which represents the view.
	 * 
	 * @var $view
	 */
	protected $view;
	
	/**
	 * After dispatching, this will contain the route that was used to reach
	 * the  current page. This can be set before dispatching to force a 
	 * route to be taken.
	 * 
	 * @var $route
	 */
	protected $route = null;
	
	/**
	 * All routes are set to this property. A route must be an instance of
	 * Europa_Route.
	 * 
	 * @var $routes
	 */
	protected $routes = array();
	
	/**
	 * Contains the instances of all controllers that are currently 
	 * dispatching in chronological order.
	 * 
	 * @var $stack
	 */
	private static $stack = null;
	
	/**
	 * Constructs the Controller and sets defaults.
	 * 
	 * @return Europa_Controller
	 */
	final public function __construct()
	{
		// retrieve class names
		$layoutClassName = $this->getLayoutClassName();
		$viewClassName   = $this->getViewClassName();

		// initialize layout and viewå
		$this->layout = new $layoutClassName();
		$this->view   = new $viewClassName();
	}
	
	/**
	 * Fires dispatching.
	 * 
	 * @param bool $register Whether or not to register this instance in the stack.
	 * @return Europa_Controller
	 */
	final public function dispatch($register = true)
	{
		// we add this dispatch instance to the stack if it is to be registered
		if ($register) {
			self::$stack[] = $this;
		}
		
		// if the route wasn't already set, find one and set it
		if (!$this->route) {
			foreach ($this->routes as $name => $route) {
				if ($route->match(self::getRequestUri())) {
					$this->route = $route;
					
					break;
				}
			}
		}
		
		// if a route still wasn't found, provide a default
		if (!$this->route) {
			$this->route = $this->getDefaultRoute();
		}
		
		// set the controller and action names, and the layout and view
		$controllerPaths = $this->getControllerPaths();
		$controllerName  = $this->getControllerClassName();
		$actionName      = $this->getActionMethodName();
		
		// load the controller
		if (!Europa_Loader::loadClass($controllerName, $controllerPaths)) {
			throw new Europa_Controller_Exception(
				'Could not load controller <strong>'
				. $controllerName
				. '</strong> from <strong>' 
				. implode(', ', $controllerPaths)
				. '</strong>.'
				, Europa_Controller_Exception::CONTROLLER_NOT_FOUND
			);
		}
		
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
				$this->layout = null;
			}
			// otherwise it is assumed to be an array of properties for the layout
			else {
				foreach ((array) $initResult as $k => $v) {
					$this->layout->$k = $v;
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
			$routeParams      = $this->route->getAllParams();
			
			// automatically define the parameters that will be passed to the 
			// action
			foreach ($actionReflection->getParameters() as $paramIndex => $param) {
				$pos  = $param->getPosition();
				$name = $param->getName();
				
				// cascade from named parameters to index offsets then to 
				// default values if a required parameter isn't defined, an 
				// exception is thrown
				if (array_key_exists($name, $routeParams)) {
					$actionParams[$pos] = $routeParams[$name];
				}
				elseif (array_key_exists($pos, $routeParams)) {
					$actionParams[$pos] = $routeParams[$pos];
				}
				elseif ($param->isOptional()) {
					$actionParams[$pos] = $param->getDefaultValue();
				}
				else {
					throw new Europa_Controller_Exception(
						'Required request parameter <strong>$'
						. $name 
						. '</strong> for <strong>' 
						. $controllerName
						. '->' 
						. $actionName
						. '()</strong> is not set.'
						, Europa_Controller_Exception::REQUIRED_PARAMETER_NOT_DEFINED
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
				$this->view = null;
			// otherwise it is assumed to be an array of properties to apply to
			// the view
			}
			else {
				foreach ((array) $actionResult as $k => $v) {
					$this->view->$k = $v;
				}
			}
		}
		elseif ($controllerReflection->hasMethod('__call')) {
			$controllerInstance->$actionName();
		}
		else {
			throw new Europa_Controller_Exception(
				'Action <strong>' 
				. $actionName
				. '</strong> does not exist in <strong>' 
				. $controllerName
				. '</strong> and it was not trapped in <strong>__call</strong>.'
				, Europa_Controller_Exception::ACTION_NOT_FOUND
			);
		}
		
		// call a pre-rendering hook if it exists
		if ($controllerReflection->hasMethod('preRender')) {
			$controllerInstance->preRender();
		}
		
		// set the default layout script name if it hasn't been set yet
		if ($this->layout && !$this->layout->getScript()) {
			$this->layout->setScript($this->getLayoutScriptName());
		}

		// set the default view script name if it hasn't been set yet
		if ($this->view && !$this->view->getScript()) {
			$this->view->setScript($this->getViewScriptName());
		}
		
		// layout ouput assumes the view is output in it
		if ($this->layout) {
			echo $this->layout;
		}
		// if the layout is disabled, we render the view
		elseif ($this->view) {
			echo $this->view;
		}
		
		// call a post-rendering hook if it exists
		if ($controllerReflection->hasMethod('postRender')) {
			$controllerInstance->postRender();
		}
		
		// now we remove it from the dispatch stack if it is registered
		if ($register) {
			unset(self::$stack[count(self::$stack) - 1]);
		}
	}
	
	/**
	 * Sets the layout.
	 * 
	 * @param Europa_View $layout
	 * @return unknown_type
	 */
	final public function setLayout(Europa_View $layout = null)
	{
		$this->layout = $layout;
		
		return $this;
	}
	
	/**
	 * Gets the set layout.
	 * 
	 * @return Europa_View|null
	 */
	final public function getLayout()
	{
		return $this->layout;
	}
	
	/**
	 * Sets the view.
	 * 
	 * @param Europa_View $view
	 * 
	 * @return Europa_Controller
	 */
	final public function setView(Europa_View $view = null)
	{
		$this->view = $view;
		
		return $this;
	}
	
	/**
	 * Gets the set view.
	 * 
	 * @return Europa_View|null
	 */
	final public function getView()
	{
		return $this->view;
	}
	
	/**
	 * Sets a route.
	 * 
	 * @param Europa_Route $name
	 * @param $route
	 * @return Europa_Controller
	 */
	final public function setRoute($name, Europa_Route $route = null)
	{
		if ($name instanceof Europa_Route) {
			$this->route = $name;
		}
		else {
			$this->routes[$name] = $route;
		}
		
		return $this;
	}
	
	/**
	 * Gets a specified route or the route which was matched.
	 * 
	 * @param $name
	 * @return Europa_Route
	 */
	final public function getRoute($name = null)
	{
		if ($name) {
			if (isset($this->routes[$name])) {
				return $this->routes[$name];
			}
			
			return null;
		}
		
		return $this->route;
	}
	
	/**
	 * Provides a default Europa_Route if no route is matched during dispatching.
	 * 
	 * @return Europa_Route
	 */
	public function getDefaultRoute()
	{
		return new Europa_Route(
			'.*',
			null,
			'?controller=:controller&action=:action'
		);
	}
	
	/**
	 * Returns the formatted path to the controller directory. In relation
	 * to the script that instantiates the Europa_Controller class.
	 * 
	 * @return string
	 */
	protected function getControllerPaths()
	{
		return array('./app/controllers');
	}
	
	/**
	 * Returns the formatted controller name that should be instantiated.
	 * 
	 * @return string
	 */
	protected function getControllerClassName()
	{
		$controller = $this->route->getParam('controller', 'index');
		
		return Europa_String::create($controller)->camelCase(true) . 'Controller';
	}
	
	/**
	 * Returns the formatted action name that should be called.
	 * 
	 * @return string
	 */
	protected function getActionMethodName()
	{
		$action = $this->route->getParam('action', 'index');
		
		return Europa_String::create($action)->camelCase() . 'Action';
	}

	/**
	 * Returns the name of the class to be used for the layout.
	 *
	 * @return string
	 */
	protected function getLayoutClassName()
	{
		return 'Europa_View';
	}

	/**
	 * Returns the name of the class to be used for the view.
	 *
	 * @return string
	 */
	protected function getViewClassName()
	{
		return 'Europa_View';
	}
	
	/**
	 * Returns the layout script to be set. By default this is mapped to the
	 * camel-cased name of the controller route parameter.
	 * 
	 * @return string
	 */
	protected function getLayoutScriptName()
	{
		$controller = $this->route->getParam('controller', 'Index');
		
		return Europa_String::create($controller)->camelCase(true);
	}
	
	/**
	 * Returns the view script to be set. By default this is mapped to the
	 * camel-cased name of the controller as the directory and the camel-cased
	 * action name as the file.
	 * 
	 * @return string
	 */
	protected function getViewScriptName()
	{
		$route      = $this->getRoute();
		$controller = $route->getParam('controller', 'Index');
		$action     = $route->getParam('action', 'index');
		
		return Europa_String::create($controller)->camelCase(true)
		       . '/' 
		       . Europa_String::create($action)->camelCase();
	}
	
	/**
	 * Returns the Europa root URI in relation to the file that dispatched
	 * the controller.
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
			$requestUri = ltrim($requestUri, '/');
			$requestUri = substr($requestUri, strlen(self::getRootUri()));
		}

		return $requestUri;
	}
	
	/**
	 * Returns the Europa_Controller instance that is currently dispatching.
	 * 
	 * @return mixed
	 */
	final public static function getActiveInstance()
	{
		$len = count(self::$stack);
		
		// if there are dispatched instances, then return the latest one
		if ($len) {
			return self::$stack[$len - 1];
		}
		
		return null;
	}
	
	/**
	 * Returns all Europa_Controller instances that are dispatching,
	 * in chronological order, as an array.
	 * 
	 * @return array
	 */
	final public static function getStack()
	{
		return self::$stack;
	}
}