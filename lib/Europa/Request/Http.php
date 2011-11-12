<?php

namespace Europa\Request;
use Europa\Request\Uri;

/**
 * The request class representing an HTTP request.
 * 
 * @category Request
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Http extends RequestAbstract
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
     * The request headers.
     * 
     * @var array
     */
    private $headers = array();
    
    /**
     * The URI object.
     * 
     * @var Uri
     */
    private $uri;
    
    /**
     * The ip address of the client request.
     * 
     * @var string
     */
    private $ip;
    
    /**
     * Sets any defaults that may need setting.
     * 
     * @return \Europa\Request\Http
     */
    public function __construct()
    {
        $this->initDefaultUri();
        $this->initDefaultParams();
        $this->initDefaultMethod();
        $this->initDefaultHeaders();
        $this->initDefaultIp();
    }
    
    /**
     * Converts the request to a string representation.
     * 
     * @return string
     */
    public function __toString()
    {
        return $this->uri->getRequest();
    }
    
    /**
     * Sets a header.
     * 
     * @param string $name  The name of the header.
     * @param mixed  $value The value of the header.
     * 
     * @return \Europa\Request\Http
     */
    public function setHeader($name, $value)
    {
        $this->headers[$name] = $value;
        return $this;
    }
    
    /**
     * Returns the value of a single request header or null if not found.
     * 
     * @param string $name The name of the request header to retrieve.
     * 
     * @return string
     */
    public function getHeader($name)
    {
        if ($this->hasHeader($name)) {
            return $this->headers[$name];
        }
        return null;
    }
    
    /**
     * Returns whether or not the specified header exists.
     * 
     * @param string $name The name of the header.
     * 
     * @return bool
     */
    public function hasHeader($name)
    {
        return isset($this->headers[$name]);
    }
    
    /**
     * Removes the specified header.
     * 
     * @param string $name The name of the header.
     * 
     * @return \Europa\Request\Http
     */
    public function removeHeader($name)
    {
        if ($this->hasHeader($name)) {
            unset($this->headers[$name]);
        }
        return $this;
    }
    
    /**
     * Bulk-sets headers.
     * 
     * @param array $headers The headers to set.
     * 
     * @return \Europa\Request\Http
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
        return $this;
    }
    
    /**
     * Returns all of the request headers as an array.
     * 
     * The header names are formatted to appear as normal, not all uppercase
     * as in the $_SERVER super-global.
     * 
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }
    
    /**
     * Removes all headers.
     * 
     * @return \Europa\Request\Http
     */
    public function removeHeaders()
    {
        $this->headers = array();
        return $this;
    }
    
    /**
     * Returns whether or not the request accepts the passed content types.
     * 
     * @param array $types The types to check.
     * 
     * @return bool
     */
    public function accepts($types)
    {
        $accepted = $this->getAcceptedContentTypes();
        foreach ((array) $types as $type) {
            if (!in_array($type, $accepted)) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Sets the accepted content types.
     * 
     * @param array $types The types to accept.
     * 
     * @return \Europa\Request\Http
     */
    public function setAcceptedContentTypes(array $types)
    {
        return $this->setHeader('Accept', implode(',', $types));
    }
    
    /**
     * Returns the content types specified in the Accept request header. Each
     * value is trimmed for consistency, but no further formatting occurs.
     * 
     * @return array
     */
    public function getAcceptedContentTypes()
    {
        $accept = $this->getHeader('Accept');
        $accept = explode(',', $accept);
        array_walk($accept, 'trim');
        return $accept;
    }
    
    /**
     * Clears the accept header.
     * 
     * @return \Europa\Request\Http
     */
    public function removeAcceptedContentTypes()
    {
        return $this->removeHeader('Accept');
    }
    
    /**
     * Sets the request URI.
     * 
     * @param \Europa\Request\Uri $uri The URI to set.
     * 
     * @return \Europa\Request\Http;
     */
    public function setUri(Uri $uri)
    {
        $this->uri = $uri;
        return $this;
    }
    
    /**
     * Returns the request URI.
     * 
     * @return \Europa\Request\Uri
     */
    public function getUri()
    {
        return $this->uri;
    }
    
    /**
     * Sets the ip address of the request.
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
        return $this;
    }
    
    /**
     * Returns the user's real IP address based on the available environment variables.
     * 
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }
    
    /**
     * Returns whether or not an XMLHTTPRequest was made.
     * 
     * @return bool
     */
    public function isXmlHttp()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
     * Initializes the default URI for the HTTP request.
     * 
     * @return \Europa\Request\Http
     */
    private function initDefaultUri()
    {
        return $this->setUri(Uri::detect());
    }
    
    /**
     * Initializes the default parameters for the http request.
     * 
     * @return \Europa\Request\Http
     */
    private function initDefaultParams()
    {
        if (isset($_REQUEST)) {
            $this->setParams($_REQUEST);
        }
        return $this;
    }
    
    /**
     * Initializes the default method for the http request.
     * 
     * @return \Europa\Request\Http
     */
    private function initDefaultMethod()
    {
        $method = static::GET;
        if (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
            $method = $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];
        } elseif (isset($_SERVER['REQUEST_METHOD'])) {
            $method = $_SERVER['REQUEST_METHOD'];
        }
        $this->setMethod(strtolower($method));
        return $this;
    }
    
    /**
     * Initializes the default headers.
     * 
     * @return \Europa\Request\Http
     */
    private function initDefaultHeaders()
    {
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) === 'HTTP_') {
                $name = substr($name, 5);
                $name = strtolower($name);
                $name = str_replace('_', ' ', $name);
                $name = ucwords($name);
                $name = str_replace(' ', '-', $name);
                $this->setHeader($name, $value);
            }
        }
        return $this;
    }
    
    /**
     * Initializes the default ip address.
     * 
     * @return \Europa\Request\Http
     */
    private function initDefaultIp()
    {
        $ip = null;
        if (isset($_SERVER['HTTP_TRUE_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_TRUE_CLIENT_IP'];
        } elseif (isset($_SERVER['X_FORWARDED_FOR'])) {
            $ip = $_SERVER['X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $values = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip     = $values[0];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $this->setIp($ip);
    }
}
