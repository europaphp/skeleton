<?php

namespace Europa\Request;

/**
 * Blueprint for HTTP requests.
 * 
 * @category Request
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
interface HttpInterface extends RequestInterface
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
     * The PATCH method.
     * 
     * @var string
     */
    const PATCH = 'patch';

    /**
     * Returns the request method for the request.
     *
     * @return string
     */
    public function getMethod();

    /**
     * Returns the value of a single request header or null if not found.
     * 
     * @param string $name The name of the request header to retrieve.
     * 
     * @return string
     */
    public function getHeader($name);

    /**
     * Returns the request URI.
     * 
     * @return Uri
     */
    public function getUri();

    /**
     * Returns the first matched type.
     * 
     * @param array $types    The types to check.
     * @param int   $maxIndex The maximum number of items to look at in order before returning false.
     * 
     * @return bool
     */
    public function accepts($type);
}