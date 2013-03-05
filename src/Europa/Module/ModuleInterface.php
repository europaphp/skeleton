<?php

namespace Europa\Module;
use Europa\Di\DependencyInjectorInterface;

interface ModuleInterface
{
    public function bootstrap(DependencyInjectorInterface $injector);

    public function ns();

    public function name();

    public function version();

    public function path();

    public function config();

    public function dependencies();
}