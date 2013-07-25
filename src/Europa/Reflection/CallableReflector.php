<?php

namespace Europa\Reflection;
use Europa\Exception;

class CallableReflector implements ReflectorInterface
{
    private $instance;

    private $reflector;

    public function __construct($callable, array $injectable = [])
    {
        if ($callable instanceof \Closure || (is_string($callable) && function_exists($callable))) {
            $this->initFunction($callable);
        } elseif (is_array($callable)) {
            $this->initArray($callable);
        } elseif (is_object($callable) && method_exists($callable, '__invoke')) {
            $this->initInvokable($callable);
        } elseif (strpos($callable, '->')) {
            $this->initInstance($callable, $injectable);
        } else {
            throw new Exception\InvalidCallable;
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

    private function initInstance($callable, array $injectable)
    {
        $parts = explode('->', $callable, 2);
        $class = new ClassReflector($parts[0]);
        $this->instance = $class->newInstanceArgs($injectable);
        $this->reflector = new MethodReflector($this->instance, $this->resolveMethod($parts[1]));
    }
}