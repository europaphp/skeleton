<?php

namespace Europa\App;
use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;
use LogicException;
use Europa\Config\Config;
use Europa\Di\ContainerInterface;
use Europa\Di\Locator;
use Europa\Event\Manager;
use Europa\Filter\ClassNameFilter;
use Europa\Fs\Loader\ClassLoader;
use Europa\Fs\Locator\LocatorArray;
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
use Europa\View\ViewScriptInterface;
use Exception;
use RuntimeException;
use UnexpectedValueException;

class App implements AppInterface, ArrayAccess, IteratorAggregate
{
    const EVENT_ROUTE_PRE = 'route.pre';

    const EVENT_ROUTE_POST = 'route.post';

    const EVENT_ACTION_PRE = 'action.pre';

    const EVENT_ACTION_POST = 'action.post';

    const EVENT_RENDER_PRE = 'render.pre';

    const EVENT_RENDER_POST = 'render.post';

    const EVENT_SEND_PRE = 'send.pre';

    const EVENT_SEND_POST = 'send.post';

    /**
     * The application configuration.
     * 
     * @var array | Config
     */
    private $config = [
        'controller.default' => 'index',
        'controller.error'   => 'error',
        'config.controllerContainer'  => [
            'filters' => [
                'Europa\Filter\ClassNameFilter' => ['prefix' => 'Controller\\']
            ]
        ],
        'config.router'         => [],
        'config.viewNegotiator' => [],
        'paths.root'            => '..',
        'paths.app'             => '={root}/app'
    ];

    private $classLoader;

    /**
     * The locator used to locate a controller.
     * 
     * @var callable
     */
    private $controllerContainer;

    /**
     * The event manager.
     * 
     * @var ManagerInterface
     */
    private $event;

    /**
     * The language file locator.
     * 
     * @var callable
     */
    private $langLocator;

    /**
     * The list of application modules.
     * 
     * @var array
     */
    private $modules = [];

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
     * Locator used to find view scripts.
     * 
     * @var callable
     */
    private $viewLocator;

    /**
     * The view negotiator used to return the appropriate view for the given request.
     * 
     * @var callable
     */
    private $viewNegotiator;

    /**
     * The view script filter responsible for returning a default script for a `ViewScriptInterface`.
     * 
     * @var callable
     */
    private $viewScriptFilter;

    /**
     * Sets up a new application.
     * 
     * @param array | object $config The application configuration.
     * 
     * @return App
     */
    public function __construct($config = [])
    {
        $this->initConfig($config);
        $this->initClassLoader();
        $this->initControllerContainer();
        $this->initEvent();
        $this->initLangLocator();
        $this->initRequest();
        $this->initResponse();
        $this->initRouter();
        $this->initViewLocator();
        $this->initViewNegotiator();
        $this->initViewScriptFilter();
    }

    /**
     * @see self->offsetSet()
     */
    public function __set($name, $value)
    {
        $this->offsetSet($name, $value);
    }

    /**
     * @see self->offsetGet()
     */
    public function __get($name)
    {
        return $this->offsetGet($name);
    }

    /**
     * @see self->offsetExists()
     */
    public function __isset($name)
    {
        return $this->offsetExists($name);
    }

    /**
     * @see self->offsetUnset()
     */
    public function __unset($name)
    {
        $this->offsetUnset($name);
    }

    /**
     * Bootstraps the application.
     * 
     * @return App
     */
    public function bootstrap()
    {
        $this->classLoader->register();

        foreach ($this->modules as $module) {
            $this->classLoader->getLocator()->add($module->getClassLocator());
            $this->langLocator->add($module->getLangLocator());
            $this->viewLocator->add($module->getViewLocator());
            $this->router->import($module->getRoutes());
            
            $module->bootstrap();
        }

        return $this;
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
     * Registers a module.
     * 
     * @param mixed                    $offset The module index.
     * @param string | ModuleInterface $module The module to register.
     * 
     * @return App
     */
    public function offsetSet($offset, $module)
    {
        if (!$module instanceof ModuleInterface) {
            $module = new Module($this->config->paths->app . '/' . $module);
        }

        $this->modules[$offset] = $module;
    }

    /**
     * Returns the specified module or throws an exception if it does not exist.
     * 
     * @param mixed $offset The module offset.
     * 
     * @return ModuleInterface
     * 
     * @throws LogicException If the module does not exist.
     */
    public function offsetGet($offset)
    {
        if (isset($this->modules[$offset])) {
            return $this->modules[$offset];
        }

        throw new LogicException(sprintf('The module at offset "%s" does not exist.', $offset));
    }

    /**
     * Returns whether or not the module exists.
     * 
     * @param mixed $offset The module offset.
     * 
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->modules[$offset]);
    }

    /**
     * Removes the module if it exists.
     * 
     * @param mixed $offset The module offset.
     * 
     * @return bool
     */
    public function offsetUnset($offset)
    {
        if (isset($this->modules[$offset])) {
            unset($this->modules[$offset]);
        }
    }

    /**
     * Sets the controller locator.
     * 
     * @param callable $controllerContainer The locator used for finding a controller.
     * 
     * @return App
     */
    public function setControllerContainer(callable $controllerContainer)
    {
        $this->controllerContainer = $controllerContainer;
        return $this;
    }

    /**
     * Returns the controller locator.
     * 
     * @return callable
     */
    public function getControllerContainer()
    {
        return $this->controllerContainer;
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
     * Sets the filter used to return the path to the view script.
     * 
     * @param callable $viewScriptFilter The view script filter.
     * 
     * @return Negotiator
     */
    public function setViewScriptFilter(callable $viewScriptFilter)
    {
        $this->viewScriptFilter = $viewScriptFilter;
        return $this;
    }

    /**
     * Returns the view script filter.
     * 
     * @return callable
     */
    public function getViewScriptFilter()
    {
        return $this->viewScriptFilter;
    }
    
    /**
     * Routes the request.
     * 
     * @return App
     */
    private function runRouter()
    {
        $this->event->trigger(self::EVENT_ROUTE_PRE, [$this]);

        call_user_func($this->router, $this->request);
        
        $this->event->trigger(self::EVENT_ROUTE_POST, [$this]);

        return $this;
    }

    /**
     * Runs the controller.
     * 
     * @return array
     */
    private function runController()
    {
        $this->event->trigger(self::EVENT_ACTION_PRE, [$this]);

        $default   = $this->config->controller->default;
        $error     = $this->config->controller->error;
        $specified = $this->request->hasParam(self::PARAM_CONTROLLER);
        $detected  = $specified ? $this->request->getParam(self::PARAM_CONTROLLER) : $default;
        
        try {
            $controller = call_user_func($this->controllerContainer, $detected);
            $controller = call_user_func($controller, $this->request);
        } catch (Exception $e) {
            $this->request->setParam(self::PARAM_EXCEPTION, $e);
            $controller = call_user_func($this->controllerContainer, $error);
            $controller = call_user_func($controller, $this->request);
        } catch (Exception $e) {
            if ($specified) {
                throw new RuntimeException(sprintf('The specified controller "%s" could not be found in the application controller container. Tried using the default "%s" and error "%s" controllers, but they were not found either.', $detected, $default, $error));
            }
            
            throw new RuntimeException(sprintf('No controller was found in the request or router. Additionally, no default "%s" or error "%s" controllers were found in the application controller container.', $default, $error));
        }

        $this->event->trigger(self::EVENT_ACTION_POST, [$this, $controller]);

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

        if ($view instanceof ViewScriptInterface) {
            $view->setScript(call_user_func($this->viewScriptFilter, $this->request));
            $view->setScriptLocator($this->viewLocator);
        }
        
        $this->event->trigger(self::EVENT_RENDER_PRE, [$this, $view]);
        
        $rendered = $view->render($context);
        
        $this->event->trigger(self::EVENT_RENDER_POST, [$this, $rendered]);
        
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
        $this->event->trigger(self::EVENT_SEND_PRE, [$this, $rendered]);
        
        $this->response->setBody($rendered)->send();
        
        $this->event->trigger(self::EVENT_SEND_POST, [$this, $rendered]);
        
        return $this;
    }

    /**
     * Allows you to iterate over the app.
     * 
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->modules);
    }

    /**
     * Sets a default class loader.
     * 
     * @return void
     */
    private function initClassLoader()
    {
        $this->classLoader = new ClassLoader;
    }

    /**
     * Sets the default configuration and merges in the custom config.
     * 
     * @param array | object $config The custom configuration.
     * 
     * @return void
     */
    private function initConfig($config)
    {
        $this->config = new Config($this->config, $config);
    }

    /**
     * Sets a default controller container.
     * 
     * @return void
     */
    private function initControllerContainer()
    {
        $this->controllerContainer = new Locator($this->config->config->controllerContainer);
        $this->controllerContainer->args('Europa\Controller\ControllerAbstract', function() {
            return [$this->config->config->controller];
        });
    }

    /**
     * Sets a default event manager.
     * 
     * @return void
     */
    private function initEvent()
    {
        $this->event = new Manager;
    }

    /**
     * Sets a default language file locator.
     * 
     * @return void
     */
    private function initLangLocator()
    {
        $this->langLocator = new LocatorArray;
    }

    /**
     * Sets a default request based on how the app was accessed.
     * 
     * @return void
     */
    private function initRequest()
    {
        $this->request = RequestAbstract::isCli() ? new CliRequest : new HttpRequest;
    }

    /**
     * Sets a default response based on how the app was accessed.
     * 
     * @return void
     */
    private function initResponse()
    {
        $this->response = RequestAbstract::isCli() ? new CliResponse : new HttpResponse;
    }

    /**
     * Sets a default router.
     * 
     * @return void
     */
    private function initRouter()
    {
        $this->router = new Router($this->config->config->router);
    }

    /**
     * Sets a default view locator used to locate view scripts for anything implementing Europa\View\ViewScriptInterface.
     * 
     * @return void
     */
    private function initViewLocator()
    {
        $this->viewLocator = new LocatorArray;
    }

    /**
     * Sets a default view negotiator that is used to return the appropriate view for the given request.
     * 
     * @return void
     */
    private function initViewNegotiator()
    {
        $this->viewNegotiator = new Negotiator($this->config->config->viewNegotiator);
    }

    /**
     * Sets a default view script filter.
     * 
     * @return void
     */
    private function initViewScriptFilter()
    {
        $this->viewScriptFilter = function($request) {
            return $request->getParam(AppInterface::PARAM_CONTROLLER) . '/' . $request->getParam(AppInterface::PARAM_ACTION);
        };
    }
}