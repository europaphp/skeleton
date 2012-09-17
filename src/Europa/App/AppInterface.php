<?php

namespace Europa\App;

/**
 * Represents a basic implementation of a dispatcher.
 *
 * @category Dispatcher
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
interface AppInterface
{
    /**
     * Runs the application.
     * 
     * @return AppInterface
     */
    public function run();
}