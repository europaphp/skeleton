<?php

namespace Europa\Event;

/**
 * A basic interface for defining whether or not something is triggerable.
 * 
 * @category Events
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
interface EventInterface
{
    /**
     * Ensures the event manager knows what to do.
     * 
     * @param \Europa\Event\DataInterface $data The event data passed at the time of triggering.
     * 
     * @return bool
     */
    public function trigger(DataInterface $data);
}
