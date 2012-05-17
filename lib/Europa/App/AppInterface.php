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
     * Encompasses the whole dispatching process. Instantiates the controller, actions it and outputs the rendered view.
     * 
     * @return void
     */
    public function run();
}