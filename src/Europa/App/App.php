<?php

namespace Europa\App;
use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;
use Europa\Config\Config;
use Europa\Exception\Exception;
use Europa\Fs\Loader;
use Europa\Fs\Locator;
use Europa\Request\Cli as CliRequest;
use Europa\Request\Http as HttpRequest;
use Europa\Request\RequestAbstract;
use Europa\Response\Cli as CliResponse;
use Europa\Response\Http as HttpResponse;
use Europa\Router\Router;
use Europa\View\Negotiator;

class App implements ArrayAccess, IteratorAggregate
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
        'paths.root'   => '..',
        'paths.app'    => '={root}/app',
        'view.default' => 'Europa\View\Php'
    ];

    private $loader;

    private $loaderLocator;

    private $modules = [];

    private $request;

    private $response;

    private $router;

    private $views;

    private $viewLocator;

    /**
     * Sets up a new application.
     * 
     * @param array | object $config The application configuration.
     * 
     * @return App
     */
    public function __construct($config = [])
    {
        $this->config        = new Config($this->config, $config);
        $this->loader        = new Loader;
        $this->loaderLocator = new Locator;
        $this->request       = RequestAbstract::isCli() ? new CliRequest : new HttpRequest;
        $this->response      = RequestAbstract::isCli() ? new CliResponse : new HttpResponse;
        $this->router        = new Router;
        $this->views         = new Negotiator;
        $this->viewLocator   = new Locator;
    }

    /**
     * Runs the application.
     * 
     * @return App
     */
    public function __invoke()
    {
        $this->loader->register();
        $this->loader->setLocator($this->loaderLocator);
        
        foreach ($this->modules as $module) {
            $this->config->import($module->getConfig());
            $this->router->import($module->getRoutes());
            $this->loaderLocator->addPaths($module->getClassPaths());
            $this->viewLocator->addPaths($module->getViewPaths());
            
            if (is_callable($bootstrapper = $module->getBootstrapper())) {
                call_user_func($bootstrapper);
            }
        }
        
        if (!$controller = call_user_func($this->router, $this->request)) {
            Exception::toss('The router could not find a suitable controller for the given request.', $controller);
        }

        $context = call_user_func($controller, $this->request);

        if ($this->request instanceof Http && $view = $this->request->accepts($this->config->views->map->keys())) {
            $view = new $view($this->config->views->config->$view);
        }

        if ($view instanceof ViewScriptInterface) {
            $view->setLocator($this->viewLocator);
            $view->setScript();
        }

        $this->response->output(call_user_func($view, $context));

        return $this;
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

        Exception::toss('The module at offset "%s" does not exist.', $offset);
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
     * Returns an iteartor containing the modules
     * 
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->modules);
    }
}