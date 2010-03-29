<?php

/**
 * @author Trey Shugart
 */

/**
 * A route class used for determining url parameters as defined by the route
 * as well as where the dispatcher should direct the application.
 * 
 * @package Europa
 * @subpackage Route
 */
class Europa_Route
{
	/**
	 * The regular expression used to match and parse the URI according to
	 * the route definition.
	 * 
	 * @var string
	 */
	protected $_pattern;
	
	/**
	 * The array mapping of the parameter names to be parsed out of the URI
	 * with the pattern in order of appearance.
	 * 
	 * @var array
	 */
	protected $_map;
	
	/**
	 * Since it is very difficult to reverse engineer a regular expression
	 * a reverse engineering string is used to reverse engineer the route
	 * back into a URI. This allows for fluid links.
	 * 
	 * @var string
	 */
	protected $_reverse;
	
	/**
	 * Contains an associative array of the parameters that were parsed out
	 * of the request from the route definition.
	 * 
	 * @var $params
	 */
	protected $_params = array();
	
	/**
	 * Constructs the route and sets required properties.
	 * 
	 * @param string $pattern The regular expression for route matching/parsing.
	 * @param array $map Parameter mapping.
	 * @param string $reverse The reverse engineering mapping.
	 * @return Europa_Route
	 */
	final public function __construct($pattern, $map = array(), $reverse = null)
	{
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
	 * If it matches, it parses out the parameters and returns true. If it 
	 * doesn't match, it returns false.
	 * 
	 * @param string $uri The URI to match against the current route definition.
	 * @return bool
	 */
	final public function match($uri = null)
	{
		if (!$uri) {
			$uri = Europa_Controller::getActiveInstance()->getRequestUri();
		}
		
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
	 * were matched in the request.
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
	 * Returns the specified parameter. If the parameter isn't found, then the
	 * default value is returned.
	 * 
	 * @param string $name
	 * @param mixed $defaultValue
	 * @return mixed
	 */
	final public function getParam($name, $defaultValue = null)
	{
		$params = $this->getAllParams();
		
		if (array_key_exists($name, $params)) {
			return $params[$name];
		}
		
		return $defaultValue;
	}
	
	/**
	 * Returns all parameters cascading to route params, get, then post.
	 * 
	 * @return array
	 */
	final public function getAllParams()
	{
		static $params;
		
		if (!isset($params)) {
			$params = array_merge($this->_params, $_GET, $_POST);
		}
		
		return $params;
	}
}