<?php

namespace Europa\Di\Configuration;
use Europa\Di\Container;
use ReflectionClass;
use ReflectionMethod;

/**
 * Default abstraction for configurations.
 * 
 * @category Configurations
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
abstract class ConfigurationAbstract implements ConfigurationInterface
{
    /**
     * Runs each configuration method.
     * 
     * @param Container $container The container to configure.
     * 
     * @return ConfigurationAbstract
     */
    public function configure(Container $container)
    {
        $that = new ReflectionClass($this);
        foreach ($that->getMethods() as $method) {
            if ($this->isValidMethod($method)) {
                $method->invokeArgs($this, array($container));
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
        
        if ($method->getName() === 'configure') {
            return false;
        }
        
        if (!$method->isPublic()) {
            return false;
        }
        
        return true;
    }
}