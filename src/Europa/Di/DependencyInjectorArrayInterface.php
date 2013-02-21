<?php

namespace Europa\Di;

interface DependencyInjectorArrayInterface extends DependencyInjectorInterface
{
    public function add(DependencyInjectorInterface $injector);
}