<?php

namespace Europa\App;
use Closure;
use Europa\Controller\ControllerAbstract;
use Europa\Di\Provider;
use Europa\Di\Finder;
use Europa\Filter\ClassNameFilter;
use Europa\Fs\Locator;
use Europa\Request;
use Europa\Request\RequestAbstract;
use Europa\Response;
use Europa\Router\RegexRoute;
use Europa\Router\Router;
use Europa\Util\Configurable;
use Europa\View\Json;
use Europa\View\Php;
use Europa\View\Xml;

/**
 * The default container.
 * 
 * @category Controllers
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Container extends Provider
{
    use Configurable;
    
    /**
     * The path relative to this file that is the application root.
     * 
     * @var string
     */
    const PATH = '../../..';
    
    /**
     * The default configuration.
     * 
     * @var array
     */
    private $defaultConfig = [
        /**
         * The application base path if not using the default.
         * 
         * @var string
         */
        'basePath' => null,
        
        /**
         * The controller container filter's configuration.
         * 
         * @var array
         */
        'controllerFilterConfig' => [
            ['prefix' => 'Controller\\']
        ],
        
        /**
         * The helper container filter's configuration.
         * 
         * @var array
         */
        'helperFilterConfig' => [
            ['prefix' => 'Helper\\'],
            ['prefix' => 'Europa\View\Helper\\']
        ],
        
        /**
         * The language file paths relative to the application base path.
         * 
         * @var array
         */
        'langPaths' => ['app/langs/en-us' => 'ini'],
        
        /**
         * The view file paths relative to the application base path.
         * 
         * @var array
         */
        'viewPaths' => ['app/views' => 'php']
    ];
    
    /**
     * The application install path.
     * 
     * @var string
     */
    private $path;
    
    /**
     * Sets up the container.
     * 
     * @param array $config The configuration.
     * 
     * @return Container
     */
    public function __construct($config = [])
    {
        $this->setDefaultConfig();
        $this->setConfig($config);
        
        if (!$this->getConfig('basePath')) {
            $this->setConfig('basePath', __DIR__ . '/' . self::PATH);
        }
    }
    
    /**
     * Returns an application service.
     * 
     * @param ContainerInterface $controllers The controller container responsible for finding a controller.
     * @param RequestInterface   $request     The request responsible for supplying information to the controller.
     * @param ResponseInterface  $response    The response responsible for outputting the rendered view.
     * @param RouterInterface    $router      The router to use for routing the request.
     * @param ViewInterface      $view        The view responsible for rendering controller response.
     * 
     * @return App
     */
    public function app($controllers, $request, $response, $router, $view)
    {
        $app = new App($controllers, $request, $response);
        $app->setRouter($router);
        $app->setView($view);
        return $app;
    }
    
    /**
     * Returns the container responsible for locating controllers.
     * 
     * @param Closure $request Request getter.
     * 
     * @return ClosureContainer
     */
    public function controllers(Closure $request)
    {
        $finder = new Finder;
        
        foreach ($this->getConfig('controllerFilterConfig') as $config) {
            $finder->getFilter()->add(new ClassNameFilter($config));
        }
        
        return $finder;
    }
    
    /**
     * Returns the helper container.
     * 
     * @param Closure $view        View getter.
     * @param Closure $langLocator Language file locator getter.
     * @param Closure $router      The router to use for the URI helper.
     * 
     * @return ClosureContainer
     */
    public function helpers(Closure $view, Closure $langLocator, Closure $router)
    {
        $finder = new Finder;
        
        foreach ($this->getConfig('helperFilterConfig') as $config) {
            $finder->getFilter()->add(new ClassNameFilter($config));
        }
        
        $finder->config('Europa\View\Helper\Lang', function() use ($view, $langLocator) {
            return [$view(), $langLocator()];
        });
        
        $finder->config('Europa\View\Helper\Uri', function() use ($router) {
            return [$router()];
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
        $locator = new Locator($this->getConfig('basePath'));
        $locator->addPaths($this->getConfig('langPaths'));
        return $locator;
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
     * Returns the view.
     * 
     * @return RequestInterface
     */
    public function view($request)
    {
        if ($request instanceof Request\Http && $suffix = $request->getUri()->getSuffix()) {
            return $this->{'view' . ucfirst($suffix)};
        }
        return $this->viewPhp;
    }
    
    /**
     * Returns the CLI view.
     * 
     * @param ContainerInterface $helpers        The helper container to configure.
     * @param LocatorInterface   $viewPhpLocator The locator used for locating PHP views.
     * 
     * @return Php
     */
    public function viewPhp($helpers, $viewPhpLocator)
    {
        $view = new Php;
        $view->setLocator($viewPhpLocator);
        $view->setHelpers($helpers);
        return $view;
    }
    
    /**
     * Returns the view script locator.
     * 
     * @return LocatorInterface
     */
    public function viewPhpLocator()
    {
        $locator = new Locator($this->getConfig('basePath'));
        $locator->addPaths($this->getConfig('viewPaths'));
        return $locator;
    }
    
    /**
     * Returns the JSON view.
     * 
     * @return Json
     */
    public function viewJson()
    {
        return new Json;
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