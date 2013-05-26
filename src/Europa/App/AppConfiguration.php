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
use Europa\Event\Emitter;
use Europa\Exception\Exception;
use Europa\Fs\Loader;
use Europa\Fs\LocatorArray;
use Europa\Fs\LocatorInterface;
use Europa\Fs\LocatorAwareInterface;
use Europa\Module\Manager;
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

    public function app(DependencyInjectorInterface $injector)
    {
        return new App(
            $injector->get('config'),
            $injector->get('controllers'),
            $injector->get('events'),
            $injector->get('modules'),
            $injector->get('request'),
            $injector->get('response'),
            $injector->get('router'),
            $injector->get('views')
        );
    }

    public function config()
    {
        return new Config($this->config);
    }

    public function controllers(DependencyInjectorInterface $injector)
    {
        $resolver = new Resolver;
        $resolver->addCallback('Europa\Di\DependencyInjectorAwareInterface', function($controller) use ($injector) {
            $controller->setDependencyInjector($injector);
        });
        return $resolver;
    }

    public function events()
    {
        return new Emitter;
    }

    public function loader(DependencyInjectorInterface $injector)
    {
        $loader = new Loader;
        $loader->setLocator($injector->get('loaderLocator'));
        return $loader;
    }

    public function loaderLocator(DependencyInjectorInterface $injector)
    {
        return new LocatorArray($injector->get('loaderLocators'));
    }

    public function loaderLocators()
    {
        return new ArrayIterator;
    }

    public function modules(DependencyInjectorInterface $injector)
    {
        return new Manager($injector);
    }

    public function request()
    {
        return RequestAbstract::detect();
    }

    public function response()
    {
        return ResponseAbstract::detect();
    }

    public function router(DependencyInjectorInterface $injector)
    {
        return new RouterArray($injector->get('routers'));
    }

    public function routers()
    {
        return new ArrayIterator;
    }

    public function views(DependencyInjectorInterface $injector)
    {
        return function() use ($injector) {
            $view = $injector->get('viewNegotiator')->negotiate();

            if ($view instanceof LocatorAwareInterface) {
                $view->setLocator($injector->get('viewLocator'));
            }

            if ($view instanceof ScriptAwareInterface) {
                $formatter = $injector->get('viewScriptFormatter');
                $view->setScript($formatter());
            }

            if ($view instanceof DependencyInjectorAwareInterface) {
                $view->setDependencyInjector($injector->get('viewHelperInjector'));
            }

            return $view;
        };
    }

    public function viewHelperInjector(DependencyInjectorInterface $injector)
    {
        return new DependencyInjectorArray($injector->get('viewHelperInjectors'));
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

    public function viewLocator(DependencyInjectorInterface $injector)
    {
        return new LocatorArray($injector->get('viewLocators'));
    }

    public function viewLocators()
    {
        return new ArrayIterator;
    }

    public function viewNegotiator(DependencyInjectorInterface $injector)
    {
        return new Negotiator($injector->get('request'));
    }

    public function viewScriptFormatter(DependencyInjectorInterface $injector)
    {
        return function() use ($injector) {
            $config  = $injector->get('config');
            $request = $injector->get('request');

            $controller = $request->getParam($config['controller-param']);
            $controller = str_replace('.', DIRECTORY_SEPARATOR, $controller);

            $action = $request->getParam($config['action-param']);
            $action = str_replace('.', DIRECTORY_SEPARATOR, $action);

            return $controller . '/' . $action . $config['view-suffix'];
        };
    }
}