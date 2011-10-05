<?php

namespace Europa\Router\Route;
use Europa\Router\Resolver\ResolverInterface;

/**
 * Provides a base implementation for routes.
 * 
 * @category Routing
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
interface RouteInterface extends ResolverInterface
{
    /**
     * Makes a query against the route using the specified subject.
     * 
     * @param string $subject The subject to query against the route.
     * 
     * @return array|false
     */
    public function query($subject);
    
    /**
     * Provides a way to reverse engineer the route using named parameters.
     * 
     * @return string
     */
    public function reverse(array $params = array());
}