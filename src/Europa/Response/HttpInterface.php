<?php

namespace Europa\Response;
use Countable;
use IteratorAggregate;

/**
 * HTTP response blueprint.
 *
 * @category Response
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
interface HttpInterface extends Countable, IteratorAggregate, ResponseInterface
{
    /**
     * The content type header name.
     * 
     * @var string
     */
    const CONTENT_TYPE = 'Content-Type';

    /**
     * The location header name.
     * 
     * @var string
     */
    const LOCATION = 'Location';

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
     * The JSONP content type.
     * 
     * @var string
     */
    const JSONP = 'application/javascript';

    /**
     * The XML content type.
     *
     * @var string
     */
    const XML = 'text/xml';
    
    /**
     * The OK status code.
     * 
     * @var int
     */
    const OK = 200;

    /**
     * The NOT_FOUND status code.
     * 
     * @var int
     */
    const NOT_FOUND = 404;

    /**
     * The INTERNAL_SERVER_ERROR status code.
     * 
     * @var int
     */
    const INTERNAL_SERVER_ERROR = 500;
    
    /**
     * Sets a header value
     * 
     * @param string $name  The header type (in camel cased format, will be converted in output)
     * @param string $value The header value
     * 
     * @return Http
     */
    public function setHeader($name, $value);
        
    /**
     * Returns a header
     * 
     * @param string $name The header to return
     * 
     * @return string 
     */
    public function getHeader($name);
    
    /**
     * Returns whether or not the specified header exists.
     * 
     * @param string $name The header name.
     * 
     * @return bool
     */
    public function hasHeader($name);
    
    /**
     * Removes the specified header if it exists.
     * 
     * @param string $name The header name.
     * 
     * @return Http
     */
    public function removeHeader($name);
    
    /** 
     * Sets the specified headers. Accepts an array or object.
     * 
     * @param mixed $headers The headers to set.
     * 
     * @return Http
     */
    public function setHeaders($headers);
    
    /**
     * Returns all of the set headers.
     * 
     * @return array
     */
    public function getHeaders();
    
    /**
     * Removes all headers.
     * 
     * @return Http
     */
    public function removeHeaders();

    /**
     * Sets the content type.
     * 
     * @param string $type The content type.
     * 
     * @return Http
     */
    public function setContentType($type);

    /**
     * Returns the content type.
     * 
     * @return string
     */
    public function getContentType();

    /**
     * Sets the response content type using a view.
     * 
     * @param mixed $view The view to use.
     * 
     * @return Http
     */
    public function setContentTypeFromView($view);

    /**
     * Sets the content type that the specified view should be rendered as.
     * 
     * @param string $view The view class name.
     * @param string $type The content type.
     * 
     * @return Http
     */
    public function setViewContentType($view, $type);

    /**
     * Returns the content type the view should be rendered as.
     * 
     * @return string
     */
    public function getViewContentType($view);

    /**
     * Sets the value of the "Location" header.
     * 
     * @param string $location The location to set.
     * 
     * @return Http
     */
    public function setLocation($location);

    /**
     * Returns the value of the "Location" header.
     * 
     * @return string
     */
    public function getLocation();
    
    /**
     * Sets the HTTP status code.
     * 
     * @param int $status The status to set.
     * 
     * @return Http
     */
    public function setStatus($status);
    
    /**
     * Returns the status that will be sent.
     * 
     * @return int
     */
    public function getStatus();
}