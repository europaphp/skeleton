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
class Dispatcher implements DispatcherInterface
{
    /**
     * The event stack which contains a stack of callback for each event bound.
     * 
     * @var array
     */
    private $stack = array();
    
    /**
     * Binds an event handler to the stack.
     * 
     * @param string $name     The name of the event to bind to.
     * @param mixed  $callback The callback to call when triggering.
     * 
     * @return Dispatcher
     */
    public function bind($name, $callback)
    {
        // resolve from any value
        $callback = $this->resolveCallback($name, $callback);
        
        // create the event stack for the specified event if it doesn't exist
        if (!isset($this->stack[$name])) {
            $this->stack[$name] = [];
        }
        
        // add the handler to the stack
        $this->stack[$name][] = $callback;
        
        return $this;
    }
    
    /**
     * Unbinds an event.
     * 
     * @param string $name     The name of the event to unbind.
     * @param mixed  $callback The specific handler to unbind, if specified.
     * 
     * @return bool
     */
    public function unbind($name, $callback = null)
    {
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
        $stack = array();
        foreach ($this->stack as $event => $handlers) {
            if (strpos($event, $name) === 0) {
                $stack[] = $event;
            }
        }
        return $stack;
    }
    
    /**
     * Attempts to resolve the callback from any type of value.
     * 
     * @param string $name     The event name for informational purposes.
     * @param mixed  $callback The callback to resolve.
     * 
     * @return mixed
     */
    private function resolveCallback($name, $callback)
    {
        if (is_string($callback)) {
            $callback = $this->resolveCallbackFromString($name, $callback);
        }
        
        $this->validateCallback($name, $callback);
        
        return $callback;
    }
    
    /**
     * Attempts to resolve the callback from a string.
     * 
     * @param string $name     The event name for informational purposes.
     * @param mixed  $callback The callback to resolve.
     * 
     * @return mixed
     */
    private function resolveCallbackFromString($name, $callback)
    {
        if (!class_exists($callback, true)) {
            throw new InvalidArgumentException(sprintf('The specified callback event class "%s" does not exist.', $callback, $name));
        }
        
        return new $callback;
    }
    
    /**
     * Validates the callback.
     * 
     * @param string $name     The event name for informational purposes.
     * @param mixed  $callback The callback to validate.
     * 
     * @return void
     */
    private function validateCallback($name, $callback)
    {
        if (!is_callable($callback)) {
            throw new InvalidArgumentException(sprintf('The specified callback for event "%s" is not callable.', $name));
        }
    }
}