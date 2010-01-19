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
	protected
		/**
		 * Contains the parameters set on the view.
		 * 
		 * @var array $params
		 */
		$params = array(),
		
		/**
		 * The script that will be rendered. Set using Europa_View::render().
		 * 
		 * @var string $script
		 */
		$script = null,
		
		/**
		 * Holds references to all of the plugins that have been called on this
		 * view instance which are to be treated as singleton plugins for this
		 * instance only.
		 * 
		 * @var array $plugins
		 */
		$plugins = array();
	
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
		$this->params = $params;
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
	 * @return void;
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
	 * Returns the contents of the rendered view.
	 * 
	 * @return string
	 */
	public function __toString()
	{
		$script = $this->getScript();

		// the script must be set before rendering
		if (!$script) {
			Europa_View_Exception::trigger(
				'No script was set to be rendered.'
				, self::EXCEPTION_NO_SCRIPT_SET
			);
		}
		
		$script = $this->getScriptFullPath($script);
		
		// can't "throw" exceptions in __toString, triggering gets around that
		if (!is_file($script)) {
			Europa_View_Exception::trigger(
				'View script <strong>' 
				. $script
				. '</strong> cannot be found'
				, self::EXCEPTION_VIEW_NOT_FOUND
			);
		}
		
		// the newline character just helps to make the source look better ;)
		return $this->parseScript($script) . "\n";
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
	public function uri($uri, $params = array())
	{
		$uri = trim($uri);

		// if it has a protocol prepended just return it
		if (strpos($uri, '://') !== false) {
			return $uri;
		}

		$controller = Europa_Dispatcher::getActiveInstance();
		$route      = $controller->getRoute($uri);
		
		// if the route was found, reverse engineer it and set it
		if ($route) {
			$uri = $route->reverseEngineer($params);
		}
		
		// make consistent
		if ($uri) {
			$uri = ltrim($uri, '/');
		}

		// automate
		return '/'
			 . Europa_Dispatcher::getRootUri()
			 . '/'
			 . $uri;
	}
	
	/**
	 * Parses and returns the passed file.
	 * 
	 * @return string
	 */
	protected function parseScript($script)
	{
		// allows us to return the included file as a string
		ob_start();
		
		// include it
		include $script;
		
		// get the output buffer
		return ob_get_clean();
	}
	
	/**
	 * Returns the full path to the base view path.
	 * 
	 * @return string
	 */
	protected function getScriptFullPath($script)
	{
		return realpath('./app/views/' . $script . '.php');
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
	 * Returns a plugin class name based on the $name pased in.
	 * 
	 * @param string $name The name of the plugin to get the class name of.
	 * @return string
	 */
	protected function getPluginClassName($name)
	{
		return (string) Europa_String::create($name)->camelCase(true) . 'Plugin';
	}
}
