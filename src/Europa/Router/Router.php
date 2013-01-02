<?php

namespace Europa\Router;
use ArrayAccess;
use ArrayIterator;
use Countable;
use Europa\Config\Config;
use Europa\Exception\Exception;
use Europa\Request\RequestInterface;
use IteratorAggregate;

class Router implements ArrayAccess, Countable, IteratorAggregate
{
    private $routes = array();
    
    public function __invoke(RequestInterface $request)
    {
        foreach ($this->routes as $name => $route) {
            if ($controller = $route($name, $request)) {
                return $controller;
            }
        }
    }

    public function format($route, array $params = [])
    {
        if (!$this->offsetExists($route)) {
            Exception::toss('The route "%s" cannot be formatted because it does not exist.', $route);
        }

        $route = $this->offsetGet($route);

        if (!$route instanceof Route) {
            Exception::toss('The route "%s" must be an instance of "Europa\Router\Route" in order to be formatted.', $route);
        }

        return $route->format($params);
    }

    public function import($routes)
    {
        foreach (new Config($routes) as $name => $route) {
            $this->offsetSet($name, $route);
        }

        return $this;
    }

    public function export()
    {
        return $this->routes;
    }

    public function clear()
    {
        $this->routes = [];
        return $this;
    }

    public function offsetSet($name, $route)
    {
        if (!is_callable($route)) {
            $route = new Route($route);
        }

        if (!$name) {
            $name = count($this->routes);
        }

        $this->routes[$name] = $route;

        return $this;
    }
    
    public function offsetGet($name)
    {
        if (isset($this->routes[$name])) {
            return $this->routes[$name];
        }

        Exception::toss('Cannot get route "%s" because it does not exist.', $name);
    }
    
    public function offsetExists($name)
    {
        return isset($this->routes[$name]);
    }
    
    public function offsetUnset($name)
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
}