<?php
namespace Europa;

/**
 * Counterpart to request object, outputs headers and contents
 *
 * @category Controller
 * @package  Europa
 * @author   Paul Carvosso-White <paulcarvossowhite@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Response
{
    protected $headers      = array();
    
    const CONTENT_TYPE_JSON     = 'application/json';
    const CONTENT_TYPE_HTML     = 'text/html';
    const CONTENT_TYPE_CSV      = 'text/csv';
    const CONTENT_TYPE_XML      = 'xml';
    const HEADER_CONTENT_TYPE   = 'content-type';
    
    /**
     * Sets up common content types and the default
     * 
     * @return \Europa\Response
     */
    public function __construct()
    {
        //build the map of content aliai to content types     
        $this->setContentType(self::CONTENT_TYPE_HTML);
    }
    
    /**
     * Given content generated from a view, output any headers, set any env vars and output the content.
     * 
     * @param string $content The content to output
     * 
     * @return void
     */
    public function output($content)
    {
        foreach ($this->headers as $name => $value) {
            //ensure camelCased attr converted to headers, e.g. content-type
            $name = String::create($name)->splitUcWords('-');
            header($name->__toString() . ': ' . $value);
        }        
        echo $content;     
    }

    /**
     * Sets the content type header to the specified type
     * 
     * @param string $type One of json, http, csv, xml
     * 
     * @return true if $type exists, otherwise false
     * 
     */
    public function setContentType($type)
    {
        $this->setHeader(self::HEADER_CONTENT_TYPE, $type);
    }
    
    /**
     * Sets a header value
     * 
     * @param string $name  The header type (in camel cased format, will be converted in output)
     * @param string $value The header value
     * 
     * @return void
     */
    public function setHeader($name, $value)
    {
        $this->headers[$name] = $value;
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
    }  
    

    /**
     * Sets the specified request header.
     *
     * @param string $name  The name of the header.
     * @param mixed  $value The value of the header.
     *
     * @return mixed
     */
    public function __set($name, $value)
    {
        return $this->setHeader($name, $value);
    }

    /**
     * Returns the specified request header.
     *
     * @param string $name The name of the header.
     *
     * @return mixed
     */
    public function __get($name)
    {
        return $this->getHeader($name);
    }
    
}