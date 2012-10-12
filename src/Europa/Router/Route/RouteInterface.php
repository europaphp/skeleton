<?php

namespace Europa\Router\Route;

interface RouteInterface
{
    public function __construct($expression, array $defaults = [], array $requrements = []);

    public function __invoke($query);
}