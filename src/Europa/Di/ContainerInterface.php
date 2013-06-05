<?php

namespace Europa\Di;
use Closure;

interface ContainerInterface
{
    public function __invoke($name);

    public function register($name, Closure $service);

    public function configure(callable $configuration);

    public function setAliases($name, array $aliases);

    public function setDependencies($name, array $dependencies);

    public function setTransient($name);

    public function setTypes($name, array $types);
}