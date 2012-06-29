<?php

namespace Europa\App;
use LogicException;
use Europa\Di\ContainerInterface;
use Europa\Request\RequestInterface;
use Europa\Response\ResponseInterface;
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
     * The controller container responsible for finding a controller.
     * 
     * @var ContainerInterface
     */
    private $controllers;
    
    /**
     * The request responsible for supplying information to the controller.
     * 
     * @var RequestInterface
     */
    private $request;
    
    /**
     * The response responsible for outputting the rendered view.
     * 
     * @var ResponseInterface
     */
    private $response;
    
    /**
     * The router to use for routing the request.
     * 
     * @var RouterInterface
     */
    private $router;
    
    /**
     * The view responsible for rendering controller response.
     * 
     * @var ViewInterface
     */
    private $view;
    
    /**
     * The controller key name in the context returned from the router.
     * 
     * @var string
     */
    private $key = self::KEY;
    
    /**
     * Constructs a new application.
     * 
     * @param ContainerInterface $controllers The controller container responsible for finding a controller.
     * @param RequestInterface   $request     The request responsible for supplying information to the controller.
     * @param ResponseInterface  $response    The response responsible for outputting the rendered view.
     * @param RouterInterface    $router      The router to use for routing the request.
     * @param ViewInterface      $view        The view responsible for rendering controller response.
     * 
     * @return App
     */
    public function __construct(
        ContainerInterface $controllers,
        RequestInterface   $request,
        ResponseInterface  $response,
        RouterInterface    $router,
        ViewInterface      $view
    ) {
        $this->controllers = $controllers;
        $this->request     = $request;
        $this->response    = $response;
        $this->router      = $router;
        $this->view        = $view;
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
        // a route must be matched and contain values
        if ($context = $this->router->route($this->request)) {
            $context = $this->callController();
        } elseif (is_array($context)) {
            throw new LogicException('The matched route did not contain any context.');
        } else {
            throw new LogicException('No route was matched. Try adding a catch all.');
        }
        
        // ensure array
        $context = $this->ensureContextArray($context);
        
        // output the rendered view using the response
        $this->response->output($this->view->render($context));
        
        return $this;
    }
    
    /**
     * Calls the controller that was routed to.
     * 
     * @return array
     */
    private function callController()
    {
        try {
            return $this->resolve()->action();
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
     * @return array
     */
    private function resolve()
    {
        if ($controller = $this->request->getParam($this->key)) {
            return $this->controllers->get($controller, [$this->request]);
        }
        
        throw new LogicException(sprintf(
            'The parameter "%s" was not in the request "%s".',
            $this->key,
            (string) $this->request
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