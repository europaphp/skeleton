<?php

namespace Europa\Di;
use LogicException;

/**
 * The application service locator and container.
 * 
 * @category DI
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  http://europaphp.org/license
 */
abstract class ContainerAbstract implements ContainerInterface
{
    /**
     * Default instance name.
     * 
     * @var string
     */
    const NAME = 'default';
    
    /**
     * Non-transient services that have already been located and set up.
     * 
     * @var array
     */
    private $cache = [];
    
    /**
     * Di instances.
     * 
     * @var array
     */
    private static $instances = [];
    
    /**
     * @see self::create()
     */
    public function __call($name, array $args = [])
    {
        return $this->create($name, $args);
    }
    
    /**
     * @see self::get()
     */
    public function __get($name)
    {
        return $this->get($name);
    }
    
    /**
     * Locates the specified service and returns it.
     * 
     * @param string $name The service name.
     * @param string $args The arguments to use.
     * 
     * @return mixed
     */
    public function get($name, array $args = [])
    {
        // if arguments are passed and an instance is cached, return it
        if (!$args && isset($this->cache[$name])) {
            return $this->cache[$name];
        }
        
        // creates a new instance and invokes its methods
        $inst = $this->create($name, $args);
        
        // cache instance
        $this->cache[$name] = $inst;
        
        return $inst;
    }
    
    /**
     * Throws an exception if the dependency does not exist.
     * 
     * @param string $name The dependency name.
     * 
     * @throws LogicException
     * 
     * @return void
     */
    protected function throwNotExists($name)
    {
        $trace = debug_backtrace()[1];
        throw new LogicException(sprintf('Could not resolve dependency "%s" in "%s".',
            $name,
            get_class($this)
        ));
    }
    
    /**
     * Returns a container instance.
     * 
     * @param string $name The name of the container to get.
     * 
     * @return Container
     */
    public static function fetch($service = null)
    {
        $name = get_called_class();
        
        if (!isset(self::$instances[$name])) {
            self::$instances[$name] = new static;
        }
        
        if ($service) {
            return self::$instances[$name]->get($service);
        } else {
            return self::$instances[$name];
        }
    }
}