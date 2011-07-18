<?php

namespace Europa\Dispatcher;
use Europa\Controller\ControllerInterface;
use Europa\Di\Container;
use Europa\Request\RequestInterface;
use Europa\Response\ResponseInterface;
use Europa\StringObject;

/**
 * Dispatches the request to the controller, takes the rendered content and passes to response to output
 *
 * @category Controller
 * @package  Europa
 * @author   Paul Carvosso-White <paulcarvossowhite@gmail.com>
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Dispatcher implements DispatcherInterface
{
    const DEFAULT_CONTROLLER_KEY = 'controller';
    
    const DEFAULT_CONTROLLER_FORMATTER = 'defaultControllerFormatter';
    
    /**
     * The controller dependency locator to use.
     * 
     * @var Container
     */
    private $controllerFormatter;
    
    /**
     * Actions the appropriate controller and outputs the response.
     * 
     * @param RequestInterface  $request  The request object to dispatch
     * @param ResponseInterface $response The response object to output
     * 
     * @return void
     */
    public function dispatch(RequestInterface $request, ResponseInterface $response)
    {
        $controller = $this->resolveController($request, $response);
        $response->output($controller->render());
    }
    
    /**
     * Resolves the controller.
     * 
     * @param RequestInterface  $request  The request object to dispatch
     * @param ResponseInterface $response The response object to output
     * 
     * @return ControllerInterface
     */
    private function resolveController(RequestInterface $request, ResponseInterface $response)
    {
        // format and instantiate the new controller
        $formatter  = $this->resolveControllerFormatter();
        $controller = call_user_func($formatter, $request, $response);
        $controller = new $controller($request, $response);
        
        // make sure it's a valid instance
        if (!$controller instanceof ControllerInterface) {
            $controller = get_class($controller);
            throw new Exception("Class {$controller} is not a valid controller instance. Controllers must implement Europa\Controller\ControllerInterface.");
        }
        
        return $controller;
    }
    
    private function resolveControllerFormatter()
    {
        return $this->controllerFormatter
            ? $this->controllerFormatter
            : array($this, self::DEFAULT_CONTROLLER_FORMATTER);
    }
    
    private function defaultControllerFormatter(RequestInterface $request, ResponseInterface $response)
    {
        $controller = $request->getParam(self::DEFAULT_CONTROLLER_KEY);
        $controller = StringObject::create($controller);
        $controller->toClass();
        return 'Controller' . $controller;
    }
}
