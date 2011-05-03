<?php

namespace Europa\Controller;
use Europa\Controller;

interface FilterInterface
{
    public function filter(Controller $controller);
}