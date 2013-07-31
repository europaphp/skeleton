<?php

namespace Europa\Router;
use Europa\Di;
use Europa\Reflection;
use Europa\Request;

class Resolver
{
  use Di\ContainerAware;

  public function __invoke($controller, Request\RequestInterface $request)
  {
    if (!is_callable($controller)) {
      if (is_string($controller) && strpos($controller, '->')) {
        $parts = explode('->', $controller, 2);
        $class = new Reflection\ClassReflector($parts[0]);
        $controller = [
          $class->newInstanceArgs(
            $this->resolveConstructorParams(
              $class
            )
          ),
          $parts[1]
        ];
      } else {
        return;
      }
    }

    return $controller;
  }

  public function resolveConstructorParams(Reflection\ClassReflector $class)
  {
    $params = [];
    $container = $this->getContainer();

    if ($container && $class->hasMethod('__construct')) {
      foreach ($class->getMethod('__construct')->getParameters() as $param) {
        $params[] = $container($param->getName());
      }
    }

    return $params;
  }
}