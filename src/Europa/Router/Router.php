<?php

namespace Europa\Router;
use ArrayIterator;
use Europa\Config\Config;
use Europa\Filter\ClassNameFilter;
use Europa\Filter\MethodNameFilter;
use Europa\Reflection\ClassReflector;
use Europa\Reflection\MethodReflector;
use Europa\Request\RequestInterface;
use Europa\Router\Route\Token;
use InvalidArgumentException;
use LogicException;
use RuntimeException;

/**
 * Default request router implementation.
 * 
 * @category Router
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Router implements RouterInterface
{
    /**
     * The request filter used to turn a request into a string.
     * 
     * @var mixed
     */
    private $requestFilter;

    /**
     * The array of routes to match.
     * 
     * @var array
     */
    private $routes = array();

    /**
     * Sets up the router.
     * 
     * @return Router
     */
    public function __construct()
    {
        $this->requestFilter = [$this, 'requestFilter'];
    }
    
    /**
     * Routes the specified request.
     * 
     * @param RequestInterface $request The request to route.
     * 
     * @return callable | void
     */
    public function __invoke(RequestInterface $request)
    {
        $requestString = call_user_func($this->requestFilter, $request);

        foreach ($this->routes as $route) {
            if (is_array($matched = call_user_func($route, $requestString))) {
                $request->setParams($matched);
                return true;
            }   
        }

        return false;
    }

    /**
     * Sets the request filter.
     * 
     * @param callable $filter The filter.
     * 
     * @return Router
     */
    public function setRequestFilter(callable $filter)
    {
        $this->requestFilter = $filter;
        return $this;
    }

    /**
     * Returns the request filter.
     * 
     * @return mixed
     */
    public function getRequestFilter()
    {
        return $this->requestFilter;
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
        if (is_callable($routes)) {
            $routes = call_user_func($routes);
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
        if (is_string($route)) {
            $route = new Token($route, [$this->config->params->controller => $name]);
        } elseif (!is_callable($route)) {
            throw new InvalidArgumentException(sprintf('The route specified as "%s" is not callable.', $name));
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

    /**
     * The default request filter.
     * 
     * @return string
     */
    private function requestFilter(RequestInterface $request)
    {
        return $request->getMethod() . ' ' . $request->getUri()->getRequestPart();
    }
}