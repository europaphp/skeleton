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
class RouterArray implements RouterInterface
{
    private $routers = array();
    
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
     * Adds a router to the array.
     * 
     * @param Europa\Router\RouterInterface $router The router to add.
     * 
     * @return \Europa\Router\RouterArray
     */
    public function addRouter(RouterInterface $router)
    {
        $this->routers[] = $router;
        return $this;
    }
}
