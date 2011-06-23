<?php

namespace Europa\Di;

/**
 * A container dependency that represents a given class. The class represented within is configurable by setting
 * constructor parameters and queuing methods to be called with parameters post-construction. If an instance of
 * \Europa\Di\Dependency is passed as a parameter to either the constructor or a method, the dependency is retrieved
 * before it is passed to the constructor or method. By enabling this, it allows us to preserve object configuration
 * overhead right up until the point it is required by another dependency.
 * 
 * @category DependencyInjection
 * @package  Europa\Di
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Dependency
{
    /**
     * The constructor arguments.
     * 
     * @var array
     */
    private $args = array();
    
    /**
     * The dependency class name.
     * 
     * @var string
     */
    private $class;
    
    /**
     * The dependency instance if it was already created.
     * 
     * @var mixed
     */
    private $instance;
    
    /**
     * The methods to call and the arguments to pass to them.
     * 
     * @var array
     */
    private $queue = array();
    
    /**
     * Constructs a new dependency representing the specified class.
     * 
     * @param string $class The name of the class to represent.
     * 
     * @return \Europa\Di\Dependency
     */
    public function __construct($class)
    {
        $this->class = $class;
    }
    
    /**
     * Returns the represented dependency instance. If it is has not been created yet, it is created then cached and
     * returned.
     * 
     * @return mixed
     */
    public function get()
    {
        if (!$this->instance) {
            $this->instance = $this->create();
        }
        return $this->instance;
    }
    
    /**
     * Creates a new instance then returns it.
     * 
     * @param array $args Any last minute constructor arguments.
     * 
     * @return mixed
     */
    public function create(array $args = array())
    {
        $instance = $this->invokeClass($args);
        $this->invokeQueue($instance);
        return $instance;
    }
    
    /**
     * Sets constructor arguments. If this is called after creation, it does not take effect until the next creation.
     * 
     * @param array $args The arguments to pass to the constructor.
     * 
     * @return \Europa\Di\Dependency
     */
    public function configure(array $args)
    {
        $this->args = $args;
        return $this;
    }
    
    /**
     * Sets a method to be called and the arguments for that method.
     * 
     * @param string $method The method to call.
     * @param array  $args   The arguments to pass to the method.
     * 
     * @return \Europa\Di\Dependency
     */
    public function queue($method, array $args = array())
    {
        $this->queue[] = array($method, $args);
        return $this;
    }
    
    /**
     * Invokes the represented dependency and returns it.
     * 
     * @param array $args Any last minute constructor arguments.
     * 
     * @return mixed
     */
    private function invokeClass(array $args = array())
    {
        try {
            $args     = array_merge_recursive($this->args, $args);
            $instance = new \ReflectionClass($this->class);
            if ($instance->hasMethod('__construct') && $args) {
                $args = $this->args;
                $this->filterArgsForDependencies($args);
                
                $instance = $instance->newInstanceArgs($args);
            } else {
                $instance = $instance->newInstance();
            }
        } catch (\Exception $e) {
            throw new Exception(
                "Could not invoke dependency class {$this->class} with message: {$e->getMessage()}.",
                $e->getCode()
            );
        }
        return $instance;
    }
    
    /**
     * Invokes the represented dependencies queue.
     * 
     * @param mixed $instance The instance of dependency to call the queue on.
     * 
     * @return \Europa\Di\Dependency
     */
    private function invokeQueue($instance)
    {
        foreach ($this->queue as $queue) {
            $method = $queue[0];
            $args   = $queue[1];
            if ($args) {
                $this->filterArgsForDependencies($args);
                try {
                    $reflect = new \ReflectionMethod($this->class, $method);
                    $reflect->invokeArgs($instance, $args);
                } catch (\Exception $e) {
                    throw new Exception(
                        "Could not call queued method {$method} for {$this->class} with message: {$e->getMessage()}.",
                        $e->getCode()
                    );
                }
            } else {
                $instance->$method();
            }
        }
        return $this;
    }
    
    /**
     * Filters arguments for a constructor or method and makes sure any top-level dependencies that were passed in
     * are converted into the dependency instance that they represent.
     * 
     * @param array &$args The arguments to filter.
     * 
     * @return array
     */
    private function filterArgsForDependencies(array &$args)
    {
        foreach ($args as &$arg) {
            if ($arg instanceof Dependency) {
                $arg = $arg->get();
            }
        }
        return $this;
    }
}