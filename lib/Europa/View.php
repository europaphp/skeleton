<?php

/**
 * A base class for views in Europa.
 * 
 * @category Views
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2010 Trey Shugart http://europaphp.org/license
 */
namespace Europa
{
    abstract class View implements \ArrayAccess, \Iterator, \Countable
    {
        /**
         * The parameters and helpers bound to the view.
         * 
         * @var array
         */
        protected $params = array();
        
        /**
         * Renders the view in whatever way necessary.
         * 
         * @return string
         */
        abstract public function __toString();
        
        /**
         * Similar to calling a helper via Europa_View->__call(), but treats the
         * helper as a singleton and once instantiated, that instance is always
         * returned for the duration of the Europa_View object's lifespan unless
         * unset.
         * 
         * @param string $name The name of the property to get or helper to load.
         * 
         * @return mixed
         */
        public function __get($name)
        {
            if (isset($this->params[$name])) {
                return $this->params[$name];
            }
            return null;
        }
        
        /**
         * Sets a parameter.
         * 
         * @param string $name  The parameter to set.
         * @param mixed  $value The value to set.
         * 
         * @return bool
         */
        public function __set($name, $value)
        {
            $this->params[$name] = $value;
        }
        
        /**
         * Returns whether a parameter is set or not.
         * 
         * @param string $name The parameter to check.
         * 
         * @return bool
         */
        public function __isset($name)
        {
            return array_key_exists($name, $this->params);
        }
        
        /**
         * Unsets a parameter
         * 
         * @param string $name The parameter to unset.
         * 
         * @return void
         */
        public function __unset($name)
        {
            unset($this->params[$name]);
        }
        
        /**
         * Applies a group of parameters to the view.
         * 
         * @param mixed $params The params to set. Can be any iterable value.
         * 
         * @return Europa_View
         */
        public function setParams($params)
        {
            if (is_array($params) || is_object($params)) {
                foreach ($params as $name => $value) {
                    $this->$name = $value;
                }
            }
            return $this;
        }
        
        /**
         * Returns the parameters bound to the view.
         * 
         * In most cases, this is will only be used when determining which
         * properties are public internally or when serializing view objects
         * externally.
         * 
         * @return array
         */
        public function getParams()
        {
            return $this->params;
        }
        
        /**
         * Returns whether or not the passed parameters exist.
         * 
         * @param array $params The parameters to check for.
         * 
         * @return bool
         */
        public function hasParams(array $params)
        {
            foreach ($params as $name) {
                if (!$this->__isset($name)) {
                    return false;
                }
            }
            return true;
        }
        
        /**
         * Removes all parameters.
         * 
         * @return Europa_View
         */
        public function removeParams()
        {
            $this->params = array();
            return $this;
        }
        
        /**
         * Sets the specified parameter.
         * 
         * @param string $name  The parameter name.
         * @param mixed  $value The parameter value.
         * 
         * @return Europa_View
         */
        public function offsetSet($name, $value)
        {
            $this->__set($name, $value);
            return $this;
        }
        
        /**
         * Returns the specified parameter.
         * 
         * @param string $name The parameter name.
         * 
         * @return mixed
         */
        public function offsetGet($name)
        {
            return $this->__get($name);
        }
        
        /**
         * Returns whether or not the specified parameter exists.
         * 
         * @param string $name The parameter name.
         * 
         * @return bool
         */
        public function offsetExists($name)
        {
            return $this->__isset($name);
        }
        
        /**
         * Unsets the specified parameter.
         * 
         * @param string $name The parameter name.
         * 
         * @return Europa_View
         */
        public function offsetUnset($name)
        {
            $this->__unset($name);
            return $this;
        }
        
        /**
         * Returns the current parameter.
         * 
         * @return mixed
         */
        public function current()
        {
            return current($this->params);
        }
        
        /**
         * Returns the current parameter name.
         * 
         * @return string
         */
        public function key()
        {
            return key($this->params);
        }
        
        /**
         * Moves to the next parameter.
         * 
         * @return Europa_View
         */
        public function next()
        {
            next($this->params);
            return $this;
        }
        
        /**
         * Resets iteration.
         * 
         * @return Europa_View
         */
        public function rewind()
        {
            reset($this->params);
            return $this;
        }
        
        /**
         * Returns whether or not iteration is still valid.
         * 
         * @return bool
         */
        public function valid()
        {
            return isset($this->{$this->key()});
        }
        
        /**
         * Returns the number of parameters.
         * 
         * @return int
         */
        public function count()
        {
            return count($this->params);
        }
    }
}