<?php

use Europa\App\App;
use Europa\Config\Config;
use Europa\Controller\ControllerAbstract;
use Europa\Di\Provider;
use Europa\Di\Finder;
use Europa\Event\Manager as EventManager;
use Europa\Filter\ClassNameFilter;
use Europa\Fs\Locator;
use Europa\Fs\Loader;
use Europa\Module\Manager as ModuleManager;
use Europa\Request;
use Europa\Request\RequestAbstract;
use Europa\Response;
use Europa\Router\RegexRoute;
use Europa\Router\Router;
use Europa\View\Json;
use Europa\View\Jsonp;
use Europa\View\Php;
use Europa\View\ViewScriptInterface;
use Europa\View\Xml;

/**
 * The default container.
 * 
 * @category Controllers
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Europa extends Provider
{
    /**
     * The default configuration.
     * 
     * - `cliViewPath` The path that cli view scripts reside under.
     * - `controllerFilterConfig` The controller filter configuration used to resolve controller class names.
     * - `helperFilterConfig` The helper filter configuration used to resolve helper class names.
     * - `jsonpCallbackKey` If a content type of JSON is requested - either by using a `.json` suffix or by using an `application/json` content type request header - and this is set in the request, a `Jsonp` view instance is used rather than `Json` and the value of this request parameter is used as the callback.
     * - `classPaths` Class load paths that will be added to the `loaderLocator`.
     * - `langPaths` Language paths and suffixes to supply to the language file locator.
     * - `viewPaths` View paths and suffixes to supply to the view script locator.
     * - `viewTypes` Mapping of content-type to view class mapping.
     * - `webViewPath` The path that web view script reside under.
     * 
     * @var array
     */
    private $config = [
        'controllers.filter.configs' => [
            ['prefix' => 'Controller\\']
        ],
        'helpers.filter.configs' => [
            ['prefix' => 'Helper\\'],
            ['prefix' => 'Europa\View\Helper\\']
        ],
        'paths.app'            => '={root}/app',
        'paths.root'           => '.',
        'paths.classes'        => ['classes'     => 'php'],
        'paths.langs'          => ['langs/en-us' => 'ini'],
        'paths.views'          => ['views'       => 'php'],
        'views.cli'            => 'cli',
        'views.web'            => 'web',
        'views.jsonp.callback' => Jsonp::CALLBACK,
        'views.map'            => [
            'application/json'       => 'Json',
            'application/javascript' => 'Jsonp',
            'text/xml'               => 'Xml',
            'text/html'              => 'Php'
        ]
    ];

    /**
     * Sets up the container.
     * 
     * @param string $root   The application install path.
     * @param array  $config The configuration.
     * 
     * @return Container
     */
    public function __construct($config = [])
    {
        $this->config = new Config($this->config, $config);
        
        if (!$this->config->paths->root = realpath($this->config->paths->root)) {
            throw new UnexpectedValueException(sprintf(
                'A valid applicaiton root must be specified in the configuration as "root". The path "%s" is not valid.',
                $this->config->paths->root
            ));
        }

        $this->loader->register();
    }

    /**
     * Returns an application service.
     * 
     * @return App
     */
    public function app()
    {
        return new App($this);
    }

    /**
     * Returns the container configuration.
     * 
     * @return Config
     */
    public function config()
    {
        return $this->config;
    }

    /**
     * Returns the container responsible for locating controllers.
     * 
     * @return ClosureContainer
     */
    public function controllers()
    {
        $finder = new Finder;

        foreach ($this->config->controllers->filter->configs as $config) {
            $finder->getFilter()->add(new ClassNameFilter($config->export()));
        }
        
        $finder->config('Europa\Controller\ControllerAbstract', function() {
            return [$this->request];
        });

        return $finder;
    }

    /**
     * Returns a new event manager.
     * 
     * @return EventManager
     */
    public function event()
    {
        $event = new EventManager;
        $event->bind('render.pre', function($app) {
            if ($this->view instanceof ViewScriptInterface) {
                $script = $this->request->isCli() ? $this->config->views->cli : $this->config->views->web;
                $script = $script . '/' . $app->getController();
                $script = str_replace(' ', '/', $script);

                $this->view->setScript($script);
            }
        });

        return $event;
    }

    /**
     * Returns the helper container.
     * 
     * @return ClosureContainer
     */
    public function helpers()
    {
        $finder = new Finder;

        foreach ($this->config->helpers->filter->configs as $config) {
            $finder->getFilter()->add(new ClassNameFilter($config->export()));
        }

        $finder->config('Europa\View\Helper\Lang', function() {
            return [$this->view, $this->langLocator];
        });

        $finder->config('Europa\View\Helper\Uri', function() {
            return [$this->router];
        });

        return $finder;
    }

    /**
     * Returns the language file locator.
     * 
     * @return LocatorInterface
     */
    public function langLocator()
    {
        $locator = new Locator;
        $locator->setBasePath($this->config->paths->app);
        return $locator;
    }

    /**
     * Returns the class loader.
     * 
     * @return Loader
     */
    public function loader()
    {
        $loader = new Loader;
        $loader->setLocator($this->loaderLocator);
        return $loader;
    }

    /**
     * Returns the class file locator.
     * 
     * @return LocatorInterface
     */
    public function loaderLocator()
    {
        $locator = new Locator;
        $locator->setBasePath($this->config->paths->app);
        return $locator;
    }

    /**
     * Returns a new module manager.
     * 
     * @return ModuleManager
     */
    public function modules()
    {
        return new ModuleManager($this->config->paths->app);
    }

    /**
     * Returns the request.
     * 
     * @return RequestInterface
     */
    public function request()
    {
        return RequestAbstract::isCli() ? $this->requestCli : $this->requestHttp;
    }

    /**
     * Returns the CLI request.
     * 
     * @return Request\Cli
     */
    public function requestCli()
    {
        return new Request\Cli;
    }

    /**
     * Returns the HTTP request.
     * 
     * @return Request\Http
     */
    public function requestHttp()
    {
        return new Request\Http;
    }

    /**
     * Returns the response.
     * 
     * @return ResponseInterface
     */
    public function response()
    {
        return RequestAbstract::isCli() ? $this->responseCli : $this->responseHttp;
    }

    /**
     * Returns the CLI response.
     * 
     * @return Response\Cli;
     */
    public function responseCli()
    {
        return new Response\Cli;
    }

    /**
     * Returns the HTTP response.
     * 
     * @return Response\Http;
     */
    public function responseHttp()
    {
        return new Response\Http;
    }

    /**
     * Returns the correct router for the correct interface.
     * 
     * @return Router
     */
    public function router()
    {
        return RequestAbstract::isCli() ? $this->routerCli : $this->routerHttp;
    }

    /**
     * Returns a router for CLI purposes.
     * 
     * @return Router
     */
    public function routerCli()
    {
        $router = new Router;
        $router->setRoute('default', new RegexRoute('(?<controller>.+)', ':controller', ['controller' => 'help']));
        return $router;
    }

    /**
     * Returns a router for http purposes.
     * 
     * @return Router
     */
    public function routerHttp()
    {
        $router = new Router;
        $router->setRoute('default', new RegexRoute('(?<controller>[^.?]+)?', ':controller', ['controller' => 'index']));
        return $router;
    }

    /**
     * Returns the view. Always falls back to using the PHP view.
     * 
     * @return RequestInterface
     */
    public function view()
    {
        // We only negotiate a content type if the request is using Http.
        if ($this->request instanceof Request\Http) {
            $method = null;

            // Specifying a suffix overrides the Accept header.
            if ($suffix = $this->request->getUri()->getSuffix()) {
                $method = 'view' . ucfirst($suffix);
            } elseif ($type = $this->request->accepts(array_keys($this->config->views->map->export()))) {
                $method = 'view' . $this->config->views->map->$type;
            }

            // Only render a different view if one exists.
            if ($method && method_exists($this, $method)) {
                return $this->$method;
            }
        }

        // Default to using a PHP view.
        return $this->viewPhp;
    }

    /**
     * Returns the CLI view.
     * 
     * @return Php
     */
    public function viewPhp()
    {
        $view = new Php;
        $view->setLocator($this->viewLocator);
        $view->setHelperContainer($this->helpers);
        return $view;
    }

    /**
     * Returns the view script locator.
     * 
     * @return LocatorInterface
     */
    public function viewLocator()
    {
        $locator = new Locator;
        $locator->setBasePath($this->config->paths->app);
        return $locator;
    }

    /**
     * Returns a new JSON view.
     * 
     * @return Json
     */
    public function viewJson()
    {
        if ($this->request->hasParam($this->config->views->jsonp->callback)) {
            return $this->viewJsonp;
        }
        return new Json;
    }

    /**
     * Returns the JSON view.
     * 
     * @return Json
     */
    public function viewJsonp()
    {
        return new Jsonp($this->request->getParam($this->config->views->jsonp->callback));
    }

    /**
     * Returns the XML view.
     * 
     * @return Xml
     */
    public function viewXml()
    {
        return new Xml;
    }
}