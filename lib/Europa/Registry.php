<?php

/**
 * Sets variables to be stored in a common place and accessible from anywhere. 
 * Variable name-spacing can be done any way you want, but it is recommended 
 * to follow a strict convention when doing so.
 * 
 * @category Registry
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
namespace Europa
{
    class Registry
    {
        /**
         * Holds all registry variables.
         * 
         * @var array
         */
        protected static $registry = array();
        
        /**
         * Retrieves a variable from the registry.
         * 
         * @param string $key The registry variable to retrieve.
         * 
         * @return mixed
         */
        public static function get($key)
        {
            if (isset(self::$registry[$key])) {
                return self::$registry[$key];
            }
            return null;
        }
        
        /**
         * Sets a variable named $key with the value of $val inside of $namespace.
         * 
         * @param $key string The name of the registry variable.
         * @param $val mixed  The value of the registry variable.
         * 
         * @return mixed
         */
        public static function set($key, $val)
        {
            self::$registry[$key] = $val;
            return $val;
        }
        
        /**
         * Removes the variable named $key from $namespace returning the value
         * of the variable that was removed.
         * 
         * @param string $key The name of the registry variable to remove.
         * 
         * @return mixed
         */
        public static function remove($key)
        {
            if (isset(self::$registry[$key])) {
                $val = self::$registry[$key];
                unset(self::$registry[$key]);
                return $val;
            }
            return null;
        }
    }
}