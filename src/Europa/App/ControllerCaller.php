<?php

namespace Europa\App;
use Europa\Di;
use Europa\Reflection;
use Europa\Request;

class ControllerCaller implements Di\ContainerAwareInterface, Request\RequestAwareInterface
{
  use Di\ContainerAware;
  use Request\RequestAware;

  public function __invoke(callable $controller)
  {
    $parameters = [];
    $controller = new Reflection\CallableReflector($controller);
    $instance = $controller->getInstance();
  
    // If the controller is an instance, we have already injected
    // dependencies into it's constructor. To give the user the most from
    // the caller, we now inject named parameters into the method being
    // called.
    //
    // If the controller is just a callable, we simply inject dependencies
    // matching the parameter names into the callable since this is the
    // only chance it will have access to dependencies in the container.
    foreach ($controller->getReflector()->getParameters() as $parameter) {
      if ($instance && $this->request) {
        if ($this->request->hasParam($parameter->getName())) {
          $parameters[] = $this->request->getParam($parameter->getName());
        }
      } elseif ($container = $this->container) {
        $parameters[] = $container($parameter->getName());
      }
    }
  
    return $controller->invokeArgs($parameters) ?: [];
  }
}
