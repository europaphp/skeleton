<?php

namespace Europa\Di;
use Closure;
use Europa\Di\Exception\CircularReferenceException;
use Europa\Filter\ClassNameFilter;
use Europa\Filter\FilterAware;
use Europa\Filter\FilterAwareInterface;
use Europa\Reflection\ClassReflector;
use Europa\Exception\Exception;

class Finder implements FilterAwareInterface, FinderInterface
{
    use FilterAware;

    private $args = [];

    private $cache = [];

    private $callbacks = [];

    private $loading = [];

    private $transient = false;

    public function __construct()
    {
        $this->setFilter(new ClassNameFilter);
    }

    public function get($name)
    {
        if (isset($this->cache[$name])) {
            return $this->cache[$name];
        }

        if (isset($this->loading[$name])) {
            throw new CircularReferenceException($name, array_keys($this->loading));
        }

        $this->loading[$name] = true;

        $class = call_user_func($this->filter, $name);

        if (!class_exists($class)) {
            Exception::toss('The class "%s" does not exist.', $class);
        }

        $args = [];

        foreach ($this->args as $instanceof => $instanceofArgs) {
            if ($this->is($class, $instanceof)) {
                $args = array_merge($args, $instanceofArgs());
            }
        }

        $class = new ClassReflector($class);
        $class = $class->newInstanceArgs($args);

        foreach ($this->callbacks as $instanceof => $callback) {
            if ($this->is($class, $instanceof)) {
                $callback($class);
            }
        }

        unset($this->loading[$name]);

        if ($this->transient) {
            return $class;
        }

        return $this->cache[$name] = $class;
    }

    public function has($name)
    {
        return class_exists(call_user_func($this->filter, $name));
    }

    public function addArgs($instanceof, Closure $closure)
    {
        $this->args[$instanceof] = $closure;
        return $this;
    }

    public function addCallback($instanceof, Closure $closure)
    {
        $this->callbacks[$instanceof] = $closure;
        return $this;
    }

    public function setTransient()
    {
        $this->transient = true;
        return $this;
    }

    private function is($class, $instanceof)
    {
        return $class === $instanceof || is_subclass_of($class, $instanceof);
    }
}