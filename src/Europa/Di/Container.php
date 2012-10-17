<?php

namespace Europa\Di;
use Closure;
use Europa\Reflection\ClassReflector;
use Exception;
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
class Container implements ContainerInterface
{
    /**
     * Non-transient services that have already been located and set up.
     * 
     * @var array
     */
    private $cache = [];

    /**
     * List of available services.
     * 
     * @var array
     */
    private $services = [];

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
     * Returns an instance of the specified service.
     * 
     * @param string $name The name of the service to return.
     * 
     * @return mixed
     */
    public function __invoke($name)
    {
        return $this->__get($name);
    }

    /**
     * Since you can't infer a property and call it at the same time, you have to proxy it via __call().
     * 
     * @param string $name The service name.
     * @param array  $args The service arguments. These are ignored.
     * 
     * @return mixed
     */
    public function __call($name, array $args = [])
    {
        if (!is_callable($service = $this->__get($name))) {
            throw new UnexpectedValueException(sprintf('Cannot invoke service "%s" in container "%s" because it is not callable.', $name, $this->name()));
        }
        
        return call_user_func_array($service, $args);
    }

    /**
     * Registers a service.
     * 
     * @param string $name The service name.
     * @param mixed  $value The service value. If this is `!is_callable($service)` then it is wrapped in a `Closure`.
     * 
     * @return Container
     */
    public function __set($name, $value)
    {
        if (!$value instanceof Closure) {
            $value = function() use ($value) {
                return $value;
            };
        }

        $this->services[$name] = $value;
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
        if (isset($this->cache[$name])) {
            return $this->cache[$name];
        }

        if (isset($this->services[$name])) {
            $service = $this->services[$name];
        } else {
            $this->throwNotExists($name);
        }

        if (isset($this->transient[$name])) {
            return $service($this);
        }

        return $this->cache[$name] = $service($this);
    }

    /**
     * Returns whether or not the specified service exists.
     * 
     * @parma string $name The service name.
     * 
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->services[$name]);
    }

    /**
     * Unregisters the specified service.
     * 
     * @parma string $name The service name.
     * 
     * @return void
     */
    public function __unset($name)
    {
        if (isset($this->services[$name])) {
            unset($this->services[$name]);
        }

        if (isset($this->cache[$name])) {
            unset($this->cache[$name]);
        }
    }

    /**
     * Configures the container.
     * 
     * @param ConfigurationInterface $configuration The configuration to use to configure the container.
     * 
     * @return Container
     */
    public function configure(ConfigurationInterface $configuration)
    {
        $configuration->configure($this);
        return $this;
    }

    /**
     * Returns the name of the container.
     * 
     * @return string
     */
    public function name()
    {
        foreach (self::$instances as $name => $instance) {
            if ($this === $instance) {
                return $name;
            }
        }
    }

    /**
     * Denotes certain services as transient.
     * 
     * @param mixed $names The name or names of the transient services.
     * 
     * @return Container
     */
    public function transient($name)
    {
        $this->transient[$name] = $name;

        if (isset($this->cache[$name])) {
            unset($this->cache[$name]);
        }

        return $this;
    }

    /**
     * Returns whether or not the specified service is transient.
     * 
     * @return bool
     */
    public function isTransient($name)
    {
        return isset($this->transient[$name]);
    }

    /**
     * Statically configures and returns the container of the specified name.
     * 
     * @param string $name The name of the container to return.
     * @param array  $args The arguments to pass to the container's constructor.
     * 
     * @return Container
     */
    public static function __callStatic($name, array $args = [])
    {
        $key = get_called_class() . '::' . $name . '()';

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
     * Throws an exception if the dependency cannot be found.
     * 
     * @param string $name The dependency name.
     * 
     * @return void
     */
    protected function throwNotExists($name)
    {
        $message = 'The service "%s" does not exist in "%s"';

        foreach (self::$instances as $other => $instance) {
            if ($instance->__isset($name)) {
                $message .= ', however, a service with the same name exists in "' . $other . '".';
            }
        }

        throw new LogicException(sprintf(
            $message,
            $name,
            $this->name() ?: get_class($this) . '::[unknown]()'
        ));
    }
}