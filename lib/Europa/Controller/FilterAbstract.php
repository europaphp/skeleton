<?php

namespace Europa\Controller;

class FilterAbstract implements FilterInterface
{
    protected $controller;

    public function __construct(\Europa\Controller $controller)
    {
        $this->controller = $controller;
    }
}