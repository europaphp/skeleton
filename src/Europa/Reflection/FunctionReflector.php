<?php

namespace Europa\Reflection;
use Europa\Exception\Exception;
use ReflectionFunction;

 class FunctionReflector extends ReflectionFunction implements ParameterAwareInterface, ReflectorInterface
{
    use ParameterAwareTrait;

    public function __toString()
    {
        return $this->name;
    }

    public function __invoke()
    {
        return $this->invokeArgs(func_get_args());
    }

    public function invokeArgs(array $args = array())
    {
        if (func_num_args() === 2 && $this->getNumberOfParameters() > 0) {
            return parent::invokeArgs($this->mergeNamedArgs($args));
        }

        return $this->invoke();
    }

    public function getDocBlock()
    {
        return new DocBlock($this->getDocComment());
    }
}