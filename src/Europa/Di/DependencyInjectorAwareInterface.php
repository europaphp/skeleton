<?php

namespace Europa\Di;

interface DependencyInjectorAwareInterface
{
    public function setDependencyInjector(DependencyInjectorInterface $injector);

    public function getDependencyInjector();
}