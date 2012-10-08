<?php

namespace Europa\Bootstrapper;

/**
 * Base bootstrapper interface.
 * 
 * @category Boot
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
interface BootstrapperInterface
{
    /**
     * Bootstraps the app.
     * 
     * @return BootInterface
     */
    public function bootstrap();
}