<?php

/**
 * @author Trey Shugart
 */

/**
 * A route class used for matching via regular expressions.
 * 
 * @package Europa
 * @subpackage Request
 * @subpackage Route
 */
final class Europa_Request_Route_Regex extends Europa_Request_Route_Abstract
{
	/**
	 * The array mapping of the parameter names to be parsed out of the URI
	 * with the expression in order of appearance.
	 * 
	 * @var array
	 */
	protected $_parameterMap;
	
	/**
	 * Since it is very difficult to reverse engineer a regular expression
	 * a reverse engineering string is used to reverse engineer the route
	 * back into a URI. This allows for fluid links.
	 * 
	 * @var string
	 */
	protected $_uriMap;
	
	/**
	 * Constructs the route and sets required properties.
	 * 
	 * @param string $expression The expression for route matching/parsing.
	 * @param array $map Parameter mapping.
	 * @param string $reverse The reverse engineering mapping.
	 * @return Europa_Route
	 */
	public function __construct($expression, $parameterMap = array(), $uriMap = null)
	{
		// set expression using the parent
		parent::__construct($expression);
		
		// set additional parameters
		$this->_parameterMap = (array)  $parameterMap;
		$this->_uriMap       = (string) $uriMap;
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
	public function getUri($params = array())
	{
		$parsed = $this->_uriMap;
		$params = array_merge($this->getParams(), $params);
		
		foreach ($params as $name => $value) {
			$parsed = str_replace(':' . $name, $value, $parsed);
		}
		
		return $parsed;
	}
	
	/**
	 * Maps the passed in parameters based on their index to the paramter
	 * map given to the route.
	 * 
	 * @param array $params
	 * @return void
	 */
	public function mapParams($params)
	{
		foreach ($this->_parameterMap as $index => $name) {
			if (array_key_exists($index, $params)) {
				$this->setParam($name, $matches[$index]);
			}
		}
	}
	
	/**
	 * Matches the passed URI to the route.
	 * 
	 * Can be extended to provide a custom routing algorithm. Returns the
	 * matched parameters
	 * 
	 * @param string $uri The URI to match against the current route
	 * definition.
	 * @return array|bool
	 */
	public function match($uri)
	{
		preg_match('#' . $this->_expression . '#', $uri, $matches);
		
		if ($matches) {
			// shift off the full match
			array_shift($matches);
			
			return $matches;
		}
		
		return false;
	}
}