<?php

namespace Europa\App;
use LogicException;
use Europa\Config\Config;
use Europa\Di\ContainerInterface;
use Europa\Di\Locator;
use Europa\Event\Manager;
use Europa\Filter\ClassNameFilter;
use Europa\Request\Cli as CliRequest;
use Europa\Request\Http as HttpRequest;
use Europa\Request\RequestInterface;
use Europa\Request\RequestAbstract;
use Europa\Response\Cli as CliResponse;
use Europa\Response\Http as HttpResponse;
use Europa\Response\ResponseInterface;
use Europa\Router\Router;
use Europa\Router\RouterInterface;
use Europa\View\Negotiator;
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
class App
{
    /**
     * The application configuration.
     * 
     * @var array | Config
     */
    private $config = [
        'controller' => [
            'param' => 'controller'
        ],
        'controllerLocator'  => [
            'filters' => [
                'Europa\Filter\ClassNameFilter' => ['prefix' => 'Controller\\']
            ]
        ],
        'events.action.pre'  => 'action.pre',
        'events.action.post' => 'action.post',
        'events.render.pre'  => 'render.pre',
        'events.render.post' => 'render.post',
        'events.route.pre'   => 'route.pre',
        'events.route.post'  => 'route.post',
        'events.send.pre'    => 'send.pre',
        'events.send.post'   => 'send.post',
        'router'             => [],
        'viewNegotiator'     => []
    ];

    /**
     * The locator used to locate a controller.
     * 
     * @var callable
     */
    private $controllerLocator;

    /**
     * The event manager.
     * 
     * @var ManagerInterface
     */
    private $event;

    /**
     * The request.
     * 
     * @var RequestInterface
     */
    private $request;

    /**
     * The response.
     * 
     * @var ResponseInterface
     */
    private $response;

    /**
     * The router.
     * 
     * @var callable
     */
    private $router;

    /**
     * The view negotiator.
     * 
     * @var callable
     */
    private $viewNegotiator;

    /**
     * The request filter.
     * 
     * @var callable
     */
    private $requestFilter;
    
    /**
     * Constructs a new application.
     * 
     * @param mixed $config The configuration.
     * 
     * @return App
     */
    public function __construct($config = [])
    {
        $this->config            = new Config($this->config, $config);
        $this->controllerLocator = new Locator($this->config->controllerLocator);
        $this->event             = new Manager;
        $this->request           = RequestAbstract::isCli() ? new CliRequest : new HttpRequest;
        $this->response          = RequestAbstract::isCli() ? new CliResponse : new HttpResponse;
        $this->router            = new Router($this->config->router);
        $this->viewNegotiator    = new Negotiator($this->config->viewNegotiator);
        $this->requestFilter     = [$this, 'requestFilter'];

        // Pass on the controller configuration.
        $this->controllerLocator->args('Europa\Controller\ControllerAbstract', function() {
            return [$this->config->controller];
        });
    }

    /**
     * Runs the application.
     * 
     * @return App
     */
    public function __invoke()
    {
        $this->runResponse($this->runView($this->runController($this->runRouter())));
        return $this;
    }

    /**
     * Sets the controller locator.
     * 
     * @param callable $controllerLocator The locator used for finding a controller.
     * 
     * @return App
     */
    public function setControllerLocator(callable $controllerLocator)
    {
        $this->controllerLocator = $controllerLocator;
        return $this;
    }

    /**
     * Returns the controller locator.
     * 
     * @return callable
     */
    public function getControllerLocator()
    {
        return $this->controllerLocator;
    }

    /**
     * Sets the event manager that handles the triggering of application events.
     * 
     * @param ManagerInterface $event The application event manager.
     * 
     * @return App
     */
    public function setEvent(ManagerInterface $event)
    {
        $this->event = $event;
        return $this;
    }

    /**
     * Returns the application event manager.
     * 
     * @return ManagerInterface
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Sets the application request.
     * 
     * @param RequestInterface $request The request to use.
     * 
     * @return App
     */
    public function setRequest(RequestInterface $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * Returns the application request.
     * 
     * @return RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Sets the application response.
     * 
     * @param ResponseInterface $reseponse The reseponse to use.
     * 
     * @return App
     */
    public function setResponse(ResponseInterface $response)
    {
        $this->response = $response;
        return $this;
    }

    /**
     * Returns the application response.
     * 
     * @return ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Sets the application router.
     * 
     * @param callable $router The router to use.
     * 
     * @return App
     */
    public function setRouter(callable $router)
    {
        $this->router = $router;
        return $this;
    }

    /**
     * Returns the application router.
     * 
     * @return callable
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * Sets the application view negotiator.
     * 
     * @param callable $viewNegotiator The view negotiator to use.
     * 
     * @return App
     */
    public function setViewNegotiator(callable $viewNegotiator)
    {
        $this->viewNegotiator = $viewNegotiator;
        return $this;
    }

    /**
     * Returns the application view negotiator.
     * 
     * @return callable
     */
    public function getViewNegotiator()
    {
        return $this->viewNegotiator;
    }

    /**
     * Sets the filter that is used to turn the application request into a string.
     * 
     * @param callable $requestFilter The filter to use.
     * 
     * @return App
     */
    public function setRequestFilter(callable $requestFilter)
    {
        $this->requestFilter = $requestFilter;
        return $this;
    }

    /**
     * returns the request filter.
     * 
     * @return callable
     */
    public function getRequestFilter()
    {
        return $this->requestFilter;
    }
    
    /**
     * Routes the request.
     * 
     * @return App
     */
    private function runRouter()
    {
        $context = [];

        $this->event->trigger($this->config->events->route->pre, [$this]);

        if ($this->router) {
            $context = call_user_func($this->requestFilter, $this->request);
            $context = call_user_func($this->router, $context);
            $context = is_array($context) ? $context : [];
        }
        
        $this->event->trigger($this->config->events->route->post, [$this, $context]);

        return array_merge($this->request->getParams(), $context);
    }

    /**
     * Runs the controller.
     * 
     * @return array
     */
    public function runController(array $context)
    {
        $this->event->trigger($this->config->events->action->pre, [$this]);
        
        $controller = $this->request->getParam($this->config->controller->param);
        
        try {
            $controller = call_user_func($this->controllerLocator, $controller);
        } catch (Exception $e) {
            throw new RuntimeException(sprintf('The controller "%s" could not be found in the supplied service locator.', $controller));
        }

        $controller = call_user_func($controller, $this->request->getParams());

        $this->event->trigger($this->config->events->action->post, [$this, $controller]);

        return $controller ?: [];
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
        $view = call_user_func($this->viewNegotiator, $this->request);
        
        $this->event->trigger($this->config->events->render->pre, [$this, $view]);
        
        $rendered = $view->render($context);
        
        $this->event->trigger($this->config->events->render->post, [$this, $rendered]);
        
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
        $this->event->trigger($this->config->events->send->pre, [$this, $rendered]);
        
        $this->response->setBody($rendered)->send();
        
        $this->event->trigger($this->config->events->send->post, [$this, $rendered]);
        
        return $this;
    }

    private function requestFilter(RequestInterface $request)
    {
        return $request->__toString();
    }
}