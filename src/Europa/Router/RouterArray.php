<?php

namespace Europa\Router;
use Europa\Request\RequestInterface;

class RouterArray implements RouterArrayInterface
{
    private $routers = [];

    public function route(RequestInterface $request)
    {
        foreach ($this->routers as $router) {
            if ($router->route($request)) {
                return true;
            }
        }

        return false;
    }

    public function format($name, array $params = [])
    {
        foreach ($this->routers as $router) {
            if ($router->has($name)) {
                return $router->get($name)->format($params);
            }
        }

        Exception::toss('The route "%s" cannot be formatted because it does not exist.', $name);
    }

    public function add(RouterInterface $router)
    {
        $this->routers[] = $router;
        return $this;
    }
}