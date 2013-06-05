<?php

namespace Europa\Controller;
use Europa\Exception\Exception;
use Europa\Reflection\ClassReflector;
use Europa\Reflection\MethodReflector;
use Europa\Reflection\ReflectorInterface;

abstract class ControllerAbstract
{
    const DOC_TAG_FILTER = 'filter';

    public function forward($to)
    {
        return $this->invoke($to);
    }

    private function getFiltersFor(ReflectorInterface $reflector)
    {
        $filters = [];

        foreach ($reflector->getDocBlock()->getTags(static::DOC_TAG_FILTER) as $filter) {
            $parts = explode(' ', $filter->value(), 2);
            $class = trim($parts[0]);
            $value = isset($parts[1]) ? trim($parts[1]) : '';

            if (!class_exists($class)) {
                Exception::toss('The filter "%s" specified for "%s" does not exist.', $class, $reflector->__toString());
            }

            $filters[] = new $class($value);
        }

        return $filters;
    }

    private function applyClassFilters(ClassReflector $class, MethodReflector $method, array &$context)
    {
        foreach ($this->getFiltersFor($class) as $filter) {
            $filter($this, $class, $method, $context);
        }
    }

    private function applyActionFilters(ClassReflector $class, MethodReflector $method, array &$context)
    {
        foreach ($this->getFiltersFor($method) as $filter) {
            $filter($this, $class, $method, $context);
        }
    }
}