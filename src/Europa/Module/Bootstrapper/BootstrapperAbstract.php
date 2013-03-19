<?php

namespace Europa\Module\Bootstrapper;
use Europa\Di\DependencyInjectorInterface;
use Europa\Module\ModuleInterface;

abstract class BootstrapperAbstract extends \Europa\Bootstrapper\BootstrapperAbstract implements BootstrapperInterface
{
    protected $injector;

    protected $module;

    public function __construct(ModuleInterface $module, DependencyInjectorInterface $injector)
    {
        $this->injector = $injector;
        $this->module   = $module;
    }
}