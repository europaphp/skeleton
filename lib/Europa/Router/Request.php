<?php

/**
 * A request router.
 * 
 * @category Router
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2010 Trey Shugart http://europaphp.org/license
 */
class Europa_Router_Request extends Europa_Router
{
    /**
     * The request to route.
     * 
     * @var Europa_Request
     */
    private $_request;
    
    /**
     * Sets up the request router using the specified request.
     * 
     * @param Europa_Request $request The request to route.
     * 
     * @return Europa_Router_Request
     */
    public function __construct(Europa_Request $request)
    {
        $this->_request = $request;
    }
    
    /**
     * Returns the request to be routed.
     * 
     * @return Europa_Router_Request
     */
    public function getRequest()
    {
        return $this->_request;
    }
    
    /**
     * Dispatches the set request. If $subject is specified, then it overwrits the
     * default Europa_Request->__toString() return value for route matching.
     * 
     * The request that was routed is returned. If the request isn't matched, then
     * false is returned.
     * 
     * @param string $subject The subject being routed.
     * 
     * @return Europa_Request
     */
    public function dispatch($subject = null)
    {
        $request = $this->getRequest();
        $subject = $subject ? $subject : $request->__toString();
        return $request->setParams($this->query($subject))->dispatch();
    }
}