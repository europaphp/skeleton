<?php

namespace Europa\Di;
use ArrayIterator;
use Europa\Reflection\ClassReflector;
use IteratorAggregate;

abstract class ConfigurationAbstract implements ConfigurationInterface, IteratorAggregate
{
    private $methods;

    public function configure(ContainerInterface $container)
    {
        foreach ($this as $method) {
            $container->__set($method, function() use ($method) {
                return call_user_func_array([$this, $method], func_get_args());
            });
        }
    }

    public function getIterator()
    {
        if ($this->methods) {
            return $this->methods;
        }

        $class         = new ClassReflector($this);
        $this->methods = new ArrayIterator;

        foreach ($class->getMethods() as $method) {
            if ($method->isInherited() || $method->isMagic()) {
                continue;
            }

            $this->methods[] = $method->getName();
        }

        return $this->methods;
    }
}