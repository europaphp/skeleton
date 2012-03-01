<?php

namespace Europa\Dispatcher;
use Europa\Controller\ControllerInterface;
use Europa\Dispatcher\Negotiator\NegotiatorInterface;
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
    private $controllerKey = self::DEFAULT_CONTROLLER_KEY;
    
    /**
     * The filter to use for the controller.
     * 
     * @var \Europa\Filter\FilterInterface
     */
    private $controllerFilter;
    
    /**
     * A list of routers to use.
     * 
     * @var array
     */
    private $router;
    
    /**
     * Whether or not the filter is being used.
     * 
     * @var bool
     */
    private $useControllerFilter = true;
    
    /**
     * Whether or not a router is being used.
     * 
     * @var bool
     */
    private $useRouter = true;
    
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
        $this->controllerKey = $key;
        return $this;
    }
    
    /**
     * Returns the controller key.
     * 
     * @return string
     */
    public function getControllerKey()
    {
        return $this->controllerKey;
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
     * Returns the controller filter.
     * 
     * @return \Europa\Filter\FilterInterface
     */
    public function getControllerFilter()
    {
        return $this->controllerFilter;
    }
    
    /**
     * Returns whether or not a controller filter is present.
     * 
     * @return bool
     */
    public function hasControllerFilter()
    {
        return $this->controllerFilter instanceof FilterInterface;
    }
    
    /**
     * Removes the controller filter.
     * 
     * @return \Europa\Dispatcher\Dispatcher
     */
    public function removeControllerFilter()
    {
        $this->controllerFilter = null;
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
     * Returns the router set on the dispatcher.
     * 
     * @return \Europa\Router\Router
     */
    public function getRouter()
    {
        return $this->router;
    }
    
    /**
     * Returns whether or not the instance has a router.
     * 
     * @return bool
     */
    public function hasRouter()
    {
        return $this->router instanceof RouterInterface;
    }
    
    /**
     * Removes the router.
     * 
     * @return \Europa\Dispatcher\Dispatcher
     */
    public function removeRouter()
    {
        $this->router = null;
        return $this;
    }
    
    /**
     * Enables the filter if it has been disabled.
     * 
     * @return \Europa\Dispatcher\Dispatcher
     */
    public function enableFilter()
    {
        $this->useControllerFilter = true;
        return $this;
    }
    
    /**
     * Disables the filter.
     * 
     * @return \Europa\Dispatcher\Dispatcher
     */
    public function disableFilter()
    {
        $this->useControllerFilter = false;
        return $this;
    }
    
    /**
     * Enables the router if it has been disabled.
     * 
     * @return \Europa\Dispatcher\Dispatcher
     */
    public function enableRouter()
    {
        $this->useRouter = true;
        return $this;
    }
    
    /**
     * Disables the router.
     * 
     * @return \Europa\Dispatcher\Dispatcher
     */
    public function disableRouter()
    {
        $this->useRouter = false;
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
        // get the controller class name
        $controller = $this->route($request);
        
        // ensure the controller is valid
        $this->validateController($controller);
        
        // safe to assume it's a valid object now
        $controller = new $controller($request, $response);
        
        // since it implements the interface, we know this is available
        $controller->action();
        
        // output the result of the controller
        $response->output($controller->render());
    }
    
    /**
     * Routes the particular request and returns the controller class as a string.
     * 
     * @param \Europa\Request\RequestInterface The request to route.
     * 
     * @return string
     */
    private function route(RequestInterface $request)
    {
        // any type of router can be applied
        if ($this->useRouter && $this->router) {
            $this->router->route($request);
        }
        
        // the controller parameter originates on the request
        $controller = $request->getParam($this->controllerKey);
        
        // we don't require a controller filter as the router may have done the necessary steps
        if ($this->useControllerFilter && $this->controllerFilter) {
            $controller = $this->controllerFilter->filter($controller);
        }
        
        return $controller;
    }
    
    /**
     * Ensures that the specified controller is valid and can be used.
     * 
     * @param string $controller The controller to validate.
     * 
     * @throws \ReflectionException      If the controller cannot be found.
     * @throws \InvalidArgumentException If the controller does not implement a valid interface.
     * 
     * @return void
     */
    private function validateController($controller)
    {
        // attempt to reflect it
        try {
            $reflector = new \ReflectionClass($controller);
        } catch (\ReflectionException $e) {
            throw new \InvalidArgumentException(
                "Controller {$controller} could not be located with message: {$e->getMessage()}"
            );
        }
        
        // make sure it's a valid instance
        if (!$reflector->implementsInterface('\Europa\Controller\ControllerInterface')) {
            throw new \InvalidArgumentException(
                "Controller {$controller} must implement \Europa\Controller\ControllerInterface."
            );
        }
    }
}
