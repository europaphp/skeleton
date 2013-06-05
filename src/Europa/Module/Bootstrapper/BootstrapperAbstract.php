<?php

namespace Europa\Module\Bootstrapper;
use Europa\Di\DependencyInjectorInterface;
use Europa\Module\ModuleInterface;

abstract class BootstrapperAbstract extends \Europa\Bootstrapper\BootstrapperAbstract implements BootstrapperInterface
{
    protected $container;

    protected $module;

    public function __construct(ModuleInterface $module, callable $container)
    {
        $this->container = $container;
        $this->module = $module;
    }

    protected function container($service)
    {
        $container = $this->container;
        return $container($service);
    }
}