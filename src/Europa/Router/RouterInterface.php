<?php

namespace Europa\Router;
use Europa\Request\RequestInterface;

interface RouterInterface
{
    public function route(RequestInterface $request);

    public function format($name, array $params = []);
}