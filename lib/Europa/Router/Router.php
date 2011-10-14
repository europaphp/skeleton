<?php

namespace Europa\Router;
use Europa\Request\RequestInterface;
use Europa\Router\Resolver\ResolverInterface;

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
     * The subject to match.
     * 
     * @var string
     */
    private $subject;
    
    /**
     * The array of routes to match.
     * 
     * @var array
     */
    private $routes = array();
    
    /**
     * Sets the subject.
     * 
     * @param string $subject The subject to match.
     * 
     * @return RequestRouter
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }
    
    /**
     * Returns the subject.
     * 
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }
    
    /**
     * Adds a resolver to the router.
     * 
     * @param \Europa\Router\Resolver\ResolverInterface $resolver The resolver to add.
     * 
     * @return \Europa\Router\RequestRouter
     */
    public function addResolver(ResolverInterface $resolver)
    {
        $this->resolvers[] = $resolver;
        return $this;
    }
    
    /**
     * Routes the specified request. If a subject is specified it is used instead of the default Europa request URI.
     * 
     * @param \Europa\Request\RequestInterface $request The request to route.
     * 
     * @return bool
     */
    public function route(RequestInterface $request)
    {
        // figure out the subject to match
        $subject = $this->subject;
        
        // by default if no subject is specified, it uses the default string representation of the request
        if (!$subject) {
            $subject = $request->__toString();
        }
        
        // query the router and apply parameters on successful result
        $params = $this->query();
        if ($params !== false) {
            $request->setParams($params);
        }
        
        return false;
    }
    
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
