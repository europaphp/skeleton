<?php

namespace Europa\Router;
use ArrayAccess;
use ArrayIterator;
use Countable;
use Europa\Exception\Exception;
use Europa\Request\RequestInterface;
use IteratorAggregate;

/**
 * Default request router implementation.
 * 
 * @category Router
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Router implements ArrayAccess, Countable, IteratorAggregate
{
    /**
     * The array of routes to match.
     * 
     * @var array
     */
    private $routes = array();
    
    /**
     * Queries the router.
     * 
     * @param string $query The query to route.
     * 
     * @return callable | void
     */
    public function __invoke(RequestInterface $request)
    {
        foreach ($this->routes as $name => $route) {
            if ($controller = call_user_func($route, $name, $request)) {
                return $controller;
            }   
        }
    }

    /**
     * Imports a list of routes.
     * 
     * @param mixed $routes The routes to add.
     * 
     * @return Router
     */
    public function import($routes)
    {
        if (is_string($routes)) {
            $adapter = pathinfo($routes, PATHINFO_EXTENSION);
            $adapter = 'Europa\Router\Adapter\\' . ucfirst($adapter);

            if (!class_exists($adapter)) {
                Exception::toss('The router adapter "%s" does not exist.', $adapter);
            }

            return $this->import(new $adapter($routes));
        }

        if (is_callable($routes)) {
            $routes = $routes();
        }

        if (is_array($routes) || is_object($routes)) {
            foreach ($routes as $name => $route) {
                $this->offsetSet($name, $route);
            }
        }

        return $this;
    }

    /**
     * Returns the array of routes.
     * 
     * @return array
     */
    public function export()
    {
        return $this->routes;
    }

    /**
     * Clears all routes.
     * 
     * @return Router
     */
    public function clear()
    {
        $this->routes = [];
        return $this;
    }

    /**
     * Sets a route. The `$name` is used as a default controller if passing the `$route` as a string.
     * 
     * @param string $name  The name of the route.
     * @param mixed  $route The route to use.
     * 
     * @return Router
     */
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
    
    /**
     * Returns the route if it exists.
     * 
     * @param string $name The route name.
     * 
     * @return RouteInterface | null
     */
    public function offsetGet($name)
    {
        if (isset($this->routes[$name])) {
            return $this->routes[$name];
        }

        Exception::toss('Cannot get route "%s" because it does not exist.', $name);
    }
    
    /**
     * Returns whether or not the route exists.
     * 
     * @param string $name The route name.
     * 
     * @return bool
     */
    public function offsetExists($name)
    {
        return isset($this->routes[$name]);
    }
    
    /**
     * Removes the route.
     * 
     * @param string $name The route name.
     * 
     * @return Router
     */
    public function offsetUnset($name)
    {
        if (isset($this->routes[$name])) {
            unset($this->routes[$name]);
        }

        return $this;
    }

    /**
     * Returns the number of routes in the router.
     * 
     * @return int
     */
    public function count()
    {
        return count($this->routes);
    }

    /**
     * Returns the iterator.
     * 
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->routes);
    }
}