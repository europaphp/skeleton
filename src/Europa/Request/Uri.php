<?php

namespace Europa\Request;

class Uri
{
    const DEFAULT_SCHEME = 'http';

    const DEFAULT_PORT = 80;

    private $scheme = self::DEFAULT_SCHEME;

    private $username;

    private $password;

    private $host;

    private $port = self::DEFAULT_PORT;

    private $root;

    private $request;

    private $suffix;

    private $params = array();

    private $fragment;

    private $portMap = array(
        'http'  => 80,
        'https' => 443
    );

    public function __construct($uri = null)
    {
        $this->fromString($uri);
    }

    public function __toString()
    {
        return $this->getHostPart()
            . $this->getRootPart()
            . $this->getRequestPart()
            . $this->getQueryPart()
            . $this->getFragmentPart();
    }

    public function __set($name, $value)
    {
        return $this->setParam($name, $value);
    }

    public function __get($name)
    {
        return $this->getParam($name);
    }

    public function __isset($name)
    {
        return $this->hasParam($name);
    }

    public function __unset($name)
    {
        return $this->removeParam($name);
    }

    public function setParam($name, $value)
    {
        $this->params[$name] = $value;
        return $this;
    }

    public function getParam($name)
    {
        if ($this->hasParam($name)) {
            return $this->params[$name];
        }
        return null;
    }

    public function hasParam($name)
    {
        return isset($this->params[$name]);
    }

    public function removeParam($name)
    {
        if ($this->hasParam($name)) {
            unset($this->params[$name]);
        }
        return $this;
    }

    public function setParams(array $params)
    {
        $this->params = array_merge($this->params, $params);
        return $this;
    }

    public function getParams()
    {
        return $this->params;
    }

    public function removeParams()
    {
        $this->params = array();
        return $this;
    }

    public function setScheme($scheme)
    {
        $this->scheme = $scheme;
        return $this;
    }

    public function getScheme()
    {
        return $this->scheme;
    }

    public function getSchemePart()
    {
        if ($scheme = $this->getScheme()) {
            return $scheme . '://';
        }
        return '';
    }

    public function setUsername($user)
    {
        $this->username = $user;
        return $this;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setPassword($pass)
    {
        $this->password = $pass;
        return $this;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setHost($host)
    {
        $this->host = $host;
        return $this;
    }

    public function getHost()
    {
        return $this->host;
    }

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

    public function setPort($port)
    {
        $this->port = (int) $port;
        return $this;
    }

    public function getPort()
    {
        return $this->port;
    }

    public function getPortPart()
    {
        if ($port = $this->getPort()) {
            if ($this->portMap[$this->getScheme()] === $port) {
                return null;
            }

            return ':' . $port;
        }
        return '';
    }

    public function setRoot($root)
    {
        $this->root = trim($root, '/');
        return $this;
    }

    public function getRoot()
    {
        return $this->root;
    }

    public function getRootPart()
    {
        return $this->root ? '/' . $this->root : '';
    }

    public function setRequest($request)
    {
        // normalize
        $request = trim($request, '/');

        // take off the suffix if it exists and set it
        if (strpos($request, '.') !== false) {
            $parts = explode('.', $request, 2);

            // a suffix cannot have a forward slash in it
            if (strpos($parts[1], '/') === false) {
                $this->setSuffix(array_pop($parts));
                $request = $parts[0];
            }
        }

        // apply the new request
        $this->request = $request;

        return $this;
    }

    public function getRequest()
    {
        return $this->request ? $this->request : '';
    }

    public function getRequestPart()
    {
        return $this->request ? '/' . $this->getRequest() . $this->getSuffixPart() : '';
    }

    public function setSuffix($suffix)
    {
        $this->suffix = $suffix;
        return $this;
    }

    public function getSuffix()
    {
        return $this->suffix;
    }

    public function getSuffixPart()
    {
        return $this->suffix ? '.' . $this->suffix : '';
    }

    public function setQuery($query)
    {
        parse_str(ltrim($query, '?'), $params);
        $this->setParams($params);
        return $this;
    }

    public function getQuery()
    {
        return urldecode(http_build_query($this->getParams()));
    }

    public function getQueryPart()
    {
        if ($query = $this->getQuery()) {
            return '?' . $query;
        }
        return '';
    }

    public function setFragment($frag)
    {
        $this->fragment = trim($frag, '#');
        return $this;
    }

    public function getFragment()
    {
        return $this->fragment;
    }

    public function getFragmentPart()
    {
        if ($frag = $this->getFragment()) {
            return '#' . $frag;
        }
        return '';
    }

    public function fromString($uri)
    {
        $uri = (string) $uri;
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

        if (!isset($uri['port']) && isset($uri['scheme']) && isset($this->portMap[$uri['scheme']])) {
            $this->setPort($this->portMap[$uri['scheme']]);
        }

        return $this;
    }

    public function redirect()
    {
        header('Location: ' . $this->__toString());
        exit;
    }

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

    public static function detectScheme()
    {
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            return 'https';
        } elseif (isset($_SERVER['HTTP_HOST'])) {
            return 'http';
        }

        return null;
    }

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

    public static function detectPort()
    {
        if (isset($_SERVER['REMOTE_PORT'])) {
            return $_SERVER['REMOTE_PORT'];
        }

        return null;
    }

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

    public static function detectRequest()
    {
        // remove the root uri from the request uri to get the relative request uri for the framework
        $requestUri = self::getServerRequestUri();
        $requestUri = parse_url($requestUri, PHP_URL_PATH);

        // remove the query string
        $requestUri = explode('?', $requestUri);
        $requestUri = $requestUri[0];

        // format the rest
        $requestUri = trim($requestUri, '/');
        $requestUri = substr($requestUri, strlen(static::detectRoot()));
        $requestUri = trim($requestUri, '/');

        return $requestUri;
    }

    public static function detectQuery()
    {
        if (isset($_SERVER['QUERY_STRING'])) {
            return $_SERVER['QUERY_STRING'];
        }

        return null;
    }

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