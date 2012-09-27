<?php

namespace Europa\Event;

/**
 * A trait that allows an event dispatcher to be applied to it.
 * 
 * @category Event
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
trait Eventable
{
    /**
     * Event object.
     * 
     * @var array
     */
    private $event;

    /**
     * Sets or returns an event object. If returning an event object and one does not already exist, then a default one
     * is created. If setting an event object, then the current trait instance is returned.
     * 
     * @param DispatcherInterface $event The event to set. If not set, the current event is returned.
     * 
     * @return DispatcherInterface | Eventable
     */
    public function event(DispatcherInterface $event = null)
    {
        // set event
        if ($event) {
            $this->event = $event;
            return $this;
        }

        // set if not exists
        if (!$this->event) {
            $this->event = new Dispatcher;
        }

        // get event
        return $this->event;
    }
}