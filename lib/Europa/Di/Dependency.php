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
     * Sets a method to be called and the arguments for that method.
     * 
     * @param string $method The method to call.
     * @param array  $args   The arguments to pass to the method.
     * 
     * @return \Europa\Di\Dependency
     */
    public function __call($method, array $args = array())
    {
        return $this->queue($method, $args);
    }
    
    /**
     * Configures the object. If the object is already exists, it is reset.
     * 
     * @param array $args The arguments to re-configure the instance with.
     * 
     * @return \Europa\Di\Dependency
     */
    public function configure(array $args)
    {
        $this->reset();
        $this->args = $args;
        return $this;
    }
    
    /**
     * Queues a method to be called after instantiation. If the object already exists, it is reset.
     * 
     * @param string $method The method to queue.
     * @param array  $args   The arguments to pass to the method.
     * 
     * @return \Europa\Di\Dependency
     */
    public function queue($method, array $args = array())
    {
        $this->reset();
        $this->queue[] = array($method, $args);
        return $this;
    }
    
    /**
     * Sets an instance and makes sure that it is an instance that the dependency represents.
     * 
     * @param mixed $instance The instance to set.
     * 
     * @return \Europa\Di\Dependency
     */
    public function set($instance)
    {
        if (!is_object($instance)) {
            $type = gettype($instance);
            throw new Exception("Only object instances may be registered. Type {$type} given.");
        }
        
        if (!$instance instanceof $this->class) {
            $class = get_class($instance);
            throw new Exception("The instance must be an instance of {$this->class}. Instance of {$class} given.");
        }
        
        $this->instance = $instance;
        return $this;
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
            $this->instance = $this->invokeClass();
            $this->invokeQueue($this->instance);
        }
        return $this->instance;
    }
    
    /**
     * Creates a new instance then returns it. Does not cache the instance.
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
     * Destroys the current instance so it can be reconfigured the next time it is accessed.
     * 
     * @return \Europa\Di\Dependency
     */
    public function reset()
    {
        $this->instance = null;
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
            $this->filterArgsForDependencies($args);
            if (method_exists($instance, $method)) {
                call_user_func_array(array($instance, $method), $args);
            } elseif (method_exists($instance, '__call')) {
                call_user_func(array($instance, '__call'), $method, $args);
            } else {
                throw new Exception("Method {$method} or __call does not exist for {$this->class}.");
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