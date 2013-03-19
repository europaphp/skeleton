<?php

namespace Europa\Di;
use Closure;

interface ResolverInterface extends DependencyInjectorInterface
{
    public function addArgs($instanceof, Closure $closure);

    public function addCallback($instanceof, Closure $closure);

    public function setTransient();
}