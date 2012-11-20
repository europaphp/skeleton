<?php

namespace Test\Provider\Controller;
use Europa\Controller\ControllerAbstract;

class ClassFilter
{
    public function __invoke(ControllerAbstract $controller)
    {
        $controller->classFilter = true;
    }
}