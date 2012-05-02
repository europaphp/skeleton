<?php

namespace Europa\Dispatcher;
use Europa\Controller\ControllerInterface;
use Europa\Dispatcher\Negotiator\NegotiatorInterface;
use Europa\Filter\FilterInterface;
use Europa\Filter\ClassNameFilter;
use Europa\Request\Cli as CliRequest;
use Europa\Request\Http as HttpRequest;
use Europa\Request\RequestAbstract;
use Europa\Request\RequestInterface;
use Europa\Response\Cli as CliResponse;
use Europa\Response\Http as HttpResponse;
use Europa\Response\ResponseInterface;
use Europa\Router\RouterInterface;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;

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
     * @var FilterInterface
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
     * @return Dispatcher
     */
    public function __construct()
    {
        $isCli = RequestAbstract::isCli();
        
        $this->setRequest($isCli ? new CliRequest : new HttpRequest);
        $this->setResponse($isCli ? new CliResponse : new HttpResponse);
        $this->setControllerFilter(new ClassNameFilter);
    }
    
    /**
     * Sets the proper key to use for retrieving the controller parameter from the request.
     * 
     * @param string $key The name of the parameter in the request that contains the name of the controller.
     * 
     * @return Dispatcher
     */
    public function setControllerKey($key)
    {
        $this->controllerKey = $key;
        return $this;
    }
    
    /**
     * Sets the filter to use.
     * 
     * @param FilterInterface $filter The filter to use.
     * 
     * @return Dispatcher
     */
    public function setControllerFilter(FilterInterface $filter)
    {
        $this->controllerFilter = $filter;
        return $this;
    }
    
    /**
     * Sets the request to use.
     * 
     * @param RequestInterface $request The request to use.
     * 
     * @return Dispatcher
     */
    public function setRequest(RequestInterface $request)
    {
        $this->request = $request;
        return $this;
    }
    
    /**
     * Returns the request.
     * 
     * @return RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }
    
    /**
     * Sets the response to use.
     * 
     * @param ResponseInterface $response The response to use.
     * 
     * @return Dispatcher
     */
    public function setResponse(ResponseInterface $response)
    {
        $this->response = $response;
        return $this;
    }
    
    /**
     * Returns the response.
     * 
     * @return ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }
    
    /**
     * Adds a router to use. Routers should be added in order of priority.
     * 
     * @param RouterInterface $router The router to add.
     * 
     * @return Dispatcher
     */
    public function setRouter(RouterInterface $router)
    {
        $this->router = $router;
        return $this;
    }
    
    /**
     * Enables the filter if it has been disabled.
     * 
     * @return Dispatcher
     */
    public function enableFilter()
    {
        $this->useControllerFilter = true;
        return $this;
    }
    
    /**
     * Disables the filter.
     * 
     * @return Dispatcher
     */
    public function disableFilter()
    {
        $this->useControllerFilter = false;
        return $this;
    }
    
    /**
     * Enables the router if it has been disabled.
     * 
     * @return Dispatcher
     */
    public function enableRouter()
    {
        $this->useRouter = true;
        return $this;
    }
    
    /**
     * Disables the router.
     * 
     * @return Dispatcher
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
     * @return void
     */
    public function dispatch()
    {
        // get the controller class name
        $controller = $this->route();
        
        // ensure the controller is valid
        $this->validateController($controller);
        
        // safe to assume it's a valid object now
        $controller = new $controller($this->request, $this->response);
        
        // since it implements the interface, we know this is available
        $controller->action();
        
        // output the result of the controller
        $this->response->output($controller->render());
    }
    
    /**
     * Routes the particular request and returns the controller class as a string.
     * 
     * @return string
     */
    private function route()
    {
        // any type of router can be applied
        if ($this->useRouter && $this->router) {
            $this->router->route($this->request);
        }
        
        // the controller parameter originates on the request
        $controller = $this->request->getParam($this->controllerKey);
        
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
     * @throws ReflectionException      If the controller cannot be found.
     * @throws InvalidArgumentException If the controller does not implement a valid interface.
     * 
     * @return void
     */
    private function validateController($controller)
    {
        // attempt to reflect it
        try {
            $reflector = new ReflectionClass($controller);
        } catch (ReflectionException $e) {
            throw new InvalidArgumentException(
                "Controller {$controller} could not be located with message: {$e->getMessage()}"
            );
        }
        
        // make sure it's a valid instance
        if (!$reflector->implementsInterface('\Europa\Controller\ControllerInterface')) {
            throw new InvalidArgumentException(
                "Controller {$controller} must implement \Europa\Controller\ControllerInterface."
            );
        }
    }
}
