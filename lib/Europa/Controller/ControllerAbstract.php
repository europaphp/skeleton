<?php

namespace Europa\Controller;
use Europa\Reflection\MethodReflector;
use Europa\Request\RequestInterface;
use Europa\Response\ResponseInterface;
use LogicException;

/**
 * A default implementation of the controller interface.
 * 
 * @category Controllers
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
abstract class ControllerAbstract implements ControllerInterface
{
    /**
     * The request object.
     * 
     * @var RequestInterface
     */
    private $request;
    
    /**
     * The response object.
     * 
     * @var ResponseInterface
     */
    private $response;
    
    /**
     * Returns the method that the controller should call during actioning.
     * 
     * @return string
     */
    abstract public function getActionMethod();
    
    /**
     * Constructs a new controller using the specified request and response.
     *
     * @param RequestInterface  $request  The request to use.
     * @param ResponseInterface $response The response to use.
     *
     * @return ControllerAbstract
     */
    public function __construct(RequestInterface $request, ResponseInterface $response)
    {
        $this->request  = $request;
        $this->response = $response;
    }
    
    /**
     * Returns the request object.
     * 
     * @return RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }
    
    /**
     * Returns the response object.
     * 
     * @return ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }
    
    /**
     * Executes the controller's action. Both preAction and postAction hooks are invoked. If filtering is enabled and
     * any are applied to the action, they are applied before the preAction hook.
     * 
     * @return mixed
     */
    public function action()
    {
        $method = $this->getActionMethod();
        
        // attempt to call the action or use __call as a catch-all
        if (method_exists($this, $method)) {
            $result = (new MethodReflector($this, $method))->invokeNamedArgs($this, $this->request->getParams());
        } else if (method_exists($this, '__call')) {
            $result = $this->__call($method, $params);
        } else {
            throw new LogicException("Method \"{$method}\" is not supported and was not trapped in \"__call\".");
        }
        
        return $result;
    }
}
