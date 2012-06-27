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
     * List of transient services.
     * 
     * @var array
     */
    private $transient = [];
    
    /**
     * Di instances.
     * 
     * @var array
     */
    private static $instances = [];
    
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
        
        // instance must exist
        if (!$inst) {
            $trace = debug_backtrace()[0];
            throw new LogicException(sprintf('Could not resolve dependency "%s" in "%s".',
                $name,
                get_class($this)
            ));
        }
        
        // only cache the instance if it is not transient
        if ($inst && !in_array($name, $this->transient)) {
            $this->cache[$name] = $inst;
        }
        
        return $inst;
    }
    
    /**
     * Marks a service as transient.
     * 
     * @param string $alias The service name.
     * 
     * @return Container
     */
    public function transient($alias)
    {
        $this->transient[] = $alias;
        return $this;
    }
    
    /**
     * Returns a container instance.
     * 
     * @param string $name The name of the container to get.
     * 
     * @return Container
     */
    public static function fresh($service = null)
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