<?php

/**
 * @author Trey Shugart
 */

/**
 * A base class for views in Europa.
 * 
 * @package Europa
 * @subpackage View
 */
abstract class Europa_View_Abstract
{
	/**
	 * Contains the parameters set on the view.
	 * 
	 * @var array $params
	 */
	protected $_params = array();
	
	/**
	 * Holds references to all of the plugins that have been called on this
	 * view instance which are to be treated as singleton plugins for this
	 * instance only.
	 * 
	 * @var array $plugins
	 */
	protected $_plugins = array();
	
	/**
	 * Renders the view in whatever way necessary.
	 * 
	 * @return string
	 */
	abstract public function __toString();
	
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
	 * @return void
	 */
	public function __unset($name)
	{
		unset($this->_params[$name]);
	}
	
	/**
	 * Attempts to load a plugin and executes it. Returns null of not found.
	 * 
	 * @return mixed
	 */
	public function __call($func, $args = array())
	{
		$class = $this->_getPluginClassName($func);
		
		// if unable to load, return null
		if (!Europa_Loader::loadClass($class)) {
			return null;
		}
		
		// reflect and invoke with passed args
		$class = new ReflectionClass($class);
		$class = $class->newInstanceArgs($args);
		
		return $class;
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

		$request = Europa_Request::getActiveInstance();
		$route   = $request->getRoute($uri);
		
		// if the route was found, reverse engineer it and set it
		if ($route) {
			$uri = $route->getUri($params);
		}
		
		// make consistent
		if ($uri) {
			$uri = '/' . ltrim($uri, '/');
		}
		
		// if there is a root uri, add a forward slash to it
		$root = Europa_Request::getRootUri();
		if ($root) {
			$root = '/' . $root;
		}

		// automate
		return $root . $uri;
	}
	
	/**
	 * Returns a plugin class name based on the $name passed in.
	 * 
	 * @param string $name The name of the plugin to get the class name of.
	 * @return string
	 */
	protected function _getPluginClassName($name)
	{
		return (string) Europa_String::create($name)->camelCase(true) . 'Helper';
	}
}
