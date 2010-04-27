<?php

/**
 * The heart of EuropaPHP. This is where it all starts and ends.
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
	 * A child of Europa_View_Abstract which represents the layout.
	 * 
	 * @var Europa_View
	 */
	protected $_layout = null;
	
	/**
	 * An child of Europa_View_Abstract which represents the view.
	 * 
	 * @var Europa_View
	 */
	protected $_view = null;
	
	/**
	 * The route that was matched during dispatching.
	 * 
	 * @var Europa_Request_Route
	 */
	protected $_route = null;
	
	/**
	 * All routes are set to this property. A route must be an instance of
	 * Europa_Request_Route.
	 * 
	 * @var array
	 */
	protected $_routes = array();
	
	/**
	 * Contains the instances of all requests that are currently 
	 * dispatching in chronological order.
	 * 
	 * @var array
	 */
	private static $_stack = array();
	
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
	 * Renders the layout and view or any combination of the two depending on
	 * if they are enabled/disabled.
	 * 
	 * @return string
	 */
	public function __toString()
	{
		$layout = $this->getLayout();
		$view   = $this->getView();
		
		// set default scripts if not set
		if ($layout && !$layout->getScript()) {
			$layout->setScript($this->getLayoutScriptName());
		}
		if ($view && !$view->getScript()) {
			$view->setScript($this->getViewScriptName());
		}
		
		// render
		if ($layout && $view) {
			return (string) $layout;
		} elseif ($view) {
			return (string) $view;
		}
		
		return null;
	}
	
	/**
	 * Sets the layout.
	 * 
	 * @param Europa_View $layout The layout to use.
	 * @return Europa_Request
	 */
	public function setLayout(Europa_View $layout = null)
	{
		$this->_layout = $layout;
		return $this;
	}
	
	/**
	 * Gets the set layout.
	 * 
	 * @return Europa_View_Abstract|null
	 */
	public function getLayout()
	{
		return $this->_layout;
	}
	
	/**
	 * Sets the view.
	 * 
	 * @param Europa_View $view The view to use.
	 * @return Europa_Request
	 */
	public function setView(Europa_View $view = null)
	{
		$this->_view = $view;
		return $this;
	}
	
	/**
	 * Gets the set view.
	 * 
	 * @return Europa_View_Abstract|null
	 */
	public function getView()
	{
		return $this->_view;
	}
	
	/**
	 * Returns the layout script to be set. By default this is mapped to the
	 * camel-cased name of the controller route parameter.
	 * 
	 * @return string
	 */
	public function getLayoutScriptName()
	{
		$controller = $this->getParam('controller', 'index');
		return Europa_String::create($controller)->camelCase(false);
	}
	
	/**
	 * Returns the view script to be set. By default this is mapped to the
	 * camel-cased name of the controller as the directory and the camel-cased
	 * action name as the file.
	 * 
	 * @return string
	 */
	public function getViewScriptName()
	{
		$controller = $this->getParam('controller', 'index');
		$action     = $this->getParam('action', 'index');
		return Europa_String::create($controller)->camelCase(false)
		     . '/' 
		     . Europa_String::create($action)->camelCase(false);
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
	 * If running from CLI, '.' will be returned.
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
	 * If the running from CLI, then false will be returned.
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