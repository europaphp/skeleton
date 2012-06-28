<?php

namespace Europa\Di;
use Europa\Filter\LowerCamelCaseFilter;
use Europa\Reflection\MethodReflector;
use Exception;
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
     * Configures the specified instance.
     * 
     * @param string $class The class to configure.
     * @param array  $args  Any arguments to merge with the configuration.
     * 
     * @return mixed
     */
    public function create($name, array $args = [])
    {
        if ($method = $this->resolveMethod($name)) {
            $args = array_merge($this->resolveMethodDependencies($method), $args);
            return $method->invokeArgs($this, $args);
        }
            $this->throwNotExists($name);
    }
    
    /**
     * Resolves the mapping for the specified service.
     * 
     * @param string $name The service name.
     * 
     * @return string
     */
    private function resolveMethod($name)
    {
        $name = (new LowerCamelCaseFilter)->filter($name);
        if (method_exists($this, $name)) {
            return new MethodReflector($this, $name);
        }
        return false;
    }
    
    /**
     * Resolves the dependencies for the specified method.
     * 
     * @param MethodReflector $method The method to resolve the dependencies for.
     * 
     * @return array
     */
    private function resolveMethodDependencies(MethodReflector $method)
    {
        $deps = [];
        
        // resolve each parameter
        foreach ($method->getParameters() as $param) {
            // we attempt to check the type
            $type = $param->getClass();
            
            // create a closure that returns the dependency
            $get = function() use ($method, $param) {
                return $this->get($param->getName());
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