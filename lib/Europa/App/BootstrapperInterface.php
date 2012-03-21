<?php

namespace Europa\App;

/**
 * Base bootstrapper interface.
 * 
 * @category App
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
interface BootstrapperInterface
{
    /**
     * Bootstraps the app.
     * 
     * @return BootstrapperInterface
     */
    public function boot();
}