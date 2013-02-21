<?php

namespace Europa\Di;
use Closure;

interface FinderInterface extends DependencyInjectorInterface
{
    public function addArgs($instanceof, Closure $closure);

    public function addCallback($instanceof, Closure $closure);
}