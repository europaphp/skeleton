<?php

namespace Europa\Dispatcher;
use Europa\Controller\ControllerInterface;
use Europa\Filter\FilterInterface;
use Europa\Filter\ClassNameFilter;
use Europa\Request\RequestInterface;
use Europa\Response\ResponseInterface;
use Europa\Router\RouterInterface;

/**
 * Dispatches the request to the controller, takes the rendered content and passes to response to output.
 *
 * @category Dispatcher
 * @package  Europa
 * @author   Paul Carvosso-White <paulcarvossowhite@gmail.com>
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Dispatcher implements DispatcherInterface
{
    /**
     * The default controller key.
     * 
     * @var string
     */
    const DEFAULT_CONTROLLER_KEY = 'controller';
    
    /**
     * The name of the parameter in the request that contains the controller name.
     * 
     * @var string
     */
    private $key = self::DEFAULT_CONTROLLER_KEY;
    
    /**
     * The filter to use for the controller.
     * 
     * @var \Europa\Filter\FilterInterface
     */
    private $filter;
    
    /**
     * A list of routers to use.
     * 
     * @var array
     */
    private $router;
    
    /**
     * Initializes the dispatcher.
     * 
     * @return \Europa\Dispatcher\Dispatcher
     */
    public function __construct()
    {
        $this->setControllerFilter(new ClassNameFilter);
    }
    
    /**
     * Sets the proper key to use for retrieving the controller parameter from the request.
     * 
     * @param string $key The name of the parameter in the request that contains the name of the controller.
     * 
     * @return \Europa\Dispatcher\Dispatcher
     */
    public function setControllerKey($key)
    {
        $this->key = $key;
        return $this;
    }
    
    /**
     * Sets the filter to use.
     * 
     * @param \Europa\Filter\FilterInterface $filter The filter to use.
     * 
     * @return \Europa\Dispatcher\Dispatcher
     */
    public function setControllerFilter(FilterInterface $filter)
    {
        $this->controllerFilter = $filter;
        return $this;
    }
    
    /**
     * Adds a router to use. Routers should be added in order of priority.
     * 
     * @param \Europa\Router\RouterInterface $router The router to add.
     * 
     * @return \Europa\Dispatcher\Dispatcher
     */
    public function setRouter(RouterInterface $router)
    {
        $this->router = $router;
        return $this;
    }
    
    /**
     * Actions the appropriate controller and outputs the response. If no router is specified, it attempts to dispatch
     * using whatever parameters that are already available on the request.
     * 
     * @param RequestInterface  $request  The request object to dispatch.
     * @param ResponseInterface $response The response object to output.
     * 
     * @return void
     */
    public function dispatch(RequestInterface $request, ResponseInterface $response)
    {
        // any type of router can be applied
        if ($this->router) {
            $this->router->route($request);
        }
        
        // the controller parameter originates on the request
        $controller = $request->getParam($this->key);
        
        // we don't require a controller filter as the router may have done the necessary steps
        if ($this->controllerFilter) {
            $controller = $this->controllerFilter->filter($controller);
        }
        
        // if it doesn't implement the base interface we won't know how to use it
        if (!is_subclass_of($controller, '\Europa\Controller\ControllerInterface')) {
            throw new \InvalidArgumentException("Class {$controller} must implement \Europa\Controller\ControllerInterface.");
        }
        
        // safe to assume it's a valid object now
        $controller = new $controller($request, $response);
        
        // since it implements the interface, we know this is available
        $controller->action();
        
        // output the result of the controller
        $response->output($controller->render());
    }
}
