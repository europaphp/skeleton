<?php

namespace Europa\App;
use LogicException;
use Europa\Di\ContainerInterface;
use Europa\Event\Eventable;
use Europa\Request\RequestInterface;
use Europa\Response\ResponseInterface;
use Europa\Router\RouterInterface;
use Europa\View\ViewInterface;
use Europa\View\ViewScriptInterface;
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
    use Eventable;
    
    /**
     * The default controller key name.
     * 
     * @var string
     */
    const KEY = 'controller';

    /**
     * Event that gets called before routing.
     * 
     * @var string
     */
    const EVENT_ROUTE_PRE = 'route.pre';

    /**
     * Event that gets called after routing.
     * 
     * @var string
     */
    const EVENT_ROUTE_POST = 'route.post';

    /**
     * Event that gets called before actioning.
     * 
     * @var string
     */
    const EVENT_ACTION_PRE = 'action.pre';

    /**
     * Event that gets called after actioning.
     * 
     * @var string
     */
    const EVENT_ACTION_POST = 'action.post';

    /**
     * Event that gets called before rendering.
     * 
     * @var string
     */
    const EVENT_RENDER_PRE = 'render.pre';

    /**
     * Event that gets called after rendering.
     * 
     * @var string
     */
    const EVENT_RENDER_POST = 'render.post';

    /**
     * Event that gets called before sending.
     * 
     * @var string
     */
    const EVENT_SEND_PRE = 'send.pre';

    /**
     * Event that gets called after sending.
     * 
     * @var string
     */
    const EVENT_SEND_POST = 'send.post';
    
    /**
     * The controller container responsible for finding a controller.
     * 
     * @var ContainerInterface
     */
    private $controllers;
    
    /**
     * The controller key name in the context returned from the router.
     * 
     * @var string
     */
    private $key = self::KEY;
    
    /**
     * The request responsible for supplying information to the controller.
     * 
     * @var RequestInterface
     */
    private $request;
    
    /**
     * The response responsible for sending the rendered view.
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
     * Constructs a new application.
     * 
     * @param ContainerInterface $controllers The controller container responsible for finding a controller.
     * @param RequestInterface   $request     The request responsible for supplying information to the controller.
     * @param ResponseInterface  $response    The response responsible for sending the rendered view.
     * 
     * @return App
     */
    public function __construct(ContainerInterface $controllers, RequestInterface $request, ResponseInterface $response)
    {
        $this->controllers = $controllers;
        $this->request     = $request;
        $this->response    = $response;
    }
    
    /**
     * Directly sets which controller to use on the request.
     * 
     * @param string $controller The controller name.
     * 
     * @return App
     */
    public function setController($controller)
    {
        $this->request->setParam($this->key, $controller);
        return $this;
    }
    
    /**
     * Returns the name of the controller from the request.
     * 
     * @return string
     */
    public function getController()
    {
        return $this->request->getParam($this->key);
    }
    
    /**
     * Sets the controller container.
     * 
     * @param ContainerInterface $controllers The controller container.
     * 
     * @return App
     */
    public function setControllers(ContainerInterface $controllers)
    {
        $this->controllers = $controllers;
        return $this;
    }
    
    /**
     * Returns the controller container.
     * 
     * @return ContainerInterface
     */
    public function getControllers()
    {
        return $this->controllers;
    }
    
    /**
     * Sets the request.
     * 
     * @param RequestInterface $request The request.
     * 
     * @return App
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
     * Sets the response.
     * 
     * @param ResponseInterface $response The response.
     * 
     * @return App
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
     * Sets the router.
     * 
     * @param RouterInterface $router The router.
     * 
     * @return App
     */
    public function setRouter(RouterInterface $router)
    {
        $this->router = $router;
        return $this;
    }
    
    /**
     * Returns the router.
     * 
     * @return RouterInterface
     */
    public function getRouter()
    {
        return $this->router;
    }
    
    /**
     * Removes the router.
     * 
     * @return App
     */
    public function removeRouter()
    {
        $this->router = null;
        return $this;
    }
    
    /**
     * Sets the view.
     * 
     * @param ViewInterface $view The view.
     * 
     * @return App
     */
    public function setView(ViewInterface $view)
    {
        $this->view = $view;
        return $this;
    }
    
    /**
     * Returns the view.
     * 
     * @return ViewInterface
     */
    public function getView()
    {
        return $this->view;
    }
    
    /**
     * Removes the view.
     * 
     * @return App
     */
    public function removeView()
    {
        $this->view = null;
        return $this;
    }
    
    /**
     * Sets the controller key name in the context returned from the router.
     * 
     * @param string $key The controller key.
     * 
     * @return App
     */
    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }
    
    /**
     * Sets the key.
     * 
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }
    
    /**
     * Runs the application.
     * 
     * @return App
     */
    public function run()
    {
        return $this->runRouter()->runResponse($this->runView($this->runController()));
    }
    
    /**
     * Routes the request.
     * 
     * @return App
     */
    private function runRouter()
    {    
        $this->event()->trigger(self::EVENT_ROUTE_PRE, [$this]);
        
        if ($this->router) {
            $this->router->route($this->request);
        }
        
        $this->event()->trigger(self::EVENT_ROUTE_POST, [$this]);
        
        return $this;
    }
    
    /**
     * Actions the controller.
     * 
     * @return array
     */
    private function runController()
    {    
        $this->event()->trigger(self::EVENT_ACTION_PRE, [$this]);
        
        $context = $this->callController();
        $context = $this->ensureContextArray($context);
        
        $this->event()->trigger(self::EVENT_ACTION_POST, [$this, $context]);
        
        return $context;
    }
    
    /**
     * Runs the view and returns the rendered string.
     * 
     * @param array $context The context returned from the actioned controller.
     * 
     * @return string
     */
    private function runView(array $context)
    {
        $rendered = null;
        
        $this->event()->trigger(self::EVENT_RENDER_PRE, [$this, $context]);
        
        if ($this->view) {
            $rendered = $this->response->setBody($this->view->render($context));
        }
        
        $this->event()->trigger(self::EVENT_RENDER_POST, [$this, $rendered]);
        
        return $rendered;
    }
    
    /**
     * Sends the response.
     * 
     * @param string $rendered The rendered view.
     * 
     * @return App
     */
    private function runResponse($rendered)
    {
        $this->event()->trigger(self::EVENT_SEND_PRE, [$this, $rendered]);
        
        $this->response->send();
        
        $this->event()->trigger(self::EVENT_SEND_POST, [$this, $rendered]);
        
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
            return $this->resolveController()->action();
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
    private function resolveController()
    {
        if ($controller = $this->getController()) {
            return $this->controllers->__get($controller);
        }
        
        throw new LogicException(sprintf(
            'The controller parameter "%s" was not found in the request "%s".',
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
            throw new UnexpectedValueException('Controller actions must return an array.');
        }
        
        return $context;
    }
}