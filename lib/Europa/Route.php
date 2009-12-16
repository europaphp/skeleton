<?php

/**
 * @author     Trey Shugart
 * @package    Europa
 * @subpackage Route
 */
class Europa_Route
{
	protected
		/**
		 * The regular expression used to match and parse the URI according to
		 * the route defenition.
		 * 
		 * @var $_pattern
		 */
		$_pattern,
		
		/**
		 * The array mapping of the parameter names to be parsed out of the URI
		 * with the pattern in order of appearance.
		 */
		$_map,
		
		/**
		 * Since it is very difficult to reverse engineer a regular expression
		 * a reverse engineering string is used to reverse engineer the route
		 * back into a URI. This allows for fluid links.
		 * 
		 * @var $_reverse
		 */
		$_reverse,
		
		/**
		 * Contains an associative array of the parameters that were parsed out
		 * of the request from the route defenition.
		 * 
		 * @var $_params
		 */
		$_params = array();
	
	/**
	 * Constructs the route and sets required properties.
	 * 
	 * @param string $pattern The regular expression to use for route matching 
	 *                        and parsing.
	 * @param array  $map     The parameter names in order of appearance in the 
	 *                        regex.
	 * @param string $reverse The reverse engineering mapping.
	 * 
	 * @return Europa_Route
	 */
	public function __construct($pattern, $map = array(), $reverse = null)
	{
		// if the first parameter is an array, we assume it's a 
		// parameter mapping
		if (is_array($pattern)) {
			$map     = $pattern;
			$pattern = null;
		}
		
		$this->_pattern = $pattern;
		$this->_map     = (array) $map;
		$this->_reverse = $reverse;
		
		// set default parameters if set
		foreach ($this->_map as $index => $name) {
			if (is_string($index)) {
				$this->_params[$index] = $name;
			}
		}
	}
	
	/**
	 * Matches the passed URI to the route.
	 * 
	 * If it matches, it parses out the parameters, sets appropriate properties 
	 * and returns true. If it doesn't match, it returns false.
	 * 
	 * @param string $uri The URI to match against the current route defenition.
	 * 
	 * @return bool
	 */
	final public function match($uri = null)
	{
		static $hasMatch;
		
		$uri = $uri 
		     ? $uri 
		     : Europa_Controller::getActiveInstance()->getRequestUri();
		
		preg_match('#' . $this->_pattern . '#', $uri, $matches);
		
		if ($matches) {
			// shift off the full match
			array_shift($matches);
			
			// override any default/static parameters if they are set
			foreach ($this->_map as $index => $name) {
				if (array_key_exists($index, $matches)) {
					$this->_params[$name] = $matches[$index];
				}
			}
			
			// merge the parameters and cascade through all request parameters
			$this->_params = array_merge(
				$this->_params,
				$_POST,
				$_GET
			);
			
			return true;
		}
		
		return false;
	}
	
	/**
	 * Reverse engineers the current route to produce a formatted URI. This
	 * allows routes and links to change based on the route name without ever
	 * having to change the link URI's throughout the application.
	 * 
	 * @param array $params Any parameters to substitute for the parameters that
	 *                      were matched in the request.
	 * 
	 * @return string
	 */
	final public function reverseEngineer($params = array())
	{
		$parsed = $this->_reverse;
		$params = array_merge($this->getParams(), $params);
		
		foreach ($params as $name => $value) {
			$parsed = str_replace(':' . $name, $value, $parsed);
		}
		
		return $parsed;
	}
	
	/**
	 * Returns the specified parameter.
	 * 
	 * @param string $name
	 * @param mixed  $defaultValue
	 * 
	 * @return mixed
	 */
	final public function getParam($name, $defaultValue = null)
	{
		return array_key_exists($name, $this->_params)
		       ? $this->_params[$name]
		       : $defaultValue;
	}
	
	/**
	 * Returns all parameters.
	 * 
	 * @return array
	 */
	final public function getParams()
	{
		return $this->_params;
	}
}