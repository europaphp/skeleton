<?php

/**
 * A helper for formatting a passed in URI.
 * 
 * @category Helpers
 * @package  UriHelper
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart
 * @link     http://europaphp.org/license
 */
class UriHelper
{
	/**
	 * The URI that was passed to the helper.
	 * 
	 * @var string
	 */
	private $_uri = null;
	
	/**
	 * The params that were passed to the helper.
	 * 
	 * @var array
	 */
	private $_params = array();
	
	/**
	 * Constructs a new URI and sets the URI to format and the parameters
	 * if getting from a route.
	 * 
	 * @param string $uri The URI to format.
	 * @param array $params The params, if any, to pass to the route.
	 * @return UriHelper
	 */
	public function __construct($uri = null, array $params = array())
	{
		$this->_uri    = trim($uri);
		$this->_params = $params;
	}
	
	/**
	 * Formats the URI and returns it.
	 * 
	 * If the URI has a protocol, then it is not formatted and just returned.
	 * If the URI matches a named route, then the URI is retrieved from the
	 * route and the parameters are used to build the URI. If no protocol is
	 * passed and no route is matched, then the root URI is prepended and it
	 * is properly formatted and returned as an absolute URI.
	 * 
	 * @return string
	 */
	public function __toString()
	{
		$request = Europa_Request_Http::getActiveInstance();
		$uri     = $this->_uri;
		$params  = $this->_params;
		
		// if it has a protocol prepended just return it
		if (strpos($uri, '://') !== false) {
			return $uri;
		}
		// if the route was found, reverse engineer it and set it
		$route = $request->getRoute($uri);
		if ($route) {
			$uri = $route->getUri($params);
		}
		// make consistent
		if ($uri) {
			$uri = '/' . ltrim($uri, '/');
		}
		// if there is a root uri, add a forward slash to it
		$root = Europa_Request_Http::getRootUri();
		if ($root) {
			$root = '/' . $root;
		}
		// automate
		return $root . $uri;
	}
}