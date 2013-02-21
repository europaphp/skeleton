<?php

namespace Europa\Router;
use ArrayIterator;
use Countable;
use Europa\Config\Config;
use Europa\Exception\Exception;
use Europa\Request\RequestInterface;
use IteratorAggregate;

class Router implements Countable, IteratorAggregate, RouterInterface
{
    private $routes = [];

    public function route(RequestInterface $request)
    {
        foreach ($this->routes as $name => $route) {
            if ($params = $route->query($request)) {
                $request->setParams($params);
                return true;
            }
        }

        return false;
    }

    public function format($name, array $params = [])
    {
        if (!isset($this->routes[$name])) {
            Exception::toss('The route "%s" cannot be formatted because it does not exist.', $name);
        }

        return $this->routes[$name]->format($params);
    }

    public function set($name, RouteInterface $route)
    {
        $this->routes[$name] = $route;
        return $this;
    }

    public function get($name)
    {
        if (isset($this->routes[$name])) {
            return $this->routes[$name];
        }

        Exception::toss('The route "%s" does not exist.', $name);
    }

    public function has($name)
    {
        return isset($this->routes[$name]);
    }

    public function remove($name)
    {
        if (isset($this->routes[$name])) {
            unset($this->routes[$name]);
        }

        return $this;
    }

    public function count()
    {
        return count($this->routes);
    }

    public function getIterator()
    {
        return new ArrayIterator($this->routes);
    }

    public function import($routes)
    {
        foreach (new Config($routes) as $name => $route) {
            $this->set($name, new Route($route));
        }

        return $this;
    }
}