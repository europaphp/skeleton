<?php

namespace Europa\Controller;

interface FilterInterface
{
    public function __construct(\Europa\Controller $controller, \Europa\Controller\FilterData $data);

    public function filter();
}