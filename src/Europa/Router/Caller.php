<?php

namespace Europa\Router;
use Europa\Di;
use Europa\Reflection;
use Europa\Request;

class Caller
{
  use Di\ContainerAware;

  public function __invoke(callable $controller, Request\RequestInterface $request)
  {
    $container = $this->getContainer();
    $parameters = [];
    $controller = new Reflection\CallableReflector($controller);
    $instance = $controller->getInstance();

    // If the controller is an instance, we have already injected
    // dependencies into it's constructor. To give the user the most from
    // the router, we now inject named parameters into the method being
    // called.
    //
    // If the controller is just a callable, we simply inject dependencies
    // matching the parameter names into the callable since this is the
    // only chance it will have access to dependencies in the container.
    foreach ($controller->getReflector()->getParameters() as $parameter) {
      if ($instance) {
        if ($request->hasParam($parameter->getName())) {
          $parameters[] = $request->getParam($parameter->getName());
        }
      } elseif ($container) {
        $parameters[] = $container($parameter->getName());
      }
    }

    return $controller->invokeArgs($parameters) ?: [];
  }
}