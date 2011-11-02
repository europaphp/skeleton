<?php

namespace Europa\Di;

/**
 * When invoked by a child class, it executes each method in the order they were defined.
 *
 * @category Application
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
abstract class ConfigurationAbstract implements ConfigurationInterface
{
    /**
     * Iterates through each method in the extending class and calls them in the order in which they were defined.
     * 
     * @return void
     */
    final public function configure(Container $container)
    {
        $class = new \ReflectionClass($this);
        foreach ($class->getMethods() as $method) {
            if ($this->isValidMethod($method)) {
                $method->invoke($this, $container);
            }
        }
    }
    
    /**
     * Returns whether or not the method is a valid bootstrap method.
     * 
     * @param \ReflectionMethod $method The method to check.
     * 
     * @return bool
     */
    private function isValidMethod(\ReflectionMethod $method)
    {
        if ($method->isConstructor()) {
            return false;
        }
        
        if (!$method->isPublic()) {
            return false;
        }
        
        if ($method->class !== get_class($this)) {
            return false;
        }
        
        if (strpos($method->getName(), '__') === 0) {
            return false;
        }
        
        return true;
    }
}
