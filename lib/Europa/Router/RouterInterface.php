<?php

namespace Europa\Router;
use Europa\Request\RequestInterface;

/**
 * Interface for defining request routers.
 * 
 * @category Router
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
interface RouterInterface
{
    /**
     * Routes the specified route using the specified resolver.
     * 
     * @param \Europa\Request\RequestInterface $request The request to route.
     * 
     * @return void
     */
    public function route(RequestInterface $request);
    
    /**
     * Reverse engineers the specified route.
     * 
     * @param string $name   The name of the route to reverse engineer.
     * @param array  $params The parameters to use when reverse engineering the route.
     * 
     * @throws \LogicException If the route cannot be found.
     * 
     * @return string
     */
    public function reverse($name, array $params = array());
}
