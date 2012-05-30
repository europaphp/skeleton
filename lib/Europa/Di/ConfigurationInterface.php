<?php

namespace Europa\Di;

/**
 * Configuration interface.
 * 
 * @category Configurations
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
interface ConfigurationInterface
{
    /**
     * Configures the specified container.
     * 
     * @param \Europa\Di\Container $container The container to configure.
     * 
     * @return void
     */
    public function configure(Container $container);
}