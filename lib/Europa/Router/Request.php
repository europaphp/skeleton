<?php

/**
 * A request router.
 * 
 * @category Router
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart http://europaphp.org/license
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
        $this->setRequest($request);
    }
    
    /**
     * Sets the request to route.
     * 
     * @param Europa_Request $request The request to route.
     * 
     * @return Europa_Router_Request
     */
    public function setRequest(Europa_Request $request)
    {
        $this->_request = $request;
        return $this;
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
        $params  = $this->query($subject);
        if ($params === false) {
            return false;
        }
        return $request->setParams($params)->dispatch();
    }
}