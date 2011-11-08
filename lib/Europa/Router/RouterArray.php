<?php

namespace Europa\Router;
use Europa\Request\RequestInterface;

/**
 * Routes a request using multiple routers.
 * 
 * @category Router
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class RouterArray implements RouterInterface
{
    /**
     * The array of routers to route with.
     * 
     * @var array
     */
    private $routers = array();
    
    /**
     * Adds a router to the array.
     * 
     * @param Europa\Router\RouterInterface $router The router to add.
     * 
     * @return \Europa\Router\RouterArray
     */
    public function add(RouterInterface $router)
    {
        $this->routers[] = $router;
        return $this;
    }
    
    /**
     * Routes the specified route using the specified resolver.
     * 
     * @param \Europa\Request\RequestInterface $request The request to route.
     * 
     * @return \Europa\Router\RouterArray
     */
    public function route(RequestInterface $request)
    {
        foreach ($this->routers as $router) {
            if ($router->route($request)) {
                continue;
            }
        }
        return $this;
    }
    
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
    public function reverse($name, array $params = array())
    {
    	foreach ($this->routers as $router) {
    		if ($router->hasRoute($name)) {
    			return $router->getRoute($name)->reverse($params);
    		}
    	}
    	throw new \LogicException("Could not reverse engineer route {$name} because it could not be found.");
    }
}
