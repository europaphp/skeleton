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
     * @param string $name The name of the event to trigger.
     * 
     * @return bool
     */
    public function trigger($name);

    /**
     * Triggers an event stack using an array of arguments.
     * 
     * @param string $name The name of the event to trigger.
     * @param array  $args Any arguments to pass to the event or event stack at the time of triggering.
     * 
     * @return bool
     */
    public function triggerArray($name, array $args = []);
}