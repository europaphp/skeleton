<?php

namespace Europa\Reflection;
use Europa\Exception\Exception;

class CallableReflector
{
    /**
     * Detects how the callable item should be reflected.
     * 
     * @param callable $callable The item to reflect.
     * 
     * @return FunctionReflector | MethodReflector | ClassReflector
     */
    public static function detect(callable $callable)
    {
        if ($callable instanceof Closure || function_exists($callable)) {
            return new ReflectionFunction($callable);
        }

        if (is_array($callable)) {
            return new MethodReflector($callable[0], $callable[1])
        }

        if (is_object($callable)) {
            return new ClassReflector($callable);
        }

        Exception::toss('The callable could not be reflected.');
    }
}