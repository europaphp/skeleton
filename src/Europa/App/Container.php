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
use Europa\View\Json;
use Europa\View\Php;
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
    /**
     * The application install path.
     * 
     * @var string
     */
    private $root;
    
    /**
     * The default configuration.
     * 
     * @var array
     */
    private $config = [
        'controllerFilterConfig' => [
            ['prefix' => 'Controller\\']
        ],
        'helperFilterConfig' => [
            ['prefix' => 'Helper\\'],
            ['prefix' => 'Europa\View\Helper\\']
        ],
        'jsonp'     => 'callback',
        'langPaths' => ['app/langs/en-us' => 'ini'],
        'viewPaths' => ['app/views' => 'php'],
        'viewTypes' => [
            'application/json' => 'Json',
            'text/xml'         => 'Xml',
            'text/html'        => 'Php'
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
    public function __construct($root, array $config = [])
    {
        $this->root   = realpath($root);
        $this->config = array_merge($this->config, $config);
        
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

        foreach ($this->config['controllerFilterConfig'] as $config) {
            $finder->getFilter()->add(new ClassNameFilter($config));
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

        foreach ($this->config['helperFilterConfig'] as $config) {
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
        $locator = new Locator($this->root);
        $locator->addPaths($this->config['langPaths']);
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
            } elseif ($type = $request->accepts(array_keys($this->config['viewTypes']))) {
                $method = 'view' . $this->config['viewTypes'][$type];
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
        $locator = new Locator($this->root);
        $locator->addPaths($this->config['viewPaths']);
        return $locator;
    }

    /**
     * Returns the JSON view.
     * 
     * @return Json
     */
    public function viewJson($request)
    {
        return new Json(['jsonp' => $request->getParam($this->config['jsonp'])]);
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