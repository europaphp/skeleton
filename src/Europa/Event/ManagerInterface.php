<?php

namespace Europa\Event;

/**
 * The event dispatcher interface.
 * 
 * @category Events
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
interface ManagerInterface
{
    /**
     * Binds an event handler to the stack.
     * 
     * @param string   $name     The name of the event to bind to.
     * @param callable $callback The callback to call when triggering.
     * 
     * @return Dispatcher
     */
    public function bind($name, callable $callback);
    
    /**
     * Unbinds an event.
     * 
     * @param string   $name     The name of the event to unbind.
     * @param callable $callback The specific handler to unbind, if specified.
     * 
     * @return bool
     */
    public function unbind($name, callable $callback = null);
    
    /**
     * Triggers an event stack.
     * 
     * @param stirng $name The name of the event to trigger.
     * @param array  $data Any data to pass to the event or event stack at the time of triggering.
     * 
     * @return bool
     */
    public function trigger($name, array $data = []);
}