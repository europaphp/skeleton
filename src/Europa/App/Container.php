<?php

namespace Europa\App;
use Closure;
use Europa\Config\Configurable;
use Europa\Controller\ControllerAbstract;
use Europa\Di\Provider;
use Europa\Di\Finder;
use Europa\Filter\ClassNameFilter;
use Europa\Fs\Locator;
use Europa\Fs\Loader;
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
use UnexpectedValueException;

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
     * The application install path.
     * 
     * @var string
     */
    private $root;
    
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
    private $defaultConfig = [
        'cliViewPath' => 'cli',
        'controllerFilterConfig' => [
            ['prefix' => 'Controller\\']
        ],
        'helperFilterConfig' => [
            ['prefix' => 'Helper\\'],
            ['prefix' => 'Europa\View\Helper\\']
        ],
        'jsonpCallbackKey' => Jsonp::CALLBACK,
        'classPaths' => [],
        'langPaths' => [],
        'viewPaths' => [],
        'viewTypes' => [
            'application/json'       => 'Json',
            'application/javascript' => 'Jsonp',
            'text/xml'               => 'Xml',
            'text/html'              => 'Php'
        ],
        'webViewPath' => 'web'
    ];

    /**
     * Sets up the container.
     * 
     * @param string $root   The application install path.
     * @param array  $config The configuration.
     * 
     * @return Container
     */
    public function __construct($root, $config = [])
    {
        $this->root = realpath($root);
        $this->config()->import($config);
        
        if (!$this->root) {
            throw new UnexpectedValueException(sprintf(
                'A valid "path" must be specified for the container "%s". The path "%s" is not valid.',
                get_class(),
                $root
            ));
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
        $app->event()->bind(App::EVENT_RENDER_PRE, function($app) {
            if ($app->getView() instanceof ViewScriptInterface) {
                $script = $app->getRequest()->isCli() ? $this->config()->cliViewPath : $this->config()->webViewPath;
                $script = $script . '/' . $app->getRequest()->controller;
                $script = str_replace(' ', '/', $script);
                $app->getView()->setScript($script);
            }
        });
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

        foreach ($this->config()->controllerFilterConfig as $config) {
            $finder->getFilter()->add(new ClassNameFilter($config->export()));
        }
        
        $finder->config('Europa\Controller\RestController', function() use ($request) {
            return [$request()];
        });

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

        foreach ($this->config()->helperFilterConfig as $config) {
            $finder->getFilter()->add(new ClassNameFilter($config->export()));
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
        $locator = new Locator;
        $locator->setBasePath($this->root);
        $locator->addPaths($this->config()->langPaths->export());
        return $locator;
    }

    /**
     * Returns the class loader.
     * 
     * @param Locator $loaderLocator The locator used to locate class files.
     * 
     * @return Loader
     */
    public function loader($loaderLocator)
    {
        $loader = new Loader;
        $loader->setLocator($loaderLocator);
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
        $locator->setBasePath($this->root);
        $locator->addPaths($this->config()->classPaths->export());
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
     * Returns the view. Always falls back to using the PHP view.
     * 
     * @return RequestInterface
     */
    public function view($request)
    {
        // We only negotiate a content type if the request is using Http.
        if ($request instanceof Request\Http) {
            $method = null;

            // Specifying a suffix overrides the Accept header.
            if ($suffix = $request->getUri()->getSuffix()) {
                $method = 'view' . ucfirst($suffix);
            } elseif ($type = $request->accepts(array_keys($this->config()->viewTypes->export()))) {
                $method = 'view' . $this->config()->viewTypes->$type;
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
     * @param ContainerInterface $helpers        The helper container to configure.
     * @param LocatorInterface   $viewPhpLocator The locator used for locating PHP views.
     * 
     * @return Php
     */
    public function viewPhp($helpers, $viewPhpLocator)
    {
        $view = new Php;
        $view->setLocator($viewPhpLocator);
        $view->setHelperContainer($helpers);
        return $view;
    }

    /**
     * Returns the view script locator.
     * 
     * @return LocatorInterface
     */
    public function viewPhpLocator()
    {
        $locator = new Locator;
        $locator->setBasePath($this->root);
        $locator->addPaths($this->config()->viewPaths->export());
        return $locator;
    }

    /**
     * Returns a new JSON view.
     * 
     * @return Json
     */
    public function viewJson($request)
    {
        if ($request->hasParam($this->config()->jsonpCallbackKey)) {
            return $this->viewJsonp;
        }
        return new Json;
    }

    /**
     * Returns the JSON view.
     * 
     * @return Json
     */
    public function viewJsonp($request)
    {
        return new Jsonp($request->getParam($this->config()->jsonpCallbackKey));
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