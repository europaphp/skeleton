<?php

namespace Europa\Router;
use Europa\Controller\ControllerInterface;
use Europa\Request\RequestInterface;

/**
 * Provides a base implementation for routes.
 * 
 * @category Routing
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
interface RouteInterface
{
    /**
     * Makes a query against the route.
     * 
     * @param string $query The query.
     * 
     * @return array | false
     */
    public function query($query);
    
    /**
     * Provides a way to reverse engineer the route using named parameters.
     * 
     * @param array $params The parameters to format the route with.
     * 
     * @return string
     */
    public function format(array $params = array());
}