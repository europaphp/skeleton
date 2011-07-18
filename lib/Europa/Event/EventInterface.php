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
     * @return bool
     */
    public function trigger(array $data = array());
}