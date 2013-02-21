<?php

namespace Europa\Router;

interface RouterArrayInterface extends RouterInterface
{
    public function add(RouterInterface $router);
}