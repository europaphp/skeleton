<?php

namespace Europa\Boot;

/**
 * Base bootstrapper interface.
 * 
 * @category App
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
interface BootInterface
{
    /**
     * Bootstraps the app.
     * 
     * @return BootInterface
     */
    public function boot();
}