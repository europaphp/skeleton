<?php

namespace Europa\Controller;
use Europa\Reflection\ClassReflector;
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
     * The request used to dispatch to this controller.
     * 
     * @var RequestInterface
     */
    private $request;
    
    /**
     * The response used to set headers for output
     *
     * @var ResponseInterface
     */
    private $response;

    /**
     * Whether or not to apply filters to action.
     * 
     * @var bool
     */
    private $useFilters = false;
    
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
     * Returns the request being used.
     * 
     * @return RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }
    
    /**
     * Returns the request being used.
     * 
     * @return ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Switches filters on or off.
     * 
     * @param bool $switch Whether or not to enable filters.
     * 
     * @return ControllerAbstract
     */
    public function filter($switch = true)
    {
        $this->useFilters = $switch ? true : false;
        return $this;
    }
    
    /**
     * Executes the controller's action. Both preAction and postAction hooks are invoked. If filtering is enabled and
     * any are applied to the action, they are applied before the preAction hook.
     * 
     * @return mixed
     */
    public function action()
    {
        // the method to execute
        $method = $this->getActionMethod();
        
        // apply all detected filters to the specified method
        $this->applyFiltersTo($method);
        
        // the return value of the action is the view context
        $result = $this->executeMethod($method, $this->request->getParams());
        
        // chainable
        return $result;
    }

    /**
     * Applies filters to the specified method.
     * 
     * @param string $method The method to apply the filters to.
     * 
     * @return ControllerAbstract
     */
    private function applyFiltersTo($method)
    {
        if (!$this->useFilters) {
            return;
        }
        
        $class  = (new ClassReflector($this))->getDocBlock();
        $class  = $class->hasTag('filter') ? $class->getTags('filter') : [];
        $method = (new MethodReflector($this, $method))->getDocBlock();
        $method = $method->hasTag('filter') ? $method->getTags('filter') : [];
        
        foreach (array_merge($class, $method) as $filter) {
            $filter = $filter->getInstance();
            $filter->filter($this);
        }
        
        return $this;
    }
    
    /**
     * Executes the specified method.
     * 
     * @param string $method The method to execute.
     * @param array  $params The parameters to pass to the method.
     * 
     * @return ControllerAbstract
     */
    private function executeMethod($method, array $params = array())
    {
        // attempt to call the action
        if (method_exists($this, $method)) {
            $reflector = new MethodReflector($this, $method);
            return $reflector->invokeNamedArgs($this, $params);
        }
        
        // attempt to catch with __call
        if (method_exists($this, '__call')) {
            return $this->__call($method, $params);
        }
        
        throw new LogicException("Method \"{$method}\" is not supported and was not trapped in \"__call\".");
    }
}
