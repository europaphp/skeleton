<?php

namespace Europa\Di;
use Closure;
use Europa\Config\Config;
use Europa\Filter\ClassResolutionFilter;
use Europa\Reflection\ClassReflector;
use LogicException;
use UnexpectedValueException;

/**
 * The application service locator and container.
 * 
 * @category DI
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  http://europaphp.org/license
 */
class ServiceLocator extends Container
{
    /**
     * A token that represents any type of object.
     * 
     * @var string
     */
    const ALL = '*';
    
    /**
     * Configuration.
     * 
     * @var array
     */
    private $args = [];
    
    /**
     * Queue.
     * 
     * @var array
     */
    private $call = [];

    /**
     * The class resolution filter for resolving dependencies.
     * 
     * @var ClassResolutionFilter
     */
    private $filter;

    /**
     * Default configuration.
     * 
     * @var array | Config
     */
    private $config = [
        'filters' => [],
        'args'    => [],
        'calls'   => []
    ];
    
    /**
     * Sets up a new locator.
     * 
     * @return ServiceLocator
     */
    public function __construct($config = [])
    {
        $this->config = new Config($this->config, $config);
        $this->filter = new ClassResolutionFilter($this->config->filters);

        foreach ($this->config->args as $type => $args) {
            $this->args($type, $args);
        }

        foreach ($this->config->calls as $type => $call) {
            $this->call($type, $call);
        }
    }

    /**
     * Returns the specified service.
     * 
     * @param string $name The service name.
     * 
     * @return mixed
     */
    public function __get($name)
    {
        if (parent::__isset($name)) {
            return parent::__get($name);
        }

        if ($this->__isset($name)) {
            $filter  = $this->filter;
            $service = $filter($name);
            $service = $this->createInstance($service);

            if (!$this->isTransient($name)) {
                parent::__set($name, $service);
            }

            return $service;
        }

        $this->throwNotExists($name);
    }

    /**
     * Returns whether or not the specified service exists.
     * 
     * @return bool
     */
    public function __isset($name)
    {
        return class_exists($this->filter->__invoke($name)) || parent::__isset($name);
    }

    /**
     * Sets the filter to use for dependency class name resolution.
     * 
     * @param callable $filter The filter.
     * 
     * @return ServiceLocator
     */
    public function setFilter(callable $filter)
    {
        $this->filter = $filter;
        return $this;
    }
    
    /**
     * Returns the filter.
     * 
     * @return FilterInterface
     */
    public function getFilter()
    {
        return $this->filter;
    }
    
    /**
     * Sets the configuration for a type of instance.
     * 
     * @param string $type The class type.
     * @param mixed  $fn   The closure to run against the instance.
     * 
     * @return ServiceLocator
     */
    public function args($type, $fn = null)
    {
        if (is_callable($type)) {
            $fn   = $type;
            $type = self::ALL;
        }

        if (!is_callable($fn)) {
            $fn = function() use ($fn) {
                return $fn;
            };
        }
        
        $this->args[$type] = $fn;
        
        return $this;
    }
    
    /**
     * Queues a method for a type of instance.
     * 
     * @param string $type The class type.
     * @param mixed  $fn   The closure to run against the instance.
     * 
     * @return ServiceLocator
     */
    public function call($type, $fn = null, array $args = [])
    {
        if (is_callable($type) {
            $fn   = $type;
            $type = self::ALL;
        }

        if (!is_callable($fn)) {
            $fn = function($service) use ($fn) {
                call_user_func_array([$service, $fn], $args);
            };
        }
        
        $this->call[] = [
            'func' => $fn,
            'type' => $type
        ];
        
        return $this;
    }
    
    /**
     * Creates a new instance.
     * 
     * @param string $class The class instance to create.
     * 
     * @return mixed
     */
    private function createInstance($class)
    {
        $class = new ClassReflector($class);
        $args  = [];
        
        foreach ($this->args as $type => $fn) {
            if ($type === self::ALL || $class->is($type)) {
                $temp = $fn($this);
                $temp = is_array($temp) ? $temp : [$temp];
                $args = array_merge($args, $temp);
            }
        }
        
        return $this->invokeQueue($class->newInstanceArgs($args));
    }
    
    /**
     * Invokes the queue for the specified class.
     * 
     * @param mixed $class The class instance to invoke the queue on.
     * 
     * @return mixed
     */
    private function invokeQueue($class)
    {
        $refl = new ClassReflector($class);
        
        foreach ($this->call as $item) {
            if ($item['type'] === self::ALL || $refl->is($item['type'])) {
                $item['func']($this, $class);
            }
        }
        
        return $class;
    }
}