<?php

/**
 * A basic router.
 * 
 * @category Router
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2010 Trey Shugart http://europaphp.org/license
 */
namespace Europa
{
    class Router implements \Iterator, \ArrayAccess, \Countable
    {
        /**
         * The route that is matched upon querying.
         * 
         * @var \Europa\Route|null
         */
        protected $route = null;
        
        /**
         * The array of routes to match.
         * 
         * @var array
         */
        protected $routes = array();
        
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
         * Sets a route.
         * 
         * @param string        $name  The name of the route.
         * @param \Europa\Route $route The route to use.
         * 
         * @return \Europa\Router
         */
        public function setRoute($name, \Europa\Route $route)
        {
            $this->routes[$name] = $route;
            return $this;
        }

        /**
         * Gets a specified route.
         * 
         * @param string $name The name of the route to get.
         * 
         * @return \Europa\Route|null
         */
        public function getRoute($name = null)
        {
            // if a name isn't specified, return the matched route
            if (!$name) {
                return $this->route;
            }
            
            // if a name is specified, return that route
            if (isset($this->routes[$name])) {
                return $this->routes[$name];
            }
            
            // by default return nothing
            return null;
        }
        
        /**
         * Clears the route that was matched by the query.
         * 
         * @return \Europa\Router
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
        
        /**
         * Returns the specified route.
         * 
         * @param mixed $offset The route to get.
         * 
         * @return \Europa\Route|null
         */
        public function offsetGet($offset)
        {
            return $this->getRoute($offset);
        }
        
        /**
         * Sets the specified route.
         * 
         * @param mixed         $offset The name of the route.
         * @param \Europa\Route $route  The route to set.
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
            return isset($this->routes);
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
            if (isset($this->routes[$offset])) {
                unset($this->routes[$offset]);
            }
        }
    }
}