<?php

/**
 * @package    Europa
 * @subpackage View
 */

/**
 * Renders view scripts. Used to render both the layout and view in Europa.
 */
class Europa_View
{
	const 
		/**
		 * Thrown when a view could not be found. Does not get thrown until a 
		 * view is rendered.
		 */
		EXCEPTION_VIEW_NOT_FOUND = 4,
		
		/**
		 * The exception that is thrown when a helper was called but could not 
		 * be found.
		 */
		EXCEPTION_HELPER_NOT_FOUND = 5,
		
		/**
		 * Thrown when the script hasn't be set before being rendered.
		 */
		EXCEPTION_SCRIPT_NOT_SET = 6;
	
	protected
		/**
		 * Contains the parameters set on the view.
		 * 
		 * @var array $_params
		 */
		$_params = array(),
		
		/**
		 * The script that will be rendered. Set using Europa_View::render().
		 * 
		 * @var string $_script
		 */
		$_script = null,
		
		/**
		 * Holds references to all of the plugins that have been called on this
		 * view instance which are to be treated as singleton plugins for this
		 * instance only.
		 * 
		 * @var array $_plugins
		 */
		$_plugins = array();
	
	/**
	 * Construct the view and sets defaults.
	 * 
	 * @param string $script The script to render.
	 * @param array  $params The arguments to pass to the script.
	 * 
	 * @return Europa_View
	 */
	public function __construct($script = null, $params = array())
	{
		// set a script if defined
		$this->setScript($script);
		
		// and set arguments
		$this->_params = $params;
	}
	
	/**
	 * Similar to calling a plugin via Europa_View->__call(), but treats the
	 * plugin as a singleton and once instantiated, that instance is always
	 * returned for the duration of the Europa_View object's lifespan.
	 * 
	 * @param string $name The name of the plugin to load.
	 * @return object
	 */
	public function __get($name)
	{
		// attempt to grab an argument
		if (isset($this->_params[$name])) {
			return $this->_params[$name];
		}
		
		if (!isset($this->_plugins[$name])) {
			$plugin = $this->__call($name);
			
			if ($plugin) {
				$this->_plugins[$name] = $plugin;
			}
		}
		
		if (isset($this->_plugins[$name])) {
			return $this->_plugins[$name];
		}
		
		return null;
	}
	
	/**
	 * When setting a property, it actually maps to the parameter array.
	 * 
	 * @param string $name
	 * @param mixed $value
	 * @return Europa_View
	 */
	public function __set($name, $value)
	{
		$this->_params[$name] = $value;
	}
	
	/**
	 * Returns whether or not the specified variable is set or not.
	 * 
	 * @param string $name
	 * @return boolean;
	 */
	public function __isset($name)
	{
		return isset($this->_params[$name]);
	}
	
	/**
	 * Unsets the specified variable.
	 * 
	 * @param string $name
	 * @return void;
	 */
	public function __unset($name)
	{
		unset($this->_params[$name]);
	}
	
	/**
	 * Loads a plugin and executes it. Throws an exception if not found.
	 * 
	 * @return mixed
	 */
	public function __call($func, $args = array())
	{
		$class = $this->_formatPluginClassName($func);
		$class = (string) $class;
		
		// if the class can't be loaded, return null
		if (!Europa_Loader::loadClass($class, $this->_getPluginBasePath())) {
			return null;
		}
		
		// istantiate the plugin passing the current view object into the it
		$class = new $class($this);
		
		// then call the plugin method if it exists
		if (method_exists($class, 'init')) {
			return call_user_func_array(array($class, 'init'), $args);
		}
		
		return $class;
	}
	
	/**
	 * Returns the contents of the rendered view.
	 * 
	 * @return string
	 */
	public function __toString()
	{
		// the script must be set before rendering
		if (!$this->getScript()) {
			Europa_Exception::trigger(
				'No script was set to be rendered.'
				, self::EXCEPTION_NO_SCRIPT_SET
			);
		}
		
		$file = $this->_getViewFullPath();
		
		// can't "throw" exceptions in __toString, triggering gets around that
		if (!is_file($file)) {
			Europa_Exception::trigger(
				'View script <strong>' 
				. $file 
				. '</strong> cannot be found'
				, self::EXCEPTION_VIEW_NOT_FOUND
			);
		}
		
		// the newline character just helps to make the source look better ;)
		return $this->_parseViewFile() . "\n";
	}
	
	/**
	 * Sets the script to be rendered.
	 * 
	 * @param String $script The path to the script to be rendered relative 
	 *                       to the view path, excluding the extension.
	 * 
	 * @return Object Europa_View
	 */
	public function setScript($script)
	{
		$this->_script = $script;
		
		return $this;
	}
	
	/**
	 * Returns the script that is going to be rendered.
	 * 
	 * @return string
	 */
	public function getScript()
	{
		return $this->_script;
	}
	
	/**
	 * Provides an easy way to reverse engineer a route for the current
	 * dispatching controller and returns the resulting uri.
	 * 
	 * Allows for fluid URIs. If no route is found matching the passed $uri, 
	 * then the uri is parsed depending on how it is formatted, necessary
	 * modifications are made, then it is returned.
	 * 
	 * @param string $uri The request URI to transform.
	 * 
	 * @return string
	 */
	public function uri($uri, $params = array())
	{
		$controller = Europa_Dispatcher::getActiveInstance();
		$route      = $controller->getRoute($uri);
		
		// if the route was found, reverse engineer it and set it
		if ($route) {
			$uri = $this->uri($route->reverseEngineer($params));
		}
		
		// if a uri is passed, format it
		if ($uri) {
			$uri = ltrim($uri, '/');
		}
		
		if (stripos($uri, 'http://') !== 0) {
			$uri = '/' 
		         . Europa_Dispatcher::getActiveInstance()->getRootUri() 
		         . '/' 
		         . $uri;
		}
		
		// automate the root uri
		return $uri;
	}
	
	/**
	 * Parses and returns the passed file.
	 * 
	 * @return string
	 */
	protected function _parseViewFile()
	{
		// allows us to return the included file as a string
		ob_start();
		
		// include it
		include $this->_getViewFullPath();
		
		// get the output buffer
		return ob_get_clean();
	}
	
	/**
	 * Returns the full path to the base view path.
	 * 
	 * @return string
	 */
	protected function _getViewFullPath()
	{
		return realpath('./app/views/' . $this->getScript() . '.php');
	}
	
	/**
	 * Returns the full path to the view base plugin path.
	 * 
	 * @return string
	 */
	protected function _getPluginBasePath()
	{
		return realpath('./app/plugins');
	}
	
	/**
	 * Returns a plugin class name based on the $name pased in.
	 * 
	 * @param string $name The name of the plugin to get the class name of.
	 * @return string
	 */
	protected function _formatPluginClassName($name)
	{
		return (string) Europa_String::create($name)->camelCase(true) . 'Plugin';
	}
}
