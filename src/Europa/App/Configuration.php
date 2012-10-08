<?php

namespace Europa\App;
use Europa\Config\Config;
use Europa\Controller\ControllerAbstract;
use Europa\Di\ConfigurationAbstract;
use Europa\Di\Locator as DiLocator;
use Europa\Event\Manager as EventManager;
use Europa\Filter\ClassNameFilter;
use Europa\Fs\Locator as FsLocator;
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
class Configuration extends ConfigurationAbstract
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
        'controllers' => [
            ['prefix' => 'Controller\\']
        ],
        'helpers' => [
            ['prefix' => 'Helper\\'],
            ['prefix' => 'Europa\View\Helper\\']
        ],
        'paths.app'            => '={root}/app',
        'paths.root'           => '..',
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
        
        if (!$this->config['paths.root'] = realpath($this->config['paths.root'])) {
            throw new UnexpectedValueException(sprintf(
                'A valid applicaiton root must be specified in the configuration as "root". The path "%s" is not valid.',
                $this->config['paths.root']
            ));
        }
    }

    /**
     * Returns an application service.
     * 
     * @return App
     */
    public function app($container)
    {
        return new App($container);
    }

    /**
     * Returns the container responsible for locating controllers.
     * 
     * @return ClosureContainer
     */
    public function controllers($container)
    {
        $locator = new DiLocator;

        foreach ($this->config['controllers'] as $config) {
            $locator->getFilter()->add(new ClassNameFilter($config->export()));
        }
        
        $locator->args('Europa\Controller\ControllerAbstract', function() use ($container) {
            return [$container->request];
        });

        return $locator;
    }

    /**
     * Returns a new event manager.
     * 
     * @return EventManager
     */
    public function event($container)
    {
        $event = new EventManager;
        $event->bind('render.pre', function($app) use ($container) {
            if ($container->view instanceof ViewScriptInterface) {
                $script = $container->request->isCli() ? $this->config['views.cli'] : $this->config['views.web'];
                $script = $script . '/' . $app->getController();
                $script = str_replace(' ', '/', $script);

                $container->view->setScript($script);
            }
        });

        return $event;
    }

    /**
     * Returns the helper container.
     * 
     * @return ClosureContainer
     */
    public function helpers($container)
    {
        $locator = new DiLocator;

        foreach ($this->config['helpers'] as $config) {
            $locator->getFilter()->add(new ClassNameFilter($config->export()));
        }

        $locator->args('Europa\View\Helper\Lang', function() use ($container) {
            return [$container->view, $container->langLocator];
        });

        $locator->call('Europa\View\Helper\Uri', function() use ($container) {
            return [$this->router];
        });

        return $locator;
    }

    /**
     * Returns the language file locator.
     * 
     * @return LocatorInterface
     */
    public function langLocator()
    {
        $locator = new FsLocator;
        $locator->setBasePath($this->config['paths.app']);
        return $locator;
    }

    /**
     * Returns the class loader.
     * 
     * @return Loader
     */
    public function loader($container)
    {
        $loader = new Loader;
        $loader->setLocator($container->loaderLocator);
        return $loader;
    }

    /**
     * Returns the class file locator.
     * 
     * @return LocatorInterface
     */
    public function loaderLocator()
    {
        $locator = new FsLocator;
        $locator->setBasePath($this->config['paths.app']);
        return $locator;
    }

    /**
     * Returns a new module manager.
     * 
     * @return ModuleManager
     */
    public function modules($container)
    {
        foreach ($this->config['modules.enabled'] as $module) {
            $map = [
                'classes' => 'loaderLocator',
                'langs'   => 'langLocator',
                'views'   => 'viewLocator'
            ];

            foreach ($map as $config => $locator) {
                foreach ($this->config['paths.' . $config] as $path => $suffix) {
                    $container->$locator->addPath($module . '/' . $path, $suffix);
                }
            }
        }

        return new ModuleManager($this->config['paths.app']);
    }

    /**
     * Returns the request.
     * 
     * @return RequestInterface
     */
    public function request($container)
    {
        return RequestAbstract::isCli() ? $container->requestCli : $container->requestHttp;
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
    public function response($container)
    {
        return RequestAbstract::isCli() ? $container->responseCli : $container->responseHttp;
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
    public function router($container)
    {
        return RequestAbstract::isCli() ? $container->routerCli : $container->routerHttp;
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
    public function view($container)
    {
        // We only negotiate a content type if the request is using Http.
        if ($container->request instanceof Request\Http) {
            $service = null;

            // Specifying a suffix overrides the Accept header.
            if ($suffix = $container->request->getUri()->getSuffix()) {
                $service = 'view' . ucfirst($suffix);
            } elseif ($type = $container->request->accepts(array_keys($this->config['view.map']->export()))) {
                $service = 'view' . $this->config['view.map.' . $type];
            }

            // Only render a different view if one exists.
            if ($service && isset($container, $service)) {
                return $this->$service;
            }
        }

        // Default to using a PHP view.
        return $container->viewPhp;
    }

    /**
     * Returns the CLI view.
     * 
     * @return Php
     */
    public function viewPhp($container)
    {
        $view = new Php;
        $view->setLocator($container->viewLocator);
        $view->setHelperContainer($container->helpers);
        return $view;
    }

    /**
     * Returns the view script locator.
     * 
     * @return LocatorInterface
     */
    public function viewLocator()
    {
        $locator = new FsLocator;
        $locator->setBasePath($this->config['paths.app']);
        return $locator;
    }

    /**
     * Returns a new JSON view.
     * 
     * @return Json
     */
    public function viewJson($container)
    {
        if ($container->request->hasParam($this->config['views.jsonp.callback'])) {
            return $container->viewJsonp;
        }
        return new Json;
    }

    /**
     * Returns the JSON view.
     * 
     * @return Json
     */
    public function viewJsonp($container)
    {
        return new Jsonp($container->request->getParam($this->config['views.jsonp.callback']));
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