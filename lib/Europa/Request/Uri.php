<?php

namespace Europa\Request;

/**
 * Class for URI detection and manipulation.
 * 
 * @category Uri
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Uri
{
    /**
     * The initial value of the scheme part.
     * 
     * @var string
     */
    const DEFAULT_SCHEME = 'http';
    
    /**
     * The initial value of the port part.
     * 
     * @var int
     */
    const DEFAULT_PORT = 80;
    
    /**
     * The scheme set on the URI.
     * 
     * @var string
     */
    private $scheme = self::DEFAULT_SCHEME;
    
    /**
     * The username set on the URI.
     * 
     * @var string
     */
    private $username;
    
    /**
     * The password set on the URI.
     * 
     * @var string
     */
    private $password;
    
    /**
     * The host set on the URI.
     * 
     * @var string
     */
    private $host;
    
    /**
     * The port set on the URI.
     * 
     * @var int
     */
    private $port = self::DEFAULT_PORT;
    
    /**
     * The root URI.
     * 
     * @var string
     */
    private $root;
    
    /**
     * The request set on the URI.
     * 
     * @var string
     */
    private $request;

    /**
     * The suffix associated to the request URI.
     * 
     * @var string
     */
    private $suffix;
    
    /**
     * The parameters set on the URI.
     * 
     * @var array
     */
    private $params = array();
    
    /**
     * The fragment portion of the URI.
     * 
     * @var string
     */
    private $fragment;
    
    /**
     * A port map for default scheme ports.
     * 
     * @var array
     */
    private $portMap = array(
        'http'  => 80,
        'https' => 443
    );
    
    /**
     * Constructs a new URI.
     * 
     * @param string $uri A URI to parse and apply to the current URI.
     * 
     * @return \Europa\Request\Uri
     */
    public function __construct($uri = null)
    {
        $this->fromString($uri);
    }
    
    /**
     * Converts the URI to a string.
     * 
     * @return string
     */
    public function __toString()
    {
        return $this->getHostPart()
            . $this->getRootPart()
            . $this->getRequestPart()
            . $this->getSuffixPart()
            . $this->getQueryPart()
            . $this->getFragmentPart();
    }
    
    /**
     * Sets the specified parameter.
     * 
     * @param string $name  The name of the parameter to set.
     * @param mixed  $value The value of the parameter to set.
     * 
     * @return \Europa\Request\Uri
     */
    public function __set($name, $value)
    {
        return $this->setParam($name, $value);
    }
    
    /**
     * Returns the specified parameter if it exists. If not, null is returned.
     * 
     * @param string $name The name of the parameter to get.
     * 
     * @return mixed
     */
    public function __get($name)
    {
        return $this->getParam($name);
    }
    
    /**
     * Returns whether or not the specified parameter exists.
     * 
     * @param string $name The parameter to check for.
     * 
     * @return bool
     */
    public function __isset($name)
    {
        return $this->hasParam($name);
    }
    
    /**
     * Removes the specified parameter.
     * 
     * @param string $name The parameter to remove.
     * 
     * @return \Europa\Request\Uri
     */
    public function __unset($name)
    {
        return $this->removeParam($name);
    }
    
    /**
     * Sets the specified parameter.
     * 
     * @param string $name  The name of the parameter to set.
     * @param mixed  $value The value of the parameter to set.
     * 
     * @return \Europa\Request\Uri
     */
    public function setParam($name, $value)
    {
        $this->params[$name] = $value;
        return $this;
    }
    
    /**
     * Returns the specified parameter if it exists. If not, null is returned.
     * 
     * @param string $name The name of the parameter to get.
     * 
     * @return mixed
     */
    public function getParam($name)
    {
        if ($this->hasParam($name)) {
            return $this->params[$name];
        }
        return null;
    }
    
    /**
     * Returns whether or not the specified parameter exists.
     * 
     * @param string $name The parameter to check for.
     * 
     * @return bool
     */
    public function hasParam($name)
    {
        return isset($this->params[$name]);
    }
    
    /**
     * Removes the specified parameter.
     * 
     * @param string $name The parameter to remove.
     * 
     * @return \Europa\Request\Uri
     */
    public function removeParam($name)
    {
        if ($this->hasParam($name)) {
            unset($this->params[$name]);
        }
        return $this;
    }
    
    /**
     * Bulk-sets parameters on the URI.
     * 
     * @param array $params The parameters to set.
     * 
     * @return \Europa\Request\Uri
     */
    public function setParams(array $params)
    {
        $this->params = array_merge($this->params, $params);
        return $this;
    }
    
    /**
     * Returns all set query parameters as an array.
     * 
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }
    
    /**
     * Removes all set parameters.
     * 
     * @return \Europa\Request\Uri
     */
    public function removeParams()
    {
        $this->params = array();
        return $this;
    }
    
    /**
     * Sets the scheme.
     * 
     * @param string $scheme The scheme to set.
     * 
     * @return \Europa\Request\Uri
     */
    public function setScheme($scheme)
    {
        $this->scheme = $scheme;
        return $this;
    }
    
    /**
     * Returns the scheme portion of the URI.
     * 
     * @return string
     */
    public function getScheme()
    {
        return $this->scheme;
    }
    
    /**
     * Returns the scheme part of the URI. If a scheme exists, then it is returned with followed by the colon and two
     * forward slashes.
     * 
     * @return string
     */
    public function getSchemePart()
    {
        if ($scheme = $this->getScheme()) {
            return $scheme . '://';
        }
        return '';
    }
    
    /**
     * Sets the username on the URI.
     * 
     * @param string $user The username to set.
     * 
     * @return \Europa\Request\Uri
     */
    public function setUsername($user)
    {
        $this->username = $user;
        return $this;
    }
    
    /**
     * Returns the set username.
     * 
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }
    
    /**
     * Sets the password.
     * 
     * @param string $pass The password to set.
     *
     * @return \Europa\Request\Uri
     */
    public function setPassword($pass)
    {
        $this->password = $pass;
        return $this;
    }
    
    /**
     * Returns the set password.
     * 
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }
    
    /**
     * Sets the specified host.
     * 
     * @param string $host The host to set.
     * 
     * @return \Europa\Request\Uri
     */
    public function setHost($host)
    {
        $this->host = $host;
        return $this;
    }
    
    /**
     * Returns the host portion of the uri.
     * 
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }
    
    /**
     * Returns the full host part of the URI. This includes the scheme, hostname and port. A host must exist for
     * anything to be returned at all.
     * 
     * @return string
     */
    public function getHostPart()
    {
        if ($host = $this->getHost()) {
            $auth = '';
            if ($user = $this->getUsername()) {
                $pass = $this->getPassword();
                $pass = $pass ? ':' . $pass : '';
                $auth = $user . $pass . '@';
            }
            return $this->getSchemePart() . $auth . $host . $this->getPortPart();
        }
        return '';
    }
    
    /**
     * Normalizes and sets the specified port.
     * 
     * @param mixed $port The port to set.
     * 
     * @return \Europa\Request\Uri
     */
    public function setPort($port)
    {
        $this->port = (int) $port;
        return $this;
    }
    
    /**
     * Returns the port the current request came through.
     * 
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }
    
    /**
     * Returns the port part of the URI. If the port exists and is not the default port for the scheme it is returned
     * with the loading colon. If it does not exist or is the default port for the scheme, then it is not returned.
     * In order to detect the default scheme port, however, a scheme must be set. Otherwise there is nothing to compare
     * the port against and it is returned no matter what if it is set.
     * 
     * @return string
     */
    public function getPortPart()
    {
        $port = $this->getPort();
        if ($port) {
            $scheme = $this->getScheme();
            if ($scheme && !isset($this->portMap[$scheme])) {
                return null;
            }
            if ($scheme && $this->portMap[$scheme] === $port) {
                return null;
            }
            return ':' . $port;
        }
        return '';
    }
    
    /**
     * Sets the root portion of the URI. Useful if working with sub-directories and manipulating the rest of the URI.
     * 
     * @param string $root The root of the URI.
     * 
     * @return \Europa\Request\Uri
     */
    public function setRoot($root)
    {
        $this->root = trim($root, '/');
        return $this;
    }
    
    /**
     * Returns the root if any.
     * 
     * @return string
     */
    public function getRoot()
    {
        return $this->root;
    }
    
    /**
     * Returns the root part of the URI including the leading slash. If no root exists, then null is returned.
     * 
     * @return string
     */
    public function getRootPart()
    {
        return $this->root ? '/' . $this->root : '';
    }
    
    /**
     * Takes the specified request, normalizes it and then sets it.
     * 
     * @todo Auto-detect the root URI in the request and set it if it is specified.
     * 
     * @param string $request The request to set.
     * 
     * @return \Europa\Request\Uri
     */
    public function setRequest($request)
    {
        $this->request = trim($request, '/');
        return $this;
    }

    /**
     * Returns the request portion of the URI.
     * 
     * @return string
     */
    public function getRequest()
    {
        return $this->request;
    }
    
    /**
     * Returns the request part of the URI including the leading slash. If no request exists, then null is returned.
     * 
     * @return string
     */
    public function getRequestPart()
    {
        return $this->request ? '/' . $this->request : '';
    }

    /**
     * Sets the suffix of the request.
     * 
     * @param string $suffix The suffix to set.
     * 
     * @return \Europa\Request\Uri
     */
    public function setSuffix($suffix)
    {
        // allow a suffix to be unset by passing a false value
        $suffix = $suffix ? '.' . $suffix : '';
        
        // add to the request if there is no suffix or replace the existing one if there is
        if (strpos($this->request, '.') === false) {
            $this->request = $this->request . $suffix;
        } else {
            $prefix = explode('.', $this->request, 2);
            $prefix = $prefix[0];
            
            // replace the current suffix
            $this->request = $prefix . $suffix;
        }
        
        return $this;
    }

    /**
     * Returns the suffix.
     * 
     * @return string
     */
    public function getSuffix()
    {
        $suffix = null;
        if (strpos($this->request, '.')) {
            $suffix = explode('.', $this->request);
            $suffix = end($suffix);
        }
        return $suffix;
    }

    /**
     * Returns the suffix part including the leading dot if it exists and nothing if it does not.
     * 
     * @return string
     */
    public function getSuffixPart()
    {
        return $suffix = $this->getSuffix() ? '.' . $suffix : '';
    }
    
    /** 
     * Sets the query for the URI. The query string is parsed and parameters set.
     * 
     * @param string $query The query to set.
     * 
     * @return \Europa\Request\Uri
     */
    public function setQuery($query)
    {
        parse_str(ltrim($query, '?'), $params);
        $this->setParams($params);
        return $this;
    }
    
    /**
     * Returns the query string in the current request.
     * 
     * @return string
     */
    public function getQuery()
    {
        return urldecode(http_build_query($this->getParams()));
    }
    
    /**
     * Returns the full query part of the URI. This normalizes the query so that if it exists, it is returned with a
     * leading question mark. If it does not exist, then null is returned.
     * 
     * @return string
     */
    public function getQueryPart()
    {
        if ($query = $this->getQuery()) {
            return '?' . $query;
        }
        return '';
    }
    
    /**
     * Sets the fragment.
     * 
     * @param string $frag The fragment to set.
     * 
     * @return \Europa\Request\Uri
     */
    public function setFragment($frag)
    {
        $this->fragment = trim($frag, '#');
        return $this;
    }
    
    /**
     * Gets the fragment.
     * 
     * @return string
     */
    public function getFragment()
    {
        return $this->fragment;
    }
    
    /**
     * Formats and returns the fragment.
     * 
     * @return string
     */
    public function getFragmentPart()
    {
        if ($frag = $this->getFragment()) {
            return '#' . $frag;
        }
        return '';
    }
    
    /**
     * Parses the passed URI and applies it to the current instance.
     * 
     * @param string $uri The URI to parse and apply to the current instance.
     * 
     * @return \Europa\Request\Uri
     */
    public function fromString($uri)
    {
        $uri = parse_url($uri);
        if (!$uri) {
            return $this;
        }
        
        $map = array(
            'scheme'   => 'setScheme',
            'user'     => 'setUsername',
            'pass'     => 'setPassword',
            'host'     => 'setHost',
            'port'     => 'setPort',
            'path'     => 'setRequest',
            'query'    => 'setQuery',
            'fragment' => 'setFragment'
        );
        
        foreach ($map as $index => $method) {
            if (isset($uri[$index])) {
                $this->$method($uri[$index]);
            }
        }
        
        return $this;
    }
    
    /**
     * Redirects the user to the current specified URI.
     * 
     * @return void
     */
    public function redirect()
    {
        header('Location: ' . $this->__toString());
        exit;
    }
    
    /**
     * Auto-detects and sets all available parts of the URI and returns an instance of it.
     * 
     * @return \Europa\Request\Uri
     */
    public static function detect()
    {
        $uri = new static;
        $uri->setScheme(static::detectScheme());
        $uri->setHost(static::detectHost());
        $uri->setPort(static::detectPort());
        $uri->setRoot(static::detectRoot());
        $uri->setRequest(static::detectRequest());
        $uri->setQuery(static::detectQuery());
        return $uri;
    }
    
    /**
     * Auto-detects the scheme if it is available.
     * 
     * @return string
     */
    public static function detectScheme()
    {
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            return 'https';
        } elseif (isset($_SERVER['HTTP_HOST'])) {
            return 'http';
        }
        return null;
    }
    
    /**
     * Auto-detects the host if it is available.
     * 
     * @return string
     */
    public static function detectHost()
    {
        if (isset($_SERVER['HTTP_HOST'])) {
            $host = $_SERVER['HTTP_HOST'];
            $host = explode(':', $host, 2);
            $host = $host[0];
            return $host;
        }
        return null;
    }
    
    /**
     * Auto-detects the port if it is available.
     * 
     * @return string
     */
    public static function detectPort()
    {
        if (isset($_SERVER['SERVER_PORT'])) {
            return $_SERVER['SERVER_PORT'];
        }
        return null;
    }
    
    /**
     * Auto-detects the root URI if it is available.
     * 
     * @return string
     */
    public static function detectRoot()
    {
    	if (!isset($_SERVER['SCRIPT_NAME'])) {
            return null;
        }
        $root = $_SERVER['SCRIPT_NAME'];
        $root = dirname($root);
        $root = trim($root, '/');
        return $root;
    }
    
    /**
     * Auto-detects the request URI if it is available.
     * 
     * @return string
     */
    public static function detectRequest()
    {
        // remove the root uri from the request uri to get the relative request uri for the framework
        $requestUri = self::getServerRequestUri();
        
        // remove the query string
        $requestUri = explode('?', $requestUri);
        $requestUri = $requestUri[0];
        
        // format the rest
        $requestUri = trim($requestUri, '/');
        $requestUri = substr($requestUri, strlen(static::detectRoot()));
        $requestUri = trim($requestUri, '/');

        // take off the suffix
        $requestUri = explode('.', $requestUri);
        $requestUri = $requestUri[0];

        return $requestUri;
    }

    /**
     * Auto-detects the query if it is available.
     * 
     * @return string
     */
    public static function detectQuery()
    {
        if (isset($_SERVER['QUERY_STRING'])) {
            return $_SERVER['QUERY_STRING'];
        }
        return null;
    }

    /**
     * Returns the correct request URI detected on the server.
     * 
     * @return string
     */
    public static function getServerRequestUri()
    {
        if (!isset($_SERVER['HTTP_X_REWRITE_URL']) && !isset($_SERVER['REQUEST_URI'])) {
            return null;
        }
        
        return isset($_SERVER['HTTP_X_REWRITE_URL'])
            ? $_SERVER['HTTP_X_REWRITE_URL']
            : $_SERVER['REQUEST_URI'];
    }
}
