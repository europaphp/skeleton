<?php

namespace Europa\Router;
use Europa\Di;

interface RouterInterface extends Di\ContainerAwareInterface
{
    public function when($pattern, $controller);

    public function otherwise($controller);
}