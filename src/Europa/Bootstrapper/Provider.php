<?php

namespace Europa\Bootstrapper;
use ReflectionClass;
use ReflectionMethod;

/**
 * Abstraction for bootstrap classes containing bootstrapping methods.
 * 
 * @category Boot
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
abstract class Provider implements BootstrapperInterface
{
    /**
     * Runs each bootstrap method.
     * 
     * @return Provider
     */
    public function bootstrap()
    {
        $that = new ReflectionClass($this);
        
        foreach ($that->getMethods() as $method) {
            if ($this->isValidMethod($method)) {
                $method->invoke($this);
            }
        }
        
        return $this;
    }
    
    /**
     * Returns whether or not the specified method is valid.
     * 
     * @param ReflectionMethod $method The method to check.
     * 
     * @return bool
     */
    private function isValidMethod(ReflectionMethod $method)
    {
        if ($method->isConstructor()) {
            return false;
        }
        
        if ($method->getName() === 'bootstrap') {
            return false;
        }
        
        if (!$method->isPublic()) {
            return false;
        }
        
        return true;
    }
}