<?php

namespace Europa\Controller;
use Europa\Di\DependencyInjectorAware;
use Europa\Exception\Exception;
use Europa\Reflection\ClassReflector;
use Europa\Reflection\MethodReflector;
use Europa\Reflection\ReflectorInterface;

abstract class ControllerAbstract implements ControllerInterface
{
    use DependencyInjectorAware;

    const DOC_TAG_FILTER = 'filter';

    public function __call($action, array $context = [])
    {
        if (!method_exists($this, $action)) {
            Exception::toss('The action "%s" is not defined in the controller "%s".', $action, get_class($this));
        }

        $class  = new ClassReflector($this);
        $method = $class->getMethod($action);

        $this->applyClassFilters($class, $method, $context);
        $this->applyActionFilters($class, $method, $context);
        
        return $method->invokeArgs($this, $context);
    }

    public function forward($to)
    {
        return $this->invoke($to);
    }

    public function service($service)
    {
        if (!$injector = $this->getDependencyInjector()) {
            Exception::toss('Cannot get service "%s" from controller "%s" because no container was set.', $service, get_class($this));
        }

        if (!$injector->has($service)) {
            Exception::toss('Cannot get service "%s" from controller "%s" because it does not exist in the bound container.', $service, get_class($this));
        }

        return $injector->get($service);
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