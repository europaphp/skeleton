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
    public function app()
    {
        return new App(
            $this->get('config'),
            $this->get('controllers'),
            $this->get('events'),
            $this->get('modules'),
            $this->get('request'),
            $this->get('response'),
            $this->get('router'),
            $this->get('views')
        );
    }

    public function config()
    {
        return new Config([
            'controller-param' => 'controller',
            'action-param'     => 'action',
            'view-suffix'      => '.php'
        ]);
    }

    public function controllers()
    {
        $resolver = new Resolver;
        return $resolver->addCallback('Europa\Di\DependencyInjectorAwareInterface', function($controller) {
            return $controller->setDependencyInjector($this);
        });
    }

    public function events()
    {
        return new EventManager;
    }

    public function loader()
    {
        $loader = new Loader;
        $loader->setLocator($this->get('loaderLocator'));
        return $loader;
    }

    public function loaderLocator()
    {
        return new LocatorArray($this->get('loaderLocators'));
    }

    public function loaderLocators()
    {
        return new ArrayIterator;
    }

    public function modules()
    {
        return new ModuleManager($this);
    }

    public function request()
    {
        return RequestAbstract::detect();
    }

    public function response()
    {
        return ResponseAbstract::detect();
    }

    public function router()
    {
        return new RouterArray($this->get('routers'));
    }

    public function routers()
    {
        return new ArrayIterator;
    }

    public function views()
    {
        return function() {
            $view = $this->get('viewNegotiator')->negotiate();

            if ($view instanceof LocatorAwareInterface) {
                $view->setLocator($this->get('viewLocator'));
            }

            if ($view instanceof ScriptAwareInterface) {
                $formatter = $this->get('viewScriptFormatter');
                $view->setScript($formatter());
            }

            if ($view instanceof DependencyInjectorAwareInterface) {
                $view->setDependencyInjector($this->get('viewHelperInjector'));
            }

            return $view;
        };
    }

    public function viewHelperInjector()
    {
        return new DependencyInjectorArray($this->get('viewHelperInjectors'));
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

    public function viewLocator()
    {
        return new LocatorArray($this->get('viewLocators'));
    }

    public function viewLocators()
    {
        return new ArrayIterator;
    }

    public function viewNegotiator()
    {
        return new Negotiator($this->get('request'));
    }

    public function viewScriptFormatter()
    {
        return function() {
            $config  = $this->get('config');
            $request = $this->get('request');

            $controller = $request->getParam($config['controller-param']);
            $controller = str_replace('.', DIRECTORY_SEPARATOR, $controller);

            $action = $request->getParam($config['action-param']);
            $action = str_replace('.', DIRECTORY_SEPARATOR, $action);

            return $controller . '/' . $action . $config['view-suffix'];
        };
    }
}