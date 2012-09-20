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
     * Statically configures and returns the container of the specified name.
     * 
     * @param string $name The name of the container to return.
     * @param array  $args The arguments to pass to the container's constructor.
     * 
     * @return ContainerAbstract
     */
    public static function __callStatic($name, array $args = [])
    {
        $name = static::formatName($name);

        if (!$args) {
            if (isset(self::$instances[$name])) {
                return self::$instances[$name];
            } else {
                return self::$instances[$name] = new static;
            }
        }
        
        return self::$instances[$name] = (new ClassReflector(get_called_class()))->newInstanceArgs($args);
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