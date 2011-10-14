<?php

namespace Europa\Dispatcher;
use Europa\Controller\ControllerInterface;
use Europa\Di\Container;
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
     * The controller dependency locator to use.
     * 
     * @var \Europa\Di\Container
     */
    private $container;
    
    /**
     * A list of routers to use.
     * 
     * @var array
     */
    private $routers = array();
    
    /**
     * Sets up the dispatcher.
     * 
     * @return \Europa\Dispatcher\Dispatcher
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }
    
    /**
     * Adds a router to use. Routers should be added in order of priority.
     * 
     * @param \Europa\Router\RouterInterface $router The router to add.
     * 
     * @return \Europa\Dispatcher\Dispatcher
     */
    public function addRouter(RouterInterface $router)
    {
        $this->routers[] = $router;
        return $this;
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
        foreach ($this->routers as $router) {
            if ($router->route($request)) {
                break;
            }
        }
        
        $controller = $this->resolveController($request, $response);
        $controller->action();
        $response->output($controller->render());
    }
    
    /**
     * Resolves the controller and returns an instance of it.
     * 
     * @param \Europa\Request\RequestInterface   $request  The request object to dispatch.
     * @param \Europa\Response\ResponseInterface $response The response object to output.
     * 
     * @return \Europa\Controller\ControllerInterface
     */
    private function resolveController(RequestInterface $request, ResponseInterface $response)
    {
        // format and instantiate the new controller
        $controller = $request->getParam($this->key);
        $controller = $this->container->resolve($controller)->create(array($request, $response));
        
        // make sure it's a valid instance
        if (!$controller instanceof ControllerInterface) {
            $controller = get_class($controller);
            throw new \LogicException("Class {$controller} is not a valid controller instance. Controllers must implement Europa\Controller\ControllerInterface.");
        }
        
        return $controller;
    }
}
