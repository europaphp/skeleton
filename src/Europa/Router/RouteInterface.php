<?php

namespace Europa\Router;
use Europa\Request\RequestInterface;

interface RouteInterface
{
    public function query(RequestInterface $request);

    public function format(array $params = []);
}