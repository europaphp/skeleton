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
     * Locates the specified service and returns it.
     * 
     * @param string $name The service name.
     * 
     * @return mixed
     */
    public function __get($name)
    {
        if (!isset($this->cache[$name])) {
            $this->cache[$name] = $this->__call($name);
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
        
        $name = get_called_class() . $name;

        if (!isset(self::$instances[$name])) {
            if ($args) {
                $inst = (new ClassReflector(get_called_class()))->newInstanceArgs(func_get_args());
            } else {
                $inst = new static;
            }

            self::$instances[$name] = $inst;
        }

        return self::$instances[$name];
    }
}