<?php

/**
 * The exception class for uropa\Request.
 * 
 * @category Exceptions
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
namespace Europa\Request
{
    class Exception extends \Europa\Exception
    {
        /**
         * Thrown when the controller cannot be found.
         * 
         * @var int
         */
        const CONTROLLER_NOT_FOUND = 1;
        
        /**
         * Thrown when an invalid controller formatter is set.
         * 
         * @var int
         */
        const INVALID_CONTROLLER_FORMATTER = 2;
        
        /**
         * Thrown when a controller class is instantiated and it is not an instance
         * of uropa\RouteController.
         * 
         * @var int
         */
        const INVALID_CONTROLLER = 3;
        
        /**
         * Thrown when a method has a required argument that wasn't defined in the
         * supplied request parameters.
         * 
         * @var int
         */
        const REQUIRED_METHOD_ARGUMENT_NOT_DEFINED = 4;
        
        /**
         * Thrown when a router is set and a route cannot be matched.
         * 
         * @var int
         */
        const NO_ROUTE_MATCHED = 5;
    }
}