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
class Callback implements Triggerable
{
    /**
     * The callable callback that will be triggered.
     * 
     * @var mixed
     */
    protected $callback;
    
    /**
     * Flags whether or not the current callback has been triggered.
     * 
     * @var bool
     */
    protected $triggered = false;
    
    /**
     * Constructs a new callback event.
     * 
     * @param mixed $callback The callable callback to trigger.
     * 
     * @return \Europa\Event\Callback
     */
    public function __construct($callback)
    {
        if (!is_callable($callback, true)) {
            throw new Exception(
                'Passed callback is not callable.',
                Exception::INVALID_CALLBACK
            );
        }
        $this->callback = $callback;
    }
    
    /**
     * Calls the callback passing the current object data into it.
     * 
     * @param array $data The data passed to the event at the time of triggering.
     * 
     * @return mixed
     */
    public function trigger(array $data = array())
    {
        // flag as triggered
        $this->triggered = true;

        // and return the return value of the callback passing in the event handler
        return call_user_func($this->callback, $data);
    }

    /**
     * Returns whether or not the item was triggered.
     * 
     * @return bool
     */
    public function wasTriggered()
    {
        return $this->triggered;
    }
}