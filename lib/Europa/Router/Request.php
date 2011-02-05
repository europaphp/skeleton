<?php

namespace Europa\Router;

/**
 * A request router.
 * 
 * @category Router
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Request extends \Europa\Router
{
    /**
     * The request to route.
     * 
     * @var uropa\Request
     */
    private $request;
    
    /**
     * Sets up the request router using the specified request.
     * 
     * @param uropa\Request $request The request to route.
     * 
     * @return \Europa\Router\Request
     */
    public function __construct(\Europa\Request $request)
    {
        $this->request = $request;
    }
    
    /**
     * Returns the request to be routed.
     * 
     * @return \Europa\Router\Request
     */
    public function getRequest()
    {
        return $this->request;
    }
    
    /**
     * Dispatches the set request. If $subject is specified, then it overwrits the
     * default uropa\Request->__toString() return value for route matching.
     * 
     * The request that was routed is returned. If the request isn't matched, then
     * false is returned.
     * 
     * @param string $subject The subject being routed.
     * 
     * @return uropa\Request
     */
    public function dispatch($subject = null)
    {
        $request = $this->getRequest();
        $subject = $subject ? $subject : $request->__toString();
        return $request->setParams($this->query($subject))->dispatch();
    }
}