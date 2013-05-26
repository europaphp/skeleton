<?php

namespace Europa\Event;
use InvalidArgumentException;

class Manager implements ManagerInterface
{
    private $stack = [];

    public function on($name, callable $callback)
    {
        if (!isset($this->stack[$name])) {
            $this->stack[$name] = [];
        }

        $this->stack[$name][] = $callback;

        return $this;
    }

    public function off($name = null, callable $callback = null)
    {
        if (!$name) {
            $this->stack = [];
            return $this;
        }

        foreach ($this->getStackNamesForEvent($name) as $event) {
            if ($callback) {
                foreach ($this->stack[$event] as $index => $bound) {
                    if ($bound === $callback) {
                        unset($this->stack[$event][$index]);
                    }
                }
            } else {
                unset($this->stack[$event]);
            }
        }

        return $this;
    }

    public function trigger($name)
    {
        $args = func_get_args();

        array_shift($args);

        return $this->triggerArray($name, $args);
    }

    public function bound($name, $callback = null)
    {
        foreach ($this->getStackNamesForEvent($name) as $event) {
            if ($callback) {
                foreach ($this->stack[$event] as $index => $bound) {
                    if ($bound === $callback) {
                        return true;
                    }
                }
            } elseif (isset($this->stack[$event])) {
                return true;
            }
        }

        return false;
    }

    public function triggerArray($name, array $args = [])
    {
        foreach ($this->getStackNamesForEvent($name) as $event) {
            foreach ($this->stack[$event] as $callback) {
                if (call_user_func_array($callback, $args) === false) {
                    return $this;
                }
            }
        }

        return $this;
    }

    private function getStackNamesForEvent($name)
    {
        $stack = [];

        foreach ($this->stack as $event => $handlers) {
            if (strpos($event, $name) === 0) {
                $stack[] = $event;
            }
        }

        return $stack;
    }
}