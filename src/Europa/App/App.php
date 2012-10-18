<?php

namespace Europa\App;
use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;
use Europa\Config\Config;
use Europa\Di\ServiceContainer;
use Europa\Di\ServiceContainerInterface;
use Europa\Exception\Exception;
use Europa\Fs\Loader;
use Europa\Fs\Locator;
use Europa\Request\RequestAbstract;
use Europa\Response\ResponseAbstract;
use Europa\Router\Router;
use Europa\View\HelperConfiguration;
use Europa\View\Negotiator;
use Europa\View\Php;
use Europa\View\ViewScriptInterface;

class App implements ArrayAccess, IteratorAggregate
{
    /**
     * The application configuration.
     * 
     * @var array | Config
     */
    private $config = [
        'paths.root'   => '..',
        'paths.app'    => '={root}/app',
        'view.default' => 'Europa\View\Php',
        'view.script'  => ':controller/:action',
        'view.suffix'  => 'php'
    ];

    private $loader;

    private $loaderLocator;

    private $modules = [];

    private $request;

    private $response;

    private $router;

    private $negotiator;

    private $viewLocator;

    private $viewHelpers;

    /**
     * Sets up a new application.
     * 
     * @param array | object $config The application configuration.
     * 
     * @return App
     */
    public function __construct($config = [])
    {
        $this->container                = new ServiceContainer;
        $this->container->config        = new Config($this->config, $config);
        $this->container->loader        = new Loader;
        $this->container->loaderLocator = new Locator;
        $this->container->request       = RequestAbstract::detect();
        $this->container->response      = ResponseAbstract::detect();
        $this->container->router        = new Router;
        $this->container->negotiator    = new Negotiator;
        $this->container->viewLocator   = new Locator;
        $this->container->viewHelpers   = new ServiceContainer;
        $this->container->viewHelpers->configure(new HelperConfiguration($this->container), $this->container);
    }

    /**
     * Runs the application.
     * 
     * @return App
     */
    public function __invoke()
    {
        $this->container->loader->register();
        $this->container->loader->setLocator($this->container->loaderLocator);
        
        foreach ($this->modules as $module) {
            $this->container->config->import($module->getConfig());
            $this->container->router->import($module->getRoutes());
            $this->container->loaderLocator->addPaths($module->getClassPaths());
            $this->container->viewLocator->addPaths($module->getViewPaths());
            
            if (is_callable($bootstrapper = $module->getBootstrapper())) {
                call_user_func($bootstrapper);
            }
        }
        
        if (!$controller = call_user_func($this->container->router, $this->container->request)) {
            Exception::toss('The router could not find a suitable controller for the given request.', $controller);
        }

        $context = call_user_func($controller, $this->container->request);
        $view    = call_user_func($this->container->negotiator, $this->container->request);

        $this->configureView($view);

        $this->container->response->setBody(call_user_func($view, $context ?: []));
        $this->container->response->send();

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
            $module = new Module($this->container->config->paths->app . '/' . $module);
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

    /**
     * Sets the service container to use.
     * 
     * @param ServiceContainerInterface $container The service container.
     * 
     * @return App
     */
    public function setServiceContainer(ServiceContainerInterface $container)
    {
        $this->container = $container;
        return $this;
    }

    /**
     * Returns the service container.
     * 
     * @return ServiceContainerInterface
     */
    public function getServiceContainer()
    {
        return $this->container;
    }

    /**
     * Formats the view script so it can be set on a `ViewScriptInterface` object.
     * 
     * @return string
     */
    private function formatViewScript()
    {
        $format = $this->container->config->view->script;

        if (is_callable($format)) {
            return call_user_func($format, $this->container->request);
        }

        foreach ($this->container->request->getParams() as $name => $param) {
            $format = str_replace(':' . $name, $param, $format);
        }

        return $format;
    }

    private function configureView($view)
    {
        if ($view instanceof ViewScriptInterface) {
            $view->setScriptLocator($this->container->viewLocator);
            $view->setScript($this->formatViewScript());
            $view->setScriptSuffix($this->container->config->view->suffix);
        }

        if ($view instanceof Php) {
            $view->setHelperServiceContainer($this->container->viewHelpers);
        }
    }
}