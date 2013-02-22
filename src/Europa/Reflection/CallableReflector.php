<?php

namespace Europa\Reflection;
use Closure;
use Europa\Exception\Exception;

class CallableReflector implements ReflectorInterface
{
    private $reflector;

    public function __construct(callable $callable)
    {
        if ($callable instanceof Closure || (is_string($callable) && function_exists($callable))) {
            $this->reflector = new FunctionReflector($callable);
        } elseif (is_array($callable)) {
            $this->reflector = new MethodReflector($callable[0], $callable[1]);
        } elseif (is_object($callable)) {
            $this->reflector = new ClassReflector($callable);
        }

        if (!$this->reflector) {
            Exception::toss('The callable could not be reflected.');
        }
    }

    public function __toString()
    {
        return $this->reflector->__toString();
    }

    public function getDocBlock()
    {
        return $this->reflector->getDocBlock();
    }

    public function getReflector()
    {
        return $this->reflector;
    }
}