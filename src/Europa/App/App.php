<?php

namespace Europa\App;
use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;
use Europa\Config\Config;
use Europa\Exception\Exception;
use Europa\Fs\Loader;
use Europa\Fs\Locator;
use Europa\Request\RequestAbstract;
use Europa\Response\ResponseAbstract;
use Europa\Router\Router;
use Europa\View\Negotiator;
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
        $this->request       = RequestAbstract::detect();
        $this->response      = ResponseAbstract::detect();
        $this->router        = new Router;
        $this->negotiator    = new Negotiator;
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
        $view    = call_user_func($this->negotiator, $this->request);

        if ($this->request instanceof Http && $view = $this->request->accepts($this->config->views->map->keys())) {
            $view = new $view($this->config->views->config->$view);
        }

        if ($view instanceof ViewScriptInterface) {
            $view->setScriptLocator($this->viewLocator);
            $view->setScript($this->formatViewScript());
            $view->setScriptSuffix($this->config->view->suffix);
        }

        $this->response->setBody(call_user_func($view, $context ?: []));
        $this->response->send();

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

    /**
     * Formats the view script so it can be set on a `ViewScriptInterface` object.
     * 
     * @return string
     */
    private function formatViewScript()
    {
        $format = $this->config->view->script;

        if (is_callable($format)) {
            return call_user_func($format, $this->request);
        }

        foreach ($this->request->getParams() as $name => $param) {
            $format = str_replace(':' . $name, $param, $format);
        }

        return $format;
    }
}