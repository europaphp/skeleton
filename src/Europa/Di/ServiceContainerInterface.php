<?php

namespace Europa\Di;

interface ServiceContainerInterface
{
    public function __invoke($name);

    public function __call($name, array $args = []);

    public function __set($name, $value);

    public function __get($name);

    public function __isset($name);

    public function __unset($name);

    public function transient($name);

    public function isTransient($name);

    public function provides($blueprint);

    public function mustProvide($blueprint);
}