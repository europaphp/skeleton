<?php

namespace Europa\App;
use ArrayIterator;
use Closure;
use Europa\Config\Config;
use Europa\Config\ConfigInterface;
use Europa\Di\ConfigurationAbstract;
use Europa\Di\Container;
use Europa\Di\DependencyInjectorArray;
use Europa\Di\DependencyInjectorAwareInterface;
use Europa\Di\DependencyInjectorInterface;
use Europa\Di\Resolver;
use Europa\Event\Manager as EventManager;
use Europa\Exception\Exception;
use Europa\Fs\Loader;
use Europa\Fs\LocatorArray;
use Europa\Fs\LocatorInterface;
use Europa\Fs\LocatorAwareInterface;
use Europa\Module\Manager as ModuleManager;
use Europa\Module\ManagerInterface;
use Europa\Request\RequestAbstract;
use Europa\Request\RequestInterface;
use Europa\Response\ResponseAbstract;
use Europa\Router\RouterArray;
use Europa\Router\RouterInterface;
use Europa\View\HelperConfiguration;
use Europa\View\Negotiator;
use Europa\View\NegotiatorInterface;
use Europa\View\ScriptAwareInterface;
use Traversable;

class AppConfiguration extends ConfigurationAbstract
{
    private $config = [
        'controller-param' => 'controller',
        'action-param'     => 'action',
        'view-suffix'      => '.php'
    ];

    public function __construct($config = [])
    {
        $this->config = new Config($this->config, $config);
    }

    public function app($config, $controllers, $events, $modules, $request, $response, $router, $views)
    {
        return new App(
            $config,
            $controllers,
            $events,
            $modules,
            $request,
            $response,
            $router,
            $views
        );
    }

    public function config()
    {
        return new Config($this->config);
    }

    public function controllers($self)
    {
        $resolver = new Resolver;
        $resolver->addCallback('Europa\Di\DependencyInjectorAwareInterface', function($controller) use ($self) {
            $controller->setDependencyInjector($self);
        });
        return $resolver;
    }

    public function events()
    {
        return new EventManager;
    }

    public function loader($loaderLocator)
    {
        $loader = new Loader;
        $loader->setLocator($loaderLocator);
        return $loader;
    }

    public function loaderLocator($loaderLocators)
    {
        return new LocatorArray($loaderLocators);
    }

    public function loaderLocators()
    {
        return new ArrayIterator;
    }

    public function modules($self)
    {
        return new ModuleManager($self);
    }

    public function request()
    {
        return RequestAbstract::detect();
    }

    public function response()
    {
        return ResponseAbstract::detect();
    }

    public function router($routers)
    {
        return new RouterArray($routers);
    }

    public function routers()
    {
        return new ArrayIterator;
    }

    public function views($viewHelperInjector, $viewLocator, $viewNegotiator, $viewScriptFormatter)
    {
        return function() {
            $view = $viewNegotiator->negotiate();

            if ($view instanceof LocatorAwareInterface) {
                $view->setLocator($viewLocator);
            }

            if ($view instanceof ScriptAwareInterface) {
                $formatter = $viewScriptFormatter;
                $view->setScript($formatter());
            }

            if ($view instanceof DependencyInjectorAwareInterface) {
                $view->setDependencyInjector($viewHelperInjector);
            }

            return $view;
        };
    }

    public function viewHelperInjector($viewHelperInjectors)
    {
        return new DependencyInjectorArray($viewHelperInjectors);
    }

    public function viewHelperInjectors()
    {
        $defaultContainer     = new Container;
        $defaultConfiguration = new HelperConfiguration;
        $defaultConfiguration->configure($defaultContainer);

        $viewHelperInjectors = new ArrayIterator;
        $viewHelperInjectors->append($defaultContainer);

        return $viewHelperInjectors;
    }

    public function viewLocator($viewLocators)
    {
        return new LocatorArray($viewLocators);
    }

    public function viewLocators()
    {
        return new ArrayIterator;
    }

    public function viewNegotiator($request)
    {
        return new Negotiator($request);
    }

    public function viewScriptFormatter($config, $request)
    {
        return function() use ($config, $request) {
            $controller = $request->getParam($config['controller-param']);
            $controller = str_replace('.', DIRECTORY_SEPARATOR, $controller);

            $action = $request->getParam($config['action-param']);
            $action = str_replace('.', DIRECTORY_SEPARATOR, $action);

            return $controller . '/' . $action . $config['view-suffix'];
        };
    }
}