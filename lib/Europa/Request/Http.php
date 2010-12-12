<?php

/**
 * The request class representing an HTTP request.
 * 
 * @category Request
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart http://europaphp.org/license
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
        return self::uri();
    }
    
    /**
     * Retuns the request method from the server vars and formats it. Defaults to "get".
     *
     * @return string
     */
    public function method()
    {
        if (isset($_SERVER['REQUEST_METHOD'])) {
            return strtolower($_SERVER['REQUEST_METHOD']);
        }
        return 'get';
    }
    
    /**
     * Returns the protocol part of the request.
     * 
     * @return string
     */
    public static function protocol()
    {
        static $protocol;
        if (!$protocol) {
            $protocol = 'http';
            if ($this->isSecure()) {
                $protocol .= 's';
            }
        }
        return $protocol;
    }
    
    /**
     * Returns the host part of the request.
     * 
     * @return string
     */
    public static function host()
    {
        static $host;
        if (!$host) {
            $host = $_SERVER['HTTP_HOST'];
            if ($_SERVER['SERVER_PORT'] != 80) {
                $host .= ':' . $_SERVER['SERVER_PORT'];
            }
        }
        return $host;
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
            $requestUri = substr($requestUri, strlen(self::root()));
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
    public static function full()
    {
        $uri = '';
        if ($protocol = self::protocol()) {
            $uri .= $protocol . '://';
        }
        if ($host = self::host()) {
            $uri .= $host;
        }
        if ($root = self::root()) {
            $uri .= '/' . $root;
        }
        if ($request = self::uri()) {
            $uri .= '/' . $request;
        }
        return $uri;
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
        header('Location: ' . $uri);
        exit;
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
        $headers = self::headers();
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
        $accept = self::header('Accept');
        $accept = explode(',', $accept);
        array_walk($accept, 'trim');
        
        // if a type is specified, we check to see if it is accepted
        if ($type) {
            return in_array($type, $accept);
        }
        
        // or return all types
        return $accept;
    }
}