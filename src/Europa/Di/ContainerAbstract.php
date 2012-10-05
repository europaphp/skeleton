<?php

namespace Europa\Di;
use Europa\Reflection\ClassReflector;
use Exception;
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
     * Creates a new instance specified by name.
     * 
     * @param string $name The service name.
     * @param array  $args The arguments to pass, if any.
     * 
     * @return mixed
     */
    abstract public function __call($name, array $args = []);

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
        $key = get_called_class() . $name;

        if (!$args && isset(self::$instances[$key])) {
            return self::$instances[$key];
        }
        
        try {
            return self::$instances[$key] = (new ClassReflector(get_called_class()))->newInstanceArgs($args);
        } catch (Exception $e) {
            throw new LogicException(sprintf('Could not get the container "%s" from "%s" because: %s', $name, get_called_class(), $e->getMessage()));
        }
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
        if (isset($this->transient[$name])) {
            return $this->__call($name);
        }

        if (!isset($this->cache[$name])) {
            $this->cache[$name] = $this->__call($name);
        }

        return $this->cache[$name];
    }

    /**
     * Denotes certain services as transient.
     * 
     * @param mixed $names The name or names of the transient services.
     * 
     * @return ContainerAbstract
     */
    public function transient($names)
    {
        foreach ((array) $names as $name) {
            $this->transient[$name] = $name;
        }
        return $this;
    }
}