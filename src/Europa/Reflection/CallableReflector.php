<?php

namespace Europa\Reflection;
use Europa\Exception;

class CallableReflector implements ReflectorInterface
{
    private $instance;

    private $reflector;

    public function __construct(callable $callable)
    {
        if ($callable instanceof \Closure || (is_string($callable) && function_exists($callable))) {
            $this->reflector = new FunctionReflector($callable);
        } elseif (is_array($callable)) {
            $this->instance  = is_object($callable[0]) ? $callable[0] : null;
            $this->reflector = new MethodReflector($callable[0], $callable[1]);
        } elseif (is_object($callable)) {
            $this->instance  = $callable;
            $this->reflector = new MethodReflector($callable, '__invoke');
        } else {
            throw new Exception\NotReflectable('The callable could not be reflected.');
        }
    }

    public function __call($name, array $args = [])
    {
        return call_user_func_array([$this->reflector, $name], $args);
    }

    public function __invoke()
    {
        if ($this->instance) {
            return $this->reflector->invokeArgs($this->instance, func_get_args());
        }

        return $this->reflector->invokeArgs(func_get_args());
    }

    public function __toString()
    {
        return $this->reflector->__toString();
    }

    public function getReflector()
    {
        return $this->reflector;
    }

    public function getDocBlock()
    {
        return $this->reflector->getDocBlock();
    }

    public function getClosure()
    {
        if ($this->reflector instanceof MethodReflector) {
            return $this->reflector->getClosure($this->instance);
        }

        return $this->reflector->getClosure();
    }
}