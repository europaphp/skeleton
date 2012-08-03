<?php

namespace Europa\Di;
use Europa\Reflection\ClassReflector;
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
     * Creates a new instance specified by name.
     * 
     * @param string $name The service name.
     * @param array  $args The arguments to pass, if any.
     * 
     * @return mixed
     */
    abstract public function create($name);
    
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
        $this->cache[$name] = $this->create($name, $args);
        return $this->cache[$name];
    }

    /**
     * Locates the specified service and returns it.
     * 
     * @param string $name The service name.
     * 
     * @return mixed
     */
    public function __get($name)
    {
        if (!isset($this->cache[$name])) {
            $this->__call($name);
        }
        return $this->cache[$name];
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
     * Returns a container instance. Allows multiple named instances of the same type of container.
     * 
     * @param string $name The name of the container to get.
     * @param array  $args The arguments to pass to the container constructor. Can be passed by name.
     * 
     * @return Container
     */
    public static function get($name = self::NAME, array $args = [])
    {
        if (is_array($name)) {
            $args = $name;
            $name = self::NAME;
        }
        
        $inst = static::formatName($name);
        
        if ($args || !isset(self::$instances[$inst])) {
            static::init($name, $args);
        }
        
        return self::$instances[$inst];
    }
    
    /**
     * Initializes a new instance.
     * 
     * @param string $name The name of the container to init.
     * @param array  $args The arguments to pass to the container constructor. Can be passed by name.
     * 
     * @return void
     */
    public static function init($name = self::NAME, array $args = [])
    {
        if (is_array($name)) {
            $args = $name;
            $name = self::NAME;
        }
        
        $name = static::formatName($name);
        
        if ($args) {
            $inst = (new ClassReflector(get_called_class()))->newInstanceArgs($args);
        } else {
            $inst = new static;
        }
        
        self::$instances[$name] = $inst;
    }
    
    /**
     * Returns whether or not the specified instance exists.
     * 
     * @param string $name The instance name.
     * 
     * @return bool
     */
    public static function has($name = self::NAME)
    {
        return isset(self::$instances[static::formatName($name)]);
    }
    
    /**
     * Removes the specified instance.
     * 
     * @param string $name The name of the container to remove.
     * 
     * @return void
     */
    public static function remove($name = self::NAME)
    {
        $name = static::formatName($name);
        
        if (isset(self::$instances[$name])) {
            unset(self::$instances[$name]);
        }
    }
    
    /**
     * Formats a default instance name.
     * 
     * @param string $name The name to format.
     * 
     * @return string
     */
    private static function formatName($name)
    {
        if (!$name) {
            $name = self::NAME;
        }
        return get_called_class() . '.' . $name;
    }
}