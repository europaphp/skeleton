<?php

namespace Test\Provider\Controller;
use Europa\Controller\RestController;

class MethodFilter
{
    public function __invoke($args, RestController $controller)
    {
        $controller->methodFilter = $args;
    }
}