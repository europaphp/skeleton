<?php

namespace Europa\Di;
use Closure;

interface ContainerInterface extends DependencyInjectorInterface
{
    public function configure(ConfigurationInterface $configuration);

    public function set($name, Closure $service);

    public function remove($name);

    public function setAliases($name, array $aliases);

    public function setTransient($name);
}