<?php

namespace Europa\Di;
use Closure;
use Europa\Reflection\FunctionReflector;
use Europa\Reflection\ReflectorInterface;
use ReflectionClass;

class Container implements ContainerInterface
{
    const SERVICE_SELF = 'self';

    private $aliases = [];

    private $cache = [];

    private $dependencies = [];

    private $loading = [];

    private $services = [];

    private $transient = [];

    public function __clone()
    {
        $this->cache = [];
    }

    public function configure(ConfigurationInterface $configuration)
    {
        $configuration->configure($this);
        return $this;
    }

    public function set($name, Closure $service)
    {
        $name = $this->resolveAlias($name);

        if (isset($this->cache[$name])) {
            unset($this->cache[$name]);
        }

        $this->services[$name] = $service;

        return $this;
    }

    public function get($name)
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
            Exception::toss('The service "%s" does not exist.', $name);
        }

        if ($dependencies = $this->resolveDependencies($name)) {
            $service = call_user_func_array($service, $dependencies);
        } else {
            $service = $service();
        }

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

    public function has($name)
    {
        return isset($this->services[$this->resolveAlias($name)]);
    }

    public function remove($name)
    {
        $name = $this->resolveAlias($name);

        if (isset($this->cache[$name])) {
            unset($this->cache[$name]);
        }

        if (isset($this->services[$name])) {
            unset($this->services[$name]);
        }

        return $this;
    }

    public function setAliases($name, array $aliases)
    {
        foreach ($aliases as $alias) {
            $this->aliases[$alias] = $name;
        }

        return $this;
    }

    public function getAliases($name)
    {
        $aliases = [];

        foreach ($this->aliases as $alias => $service) {
            if ($service === $name) {
                $aliases[] = $alias;
            }
        }

        return $aliases;
    }

    public function setDependencies($name, array $dependencies)
    {
        $this->dependencies[$this->resolveAlias($name)] = $dependencies;
        return $this;
    }

    public function getDependencies($name)
    {
        $name = $this->resolveAlias($name);

        if (isset($this->dependencies[$name])) {
            return $this->dependencies[$name];
        }

        return [];
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

    public function getTransient($name)
    {
        return isset($this->transeient[$name]);
    }

    public function setTypes($name, array $types)
    {
        $this->types[$this->resolveAlias($name)] = $types;
        return $this;
    }

    public function getTypes()
    {
        $name = $this->resolveAlias($name);

        if (isset($this->types[$name])) {
            return $this->types[$name];    
        }
        
        return [];
    }

    private function resolveAlias($name)
    {
        if (isset($this->aliases[$name])) {
            return $this->aliases[$name];
        }

        return $name;
    }

    private function resolveDependencies($name)
    {
        $dependencies = [];

        foreach ($this->getDependencies($name) as $dependency) {
            if ($dependency === self::SERVICE_SELF) {
                $dependencies[] = $this;
            } elseif ($this->has($dependency)) {
                $dependencies[] = $this->get($dependency);
            } else {
                throw new Exception\UndefinedDependency($name, $dependency);
            }
        }

        return $dependencies;
    }
}