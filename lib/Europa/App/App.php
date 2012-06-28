<?php

namespace Europa\App;
use Closure;
use LogicException;
use Europa\Di\ContainerInterface;
use Europa\Request\RequestInterface;
use Europa\Request\ResponseInterface;
use Europa\Router\RouterInterface;
use Europa\View\ViewInterface;
use UnexpectedValueException;

/**
 * Runs the application.
 * 
 * @category App
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  http://europaphp.org/license
 */
class App implements AppInterface
{
    /**
     * The default controller key name.
     * 
     * @var string
     */
    const KEY = 'controller';
    
    /**
     * The container that has the required components.
     * 
     * @var ContainerInterface
     */
    private $container;
    
    /**
     * The controller key name in the context returned from the router.
     * 
     * @var string
     */
    private $key = self::KEY;
    
    /**
     * Constructs a new application. The container is required to provide:
     *   - controllers
     *   - request
     *   - response
     *   - router
     *   - view
     * 
     * @param ContainerInterface $container The container to use.
     * 
     * @return App
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    /**
     * Sets the controller key name in the context returned from the router.
     * 
     * @param string $key The controller key.
     * 
     * @return App
     */
    public function key($key)
    {
        $this->key = $key;
        return $this;
    }
    
    /**
     * Runs the application.
     * 
     * @return App
     */
    public function run()
    {
        // get the request once
        $request = $this->container->get('request');
        
        // a route must be matched and contain values
        if ($context = $this->container->get('router')->route($request)) {
            $context = $this->callController($request);
        } elseif (is_array($context)) {
            throw new LogicException('The matched route did not contain any context.');
        } else {
            throw new LogicException('No route was matched. Try adding a catch all.');
        }
        
        // ensure array
        $context = $this->ensureContextArray($context);
        
        // output the rendered view using the response
        $this->container->get('response')->output($this->container->get('view')->render($context));
        
        return $this;
    }
    
    /**
     * Calls the controller that was routed to.
     * 
     * @param RequestInterface $request The request to call the controller with.
     * 
     * @return array
     */
    private function callController(RequestInterface $request)
    {
        try {
            return $this->resolve($request)->action();
        } catch (Exception $e) {
            throw new LogicException(sprintf(
                'The controller could not be resolved with message: %s',
                $e->getMessage()
            ));
        }
    }
    
    /**
     * Resolves the controller using the specified request.
     * 
     * @param RequestInterface $request The request to call the controller with.
     * 
     * @return array
     */
    private function resolve(RequestInterface $request)
    {
        if ($controller = $request->getParam($this->key)) {
            return $this->container->get('controllers')->get($controller, [$request]);
        }
        
        throw new LogicException(sprintf(
            'The parameter "%s" was not in the request "%s".',
            $this->key,
            $request->__toString()
        ));
    }
    
    /**
     * Ensures an array from the context returned from the controller.
     * 
     * @param mixed $context The context returned from the controller.
     * 
     * @return array
     */
    private function ensureContextArray($context)
    {
        // ensure array
        if (!$context) {
            $context = [];
        }
        
        // enforce array
        if (!is_array($context)) {
            throw new UnexpectedValueException('Controllers must return an array.');
        }
        
        return $context;
    }
}