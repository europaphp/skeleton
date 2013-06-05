<?php

namespace Europa\App;
use ArrayIterator;
use Closure;
use Europa\Config\Config;
use Europa\Config\ConfigInterface;
use Europa\Di\ConfigurationAbstract;
use Europa\Di\Container;
use Europa\Di\ContainerArray;
use Europa\Di\ContainerAwareInterface;
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

    public function app(callable $di)
    {
        return new App(
            $di('config'),
            $di('events'),
            $di('modules'),
            $di('request'),
            $di('response'),
            $di('router'),
            $di('views')
        );
    }

    public function config()
    {
        return new Config($this->config);
    }

    public function events()
    {
        return new Emitter;
    }

    public function loader(callable $di)
    {
        $loader = new Loader;
        $loader->setLocator($di('loaderLocator'));
        return $loader;
    }

    public function loaderLocator(callable $di)
    {
        return new LocatorArray($di('loaderLocators'));
    }

    public function loaderLocators()
    {
        return new ArrayIterator;
    }

    public function modules(callable $di)
    {
        return new Manager($di);
    }

    public function request()
    {
        return RequestAbstract::detect();
    }

    public function response()
    {
        return ResponseAbstract::detect();
    }

    public function router(callable $di)
    {
        return new RouterArray($di('routers'));
    }

    public function routers()
    {
        return new ArrayIterator;
    }

    public function views(callable $di)
    {
        return function() use ($di) {
            $view = $di('viewNegotiator');
            $view = $view($di('request'));

            if ($view instanceof LocatorAwareInterface) {
                $view->setLocator($di('viewLocator'));
            }

            if ($view instanceof ScriptAwareInterface) {
                $view->setScript();
            }

            if ($view instanceof ContainerAwareInterface) {
                $view->setContainer($di('viewHelperContainer'));
            }

            return $view;
        };
    }

    public function viewHelperContainer(callable $di)
    {
        return new ContainerArray($di('viewHelperContainers'));
    }

    public function viewHelperContainers()
    {
        $defaultContainer     = new Container;
        $defaultConfiguration = new HelperConfiguration;
        $defaultConfiguration($defaultContainer);

        $viewHelperContainers = new ArrayIterator;
        $viewHelperContainers->append($defaultContainer);

        return $viewHelperContainers;
    }

    public function viewLocator(callable $di)
    {
        return new LocatorArray($di('viewLocators'));
    }

    public function viewLocators()
    {
        return new ArrayIterator;
    }

    public function viewNegotiator()
    {
        return new Negotiator;
    }
}