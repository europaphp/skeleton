<?php

namespace Test\Provider\Controller;
use Europa\Controller\RestController;

class ClassFilter
{
    public function __invoke($args, RestController $controller)
    {
        $controller->classFilter = $args;
    }
}