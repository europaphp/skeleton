<?php

namespace Europa\Router;
use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;
use LogicException;

/**
 * Default request router implementation.
 * 
 * @category Router
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Router implements ArrayAccess, IteratorAggregate
{
    /**
     * The array of routes to match.
     * 
     * @var array
     */
    private $routes = array();
    
    /**
     * Routes the specified request.
     * 
     * @param RequestInterface $request The request to route.
     * 
     * @return bool
     */
    public function __invoke($query)
    {
        foreach ($this->routes as $route) {
            $result = call_user_func($route, $query);

            if ($result !== false) {
                return $result;
            }
        }

        return false;
    }

    /**
     * Sets a route.
     * 
     * @param string         $name  The name of the route.
     * @param RouteInterface $route The route to use.
     * 
     * @return Router
     */
    public function offsetSet($name, $route)
    {
        if (!is_callable($route)) {
            throw new LogicException(sprintf('The route "%s" is not callable.', $name));
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

        throw new LogicException(sprintf('Cannot get route "%s" because it does not exist.', $name));
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
     * Returns the iterator.
     * 
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->routes);
    }
}