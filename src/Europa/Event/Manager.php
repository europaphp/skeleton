<?php

namespace Europa\Event;
use InvalidArgumentException;

/**
 * An event dispatcher for managing multiple events and event stacks.
 * 
 * @category Events
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Manager implements ManagerInterface
{
    /**
     * The event stack which contains a stack of callback for each event bound.
     * 
     * @var array
     */
    private $stack = [];
    
    /**
     * Binds an event handler to the stack.
     * 
     * @param string   $name     The name of the event to bind to.
     * @param callable $callback The callback to call when triggering.
     * 
     * @return Dispatcher
     */
    public function bind($name, callable $callback)
    {
        // create the event stack for the specified event if it doesn't exist
        if (!isset($this->stack[$name])) {
            $this->stack[$name] = [];
        }
        
        // add the handler to the stack
        $this->stack[$name][] = $callback;
        
        return $this;
    }
    
    /**
     * Unbinds the specified event handler, event stack, or all event stacks.
     * 
     * @param string   $name     The name of the event to unbind.
     * @param callable $callback The specific handler to unbind, if specified.
     * 
     * @return bool
     */
    public function unbind($name = null, callable $callback = null)
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
    
    /**
     * Triggers an event stack.
     * 
     * @param string $name The name of the event to trigger.
     * @param array  $data Any data to pass to the event or event stack at the time of triggering.
     * 
     * @return bool
     */
    public function trigger($name, array $data = [])
    {
        foreach ($this->getStackNamesForEvent($name) as $event) {
            foreach ($this->stack[$event] as $callback) {
                if (call_user_func_array($callback, $data) === false) {
                    return $this;
                }
            }
        }
        return $this;
    }
    
    /**
     * Returns the event stack for the specified events.
     * 
     * @param string $name The name of the event.
     * 
     * @return array
     */
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