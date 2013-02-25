<?php

namespace Europa\Module;
use Europa\Bootstrapper\BootstrapperAbstract;
use Europa\Di\ContainerInterface;
use Europa\Di\Finder;
use Europa\Fs\Locator;
use Europa\Router\Router;

abstract class ModuleBootstrapperAbstract extends BootstrapperAbstract
{
    private $container;

    private $module;

    public function __construct(ContainerInterface $container, ModuleInterface $module)
    {
        $this->container = $container;
        $this->module    = $module;
    }

    public function router()
    {
        if ($path = realpath($this->module->getPath() . '/configs/routes.yml')) {
            $router = new Router;
            $router->import($path);
            $this->container->get('routers')->append($router);
        }
    }

    public function views()
    {
        if ($path = realpath($this->module->getPath() . '/views')) {
            $locator = new Locator;
            $locator->addPath($path);
            $this->container->get('viewLocators')->append($locator);
        }
    }

    protected function getConfig($name)
    {
        return $this->module->getConfig()[$name];
    }

    protected function getContainer()
    {
        return $this->container;
    }

    protected function getModule()
    {
        return $this->module;
    }

    protected function getService($name)
    {
        return $this->container->get($name);
    }
}