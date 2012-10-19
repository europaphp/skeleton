<?php

namespace Test\Provider\Controller;
use Europa\Controller\AbstractController;

class ClassFilter
{
    public function __invoke(AbstractController $controller)
    {
        $controller->classFilter = true;
    }
}