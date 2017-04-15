<?php

namespace Europa\Router;
use Europa\Common\InstanceIterator;
use Europa\Request\RequestInterface;
use Traversable;

class RouterArray implements RouterInterface
{
    private $routers;

    public function __construct(Traversable $routers)
    {
        $this->routers = new InstanceIterator('Europa\Router\RouterInterface', $routers);
    }

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
}