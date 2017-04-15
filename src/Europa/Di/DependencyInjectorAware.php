<?php

namespace Europa\Di;

trait DependencyInjectorAware
{
    private $injector;

    public function setDependencyInjector(DependencyInjectorInterface $injector)
    {
        $this->injector = $injector;
        return $this;
    }

    public function getDependencyInjector()
    {
        return $this->injector;
    }
}