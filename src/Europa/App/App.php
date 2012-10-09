<?php

namespace Europa\App;
use LogicException;
use Europa\Di\ContainerInterface;
use Europa\Request\RequestInterface;
use Europa\Response\ResponseInterface;
use Europa\Router\RouterInterface;
use Europa\View\ViewInterface;
use Europa\View\ViewScriptInterface;
use Exception;
use RuntimeException;
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
     * The controller key name in the context returned from the router.
     * 
     * @var string
     */
    private $key = self::KEY;
    
    /**
     * Constructs a new application.
     * 
     * @param ContainerInterface $container The container that has all of the required dependencies to run the app.
     * 
     * @return App
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Returns the application container.
     * 
     * @return ContainerInterface
     */
    public function container()
    {
        return $this->container;
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
        $this->container->request->setParam($this->key, $controller);
        return $this;
    }
    
    /**
     * Returns the name of the controller from the request.
     * 
     * @return string
     */
    public function getController()
    {
        return $this->container->request->getParam($this->key);
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
        $this->container->event->trigger(self::EVENT_ROUTE_PRE, [$this]);
        
        if ($this->container->router) {
            $this->container->request->setParams(call_user_func($this->container->router, $this->container->request));
        }
        
        $this->container->event->trigger(self::EVENT_ROUTE_POST, [$this]);
        
        return $this;
    }
    
    /**
     * Actions the controller.
     * 
     * @return array
     */
    private function runController()
    {    
        $this->container->event->trigger(self::EVENT_ACTION_PRE, [$this]);
        
        $context = $this->callController();
        $context = $this->ensureContextArray($context);
        
        $this->container->event->trigger(self::EVENT_ACTION_POST, [$this, $context]);
        
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
        
        $this->container->event->trigger(self::EVENT_RENDER_PRE, [$this, $context]);
        
        if ($this->container->view) {
            $rendered = $this->container->response->setBody($this->container->view->render($context));
        }
        
        $this->container->event->trigger(self::EVENT_RENDER_POST, [$this, $rendered]);
        
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
        $this->container->event->trigger(self::EVENT_SEND_PRE, [$this, $rendered]);
        
        $this->container->response->send();
        
        $this->container->event->trigger(self::EVENT_SEND_POST, [$this, $rendered]);
        
        return $this;
    }
    
    /**
     * Calls the controller that was routed to.
     * 
     * @return array
     */
    private function callController()
    {
        return call_user_func($this->resolveController());
    }
    
    /**
     * Resolves the controller using the specified request.
     * 
     * @return array
     */
    private function resolveController()
    {
        if ($controller = $this->getController()) {
            try {
                return $this->container->controllers->$controller;
            } catch (Exception $e) {
                throw new RuntimeException(sprintf(
                    'The controller "%s" could not be found in the container "%s".',
                    $controller,
                    get_class($this->container->controllers)
                ));
            }
        }
        
        throw new LogicException(sprintf(
            'The controller parameter "%s" was not found in the request "%s".',
            $this->key,
            (string) $this->container->request
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