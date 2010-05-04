<?php

/**
 * The request class representing an HTTP request.
 * 
 * @category Request
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart
 * @link     http://europaphp.org/license
 */
class Europa_Request_Http extends Europa_Request
{	
	/**
	 * Sets any defaults that may need setting.
	 * 
	 * @return Europa_Request_Http
	 */
	public function __construct()
	{
		$this->setParams($_POST)
		     ->setParams($_GET)
		     ->setLayout(new Europa_View_Php)
		     ->setView(new Europa_View_Php);
	}
	
	/**
	 * Returns the string that routes will be matched against.
	 * 
	 * @return string
	 */
	public function getRouteRequestString()
	{
		return self::getFullUri();
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
	public function getUri($uri = null, $params = array())
	{
		$uri = trim($uri);
		// if it has a protocol prepended just return it
		if (strpos($uri, '://') !== false) {
			return $uri;
		}
		// if the route was found, reverse engineer it and set it
		$route = $this->getRoute($uri);
		if ($route) {
			$uri = $route->getUri($params);
		}
		// make consistent
		if ($uri) {
			$uri = '/' . ltrim($uri, '/');
		}
		// if there is a root uri, add a forward slash to it
		$root = self::getRootUri();
		if ($root) {
			$root = '/' . $root;
		}
		// automate
		return $root . $uri;
	}

	/**
	 * Redirects the client to the specified URI.
	 * 
	 * The URI will always be transformed into a Europa URI unless 
	 * $europaRelative is set to false. The script will automatically
	 * be terminated after the redirect.
	 * 
	 * @param string $uri The URI to redirect to.
	 * @param bool $europaRelative Whether or not to automatically transform
	 * the passed URI into a Europa URI.
	 * @return void
	 */
	public function redirect($uri = '/', $europaRelative = true)
	{
		if ($europaRelative) {
			$uri = $this->uri($uri);
		}
		header('Location: ' . $uri);
		exit;
	}
	
	/**
	 * Returns the Europa root URI in relation to the file that dispatched
	 * the controller.
	 * 
	 * The Europa root URI represents the public folder in which the
	 * dispatching file resides. If the the full URI is 
	 * http://localhost/yoursite/subfoler/controller/action and the
	 * dispatching file is in "subfolder', then this will contain
	 * "yoursite/subfolder". 
	 * 
	 * The root URI is always normalized, meaning that leading and trailing
	 * slashes are trimmed.
	 *
	 * @return string
	 */
	public static function getRootUri()
	{
		static $rootUri;
		if (!isset($rootUri)) {
			$rootUri = trim(dirname($_SERVER['PHP_SELF']), '/');
		}
		return $rootUri;
	}

	/**
	 * Returns the Europa request URI in relation to the file that dispatched
	 * the controller.
	 * 
	 * The Europa request URI represents the part after the public folder in 
	 * which the dispatching file resides. If the the full URI is 
	 * http://localhost/yoursite/subfoler/controller/action and the
	 * dispatching file is in "subfolder', then this will contain
	 * "controller/action". 
	 * 
	 * The request URI is always normalized, meaning that leading and trailing
	 * slashes are trimmed.
	 *
	 * @return string
	 */
	public static function getRequestUri()
	{
		static $requestUri;
		if (!isset($requestUri)) {
			// remove the root uri from the request uri to get the relative
			// request uri for the framework
			$requestUri = isset($_SERVER['HTTP_X_REWRITE_URL'])
			            ? $_SERVER['HTTP_X_REWRITE_URL']
				        : $_SERVER['REQUEST_URI'];
			$requestUri = trim($requestUri, '/');
			$requestUri = substr($requestUri, strlen(self::getRootUri()));
			$requestUri = trim($requestUri, '/');
		}
		return $requestUri;
	}
	
	/**
	 * Returns whether or not the request is being made through SSL.
	 * 
	 * @return bool
	 */
	public static function isSecure()
	{
		return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
	}
	
	/**
	 * Returns the full URI that was used in the request.
	 * 
	 * @return string
	 */
	public static function getFullUri()
	{
		$protocol = 'http';
		if (self::isSecure()) {
			$protocol = 'https';
		}
		$port = null;
		if ($_SERVER['SERVER_PORT'] != 80) {
			$port = ':' . $_SERVER['SERVER_PORT'];
		}
		return $protocol
		     . '://'
		     . $_SERVER['HTTP_HOST']
		     . $port
		     . '/' . self::getRootUri()
		     . '/' . self::getRequestUri();
	}
	
	/**
	 * Returns all of the request headers as an array.
	 * 
	 * The header names are formatted to appear as normal, not all uppercase
	 * as in the $_SERVER super-global.
	 * 
	 * @return array
	 */
	public static function getHeaders()
	{
		static $server;
		if (!isset($server)) {
			foreach ($_SERVER as $name => $value) {
				if (substr($name, 0, 5) === 'HTTP_') {
					$name = substr($name, 5);
					$name = strtolower($name);
					$name = str_replace('_', ' ', $name);
					$name = ucwords($name);
					$name = str_replace(' ', '-', $name);
					$server[$name] = $value;
				}
			}
		}
		return $server;
	}
	
	/**
	 * Returns the value of a single request header or null if not found.
	 * 
	 * @param string $name The name of the request header to retrieve.
	 * @return string
	 */
	public static function getHeader($name)
	{
		$headers = self::getRequestHeaders();
		if (isset($headers[$name])) {
			return $headers[$name];
		}
		return null;
	}
	
	/**
	 * Returns the content types specified in the Accept request header. Each
	 * value is trimmed for consistency, but no further formatting occurs.
	 * 
	 * @return array
	 */
	public static function getAcceptedContentTypes()
	{
		$accept = self::getRequestHeader('Accept');
		$accept = explode(',', $accept);
		array_walk($accept, 'trim');
		return $accept;
	}
}