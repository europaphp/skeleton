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
    private $config = [
        'controller.param'   => 'controller',
        'controller.prefix'  => 'Controller\\',
        'controller.suffix'  => '',
        'events.action.pre'  => 'action.pre',
        'events.action.post' => 'action.post',
        'events.render.pre'  => 'render.pre',
        'events.render.post' => 'render.post',
        'events.route.pre'   => 'route.pre',
        'events.route.post'  => 'route.post',
        'events.send.pre'    => 'send.pre',
        'events.send.post'   => 'send.post'
    ];

    private $controllerLocator;

    private $event;

    private $request;

    private $response;

    private $router;

    private $viewNegotiator;
    
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
        $this->controllerLocator = new Locator;
        $this->event             = new Manager;
        $this->request           = RequestAbstract::isCli() ? new CliRequest : new HttpRequest;
        $this->response          = RequestAbstract::isCli() ? new CliResponse : new HttpResponse;
        $this->router            = new Router;
        $this->viewNegotiator    = new Negotiator($this->request);

        $this->controllerLocator->getFilter()->add(new ClassNameFilter($this->config->controller));
    }

    /**
     * Runs the application.
     * 
     * @return App
     */
    public function __invoke()
    {
        $this->runRouter()->runResponse($this->runView($this->runController()));
        return $this;
    }

    public function setConfig(ConfigInterface $config)
    {
        $this->config = $config;
        return $this;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function setControllerLocator(callable $controllerLocator)
    {
        $this->controllerLocator = $controllerLocator;
        return $this;
    }

    public function getControllerLocator()
    {
        return $this->controllerLocator;
    }

    public function setEvent(ManagerInterface $event)
    {
        $this->event = $event;
        return $this;
    }

    public function getEvent()
    {
        return $this->event;
    }

    public function setRequest(RequestInterface $request)
    {
        $this->request = $request;
        return $this;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function setResponse(ResponseInterface $response)
    {
        $this->response = $response;
        return $this;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function setRouter(callable $router)
    {
        $this->router = $router;
        return $this;
    }

    public function getRouter()
    {
        return $this->router;
    }

    public function setViewNegotiator(callable $viewNegotiator)
    {
        $this->viewNegotiator = $viewNegotiator;
        return $this;
    }

    public function getViewNegotiator()
    {
        return $this->viewNegotiator;
    }
    
    /**
     * Routes the request.
     * 
     * @return App
     */
    private function runRouter()
    {
        $this->event->trigger($this->config->events->route->pre, [$this]);

        if ($this->router) {
            call_user_func($this->router, $this->request);
        }
        
        $this->event->trigger($this->config->events->route->post, [$this, $context]);

        return $this;
    }

    /**
     * Runs the controller.
     * 
     * @return array
     */
    public function runController()
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
}