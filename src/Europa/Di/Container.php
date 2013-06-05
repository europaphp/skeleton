<?php

namespace Europa\Di;
use Closure;
use Europa\Reflection\FunctionReflector;
use Europa\Reflection\ReflectorInterface;
use ReflectionClass;

class Container implements ContainerInterface
{
    private $aliases = [];

    private $cache = [];

    private $dependencies = [];

    private $loading = [];

    private $services = [];

    private $transient = [];

    private static $instances = [];

    public function __clone()
    {
        $this->cache = [];
    }

    public function __invoke($name)
    {
        $name = $this->resolveAlias($name);

        if (isset($this->cache[$name])) {
            return $this->cache[$name];
        }

        if (isset($this->loading[$name])) {
            throw new Exception\CircularReferenceException($name, array_keys($this->loading));
        }

        $this->loading[$name] = true;

        if (isset($this->services[$name])) {
            $service = $this->services[$name];
        } else {
            throw new Exception\UnregisteredService($name, $this);
        }

        $service = $service($this);

        if (isset($this->types[$name])) {
            foreach ($this->types[$name] as $type) {
                if (!$service instanceof $type) {
                    Exception::toss(
                        'The service "%s" is required to be an instance of "%s". Instance of "%s" supplied.',
                        $name,
                        $type,
                        get_class($service)
                    );
                }
            }
        }

        unset($this->loading[$name]);

        if (isset($this->transient[$name])) {
            return $service;
        }

        return $this->cache[$name] = $service;
    }



    public function register($name, Closure $service)
    {
        $name = $this->resolveAlias($name);

        if (isset($this->cache[$name])) {
            unset($this->cache[$name]);
        }

        $this->services[$name] = $service;

        return $this;
    }

    public function configure(callable $configuration)
    {
        $configuration($this);
        return $this;
    }

    public function save($as)
    {
        if (isset(self::$instances[$as])) {
            throw new Exception\ContainerAlreadyRegistered($as);
        }

        self::$instances[$as] = $this;

        return $this;
    }

    public function name()
    {
        foreach (self::$instances as $name => $instance) {
            if ($instance === $this) {
                return $name;
            }
        }
    }

    public function setAliases($name, array $aliases)
    {
        foreach ($aliases as $alias) {
            $this->aliases[$alias] = $name;
        }

        return $this;
    }

    public function setDependencies($name, array $dependencies)
    {
        $this->dependencies[$this->resolveAlias($name)] = $dependencies;
        return $this;
    }

    public function setTransient($name)
    {
        $name = $this->resolveAlias($name);

        $this->transient[$name] = true;

        if (isset($this->cache[$name])) {
            unset($this->cache[$name]);
        }

        return $this;
    }

    public function setTypes($name, array $types)
    {
        $this->types[$this->resolveAlias($name)] = $types;
        return $this;
    }

    private function resolveAlias($name)
    {
        if (isset($this->aliases[$name])) {
            return $this->aliases[$name];
        }

        return $name;
    }

    public static function get($name)
    {
        if (isset(self::$instances[$name])) {
            return self::$instances[$name];
        }

        throw new Exception\UnregisteredContainer($name);
    }
}