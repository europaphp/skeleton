<?php

namespace Test\Provider\Controller;
use Europa\Controller\AbstractController;

class MethodFilter
{
    public function __invoke(AbstractController $controller)
    {
        $controller->methodFilter = true;
    }
}