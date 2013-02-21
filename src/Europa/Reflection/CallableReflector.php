<?php

namespace Europa\Reflection;
use Closure;
use Europa\Exception\Exception;

class CallableReflector implements ReflectorInterface
{
    private $reflector;

    public function __construct(callable $callable)
    {
        if (is_array($callable)) {
            $this->reflector = new MethodReflector($callable[0], $callable[1]);
        } elseif (is_object($callable)) {
            $this->reflector = new ClassReflector($callable);
        } elseif ($callable instanceof Closure || function_exists($callable)) {
            $this->reflector = new FunctionReflector($callable);
        }

        Exception::toss('The callable could not be reflected.');
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