<?php

namespace Europa\Bootstrapper;
use Europa\Config\Config;
use Europa\Reflection\ClassReflector;
use Europa\Reflection\MethodReflector;

/**
 * Abstraction for bootstrap classes containing bootstrapping methods.
 * 
 * @category Boot
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
abstract class BootstrapperAbstract
{
    /**
     * Runs each bootstrap method.
     * 
     * @param mixed $conf Any configuration to pass to each method.
     * 
     * @return Provider
     */
    public function __invoke($conf = [])
    {
        $that = new ClassReflector($this);
        $conf = new Config($conf);
        
        foreach ($that->getMethods() as $method) {
            if ($this->isValidMethod($method)) {
                $method->invoke($this, $conf);
            }
        }
        
        return $this;
    }
    
    /**
     * Returns whether or not the specified method is valid.
     * 
     * @param MethodReflector $method The method to check.
     * 
     * @return bool
     */
    private function isValidMethod(MethodReflector $method)
    {
        if ($method->isMagic()) {
            return false;
        }
        
        if (!$method->isPublic()) {
            return false;
        }
        
        return true;
    }
}