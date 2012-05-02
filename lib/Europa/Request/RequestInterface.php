<?php

namespace Europa\Request;

/**
 * Defines a request.
 * 
 * @category Request
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
interface RequestInterface extends \Serializable
{
    /**
     * Converts the request to a string representation.
     * 
     * @return string
     */
    public function __toString();
    
    /**
     * Sets the appropriate method.
     * 
     * @param string $method The method to set.
     * 
     * @return \Europa\Request
     */
    public function setMethod($method);
    
    /**
     * Returns the request method for the request.
     *
     * @return string
     */
    public function getMethod();
    
    /**
     * Sets the specified request parameter.
     * 
     * @param string $name  The name of the parameter.
     * @param mixed  $value The value of the parameter.
     * 
     * @return mixed
     */
    public function setParam($name, $value);
    
    /**
     * Returns the specified request parameter.
     * 
     * @param string $name The name of the parameter.
     * 
     * @return mixed
     */
    public function getParam($name);
}
