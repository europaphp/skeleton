<?php

namespace Europa\Di;
use BadMethodCallException;
use Closure;
use Exception;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionFunction;
use RuntimeException;

/**
 * A container service that represents a given class. The class represented within is configurable by setting
 * constructor parameters and queuing methods to be called with parameters post-construction. If an instance of
 * Service is passed as a parameter to either the constructor or a method, the service is retrieved
 * before it is passed to the constructor or method. By enabling this, it allows us to preserve object configuration
 * overhead right up until the point it is required by another service.
 * 
 * @category ServiceInjection
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Service
{
    /**
     * The service class name.
     * 
     * @var string
     */
    private $class;
    
    /**
     * The constructor arguments.
     * 
     * @var array
     */
    private $config;
    
    /**
     * The service instance if it was already created.
     * 
     * @var mixed
     */
    private $instance;
    
    /**
     * Whether or not the service is transient.
     * 
     * @var bool
     */
    private $transient = false;
    
    /**
     * The methods to call and the arguments to pass to them.
     * 
     * @var array
     */
    private $queue = array();
    
    /**
     * Constructs a new service representing the specified class.
     * 
     * @param string $class  The name of the class to represent.
     * @param array  $config The constructor params, if any.
     * 
     * @return Service
     */
    public function __construct($class)
    {
        $this->class = $class;
    }
    
    /**
     * Marks the service as transient. This affects how ->get() behaves. If the service is set as transient, then
     * ->get() will always return a new instance. If not, then it will cache an instance until either transient is set
     * to true or until the service is set to refresh the instance.
     * 
     * @return Service
     */
    public function transient($switch = true)
    {
        $this->transient = $switch ? true : false;
        
        return $this;
    }
    
    /**
     * Configures the object. If the object is already exists, it is reset.
     * 
     * @return Service
     */
    public function config(Closure $callback)
    {
        $this->refresh();
        
        $this->config = $callback;
        
        return $this;
    }
    
    /**
     * Queues a callback to call after instantiation. 
     * 
     * @param Closure $callback The callback to queue.
     * 
     * @return Service
     */
    public function queue(Closure $callback)
    {
        $this->refresh();
        
        $this->queue[] = $callback;
        
        return $this;
    }
    
    /**
     * Queues an array of closures.
     * 
     * @param array $queue The queue to add.
     * 
     * @return Service
     */
    public function queueAll(array $queue)
    {
        foreach ($queue as $callback) {
            $this->queue($callback);
        }
        
        return $this;
    }
    
    /**
     * Clears the queue.
     * 
     * @return Service
     */
    public function clearQueue()
    {
        $this->refresh();
        
        $this->queue = [];
        
        return $this;
    }
    
    /**
     * Creates a new instance then returns it even if it is set as transient.
     * 
     * @return mixed
     */
    public function create(array $config = [])
    {
        return $this->invoke($config);
    }
    
    /**
     * Returns the represented service instance. If the service is transient, a new instance is always returned. If not,
     * an instance is stored and used for returning.
     * 
     * @return mixed
     */
    public function get()
    {
        if ($this->transient) {
            return $this->invoke();
        } elseif (!$this->instance) {
            $this->instance = $this->invoke();
        }
        
        return $this->instance;
    }
    
    /**
     * Returns whether or not the current service exists.
     * 
     * @return bool
     */
    public function exists()
    {
        return class_exists($this->class, true);
    }
    
    /**
     * Destroys the current instance so it can be recreated even if it is transient.
     * 
     * @return Service
     */
    public function refresh()
    {
        $this->instance = null;
        
        return $this;
    }
    
    /**
     * Returns whether or not the service is of the specified type.
     * 
     * @param mixed $class The class to check for.
     * 
     * @return bool
     */
    public function is($class)
    {
        $class = is_object($class) ? get_class($class) : $class;
        
        return is_subclass_of($this->class, $class) || $this->class === $class;
    }
    
    /**
     * Invokes the class and its queue.
     * 
     * @param array $config The constructor config if overriding the default.
     * 
     * @return mixed
     */
    private function invoke(array $config = [])
    {
        if (!$config && $this->config) {
            $config = $this->config;
            $config = $config();
            $config = is_array($config) ? $config : [$config];
        }
        
        return $this->invokeQueue($this->invokeClass($config));
    }
    
    /**
     * Invokes the represented service and returns it.
     * 
     * @return mixed
     */
    private function invokeClass(array $config)
    {
        try {
            $instance = new ReflectionClass($this->class);
            
            if ($instance->hasMethod('__construct') && $config) {
                $instance = $instance->newInstanceArgs($config);
            } else {
                $instance = $instance->newInstance();
            }
        } catch (Exception $e) {
            throw new RuntimeException(
                "Could not invoke service class {$this->class} because: {$e->getMessage()}",
                $e->getCode()
            );
        }
        
        return $instance;
    }
    
    /**
     * Invokes the represented dependencies queue.
     * 
     * @param mixed $instance The instance of service to call the queue on.
     * 
     * @return Service
     */
    private function invokeQueue($instance)
    {
        foreach ($this->queue as $callback) {
            $refl = new ReflectionFunction($callback);
            
            if ($refl->getNumberOfParameters()) {
                $type = $refl->getParameters()[0]->getClass();
                
                if ($type && !$this->is($type->getName())) {
                    continue;
                }
            }
            
            $callback($instance);
        }
        
        return $instance;
    }
}
