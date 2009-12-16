<?php

/**
 * @file
 * 
 * @package    Europa
 * @subpackage Controller
 */



/*
 * Autoloading
 * 
 * Autoloading significantly increases performace, therefore is enabled
 * automatically, making it a requirement for Europa to run.
 * 
 * We first check to see if the Standard PHP Library exists. If it does
 * then it is used for autoloading. If not, then we fallbakc to 
 * __autoload.
 */

require 'Loader.php';

Europa_Loader::addLoadPath(dirname(__FILE__) . '/../');

if (function_exists('spl_autoload_register')) {
	spl_autoload_register(array('Europa_Loader', 'loadClass'));
} else {
	function __autoload($className)
	{
		Europa_Loader::loadClass($className);
	}
}



/*
 * Sets the default exception handler so that a Europa_Exception can be thrown 
 * without being wrapped in a try/catch block.
 */
set_exception_handler(array('Europa_Exception', 'handle'));



/**
 * @class
 * 
 * @name Europa_Controller
 * @desc The heart of EuropaPHP. This is where it all starts and ends.
 */
class Europa_Controller extends Europa_Base
{
	const
		/**
		 * @constant
		 * @event
		 * 
		 * @name EVENT_PRE_DISPATCH
		 * @desc Contains the event name used to trigger the event that gets called
		 *       before dispatching.
		 */
		EVENT_PRE_DISPATCH = 'Europa_Controller.preDispatch',
		
		/**
		 * @constant
		 * @event
		 * 
		 * @name EVENT_ROUTE_MATCHED
		 * @desc Contains the event name used to trigger the event when a route was
		 *       matched.
		 */
		EVENT_ROUTE_MATCHED = 'Europa_Controller.routeMatched',
		
		/**
		 * @constant
		 * @event
		 * 
		 * @name EVENT_ROUTE_NOT_MATCHED
		 * @desc Contains the event name used to trigger the event when a route was
		 *       not matched.
		 */
		EVENT_ROUTE_NOT_MATCHED = 'Europa_Controller.routeNotMatched',
		
		/**
		 * @constant
		 * @event
		 * 
		 * @name EVENT_POST_DISPATCH
		 * @desc Contains the event name used to trigger the event after dispatching
		 *       is complete.
		 */
		EVENT_POST_DISPATCH = 'Europa_Controller.postDispatch',
		
		/**
		 * @constant
		 * @exception
		 * 
		 * @name EXCEPTION_CONTROLLER_NOT_FOUND
		 * @desc The exception/error code that identifies and exception with a controller
		 *       not being found.
		 */
		EXCEPTION_CONTROLLER_NOT_FOUND = 1,
		
		/**
		 * @constant
		 * @exception
		 * 
		 * @name EXCEPTION_ACTION_NOT_FOUND
		 * @desc The exception/error code that identifies and exception with a action
		 *       not being found.
		 */
		EXCEPTION_ACTION_NOT_FOUND = 2;
	
	public
		/**
		 * @property
		 * @public
		 * 
		 * @name rootUri
		 * @desc Contains the Europa root uri. This is the base installation uri of Europa. If your
		 *       application is installed at http://example.com/my/uri/to/my-app/, this would
		 *       contain my/uri/to/my-app.
		 */
		$rootUri = null,
		
		/**
		 * @property
		 * @public
		 * 
		 * @name requestUri
		 * @desc Contains the Europa request uri. If Europa is installed at http://example.com/eu/
		 *       and the URI was http://example.com/eu/my/request/uri/, this would contain my/request/uri.
		 */
		$requestUri = null,
		
		/**
		 * @property
		 * @public
		 * 
		 * @name route
		 * @desc After dispatching, this will contain the route that was used to reach the 
		 *       current page. This can be set before dispatching to force a route to be
		 *       taken.
		 */
		$route = null,
		
		/**
		 * @property
		 * @public
		 * 
		 * @name routes
		 * @desc This is where routes are set and it should contain all routes that are to 
		 *       be matched against. The array keys will be regular expressions and the values
		 *       will be the replacements for the regular expression as if you were using
		 *       preg_replace($routeKey, $routeVal).
		 */
		$routes = array(),
		
		/**
		 * @property
		 * @public
		 * 
		 * @name controller
		 * @desc This will contain the controller that was found using the matched route.
		 *       This can be set before dispatching to override the default controller
		 *       name.
		 */
		$controller = 'index',
		
		/**
		 * @property
		 * @public
		 * 
		 * @name action
		 * @desc Similar to Europa_Controller::$controller, this can also be set 
		 *       pre-dispatch to set the default action to route to. This will contain the action 
		 *       that was found using the matched route.
		 */
		$action = 'index',
		
		/**
		 * @property
		 * @public
		 * 
		 * @name params
		 * @desc Contains an array of parameters that were parsed from the request uri.
		 */
		$params = array(),
		
		/**
		 * @property
		 * @public
		 * 
		 * @name layout
		 * @desc Contains the layout object that will be rendered. Layout variables are set
		 *       using $this->layout->varName inside of a controller. To disable 
		 *       the layout you can set this to null.
		 */
		$layout = null,
		
		/**
		 * @property
		 * @public
		 * 
		 * @name view
		 * @desc Similar to Europa_Controller::$layout, Europa_Controller::view 
		 *       contains the view object that will be rendered. To disable the view, you can 
		 *       set this to null.
		 */
		$view = null;
	
	protected
		/**
		 * @property
		 * @protected
		 * 
		 * @name _config
		 * @desc Overrides the Europa_Base configuration and gives a default configuration to
		 *       Europa_Controller.
		 */
		$_config = array(
				'controllerPath' => './app/controllers'
			);
		
	static private
		/**
		 * @property
		 * @private
		 * @static
		 * 
		 * @name _dispatchStack
		 * @desc Contains the instances of all controllers that are currently dispatching in chronological order.
		 */
		$_dispatchStack = null;
	
	
	
	/**
	 * @method
	 * @magic
	 * 
	 * @name __construct
	 * @desc Constructs the parent and sets good defaults, meaning less configuration.
	 * 
	 * @param Array[Optional] $config - An array of configuration variables.
	 * 
	 * @return Object Europa_Controller
	 */
	public function __construct($config = null)
	{
		parent::__construct($config);
		
		// set the rootUri, this can be overridden
		$this->rootUri = trim(dirname($_SERVER['PHP_SELF']), '/');
		
		// remove the root uri from the request uri to get the relative request uri for the framework
		$uri = isset($_SERVER['HTTP_X_REWRITE_URL']) ? $_SERVER['HTTP_X_REWRITE_URL'] : $_SERVER['REQUEST_URI'];
		$uri = trim($uri, '/');
		$uri = substr($uri, strlen($this->rootUri));
		$uri = trim($uri, '/');
		$uri = explode('?', $uri);
		$uri = $uri[0];
		
		// the page we are on
		$this->requestUri = $uri;
		
		// set a default layout
		$this->layout = new Europa_View(array(
				'viewPath' => './app/layouts'
			));
		
		// set a default view
		$this->view = new Europa_View;
		
		// set default layout
		$this->layout->render('index');
		
		// set the default view to render
		$this->view->render('index/index');
		
		// so we can refer to the view from inside the layout
		$this->layout->view = $this->view;
	}
	
	/**
	 * @method
	 * @public
	 * 
	 * @name dispatch
	 * @desc Fires dispatching 
	 * 
	 * @param String[Optional] $route - A custom route to pass to the dispatch call. This will override any of the set routes.
	 * 
	 * @return Object Europa_Controller
	 */
	public function dispatch($route = null)
	{
		// fire the pre dispatch event before anything else
		Europa_Event::trigger(self::EVENT_PRE_DISPATCH, array('Europa_Controller' => $this));
		
		// we add this dispatch instance to the stack
		self::$_dispatchStack[] = $this;
		
		// set the route if it was passed
		if ($route) {
			$this->route = $route;
		// if no route was passed, then check to see if it was set via a property
		} elseif (!$this->route) {
			// add a catchall route if it doesn't exist yet
			if (!isset($this->routes['(.*)'])) {
				$this->routes['(.*)'] = '$1';
			}
			
			// route matching and replacing
			// routes are matched in the order in which they are bound
			foreach ($this->routes as $regex => $routeUri) {
				// forward slashes in routes are escaped and starting/ending delimiters
				// are automated
				$regex = '/' . str_replace('/', '\/', $regex) . '/';
				
				if ($route = preg_replace($regex, $routeUri, $this->requestUri, 1)) {
					$this->route = $route;
					
					Europa_Event::trigger(self::EVENT_ROUTE_MATCHED, array('Europa_Controller' => $this));
					
					break;
				}
			}
		}
		
		// if a route was matched, apply it
		if ($this->route) {
			$routes = explode('/', $this->route, 3);
			
			// the controller is the first part of the route
			if (!empty($routes[0])) {
				$this->controller = $routes[0];
			}
			
			// the action is the second
			if (!empty($routes[1])) {
				$this->action = $routes[1];
			}
			
			// and the params are third
			if (!empty($routes[2])) {
				$this->params = explode('/', $routes[2]);
			}
			
			// take out any empty parameters
			if (count($this->params) > 0) {
				foreach ($this->params as $pKey => $pVal) {
					$this->params[$pKey] = self::_typeCast($pVal);
				}
			}
		// if a route is not matched fire the route not matched event and dispatch to the default controller/action
		} else {
			Europa_Event::trigger(self::EVENT_ROUTE_NOT_MATCHED, array('Europa_Controller' => $this));
		}
		
		// set which view to render
		$this->view->render($this->controller . '/' . $this->action);
		
		// set the controller name and action names
		$controllerName = self::_camelCase($this->controller, true) . 'Controller';
		$actionName     = self::_camelCase($this->action, false) . 'Action';
		
		// load the controller file once, using loadClass is ultimately faster than include/require once
		if (!Europa_Loader::loadClass($controllerName, $this->getConfig('controllerPath'))) {
			throw new Europa_Exception('Unable to find controller <strong>' . $controllerName . '</strong>.', self::EXCEPTION_CONTROLLER_NOT_FOUND);
		}
		
		// instantiate the controller using autoload, calls the __construct method
		$controller = new $controllerName;
		
		// so we can refer to the layout in the controller
		$controller->layout = $this->layout;
		
		// so we can refer to the view in the controller
		$controller->view = $this->view;
		
		// another way to access the parameters
		$controller->params = $this->params;
		
		if (method_exists($controller, 'init')) {
			call_user_func_array(array($controller, 'init'), $this->params);
		}
		
		// call the desired action
		if (method_exists($controller, $actionName) || method_exists($controller, '__call')) {
			call_user_func_array(array($controller, $actionName), $this->params);
		} else {
			throw new Europa_Exception('Action <strong>' . $actionName . '</strong> does not exist in <strong>' . $controllerName . '</strong> and it was not trapped in <strong>__call</strong>.', self::EXCEPTION_ACTION_NOT_FOUND);
		}
		
		// pre rendering hook
		if (method_exists($controllerName, 'preRender')) {
			call_user_func_array(array($controller, 'preRender'), $this->params);
		}
		
		// no rendering will occur if layout and view aren't set
		// post rendering hook won't get called if view isn't enabled
		if ($controller->view instanceof Europa_View) {
			// if the layout is set, echo it, otherwise just echo the view
			if ($controller->layout instanceof Europa_View) {
				echo $controller->layout;
			} else {
				echo $controller->view;
			}
			
			// post rendering hook
			if (method_exists($controllerName, 'postRender')) {
				call_user_func_array(array($controller, 'postRender'), $this->params);
			}
			
			// unset it, calling the __destruct method
			unset($controller);
		}
		
		// now we remove it from the dispatch stack
		unset(self::$_dispatchStack[count(self::$_dispatchStack) - 1]);
		
		// fire the post dispatch event after everything is done
		Europa_Event::trigger(self::EVENT_POST_DISPATCH, array('Europa_Controller' => $this));
	}
	
	
	
	/**
	 * @method
	 * @static
	 * @public
	 * 
	 * @name getActiveInstance
	 * @desc Returns the Europa_Controller instance that is currently dispatching.
	 * 
	 * @return Mixed
	 */
	static public function getActiveInstance()
	{
		$len = count(self::$_dispatchStack);
		
		// if there are dispatched instances, then return the latest one
		if ($len) {
			return self::$_dispatchStack[$len - 1];
		}
		
		return null;
	}
	
	/**
	 * @method
	 * @static
	 * @public
	 * 
	 * @name getDispatchStack
	 * @desc Returns all Europa_Controller instances that are dispatching, in chronological order, as an Array.
	 * 
	 * @return Array
	 */
	static public function getDispatchStack()
	{
		return self::$_dispatchStack;
	}
	
	
	
	/**
	 * @method
	 * @static
	 * @private
	 * 
	 * @name _normalizePath
	 * @desc Normalizes a path making directory separators consistent.
	 * 
	 * @param String $path - The path to normalize.
	 * 
	 * @return String
	 */
	static private function _normalizePath($path)
	{
		return str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
	}
	
	/**
	 * @method
	 * @static
	 * @private
	 * 
	 * @name _camelCase
	 * @desc Camelcases a string to Europa Conventions.
	 * 
	 * @param String            $str     - The string to camelcase.
	 * @param Boolean[Optional] $ucFirst - Whether or not to capitalize the first letter.
	 * 
	 * @return String
	 */
	static private function _camelCase($str, $ucFirst = false)
	{
		// if a forward slash is passed, auto ucfirst
		$autoUcFirst = strpos($str, '/') !== false;
		
		$str   = urldecode($str);
		$str   = trim($str, '/');
		$parts = explode('/', $str);
		
		foreach ($parts as $k => $v) {
			$subParts = preg_split('/[^a-zA-Z0-9]/', $v);
			
			foreach ($subParts as $kk => $vv) {
				$subParts[$kk] = ucfirst($vv);
			}
			
			$parts[$k] = implode('', $subParts);
		}
		
		$str = implode('_', $parts);
		
		if ($autoUcFirst || $ucFirst) {
			$str = ucfirst($str);
		} else {
			$str{0} = strtolower($str{0});
		}
		
		return $str;
	}
	
	/**
	 * @method
	 * @static
	 * @private
	 * 
	 * @name _typeCast
	 * @desc Takes a value and type casts it. Strings such as 'true' or 'false' will be converted to
	 *       a boolean value. Numeric strings will be converted to integers or floats and empty
	 *       strings are converted to NULL values.
	 *       
	 * @param Mixed $val - The variable to cast.
	 * 
	 * @return Mixed
	 */
	static private function _typeCast($val)
	{
		if ($val == 'true') {
			return true;
		}
		
		if ($val == 'false') {
			return false;
		}
		
		if (is_string($val) && is_numeric($val)) {
			return strpos($val, '.') === false
				? (int) $val
				: (float) $val;
		}
		
		if ($val == '') {
			return null;
		}
		
		return $val;
	}
}