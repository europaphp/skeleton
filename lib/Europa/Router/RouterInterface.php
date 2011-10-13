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
}
