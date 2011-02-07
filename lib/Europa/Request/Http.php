<?php

namespace Europa\Request;

/**
 * The request class representing an HTTP request.
 * 
 * @category Request
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Http extends \Europa\Request
{
    /**
     * The OPTIONS method.
     * 
     * @var string
     */
    const OPTIONS = 'options';
    
    /**
     * The GET method.
     * 
     * @var string
     */
    const GET = 'get';
    
    /**
     * The HEAD method.
     * 
     * @var string
     */
    const HEAD = 'head';
    
    /**
     * The POST method.
     * 
     * @var string
     */
    const POST = 'post';
    
    /**
     * The PUT method.
     * 
     * @var string
     */
    const PUT = 'put';
    
    /**
     * The DELETE method.
     * 
     * @var string
     */
    const DELETE = 'delete';
    
    /**
     * The TRACE method.
     * 
     * @var string
     */
    const TRACE = 'trace';
    
    /**
     * The CONNECT method.
     * 
     * @var string
     */
    const CONNECT = 'connect';
    
    /**
     * Sets any defaults that may need setting.
     * 
     * @return \Europa\Request\Http
     */
    public function __construct()
    {
        $this->setParams($_REQUEST);
    }
    
    /**
     * Returns the request method from the server vars and formats it. Defaults to "get".
     * It also allows the use of an "HTTP_X_HTTP_METHOD_OVERRIDE" header which can be
     * used to override default request methods. Generally this is bad practice, but
     * certain clients do no support certain methods in the HTTP specification such as
     * Flash.
     *
     * @return string
     */
    public static function method()
    {
        if (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
            $method = $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];
        } elseif (isset($_SERVER['REQUEST_METHOD'])) {
            $method = $_SERVER['REQUEST_METHOD'];
        } else {
            $method = static::GET;
        }
        return strtolower($method);
    }
    
    /**
     * Returns all of the request headers as an array.
     * 
     * The header names are formatted to appear as normal, not all uppercase
     * as in the $_SERVER super-global.
     * 
     * @return array
     */
    public static function headers()
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
     * 
     * @return string
     */
    public static function header($name)
    {
        $headers = static::headers();
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
    public static function accepts($type = null)
    {
        // parse out accept headers
        $accept = static::header('Accept');
        $accept = explode(',', $accept);
        array_walk($accept, 'trim');
        
        // if a type is specified, we check to see if it is accepted
        if ($type) {
            return in_array($type, $accept);
        }
        
        // or return all types
        return $accept;
    }
    
    /**
     * Returns the scheme of the current request. This is either "http" or "https".
     * 
     * @return string
     */
    public static function scheme()
    {
        $scheme = 'http';
        if (static::isSecure()) {
            $scheme .= 's';
        }
        return $scheme;
    }
    
    /**
     * Returns the hostname of the request.
     * 
     * @return string
     */
    public static function host()
    {
        if (isset($_SERVER['HTTP_HOST'])) {
            return $_SERVER['HTTP_HOST'];
        }
        return null;
    }
    
    /**
     * Returns the port the current request came through.
     * 
     * @return int
     */
    public static function port()
    {
        return (int) $_SERVER['SERVER_PORT'];
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
    public static function root()
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
    public static function uri()
    {
        static $requestUri;
        if (!isset($requestUri)) {
            // remove the root uri from the request uri to get the relative
            // request uri for the framework
            $requestUri = isset($_SERVER['HTTP_X_REWRITE_URL'])
                        ? $_SERVER['HTTP_X_REWRITE_URL']
                        : $_SERVER['REQUEST_URI'];
            
            // remove the query string
            $requestUri = explode('?', $requestUri);
            $requestUri = $requestUri[0];
            
            // format the rest
            $requestUri = trim($requestUri, '/');
            $requestUri = substr($requestUri, strlen(static::root()));
            $requestUri = trim($requestUri, '/');
        }
        return $requestUri;
    }
    
    /**
     * Returns the query string in the current request.
     * 
     * @return string
     */
    public static function query()
    {
        return $_SERVER['QUERY_STRING'];
    }
    
    /**
     * Returns the full URI that was used in the request.
     * 
     * @return string
     */
    public static function full()
    {
        $uri = 'http';
        
        // ssl
        if (static::isSecure()) {
            $uri .= 's';
        }
        
        // host
        $uri .= '://' .  static::host();
        
        // port
        $port = static::port();
        if (!$port != 80) {
            $uri .= ':' . $port;
        }
        
        // Europa root uri
        if ($rootUri = static::root()) {
            $uri .= '/' . $rootUri;
        }
        
        // Europa request uri
        if ($requestUri = static::request()) {
            $uri .= '/' . $requestUri;
        }
        
        // query string
        if ($queryString = static::query()) {
            $uri .= '?' . $queryString;
        }
        
        return $uri;
    }
    
    /**
     * Returns whether or not the current request is using SSL.
     * 
     * @return bool
     */
    public static function isSecure()
    {
        return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    }
    
    /**
     * Redirects the request to the specified uri.
     * 
     * @param string $uri The uri to redirect to.
     * 
     * @return void
     */
    public static function redirect($uri)
    {
        header('Location: ' . static::format($uri));
        exit;
    }
    
    /**
     * Formats the passed in URI using the Europa root URI.
     * 
     * @return string
     */
    public static function format($uri)
    {
        // check for full or absolute paths
        if (strpos($uri, 'http://') === 0 || strpos($uri, '/') === 0) {
            return $uri;
        }
        
        // if not, then prepend the root
        return '/' . static::root() . '/' . ltrim($uri, '/');
    }
}