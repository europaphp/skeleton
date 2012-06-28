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
    }
    
    /**
     * Adds a filter to use for class resolution.
     * 
     * @param FilterInterface $filter The filter to add.
     * 
     * @return Locator
     */
    public function addFilter(FilterInterface $filter)
    {
        $this->filter->add($filter);
        return $this;
    }
    
    /**
     * Creates a new instance specified by name.
     * 
     * @param string $name The service name.
     * @param array  $args The arguments to pass, if any.
     * 
     * @return mixed
     */
    public function create($name, array $args = [])
    {
        if ($class = $this->filter->filter($name)) {
            return $this->invokeQueue($this->createInstance($class, $args));
        }
        $this->throwNotExists($name);
    }
    
    /**
     * Sets the configuration for a type of instance.
     * 
     * @param Closure $fn The closure to run against the instance.
     * 
     * @return Locator
     */
    public function config($type, Closure $fn)
    {
        $this->config[$type] = $fn;
        return $this;
    }
    
    /**
     * Queues a method for a type of instance.
     * 
     * @param Closure $fn The closure to run against the instance.
     * 
     * @return Locator
     */
    public function queue(Closure $fn)
    {
        $func = new ReflectionFunction($fn);
        $args = $func->getParameters();
        
        if (!$args) {
            throw new LogicException(
                'The queue function must hint at the type of instance it is configuring as its first parameter.'
            );
        }
        
        $this->queue[] = [
            'func' => $func,
            'type' => $args[0]->getClass()->getName()
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
            if ($class->is($type)) {
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
        foreach ($this->queue as $item) {
            if ($class instanceof $item['type']) {
                $item['func']->invoke($class);
            }
        }
        return $class;
    }
}