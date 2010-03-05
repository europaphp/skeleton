<?php

/**
 * @author Trey Shugart
 */

/**
 * Renders view scripts. Used to render both the layout and view in Europa.
 * 
 * @package Europa
 * @subpackage View
 */
class Europa_View
{
	/**
	 * Contains the parameters set on the view.
	 * 
	 * @var array $params
	 */
	protected $params = array();
	
	/**
	 * The script that will be rendered. Set using Europa_View::render().
	 * 
	 * @var string $script
	 */
	protected $script = null;
	
	/**
	 * Holds references to all of the plugins that have been called on this
	 * view instance which are to be treated as singleton plugins for this
	 * instance only.
	 * 
	 * @var array $plugins
	 */
	protected $plugins = array();
	
	/**
	 * Construct the view and sets defaults.
	 * 
	 * @param string $script The script to render.
	 * @param array $params The arguments to pass to the script.
	 * @return Europa_View
	 */
	public function __construct($script = null, $params = array())
	{
		// set a script if defined
		if ($script) {
			$this->setScript($script);
		}
		
		// and set arguments
		if (is_array($params)) {
			$this->params = $params;
		}
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
		if (isset($this->params[$name])) {
			return $this->params[$name];
		}
		
		if (!isset($this->plugins[$name])) {
			$plugin = $this->__call($name);
			
			if ($plugin) {
				$this->plugins[$name] = $plugin;
			}
		}
		
		if (isset($this->plugins[$name])) {
			return $this->plugins[$name];
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
		$this->params[$name] = $value;
	}
	
	/**
	 * Returns whether or not the specified variable is set or not.
	 * 
	 * @param string $name
	 * @return boolean;
	 */
	public function __isset($name)
	{
		return isset($this->params[$name]);
	}
	
	/**
	 * Unsets the specified variable.
	 * 
	 * @param string $name
	 * @return void
	 */
	public function __unset($name)
	{
		unset($this->params[$name]);
	}
	
	/**
	 * Loads a plugin and executes it. Throws an exception if not found.
	 * 
	 * @return mixed
	 */
	public function __call($func, $args = array())
	{
		$class = $this->getPluginClassName($func);
		$class = (string) $class;
		
		// if the class can't be loaded, return null
		if (!Europa_Loader::loadClass($class, $this->getPluginPaths())) {
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
	 * Parses the view file and returns the result.
	 * 
	 * @return string
	 */
	public function __toString()
	{
		// allows us to return the included file as a string
		ob_start();
		
		// include it
		include $this->getScriptFullPath();
		
		// return the parsed view
		return ob_get_clean() . "\n";
	}
	
	/**
	 * Sets the script to be rendered.
	 * 
	 * @param String $script The path to the script to be rendered relative 
	 * to the view path, excluding the extension.
	 * @return Object Europa_View
	 */
	public function setScript($script)
	{
		$this->script = $script;
		
		return $this;
	}
	
	/**
	 * Returns the script that is going to be rendered.
	 * 
	 * @return string
	 */
	public function getScript()
	{
		return $this->script;
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
	 * @param array $params Any parameters to use when reverse-engineering.
	 * @return string
	 */
	public function uri($uri = null, $params = array())
	{
		$uri = trim($uri);

		// if it has a protocol prepended just return it
		if (strpos($uri, '://') !== false) {
			return $uri;
		}

		$controller = Europa_Controller::getActiveInstance();
		$route      = $controller->getRoute($uri);
		
		// if the route was found, reverse engineer it and set it
		if ($route) {
			$uri = $route->reverseEngineer($params);
		}
		
		// make consistent
		if ($uri) {
			$uri = '/' . ltrim($uri, '/');
		}
		
		// if there is a root uri, add a forward slash to it
		$root = Europa_Controller::getRootUri();
		if ($root) {
			$root = '/' . $root;
		}

		// automate
		return $root . $uri;
	}
	
	/**
	 * Returns the full path to the view including extension.
	 * 
	 * @return string
	 */
	protected function getScriptFullPath()
	{
		return realpath('./app/views/' . $this->script . '.php');
	}
	
	/**
	 * Returns the full path to the view base plugin path.
	 * 
	 * @return string
	 */
	protected function getPluginPaths()
	{
		return array('./app/plugins');
	}
	
	/**
	 * Returns a plugin class name based on the $name passed in.
	 * 
	 * @param string $name The name of the plugin to get the class name of.
	 * @return string
	 */
	protected function getPluginClassName($name)
	{
		return (string) Europa_String::create($name)->camelCase(true) . 'Plugin';
	}
}
