<?php

namespace Europa\Di;
use Closure;
use Europa\Exception\Exception;
use Europa\Reflection\ClassReflector;
use ReflectionClass;

class ServiceContainer implements ServiceContainerInterface
{
    private $cache = [];

    private $dependencies = [];

    private $services = [];

    private $transient = [];

    private static $instances = [];

    public function __clone()
    {
        $this->cache = [];
    }

    public function __invoke($name)
    {
        return $this->__get($name);
    }

    public function __call($name, array $args = [])
    {
        if (isset($this->services[$name])) {
            return call_user_func_array($this->services[$name], $args);
        }
        
        Exception::toss('Cannot invoke service "%s" in container "%s" because it does not exist.', $name, $this->name());
    }

    public function __set($name, $value)
    {
        if (isset($this->cache[$name])) {
            unset($this->cache[$name]);
        }

        $this->uncacheServicesThatDependOn($name);

        $this->services[$name] = $this->ensureClosure($value);
    }

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

        $service = call_user_func_array($service, $this->resolveDependenciesFor($name));

        if (isset($this->transient[$name])) {
            return $service;
        }

        return $this->cache[$name] = $service;
    }

    public function __isset($name)
    {
        return isset($this->services[$name]);
    }

    public function __unset($name)
    {
        if (isset($this->services[$name])) {
            unset($this->services[$name]);
        }

        if (isset($this->cache[$name])) {
            unset($this->cache[$name]);
        }
    }

    public function configure(callable $configuration)
    {
        $configuration($this);
        return $this;
    }

    public function name()
    {
        foreach (self::$instances as $name => $instance) {
            if ($this === $instance) {
                return $name;
            }
        }
    }

    public function fullName()
    {
        return $this->name() ?: get_class($this) . '::[unknown]()';
    }

    public function transient($name)
    {
        $this->transient[] = $name;

        if (isset($this->cache[$name])) {
            unset($this->cache[$name]);
        }

        return $this;
    }

    public function isTransient($name)
    {
        return in_array($name, $this->transient);
    }

    public function depends($name, array $on)
    {
        $this->dependencies[$name] = $on;
        return $this;
    }

    public function dependencies($name)
    {
        return isset($this->dependencies[$name])
            ? $this->dependencies[$name]
            : [];
    }

    public function isDepenent($name, $on)
    {
        return in_array($on, $this->dependencies($name));
    }

    public function provides($blueprint)
    {
        $reflector = new ReflectionClass($blueprint);

        foreach ($reflector->getMethods() as $method) {
            $name = $method->getName();

            if (!isset($this->services[$name])) {
                return false;
            }
        }

        return true;
    }

    public function mustProvide($blueprint)
    {
        if ($this->provides($blueprint)) {
            return $this;
        }

        Exception::toss('The blueprint "%s" must be provided by "%s".', $blueprint, $this->fullName());
    }

    public function save($name)
    {
        self::$instances[self::generateKey($name)] = $this;
        return $this;
    }

    public static function get($name, array $args = [])
    {
        $key = self::generateKey($name);

        if (!$args && isset(self::$instances[$key])) {
            return self::$instances[$key];
        }
        
        try {
            return self::$instances[$key] = (new ClassReflector(get_called_class()))->newInstanceArgs($args);
        } catch (\Exception $e) {
            Exception::toss('Could not get the container "%s" from "%s" because: %s', $name, get_called_class(), $e->getMessage());
        }
    }

    protected function throwNotExists($name)
    {
        $message = 'The service "%s" does not exist in "%s"';

        foreach (self::$instances as $other => $instance) {
            if ($instance->__isset($name)) {
                $message .= ', however, a service with the same name exists in "' . $other . '".';
            }
        }

        Exception::toss($message, $name, $this->fullName());
    }

    private function ensureClosure($value)
    {
        if (is_string($value)) {
            $value = function() use ($value) {
                return new $value;
            };
        }

        if (!$value instanceof Closure) {
            $value = function() use ($value) {
                return $value;
            };
        }

        return $value;
    }

    private function uncacheServicesThatDependOn($name)
    {
        foreach ($this->dependencies as $service => $dependencies) {
            if (isset($this->cache[$service]) && in_array($name, $dependencies)) {
                unset($this->cache[$service]);
            }
        }
    }

    private function resolveDependenciesFor($name)
    {
        $dependencies = [];

        foreach ($this->dependencies($name) as $dependency) {
            if ($this->__isset($dependency)) {
                $dependencies[] = $this->__get($dependency);
            } else {
                Exception::toss(
                    'The service "%s" requires that the dependency "%s" is defined.',
                    $name,
                    $dependency
                );
            }
        }

        return $dependencies;
    }

    private static function generateKey($name)
    {
        return get_called_class() . '::' . $name . '()';
    }
}