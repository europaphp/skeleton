<?php

namespace Europa\Response;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use Europa\Filter\CamelCaseSplitFilter;

/**
 * HTTP response.
 *
 * @category Response
 * @package  Europa
 * @author   Paul Carvosso-White <paulcarvossowhite@gmail.com>
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Http extends ResponseAbstract implements Countable, IteratorAggregate
{
    /**
     * The HTML content type.
     *
     * @var string
     */
    const HTML = 'text/html';
    
    /**
     * The JSON content type.
     * 
     * @var string
     */
    const JSON = 'application/json';
    
    /**
     * The OK status code.
     * 
     * @var int
     */
    const OK = 200;
    
    /**
     * The XML content type.
     *
     * @var string
     */
    const XML = 'text/xml';
    
    /**
     * The camel case split filter.
     * 
     * @var CamelCaseSplitFilter
     */
    private $filter;
    
    /**
     * The headers.
     * 
     * @var array
     */
    private $headers = array();
    
    /**
     * The HTTP status to send.
     * 
     * @var int
     */
    private $status = self::OK;
    
    /**
     * Sets up a new response object.
     * 
     * @return Http
     */
    public function __construct()
    {
        $this->filter = new CamelCaseSplitFilter;
    }
    
    /**
     * @see self::hasHeader()
     */
    public function __set($name, $value)
    {
        $this->setHeader($this->filter($name), $value);
    }
    
    /**
     * @see self::getHeader()
     */
    public function __get($name)
    {
        return $this->getHeader($this->filter($name));
    }
    
    /**
     * @see self::hasHeader()
     */
    public function __isset($name)
    {
        return $this->hasHeader($this->fitler($name));
    }
    
    /**
     * @see self::removeHeader()
     */
    public function __unset($name)
    {
        return $this->removeHeader($this->filter($name));
    }
    
    /**
     * Sets a header value
     * 
     * @param string $name  The header type (in camel cased format, will be converted in output)
     * @param string $value The header value
     * 
     * @return Http
     */
    public function setHeader($name, $value)
    {
        $this->headers[$name] = $value;
        return $this;
    }
        
    /**
     * Returns a header
     * 
     * @param string $name The header to return
     * 
     * @return string 
     */
    public function getHeader($name)
    {
        if (isset($this->headers[$name])) {
            return $this->headers[$name];
        }
        return null;
    }
    
    /**
     * Returns whether or not the specified header exists.
     * 
     * @param string $name The header name.
     * 
     * @return bool
     */
    public function hasHeader($name)
    {
        return isset($this->headers[$name]);
    }
    
    /**
     * Removes the specified header if it exists.
     * 
     * @param string $name The header name.
     * 
     * @return Http
     */
    public function removeHeader($name)
    {
        if (isset($this->headers[$name])) {
            unset($this->headers[$name]);
        }
        return $this;
    }
    
    /** 
     * Sets the specified headers. Accepts an array or object.
     * 
     * @param mixed $headers The headers to set.
     * 
     * @return Http
     */
    public function setHeaders($headers)
    {
        if (is_array($headers) || is_object($headers)) {
            foreach ($headers as $name => $value) {
                $this->setHeader($name, $value);
            }
        }
        return $this;
    }
    
    /**
     * Returns all of the set headers.
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
     * @return Http
     */
    public function removeHeaders()
    {
        $this->headers = [];
        return $this;
    }
    
    /**
     * Sets the HTTP status code.
     * 
     * @param int $status The status to set.
     * 
     * @return Http
     */
    public function setStatus($status)
    {
        $this->status = (int) $status;
        return $this;
    }
    
    /**
     * Returns the status that will be sent.
     * 
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }
    
    /**
     * Outputs the response body.
     * 
     * @return ResponseInterface
     */
    public function send()
    {
        http_response_code($this->status);
        
        foreach ($this->headers as $name => $value) {
            header(implode('-', $name) . ': ' . $value);
        }
        
        echo $this->getBody();
    }
    
    /**
     * Returns the iterator to use when iterating over the Response object.
     * 
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->headers);
    }
    
    /**
     * Returns the number of headers set.
     * 
     * @return int
     */
    public function count()
    {
        return count($this->headers);
    }
    
    /**
     * Turns the camel-cased header name into a valid header.
     * 
     * @param string $name The header name.
     * 
     * @return string
     */
    private function filter($name)
    {
        $name = $this->filter->filter($name);
        
        foreach ($name as &$part) {
            ucfirst($part);
        }
        
        return implode('-', $name);
    }
}