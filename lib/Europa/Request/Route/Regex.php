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
final class Europa_Request_Route_Regex extends Europa_Request_Route
{
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
	 * @param array $uriMap The string to use when reverse engineering the
	 * $expression in Europa_Reuqest_Route_Regex->getUri().
	 * @return Europa_Route
	 */
	public function __construct($expression, $uriMap = null)
	{
		// set expression using the parent
		parent::__construct($expression);
		
		// set additional parameters
		$this->_uriMap = (string) $uriMap;
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
			
			// bind matched parameters
			foreach ($matches as $name => $value) {
				if (is_string($name)) {
					$this->setParam($name, $value);
				}
			}
			
			return $matches;
		}
		
		return false;
	}
}