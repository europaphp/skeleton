<?php

/**
 * A route class used for matching via regular expressions.
 * 
 * @category Routing
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart
 * @link     http://europaphp.org/license
 */
final class Europa_Router_Route_Regex extends Europa_Router_Route
{
	/**
	 * The expression used to match the route.
	 * 
	 * @var string
	 */
	protected $_expression;
	
	/**
	 * Since it is very difficult to reverse engineer a regular expression
	 * a reverse engineering string is used to reverse engineer the route
	 * back into a URI. This allows for fluid links.
	 * 
	 * @var string
	 */
	protected $_map;
	
	/**
	 * Constructs the route and sets required properties.
	 * 
	 * @param string $expression The expression for route matching/parsing.
	 * @param array $map The string to use when reverse engineering the expression.
	 * @return Europa_Route
	 */
	public function __construct($expression, $map = null)
	{
		$this->_expression = $expression;
		$this->_map        = $map;
	}
	
	/**
	 * Reverse engineers the current route to produce a formatted string.
	 * 
	 * @param array $params The parameters used to reverse engineer the route.
	 * @return string
	 */
	public function reverse(array $params = array())
	{
		$parsed = $this->_map;
		foreach ($params as $name => $value) {
			$parsed = str_replace(':' . $name, $value, $parsed);
		}
		return $parsed;
	}
	
	/**
	 * Matches the passed subject to the route. Can be extended to provide a
	 * custom routing algorithm. Returns the matched parameters.
	 * 
	 * @param string $subject The URI to match against the current route definition.
	 * @return array|bool
	 */
	public function query($subject)
	{
		$subject = (string) $subject;
		if (preg_match('#' . $this->_expression . '#', $subject, $matches)) {
			array_shift($matches);
			$params = array();
			foreach ($matches as $name => $value) {
				if (is_string($name)) {
					$params[$name] = $value;
				}
			}
			return $params;
		}
		return false;
	}
}