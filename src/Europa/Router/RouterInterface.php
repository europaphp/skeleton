<?php

namespace Europa\Router;
use Europa\Di;

interface RouterInterface extends Di\ContainerAwareInterface
{
    public function when($pattern, callable $controller);

    public function otherwise(callable $controller);
}