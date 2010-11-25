<?php

/**
 * A basic router.
 * 
 * @category Router
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart http://europaphp.org/license
 */
class Europa_Router implements Iterator, ArrayAccess, Countable
{
    /**
     * The route that is matched upon querying.
     * 
     * @var Europa_Route|null
     */
    private $_route = null;
    
    /**
     * The array of routes to match.
     * 
     * @var array
     */
    protected $_routes = array();
    
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
                $this->_route = $route;
                return $params;
            }
        }
        return false;
    }

    /**
     * Sets a route.
     * 
     * @param string       $name  The name of the route.
     * @param Europa_Route $route The route to use.
     * 
     * @return Europa_Router
     */
    public function setRoute($name, Europa_Route $route)
    {
        $this->_routes[$name] = $route;
        return $this;
    }

    /**
     * Gets a specified route.
     * 
     * @param string $name The name of the route to get.
     * 
     * @return Europa_Route|null
     */
    public function getRoute($name = null)
    {
        // if a name isn't specified, return the matched route
        if (!$name) {
            return $this->_route;
        }
        
        // if a name is specified, return that route
        if (isset($this->_routes[$name])) {
            return $this->_routes[$name];
        }
        
        // by default return nothing
        return null;
    }
    
    /**
     * Clears the route that was matched by the query.
     * 
     * @return Europa_Router
     */
    public function clear()
    {
        $this->_route = null;
        return $this;
    }
    
    /**
     * Returns the number of routes bound to the router.
     * 
     * @return int
     */
    public function count()
    {
        return count($this->_routes);
    }
    
    /**
     * Returns the current route in the iteration.
     * 
     * @return Europa_Route
     */
    public function current()
    {
        return current($this->_routes);
    }
    
    /**
     * Returns the name/index of the current route.
     * 
     * @return mixed
     */
    public function key()
    {
        return key($this->_routes);
    }
    
    /**
     * Moves to the next route.
     * 
     * @return void
     */
    public function next()
    {
        next($this->_routes);
    }
    
    /**
     * Resets to the first route.
     * 
     * @return void
     */
    public function rewind()
    {
        reset($this->_routes);
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
    
    /**
     * Returns the specified route.
     * 
     * @param mixed $offset The route to get.
     * 
     * @return Europa_Route|null
     */
    public function offsetGet($offset)
    {
        return $this->getRoute($offset);
    }
    
    /**
     * Sets the specified route.
     * 
     * @param mixed        $offset The name of the route.
     * @param Europa_Route $route  The route to set.
     * 
     * @return void
     */
    public function offsetSet($offset, $route)
    {
        $this->setRoute($offset, $route);
    }
    
    /**
     * Checks to see if a route exists.
     * 
     * @param mixed $offset The route to check for.
     * 
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->_routes);
    }
    
    /**
     * Removes a route.
     * 
     * @param mixed $offset The route to remove.
     * 
     * @return void
     */
    public function offsetUnset($offset)
    {
        if (isset($this->_routes[$offset])) {
            unset($this->_routes[$offset]);
        }
    }
}