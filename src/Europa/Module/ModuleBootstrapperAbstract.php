<?php

namespace Europa\Module;
use Europa\Bootstrapper\BootstrapperAbstract;
use Europa\Di\ContainerInterface;

abstract class ModuleBootstrapperAbstract extends BootstrapperAbstract
{
    protected $module;

    protected $container;

    public function __construct(ModuleInterface $module, ContainerInterface $container)
    {
        $this->module    = $module;
        $this->container = $container;
    }
}