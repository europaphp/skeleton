<?php

namespace Europa\Di;
use ReflectionClass;

class DependencyInjectorArray implements DependencyInjectorArrayInterface
{
    private $injectors = [];

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

    public function provides($blueprint)
    {
        $reflector = new ReflectionClass($blueprint);

        foreach ($reflector->getMethods() as $method) {
            foreach ($this->injectors as $injector) {
                if ($injector->has($method->getName())) {
                    continue 2;
                }
            }

            return false;
        }

        return true;
    }

    public function add(DependencyInjectorInterface $injector)
    {
        $this->injectors[] = $injector;
        return $this;
    }
}