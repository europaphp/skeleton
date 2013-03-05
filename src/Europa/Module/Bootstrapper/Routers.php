<?php

namespace Europa\Module\Bootstrapper;
use Europa\Router\Router;

trait Routers
{
    public function routers()
    {
        if ($path = realpath($this->module->path() . '/configs/routes.yml')) {
            $router = new Router;
            $router->import($path);
            $this->injector->get('routers')->append($router);
        }
    }
}