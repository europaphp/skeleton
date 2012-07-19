<?php

namespace Europa\Di;
use Closure;
use Europa\Filter\ClassResolutionFilter;
use Europa\Filter\FilterInterface;
use Europa\Reflection\ClassReflector;
use ReflectionFunction;

/**
 * The application service locator and container.
 * 
 * @category DI
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  http://europaphp.org/license
 */
class Finder extends ContainerAbstract
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
    private $config = [];
    
    /**
     * The class resolution filter for resolving dependencies.
     * 
     * @var ClassResolutionFilter
     */
    private $filter;
    
    /**
     * Queue.
     * 
     * @var array
     */
    private $queue = [];
    
    /**
     * Sets up a new locator.
     * 
     * @return Locator
     */
    public function __construct()
    {
        $this->filter = new ClassResolutionFilter;
        $this->init();
    }
    
    /**
     * Initialisation hook for any further setup.
     * 
     * @return void
     */
    public function init()
    {
        
    }
    
    /**
     * Sets the filter to use for dependency class name resolution.
     * 
     * @param FilterInterface $filter The filter.
     * 
     * @return Finder
     */
    public function setFilter(FilterInterface $filter)
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
     * Creates a new instance specified by name.
     * 
     * @param string $name The service name.
     * @param array  $args The arguments to pass, if any.
     * 
     * @return mixed
     */
    public function __call($name, array $args = [])
    {
        if ($class = $this->filter->filter($name)) {
            return $this->invokeQueue($this->createInstance($class, $args));
        }
        $this->throwNotExists($name);
    }
    
    /**
     * Sets the configuration for a type of instance.
     * 
     * @param string  $type The class type.
     * @param Closure $fn   The closure to run against the instance.
     * 
     * @return Locator
     */
    public function config($type, Closure $fn = null)
    {
        if ($type instanceof Closure) {
            $fn   = $type;
            $type = self::ALL;
        }
        
        $this->config[$type] = $fn;
        
        return $this;
    }
    
    /**
     * Queues a method for a type of instance.
     * 
     * @param string  $type The class type.
     * @param Closure $fn   The closure to run against the instance.
     * 
     * @return Locator
     */
    public function queue($type, Closure $fn = null)
    {
        if ($type instanceof Closure) {
            $fn   = $type;
            $type = self::ALL;
        }
        
        $this->queue[] = [
            'func' => $fn,
            'type' => $type
        ];
        
        return $this;
    }
    
    /**
     * Creates a new instance.
     * 
     * @param string $class The class instance to create.
     * @param array  $args  The arguments to merge with config args.
     * 
     * @return mixed
     */
    private function createInstance($class, array $args)
    {
        $class = new ClassReflector($class);
        
        foreach ($this->config as $type => $fn) {
            if ($type === self::ALL || $class->is($type)) {
                $args = array_merge($args, (array) $fn());
            }
        }
        
        return $class->newInstanceArgs($args);
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
        
        foreach ($this->queue as $item) {
            if ($item['type'] === self::ALL || $refl->is($item['type'])) {
                $item['func']($class);
            }
        }
        
        return $class;
    }
}