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
use Europa\Di\Finder;
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

class AppConfiguration extends ConfigurationAbstract implements AppConfigurationInterface
{
    public function config()
    {
        return new Config([
            'controller-param' => 'controller',
            'action-param'     => 'action'
        ]);
    }

    public function controllers(DependencyInjectorInterface $self)
    {
        $finder = new Finder;
        return $finder->addCallback('Europa\Di\DependencyInjectorAwareInterface', function($controller) use ($self) {
            return $controller->setDependencyInjector($self);
        });
    }

    public function events()
    {
        return new EventManager;
    }

    public function loader(LocatorInterface $loaderLocator)
    {
        $loader = new Loader;
        $loader->setLocator($loaderLocator);
        return $loader;
    }

    public function loaderLocator(Traversable $loaderLocators)
    {
        return new LocatorArray($loaderLocators);
    }

    public function loaderLocators()
    {
        return new ArrayIterator;
    }

    public function modules()
    {
        return new ModuleManager;
    }

    public function request()
    {
        return RequestAbstract::detect();
    }

    public function response()
    {
        return ResponseAbstract::detect();
    }

    public function router(Traversable $routers)
    {
        return new RouterArray($routers);
    }

    public function routers()
    {
        return new ArrayIterator;
    }

    public function view(
        ConfigInterface $config,
        RequestInterface $request,
        LocatorInterface $viewLocator,
        DependencyInjectorInterface $viewHelperInjector,
        NegotiatorInterface $viewNegotiator,
        Closure $viewScriptFormatter
    ) {
        $view = $viewNegotiator->negotiate($request);

        if ($view instanceof LocatorAwareInterface) {
            $view->setLocator($viewLocator);
        }

        if ($view instanceof ScriptAwareInterface) {
            $view->setScript($viewScriptFormatter($request));
        }

        if ($view instanceof DependencyInjectorAwareInterface) {
            $view->setDependencyInjector($viewHelperInjector);
        }

        return $view;
    }

    public function viewHelperInjector(Traversable $viewHelperInjectors)
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

    public function viewLocator(Traversable $viewLocators)
    {
        return new LocatorArray($viewLocators);
    }

    public function viewLocators()
    {
        return new ArrayIterator;
    }

    public function viewNegotiator(ConfigInterface $viewNegotiatorConfig, RequestInterface $request)
    {
        return new Negotiator($viewNegotiatorConfig, $request);
    }

    public function viewNegotiatorConfig()
    {
        return new Config;
    }

    public function viewScriptFormatter(ConfigInterface $config)
    {
        return function(RequestInterface $request) use ($config) {
            $controller = $request->getParam($config['controller-param']);
            $controller = str_replace('.', DIRECTORY_SEPARATOR, $controller);

            $action = $request->getParam($config['action-param']);
            $action = str_replace('.', DIRECTORY_SEPARATOR, $action);

            return $controller . '/' . $action . '.php';
        };
    }
}