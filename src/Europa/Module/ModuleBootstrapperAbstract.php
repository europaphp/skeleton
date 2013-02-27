<?php

namespace Europa\Module;
use Europa\Bootstrapper\BootstrapperAbstract;
use Europa\Di\DependencyInjectorInterface;
use Europa\Di\Finder;
use Europa\Fs\Locator;
use Europa\Router\Router;

abstract class ModuleBootstrapperAbstract extends BootstrapperAbstract
{
    private $injector;

    private $module;

    public function __construct(DependencyInjectorInterface $injector, ModuleInterface $module)
    {
        $this->injector = $injector;
        $this->module   = $module;
    }

    public function router()
    {
        if ($path = realpath($this->module->getPath() . '/configs/routes.yml')) {
            $router = new Router;
            $router->import($path);
            $this->injector->get('routers')->append($router);
        }
    }

    public function views()
    {
        if ($path = realpath($this->module->getPath() . '/views')) {
            $locator = new Locator;
            $locator->addPath($path);
            $this->injector->get('viewLocators')->append($locator);
        }
    }

    protected function getConfig($name)
    {
        return $this->module->getConfig()[$name];
    }

    protected function getDependencyInjector()
    {
        return $this->injector;
    }

    protected function getModule()
    {
        return $this->module;
    }

    protected function getService($name)
    {
        return $this->injector->get($name);
    }
}