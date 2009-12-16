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
		EXCEPTION_HELPER_NOT_FOUND = 5;
	
	protected
		$_args = array(),
		
		/**
		 * The script that will be rendered. Set using Europa_View::render().
		 * 
		 * @var $_script
		 */
		$_script = null;
	
	/**
	 * Construct the view and sets defaults.
	 * 
	 * @param string $script The script to render.
	 * @param array  $args   The arguments to pass to the script.
	 * 
	 * @return Europa_View
	 */
	public function __construct($script = null, $args = array())
	{
		// set a script if defined
		$this->setScript($script);
		
		// and set arguments
		$this->_args = array();
	}
	
	/**
	 * Similar to calling a plugin via Europa_View->__call(), but treats the
	 * plugin as a signleton and once instantiated, that instance is always
	 * returned for the duration of the Europa_View object's lifespan.
	 * 
	 * @param string $name The name of the plugin to load.
	 * 
	 * @return object
	 */
	public function __get($name)
	{
		// attempt to grab an argument
		if (isset($this->_args[$name])) {
			return $this->_args[$name];
		}
		
		static $plugins = array();
		
		if (!isset($plugins[$name])) {
			$plugin = $this->__call($name, array($this));
			
			if ($plugin) {
				$plugins[$name] = $plugin;
			}
		}
		
		if (isset($plugins[$name])) {
			return $plugins[$name];
		}
		
		return null;
	}
	
	/**
	 * When setting a property, it actually maps to the parameter array.
	 * 
	 * @param string $name
	 * @param mixed  $value
	 * 
	 * @return Europa_View
	 */
	public function __set($name, $value)
	{
		$this->_args[$name] = $value;
	}
	
	/**
	 * Loads a plugin and executes it. Throws an exception if not found.
	 * 
	 * @return Mixed
	 */
	public function __call($func, $args)
	{
		$class = $this->_getPluginName($func);
		$class = (string) $class;
		
		// if the class can't be loaded, return null
		if (!Europa_Loader::loadClass($class, $this->_getPluginPath())) {
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
		if (!$this->_script) {
			Europa_Exception::trigger(
				'No script was set to be rendered.'
			);
		}
		
		$path      = $this->_getViewPath();
		$extension = $this->_getViewExtension();
		$extension = $extension ? '.' . $extension : '';
		$script    = $this->_script . $extension;
		$file      = realpath($path . DIRECTORY_SEPARATOR . $script);
		
		// since we can't throw an exception in __toString, we have to trigger one
		if (!is_file($file)) {
			Europa_Exception::trigger(
				'View script <strong>' 
				. $script 
				. '</strong> does not exist in <strong>' 
				. $path 
				. '</strong>.', 
				self::EXCEPTION_VIEW_NOT_FOUND
			);
		}
		
		// allows us to return the included file as a string
		ob_start();
		
		// include it
		include $file;
		
		// get the output buffer
		$contents = ob_get_clean();
			
		// and return it;
		// the newline character just helps to make the source look better ;)
		return $contents . "\n";
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
	 * Returns the full path to the base view path.
	 * 
	 * @return string
	 */
	protected function _getViewPath()
	{
		return './app/views';
	}
	
	/**
	 * Returns the extension the views should use.
	 * 
	 * @return string
	 */
	protected function _getViewExtension()
	{
		return 'php';
	}
	
	/**
	 * Returns the full path to the view base plugin path.
	 * 
	 * @return string
	 */
	protected function _getPluginPath()
	{
		return './app/plugins';
	}
	
	/**
	 * Returns a plugin class name based on the $name pased in.
	 * 
	 * @return string
	 */
	protected function _getPluginName($name)
	{
		return (string) Europa_String::create($name)->camelCase(true) . 'Plugin';
	}
}
