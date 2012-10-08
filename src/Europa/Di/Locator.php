<?php

namespace Europa\Di;
use Closure;
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
class Locator extends Container
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
    private $call = [];
    
    /**
     * Sets up a new locator.
     * 
     * @return Locator
     */
    public function __construct()
    {
        $this->filter = new ClassResolutionFilter;
    }

    public function __get($name)
    {
        if (parent::__isset($name)) {
            return parent::__get($name);
        }

        return $this->createInstance($this->filter->__invoke($name));
    }

    /**
     * Sets the filter to use for dependency class name resolution.
     * 
     * @param FilterInterface $filter The filter.
     * 
     * @return Finder
     */
    public function setFilter($filter)
    {
        if (!is_callable($filter)) {
            throw new UnexpectedValueException('The provided filter is not callable.');
        }

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
     * @param string  $type The class type.
     * @param Closure $fn   The closure to run against the instance.
     * 
     * @return Locator
     */
    public function args($type, Closure $fn = null)
    {
        if ($type instanceof Closure) {
            $fn   = $type;
            $type = self::ALL;
        }
        
        $this->args[$type] = $fn;
        
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
    public function call($type, Closure $fn = null)
    {
        if ($type instanceof Closure) {
            $fn   = $type;
            $type = self::ALL;
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
            var_dump($type);
            if ($type === self::ALL || $class->is($type)) {
                $temp = $fn();
                $temp = is_array($temp) ? $temp : [$temp];
                $args = array_merge($args, $temp);
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
        
        foreach ($this->call as $item) {
            if ($item['type'] === self::ALL || $refl->is($item['type'])) {
                $item['func']($class);
            }
        }
        
        return $class;
    }
}