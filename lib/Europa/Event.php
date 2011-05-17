<?php

namespace Europa;
use Europa\Event\Triggerable;

/**
 * An event class for managing multiple events and event stacks.
 * 
 * @category Events
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Event
{
    /**
     * The event stack which contains a stack of callback for each event bound.
     * 
     * @var array
     */
    protected static $stack = array ();
    
    /**
     * Binds an event handler to the stack.
     * 
     * @param string                    $name     The name of the event to bind to.
     * @param \Europa\Event\Triggerable $callback The callback to call when triggering.
     * 
     * @return void
     */
    public static function bind($name, Triggerable $handler)
    {
        // make sure the event has it's own stack
        if (! self::isBound($name)) {
            self::$stack [$name] = array ();
        }
        
        // and add it to the stack
        self::$stack [$name] [] = $handler;
    }
    
    /**
     * Unbinds an event.
     * 
     * @param string                    $name    The name of the event to unbind.
     * @param \Europa\Event\Triggerable $handler The specific handler to unbind, if specified.
     * 
     * @return bool
     */
    public static function unbind($name, Triggerable $handler = null)
    {
        if (self::isBound($name)) {
            if ($handler) {
                foreach (self::$stack [$name] as $k => $bound) {
                    if ($bound === $handler) {
                        unset(self::$stack [$name] [$k]);
                        return true;
                    }
                }
            }
            unset(self::$stack [$name]);
            return true;
        }
        return false;
    }
    
    /**
     * Triggers an event stack.
     * 
     * @param array $data Any data to pass to the event or event stack at the time of triggering.
     * 
     * @return bool
     */
    public static function trigger($name, array $data = array())
    {
        foreach (self::getStack($name) as $handler) {
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
    public static function isBound($name)
    {
        return isset(self::$stack [$name]);
    }
    
    /**
     * Returns the event stack.
     * 
     * @param string $eventName The event stack, if any to get the bound events for.
     * 
     * @return array
     */
    public static function getStack($name = null)
    {
        if ($name) {
            if (self::isBound($name)) {
                return self::$stack [$name];
            }
            return array ();
        }
        return self::$stack;
    }
}