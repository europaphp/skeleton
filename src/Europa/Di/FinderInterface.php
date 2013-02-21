<?php

namespace Europa\Di;

interface FinderInterface extends DependencyInjectorInterface
{
    public function setArgs($instanceof, array $args);

    public function setCallback($instanceof, callable $callback);
}