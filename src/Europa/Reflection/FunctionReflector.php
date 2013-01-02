<?php

namespace Europa\Reflection;
use Europa\Exception\Exception;
use ReflectionFunction;

class FunctionReflector extends ReflectionFunction implements ReflectorInterface
{
    public function mergeNamedArgs(array $params, $caseSensitive = false, $throw = true)
    {
        // resulting merged parameters will be stored here
        $merged = array();

        // apply strict position parameters and case sensitivity
        foreach ($params as $name => $value) {
            if (is_numeric($name)) {
                $merged[(int) $name] = $value;
            } elseif (!$caseSensitive) {
                $params[strtolower($name)] = $value;
            }
        }

        // we check each parameter and set accordingly
        foreach ($this->getParameters() as $param) {
            $pos  = $param->getPosition();
            $name = $caseSensitive ? $param->getName() : strtolower($param->getName());

            if (array_key_exists($name, $params)) {
                $merged[$pos] = $params[$name];
            } elseif (array_key_exists($pos, $params)) {
                $merged[$pos] = $params[$pos];
            } elseif ($param->isOptional()) {
                $merged[$pos] = $param->getDefaultValue();
            } elseif ($throw) {
                Exception::toss('The required parameter "%s" for function "%s()" was not specified.', $param->getName(), $this->getName());
            } else {
                $meged[$pos] = null;
            }
        }

        return $merged;
    }

    public function invokeArgs(array $args = array())
    {
        // only merged named parameters if necessary
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