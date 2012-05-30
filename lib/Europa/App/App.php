<?php

namespace Europa\App;
use Europa\Controller\ControllerInterface;
use Europa\Di\Container;
use Europa\View\ViewInterface;
use InvalidArgumentException;
use RuntimeException;
use UnexpectedValueException;

/**
 * Handles application dispatching.
 *
 * @category App
 * @package  Europa
 * @author   Paul Carvosso-White <paulcarvossowhite@gmail.com>
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class App implements AppInterface
{
    /**
     * The default controller key.
     * 
     * @var string
     */
    const CONTROLLER_KEY = 'controller';
    
    /**
     * The event triggered prior to request routing.
     * 
     * @var string
     */
    const EVENT_PRE_ROUTE = 'preRoute';
    
    /**
     * The event triggered after request routing. This gets called even if no router is set.
     * 
     * @var string
     */
    const EVENT_POST_ROUTE = 'postRoute';
    
    /**
     * The event triggered prior to controller initialization.
     * 
     * @var string
     */
    const EVENT_PRE_INIT = 'preInit';
    
    /**
     * The event triggered after controller initialization.
     * 
     * @var string
     */
    const EVENT_POST_INIT = 'postInit';
    
    /**
     * The event triggered prior to controller actioning.
     * 
     * @var string
     */
    const EVENT_PRE_ACTION = 'preAction';
    
    /**
     * The event triggered after controller actioning.
     * 
     * @var string
     */
    const EVENT_POST_ACTION = 'postAction';
    
    /**
     * The event triggered prior to action response rendering.
     * 
     * @var string
     */
    const EVENT_PRE_VIEW = 'preView';
    
    /**
     * The event triggered after the view renders the action response. This gets called even if no
     * view is set.
     * 
     * @var string
     */
    const EVENT_POST_VIEW = 'postView';
    
    /**
     * The event triggered prior to the rendered view is output.
     * 
     * @var string
     */
    const EVENT_PRE_OUTPUT = 'preOutput';
    
    /**
     * The event triggered after the rendered view is output.
     * 
     * @var string
     */
    const EVENT_POST_OUTPUT = 'postOutput';
    
    /**
     * The controller classname to use.
     * 
     * @var string
     */
    private $container;
    
    /**
     * The controller to dispatch. If set, no routing occurs. This is reset after the app is run.
     * 
     * @var string
     */
    private $controller;
    
    /**
     * The name of the parameter in the request that contains the controller name.
     * 
     * @var string
     */
    private $key = self::CONTROLLER_KEY;
    
    /**
     * Initializes the dispatcher.
     * 
     * @param Container The DI container to use for component resolution.
     * 
     * @return App
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->container->expect('controllers', 'Europa\Di\Container');
        $this->container->expect('event', 'Europa\Event\DispatcherInterface');
        $this->container->expect('request', 'Europa\Request\RequestInterface');
        $this->container->expect('response', 'Europa\Response\ResponseInterface');
    }
    
    /**
     * Sets the controller to be used.
     * 
     * @param string $controller The controller to use.
     * 
     * @return App
     */
    public function setController($controller)
    {
        $this->controller = $controller;
        return $this;
    }
    
    /**
     * Returns the current dispatching controller.
     * 
     * @return string
     */
    public function getController()
    {
        return $this->controller;
    }
    
    /**
     * Sets the proper key to use for retrieving the controller parameter from the request.
     * 
     * @param string $key The name of the parameter in the request that contains the name of the controller.
     * 
     * @return App
     */
    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }
    
    /**
     * Returns the set key.
     * 
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }
    
    /**
     * Actions the appropriate controller and outputs the response. If no router is specified, it attempts to dispatch
     * using whatever parameters that are already available on the request.
     * 
     * @return void
     */
    public function run()
    {
        // if no controller is specified, do routing
        if (!$this->controller) {
            $this->controller = $this->doRoute();
        }
        
        // initialize the controller
        $controller = $this->doInit($this->controller);
        
        // action the dispatching controller to get its response
        $context = $this->doAction($controller);
        
        // render the response from the controller
        $view = $this->doView($context);
        
        // output the rendered view
        $this->doOutput($view, $context);
        
        // remove the controller
        $this->controller = null;
    }
    
    /**
     * Performs routing on the request.
     * 
     * @return void
     */
    private function doRoute()
    {
        $this->container->event->trigger(self::EVENT_PRE_ROUTE, [$this]);
        
        // if a router is set, route the request
        if (isset($this->container->router)) {
            $this->container->router->route($this->container->request);
        }
        
        // get the controller from the request
        $controller = $this->container->request->getParam($this->key);
        
        $this->container->event->trigger(self::EVENT_POST_ROUTE, [$this, $controller]);
        
        // return the matched controller
        return $controller;
    }
    
    /**
     * Performs controller initialization.
     * 
     * @return ControllerInterface
     */
    private function doInit($controller)
    {
        // pre-init hook
        $this->container->event->trigger(self::EVENT_PRE_INIT, [$this, $controller]);
        
        // safe to assume it's a valid object now
        $controller = $this->container->controllers->$controller;
        
        // post-event hook
        $this->container->event->trigger(self::EVENT_POST_INIT, [$this, $controller]);
        
        return $controller;
    }
    
    /**
     * Performs actioning on the specified controller and returns the action's response.
     * 
     * @param ControllerInterface $controller The controller to action.
     * 
     * @return array
     */
    private function doAction(ControllerInterface $controller)
    {
        // action hook
        $this->container->event->trigger(self::EVENT_PRE_ACTION, [$this, $controller]);
        
        // the response is a simple array
        $context = $controller->action();
        
        // if a response is not given (or evaluates to a false value) default to an empty array
        if (!$context) {
            $context = [];
        }
        
        // validate response
        if (!is_array($context)) {
            throw new UnexpectedValueException('Controllers must either return nothing or an associative array.');
        }
        
        // post-action hook
        $this->container->event->trigger(self::EVENT_POST_ACTION, [$this, $controller, $context]);
        
        return $context;
    }
    
    /**
     * Performs rendering of the specified response and returns the output.
     * 
     * @param array $context The context to render.
     * 
     * @return string
     */
    private function doView(array $context)
    {
        // pre-render hook
        $this->container->event->trigger(self::EVENT_PRE_VIEW, [$this, $context]);
        
        // attempt to resolve the view
        $view = $this->resolveView();
        
        // post-render hook
        $this->container->event->trigger(self::EVENT_POST_VIEW, [$this, $view, $context]);
        
        return $view;
    }
    
    /**
     * Outputs the specified view.
     * 
     * @param ViewInterface $view    The view to output.
     * @param array         $context The context to render the view with.
     * 
     * @return void
     */
    private function doOutput(ViewInterface $view = null, array $context = [])
    {
        // pre-output hook
        $this->container->event->trigger(self::EVENT_PRE_OUTPUT, [$this, $view, $context]);
        
        // output response
        $this->container->response->output($view, $context);
        
        // post-output hook
        $this->container->event->trigger(self::EVENT_POST_OUTPUT, [$this, $view, $context]);
    }
    
    /**
     * Resolves the view to use.
     * 
     * @return ViewInterface | null
     */
    private function resolveView()
    {
        if ($view = $this->resolveViewFromSuffix()) {
            return $view;
        }
        
        if ($view = $this->resolveViewFromType()) {
            return $view;
        }
    }
    
    /**
     * Resolves the view to use.
     * 
     * @return ViewInterface | null
     */
    private function resolveViewFromSuffix()
    {
        $suffix = $this->container->request->getUri()->getSuffix();
        
        if (isset($this->container->views->$suffix)) {
            return $this->container->views->$suffix;
        }
    }
    
    /**
     * Resolves the view to use.
     * 
     * @return ViewInterface | null
     */
    private function resolveViewFromType()
    {
        foreach ($this->container->request->getAcceptedContentTypes() as $type) {
            if (isset($this->container->views->$type)) {
                return $this->container->views->$type;
            }
        }
    }
}