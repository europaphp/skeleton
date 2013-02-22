<?php

namespace Europa\App;
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

class AppConfiguration extends ConfigurationAbstract implements AppConfigurationInterface
{
    public function config()
    {
        return new Config([
            'controller-param' => 'controller',
            'action-param'     => 'action'
        ]);
    }

    public function controllers()
    {
        $finder = new Finder;
        $finder->addCallback('Europa\Controller\ControllerInterface', function($controller) {
            $controller->setDependencyInjector($this);
        });

        return $finder;
    }

    public function events()
    {
        return new EventManager;
    }

    public function loader(LocatorInterface $loaderLocators)
    {
        $loader = new Loader;
        $loader->setLocator($loaderLocators);
        return $loader;
    }

    public function loaderLocators()
    {
        return new LocatorArray;
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

    public function routers()
    {
        return new RouterArray;
    }

    public function view(
        ConfigInterface $config,
        RequestInterface $request,
        LocatorInterface $viewLocators,
        DependencyInjectorInterface $viewHelpers,
        NegotiatorInterface $viewNegotiator,
        Closure $viewScriptFormatter
    ) {
        $view = $viewNegotiator->negotiate($request);

        if ($view instanceof ScriptAwareInterface) {
            $view->setLocator($viewLocators);
            $view->setScript($viewScriptFormatter($request));
        }

        if ($view instanceof DependencyInjectorAwareInterface) {
            $view->setDependencyInjector($viewHelpers);
        }

        return $view;
    }

    public function viewHelpers()
    {
        $defaultContainer     = new Container;
        $defaultConfiguration = new HelperConfiguration;
        $defaultConfiguration->configure($defaultContainer);

        $helpers = new DependencyInjectorArray;
        $helpers->add($defaultContainer);

        return $helpers;
    }

    public function viewLocators(ConfigInterface $config)
    {
        return new LocatorArray;
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
            return $request->getParam($config['controller-param'])
                . '/'
                . $request->getParam($config['action-param'])
                . '.php';
        };
    }
}