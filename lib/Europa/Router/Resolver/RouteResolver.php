<?php

namespace Europa\Router\Resolver;
use Europa\Router\Route\RouteInterface;

/**
 * A basic route query resolver for multiple routes.
 * 
 * @category Router
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class RouteResolver implements ResolverInterface
{
    /**
     * The array of routes to match.
     * 
     * @var array
     */
    private $routes = array();
    
    /**
     * Returns the parameters from the matched route or false if no route is matched.
     * 
     * @param string $subject The subject to match.
     * 
     * @return array|false
     */
    public function query($subject)
    {
        foreach ($this->getRoutes() as $route) {
            $match = $route->query($subject);
            if ($match !== false) {
                return $match;
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
    public function setRoute($name, RouteInterface $route)
    {
        $this->routes[$name] = $route;
        return $this;
    }

    /**
     * Gets a specified route.
     * 
     * @param string $name The name of the route to get.
     * 
     * @return Route
     */
    public function getRoute($name)
    {
        // the route must exist
        if (!isset($this->routes[$name])) {
            throw new Exception('The route "' . $name . '" does not exist.');
        }
        
        return $this->routes[$name];
    }
    
    /**
     * Returns if a route was set.
     * 
     * @param string $name The name of the route to check for.
     * 
     * @return bool
     */
    public function hasRoute($name)
    {
        return isset($this->routes[$name]);
    }
    
    /**
     * Removes a route.
     * 
     * @param string $name The name of the route to remove.
     * 
     * @return bool
     */
    public function removeRoute($name)
    {
        if (isset($this->routes[$name])) {
            unset($this->routes[$name]);
        }
        return $this;
    }
    
    /**
     * Returns all routes that were set.
     * 
     * @return array
     */
    public function getRoutes()
    {
        return $this->routes;
    }
}
