<?php

namespace Europa\Reflection;

class CallableReflector implements ReflectorInterface
{
  const POINTER = '->';

  private $instance;

  private $reflector;

  public function __construct($callable)
  {
    if ($callable instanceof \Closure || (is_string($callable) && function_exists($callable))) {
      $this->initFunction($callable);
    } elseif (is_array($callable)) {
      $this->initArray($callable);
    } elseif (is_object($callable) && method_exists($callable, '__invoke')) {
      $this->initInvokable($callable);
    } elseif (is_string($callable) && strpos($callable, self::POINTER)) {
      $this->initInstance($callable);
    } else {
      throw new Exception\InvalidCallable(['type' => gettype($callable)]);
    }
  }

  public function __call($name, array $args = [])
  {
    return call_user_func_array([$this->reflector, $name], $args);
  }

  public function __invoke()
  {
    return $this->invokeArgs(func_get_args());
  }

  public function __toString()
  {
    return $this->reflector->__toString();
  }

  public function invoke()
  {
    return $this->invokeArgs(func_get_args());
  }

  public function invokeArgs(array $args = [])
  {
    if ($this->instance) {
      return $this->reflector->invokeArgs($this->instance, $args);
    }

    return $this->reflector->invokeArgs($args);
  }

  public function getInstance()
  {
    return $this->instance;
  }

  public function getReflector()
  {
    return $this->reflector;
  }

  public function getDocBlock()
  {
    return $this->reflector->getDocBlock();
  }

  private function initFunction($callable)
  {
    $this->reflector = new FunctionReflector($callable);
  }

  private function initArray($callable)
  {
    $this->instance = is_object($callable[0]) ? $callable[0] : null;
    $this->reflector = new MethodReflector($callable[0], $callable[1]);
  }

  private function initInvokable($callable)
  {
    $this->instance = $callable;
    $this->reflector = new MethodReflector($callable, '__invoke');
  }

  private function initInstance($callable)
  {
    $parts = explode(self::POINTER, $callable);
    $this->reflector = new MethodReflector($parts[0], $parts[1]);
  }
}