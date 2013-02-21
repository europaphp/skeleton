<?php

namespace Europa\Di;

interface DependencyInjectorInterface
{
    public function get($name);

    public function has($name);

    public function provides($blueprint);
}