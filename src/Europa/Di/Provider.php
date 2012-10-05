<?php

namespace Europa\Di;
use Europa\Filter\LowerCamelCaseFilter;
use Europa\Reflection\MethodReflector;
use LogicException;

/**
 * The application service locator and container.
 * 
 * @category DI
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  http://europaphp.org/license
 */
abstract class Provider extends ContainerAbstract
{
    /**
     * Creates a new instance specified by name.
     * 
     * @param string $name The service name.
     * @param array  $args  Any custom arguments to merge.
     * 
     * @return mixed
     */
    public function __call($name, array $args = [])
    {
        if (method_exists($this, $name)) {
            $method = new MethodReflector($this, $name);
            $args   = array_merge($this->resolveDependencies($method), $args);
            
            return $method->invokeArgs($this, $args);
        }

        throw new LogicException(sprintf('Could not resolve dependency "%s" in provider "%s".', $name, get_class($this)));
    }

    /**
     * Returns whether or not the specified service is available.
     * 
     * @param string $name The service name.
     * 
     * @return bool
     */
    public function __isset($name)
    {
        return method_exists($this, $name);
    }
    
    /**
     * Resolves the dependencies for the specified method.
     * 
     * @param MethodReflector $method The method to resolve the dependencies for.
     * 
     * @return array
     */
    private function resolveDependencies(MethodReflector $method)
    {
        $deps = [];
        
        // resolve each parameter
        foreach ($method->getParameters() as $param) {
            // we attempt to check the type
            $type = $param->getClass();
            
            // create a closure that returns the dependency
            $get = function() use ($method, $param) {
                return $this->__get($param->getName());
            };
            
            // If the parameter is type-hinted as a closure, then give them a closure that returns the dependency
            // otherwise just return the dependency. This allows the setup and execution of the depedency to be
            // deferred until the closure is called or just returned right away if the user doesn't need deferred
            // execution.
            $deps[] = $type && $type->getName() === 'Closure' ? $get : $get();
        }
        
        return $deps;
    }
}