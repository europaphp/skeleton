<?php

namespace Test\Provider\Controller;
use Europa\Controller\ControllerAbstract;

class MethodFilter
{
    public function __invoke(ControllerAbstract $controller)
    {
        $controller->methodFilter = true;
    }
}