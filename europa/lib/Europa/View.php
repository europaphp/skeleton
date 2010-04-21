<?php

/**
 * A base class for views in Europa.
 * 
 * @category View
 * @package  Europa
 * @author   Trey Shugart
 * @license  (c) 2010 Trey Shugart <treshugart@gmail.com>
 * @link     http://europaphp.org/license
 */
abstract class Europa_View
{
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
	 * 
	 * @return object
	 */
	public function __get($name)
	{
		$plugin      = $this->__call($name);
		$this->$name = $plugin ? $plugin : null;
		
		return $this->$name;
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
		if ($class->hasMethod('__construct')) {
			$class = $class->newInstanceArgs($args);
		} else {
			$class = $class->newInstanceArgs();
		}
		
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
