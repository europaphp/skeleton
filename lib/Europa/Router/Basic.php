<?php

namespace Europa\Router;
use Europa\Router\Route\RouteInterface;

/**
 * A basic router.
 * 
 * @category Router
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Basic implements \Iterator, \Countable
{
    /**
     * The route that is matched upon querying.
     * 
     * @var Route|null
     */
    protected $route = null;
    
    /**
     * The array of routes to match.
     * 
     * @var array
     */
    protected $routes = array();
    
    /**
     * Returns the specified route if it exists.
     * 
     * @param string $name The name of the route.
     * 
     * @return Route
     */
    public function __get($name)
    {
        return $this->getRoute($name);
    }
    
    /**
     * Shorthand for setting a named route.
     * 
     * @param string $name  The name of the route.
     * @param mixed  $route The route to set.
     * 
     * @return void
     */
    public function __set($name, $route)
    {
        $this->setRoute($name, $route);
    }
    
    /**
     * Performs route matching. The parameters are returned if matched.
     * 
     * @param string $subject The subject to match.
     * 
     * @return bool|false
     */
    public function query($subject)
    {
        foreach ($this as $route) {
            $params = $route->query($subject);
            if ($params !== false) {
                $this->route = $route;
                return $params;
            }
        }
        return false;
    }

    /**
     * Sets a route. If the first argument is an instance of Route, then the
     * route is set for that. Otherwise, it's set as a route to match against.
     * 
     * If the $route parameter is null, then that route is removed if it exists.
     * 
     * @param string        $name  The name of the route.
     * @param Route $route The route to use.
     * 
     * @return Router
     */
    public function setRoute($name, RouteInterface $route = null)
    {
        if ($name instanceof RouteInterface) {
            $this->route = $name;
        } elseif ($route) {
            $this->routes[$name] = $route;
        } elseif (isset($this->routes[$name])) {
            unset($this->routes[$name]);
        }
        return $this;
    }

    /**
     * Gets a specified route.
     * 
     * @param string $name The name of the route to get.
     * 
     * @return Route
     */
    public function getRoute($name = null)
    {
        // if no name is specified, return the matched route
        if (!$name) {
            return $this->route;
        }
        
        // if a name is specified, but doesn't exist, throw an exception
        if (!isset($this->routes[$name])) {
            throw new Exception('The route "' . $name . '" does not exist.');
        }
        
        // return the specified route
        return $this->routes[$name];
    }
    
    /**
     * Clears the route that was matched by the query.
     * 
     * @return Router
     */
    public function clear()
    {
        $this->route = null;
        return $this;
    }
    
    /**
     * Returns the number of routes bound to the router.
     * 
     * @return int
     */
    public function count()
    {
        return count($this->routes);
    }
    
    /**
     * Returns the current route in the iteration.
     * 
     * @return \Europa\Route
     */
    public function current()
    {
        return current($this->routes);
    }
    
    /**
     * Returns the name/index of the current route.
     * 
     * @return mixed
     */
    public function key()
    {
        return key($this->routes);
    }
    
    /**
     * Moves to the next route.
     * 
     * @return void
     */
    public function next()
    {
        next($this->routes);
    }
    
    /**
     * Resets to the first route.
     * 
     * @return void
     */
    public function rewind()
    {
        reset($this->routes);
    }
    
    /**
     * Returns whether or not there is another route.
     * 
     * @return bool
     */
    public function valid()
    {
        return (bool) $this->current();
    }
}