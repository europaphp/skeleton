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
	 * Contains the instances of all requests that are currently 
	 * dispatching in chronological order.
	 * 
	 * @var array
	 */
	private static $_stack = array();
	
	/**
	 * Converts the request back into the original string representation.
	 * 
	 * @return string
	 */
	abstract public function __toString();
	
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
	 * Dispatches the request to the appropriate controller/action combo.
	 * Invoking dispatching assumes routing was performed.
	 * 
	 * @return Europa_Controller
	 */
	public function dispatch()
	{
		// register the instance in the stack so it can be easily found
		self::$_stack[] = $this;
		
		// routing information
		$controller = $this->formatController();
		
		// dispatch request
		if (!Europa_Loader::loadClass($controller)) {
			throw new Europa_Request_Exception(
				'Could not load controller ' . $controller . '.',
				Europa_Request_Exception::CONTROLLER_NOT_FOUND
			);
		}
		
		// instantiate the formatted controller
		$controller = new $controller($this);
		
		// make sure it's a valid instance
		if (!$controller instanceof Europa_Controller) {
			throw new Europa_Request_Exception(
				'Class ' . get_class($controller) . ' is not a valid controller instance.'
				. 'Controller classes must derive from Europa_Controller.',
				Europa_Request_Exception::INVALID_CONTROLLER
			);
		}
		
		// action it
		$controller->action();
		
		// execute the rendering process
		$rendered = $controller->__toString();
		
		// remove the dispatch from the stack
		array_pop(self::$_stack);
		
		// return the rendered result
		return $rendered;
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
	 * Clears all parameters from the request. Includes clearing of default values.
	 * 
	 * @return Europa_Request
	 */
	public function clearParams()
	{
		$this->_params = array();
		return $this;
	}
	
	/**
	 * Returns the formatted controller name that should be instantiated.
	 * 
	 * @return string
	 */
	public function formatController()
	{
		if ($this->_controllerFormatter) {
			return call_user_func($this->_controllerFormatter, $this);
		}
		return Europa_String::create($this->getController())->toClass() . 'Controller';
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
	 * 
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