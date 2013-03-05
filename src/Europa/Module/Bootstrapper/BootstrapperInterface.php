<?php

namespace Europa\Module\Bootstrapper;
use Europa\Di\DependencyInjectorInterface;
use Europa\Module\ModuleInterface;

interface BootstrapperInterface extends \Europa\Bootstrapper\BootstrapperInterface
{
    public function __construct(ModuleInterface $module, DependencyInjectorInterface $injector);
}