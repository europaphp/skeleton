<?php

namespace Europa\Router;
use Closure;
use Europa\Request\RequestInterface;
use LogicException;

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
     * Sets the filter the router should use.
     * 
     * @var Closure
     */
    private $filter;
    
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
    public function route(RequestInterface $request)
    {
        foreach ($this->routes as $route) {
            if ($result = $route->query($this->filterRequest($request))) {
                $request->setParams($result);
                return true;
            }
        }
        return false;
    }
    
    /**
     * Reverse engineers the specified route.
     * 
     * @param string $name   The name of the route to reverse engineer.
     * @param array  $params The parameters to use when reverse engineering the route.
     * 
     * @return string
     */
    public function format($name, array $params = array())
    {
        return $this->getRoute($name)->format($params);
    }
    
    /**
     * Sets a filter used to turn the request into a matchable string.
     * 
     * @param Closure $filter The filter.
     * 
     * @return Route
     */
    public function filter(Closure $filter)
    {
        $this->filter = $filter;
        return $this;
    }

    /**
     * Sets a route.
     * 
     * @param string         $name  The name of the route.
     * @param RouteInterface $route The route to use.
     * 
     * @return Router
     */
    public function setRoute($name, RouteInterface $route)
    {
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
    public function getRoute($name)
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
    public function hasRoute($name)
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
    public function removeRoute($name)
    {
        if (isset($this->routes[$name])) {
            unset($this->routes[$name]);
        }
        throw new LogicException(sprintf('Cannot remove route "%s" because it does not exist.', $name));
    }
    
    /**
     * Filters the request.
     * 
     * @param RequestInterface $request The request to filter.
     * 
     * @return string
     */
    private function filterRequest(RequestInterface $request)
    {
        if ($this->filter) {
            return call_user_func($this->filter, $request);
        }
        return $request->__toString();
    }
}