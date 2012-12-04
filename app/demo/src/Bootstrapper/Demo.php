<?php

namespace Bootstrapper;
use Europa\Bootstrapper\BootstrapperAbstract;

/**
 * The demo bootstrapper.
 * 
 * @category App
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Demo extends BootstrapperAbstract
{
    /**
     * Sets whether or not errors should be displayed and the desired error level.
     * 
     * @return void
     */
    public function errorReporting($container)
    {
        error_reporting(E_ALL);
        ini_set('display_errors', $container->config->debug ? 'on' : 'off');
    }
}