<?php

namespace Europa\Di;
use Europa\Common\InstanceIterator;
use Europa\Exception\Exception;
use Traversable;

class DependencyInjectorArray implements DependencyInjectorInterface
{
    private $injectors;

    public function __construct(Traversable $injectors)
    {
        $this->injectors = new InstanceIterator('Europa\Di\DependencyInjectorInterface', $injectors);
    }

    public function get($name)
    {
        foreach ($this->injectors as $injector) {
            if ($injector->has($name)) {
                return $injector->get($name);
            }
        }

        Exception::toss('The service "%s" does not exist.', $name);
    }

    public function has($name)
    {
        foreach ($this->injectors as $injector) {
            if ($injector->has($name)) {
                return true;
            }
        }

        return false;
    }
}