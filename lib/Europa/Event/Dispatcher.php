<?php

namespace Europa\Event;

/**
 * An event class for managing multiple events and event stacks.
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
        if (!$this->isBound($name)) {
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
        if ($this->isBound($name)) {
            if ($handler) {
                foreach ($this->stack[$name] as $k => $bound) {
                    if ($bound === $handler) {
                        unset($this->stack[$name][$k]);
                    }
                }
            } else {
                unset($this->stack[$name]);
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
    public function trigger($name, array $data = array())
    {
        foreach ($this->getStack($name) as $handler) {
            if ($handler->trigger($data) === false) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Returns whether the event passed is bound or not.
     * 
     * @param string $eventName The name of the event to check for.
     * 
     * @return bool
     */
    public function isBound($name)
    {
        return isset($this->stack[$name]);
    }
    
    /**
     * Returns the event stack for the specified events.
     * 
     * @param string $eventName The event stack.
     * 
     * @return array
     */
    private function getStack($name)
    {
        if ($this->isBound($name)) {
            return $this->stack[$name];
        }
        return array();
    }
}