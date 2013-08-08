<?php

namespace Europa\Di;

interface ContainerInterface
{
  public function __invoke($name);

  public function register($name, callable $service);

  public function configure(callable $configuration);

  public function alias($name, array $aliases);

  public function template($name);

  public function constrain($name, array $types);
}