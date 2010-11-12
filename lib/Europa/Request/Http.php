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
        $this->setParams($_REQUEST);
    }
    
    /**
     * Converts the request back into the original string representation.
     * 
     * @return string
     */
    public function __toString()
    {
        return self::getRequestUri();
    }
    
    /**
     * Formats the passed in URI. The URI can be a relative path, absolute or
     * a named route. Whatever is passed in, it will be normalized and 
     * formatted.
     * 
     * @param string $uri The URI to format.
     * @return string
     */
    public function formatUri($uri = null, array $params = array())
    {
        // if it has a protocol prepended just return it
        if (strpos($uri, '://') !== false) {
            return $uri;
        }
        
        // check for a router/route and use it'a parameters if found
        $router = $this->getRouter();
        if ($router && $route = $router->getRoute($uri)) {
            $uri = $route->reverse($params);
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
     * Redirects the client to the specified URI. The URI is formatted using
     * Europa_Request_Http->formatUri().
     * 
     * @param string $uri The URI to redirect to.
     * @return void
     */
    public function redirect($uri = '/', array $params = array())
    {
        header('Location: ' . $this->formatUri($uri, $params));
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
        static $root;
        if (!isset($root)) {
            $path = $_SERVER['DOCUMENT_ROOT'];
            $file = dirname($_SERVER['SCRIPT_FILENAME']);
            $root = substr($file, strlen($path));
            $root = trim($root, '/');
        }
        return $root;
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
            $requestUri = explode('?', $requestUri);
            $requestUri = $requestUri[0];
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
        $headers = self::getHeaders();
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
        $accept = self::getHeader('Accept');
        $accept = explode(',', $accept);
        array_walk($accept, 'trim');
        return $accept;
    }
}