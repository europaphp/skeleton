<?php

namespace Europa\Di;
use Closure;
use Europa\Exception\Exception;
use Europa\Reflection\FunctionReflector;
use Europa\Reflection\ReflectorInterface;
use ReflectionClass;

class Container implements ContainerInterface
{
    const SELF_DEPENDENCY_NAME = 'self';

    private $aliases = [];

    private $cache = [];

    private $dependencies = [];

    private $services = [];

    private $transient = [];

    public function __clone()
    {
        $this->cache = [];
    }

    public function set($name, Closure $service)
    {
        $name = $this->resolveAlias($name);

        $this->uncache($name);

        $this->services[$name] = $service;

        return $this;
    }

    public function get($name)
    {
        $name = $this->resolveAlias($name);

        if (isset($this->cache[$name])) {
            return $this->cache[$name];
        }

        if (isset($this->services[$name])) {
            $service = $this->services[$name];
        } else {
            Exception::toss('The service "%s" does not exist.', $name);
        }

        $service = call_user_func_array($service, $this->resolveDependenciesFor($name));

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

        $this->uncache($name);

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

    public function setDependencies($name, array $dependencies)
    {
        $this->dependencies[$this->resolveAlias($name)] = $dependencies;
        return $this;
    }

    public function setTransient($name)
    {
        $name = $this->resolveAlias($name);

        $this->transient[] = $name;

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

    private function uncache($name)
    {
        if (isset($this->cache[$name])) {
            unset($this->cache[$name]);
        }

        foreach ($this->dependencies as $service => $dependencies) {
            if (isset($this->cache[$service]) && in_array($name, $dependencies)) {
                unset($this->cache[$service]);
            }
        }
    }

    private function resolveAlias($name)
    {
        if (isset($this->aliases[$name])) {
            return $this->aliases[$name];
        }

        return $name;
    }

    private function resolveDependenciesFor($name)
    {
        $dependencies = [];

        if (isset($this->dependencies[$name])) {
            foreach ($this->dependencies[$name] as $dependency) {
                if ($dependency === self::SELF_DEPENDENCY_NAME) {
                    $dependencies[] = $this;
                } elseif ($this->has($dependency)) {
                    $dependencies[] = $this->get($dependency);
                } else {
                    Exception::toss('The service "%s" requires that the dependency "%s" is defined.', $name, $dependency);
                }
            }
        }

        return $dependencies;
    }
}