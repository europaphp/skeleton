<?php

namespace Europa\Di;
use Closure;

interface ContainerInterface extends DependencyInjectorInterface
{
    public function set($name, Closure $service);

    public function remove($name);

    public function setAliases($name, array $aliases);

    public function setDependencies($name, array $dependencies);

    public function setTransient($name);
}