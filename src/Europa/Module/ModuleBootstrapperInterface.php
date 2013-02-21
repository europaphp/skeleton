<?php

namespace Europa\Module;
use Europa\Bootstrapper\BootstrapperInterface;
use Europa\Di\ContainerInterface;

interface ModuleBootstrapperInterface extends BootstrapperInterface
{
    public function __construct(ModuleInterface $module, ContainerInterface $container);
}