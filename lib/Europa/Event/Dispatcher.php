<?php

namespace Europa\Event;

/**
 * An event dispatcher for managing multiple events and event stacks.
 * 
 * @category Events
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Dispatcher
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
     * @param string                       $name     The name of the event to bind to.
     * @param \Europa\Event\EventInterface $callback The callback to call when triggering.
     * 
     * @return void
     */
    public function bind($name, EventInterface $handler)
    {
        if (!isset($this->stack[$name])) {
            $this->stack[$name] = array();
        }
        $this->stack[$name][] = $handler;
    }
    
    /**
     * Unbinds an event.
     * 
     * @param string                       $name    The name of the event to unbind.
     * @param \Europa\Event\EventInterface $handler The specific handler to unbind, if specified.
     * 
     * @return bool
     */
    public function unbind($name, EventInterface $handler = null)
    {
        foreach ($this->getStackNamesForEvent($name) as $event) {
            if ($handler) {
                foreach ($this->stack[$event] as $index => $bound) {
                    if ($bound === $handler) {
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
     * @param array $data Any data to pass to the event or event stack at the time of triggering.
     * 
     * @return bool
     */
    public function trigger($name, DataInterface $data = null)
    {
        $data = $data ? $data : new Data;
        foreach ($this->getStackNamesForEvent($name) as $event) {
            foreach ($this->stack[$event] as $handler) {
                if ($handler->trigger($data) === false) {
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
}
