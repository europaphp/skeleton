<?php

namespace Europa\Module;
use Europa\Di\DependencyInjectorInterface;

interface ModuleInterface
{
    public function bootstrap(DependencyInjectorInterface $injector);

    public function getNamespace();

    public function getName();

    public function getVersion();

    public function getPath();

    public function getConfig();

    public function getDependencies();
}