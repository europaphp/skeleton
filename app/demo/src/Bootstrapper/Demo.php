<?php

namespace Bootstrapper;
use Europa\Bootstrapper\BootstrapperAbstract;
use Europa\Config\Config;

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
     * Bootstrapper configuration.
     * 
     * @var Config
     */
    private $config = [
        'error.level' => E_ALL,
        'error.show'  => true
    ];

    /**
     * Sets up the bootstrapper.
     * 
     * @param array $config Any custom configuration to modify the behavior of the bootstrapper.
     * 
     * @return Demo
     */
    public function __construct(array $config = [])
    {
        $this->config = new Config($this->config, $config);
    }

    /**
     * Sets whether or not errors should be displayed and the desired error level.
     * 
     * @return void
     */
    public function errorReporting()
    {
        ini_set('display_errors', $this->config->error->show ? 'on' : 'off');
        error_reporting($this->config->error->level);
    }
}