<?php

namespace Bootstrapper;
use Europa;
use Europa\Bootstrap\Provider;
use Europa\Config\Config;
use Europa\View\ViewScriptInterface;

/**
 * The default application bootstrapper. Works in conjunction with Europa\App\App and Europa\App\Container.
 * 
 * @category App
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Demo extends Provider
{
    /**
     * Bootstrapper configuration.
     * 
     * @var Config
     */
    private $config = [
        'errors.level' => E_ALL,
        'errors.show'  => true
    ];

    /**
     * Sets up the bootstrapper.
     * 
     * @param array $config Any custom configuration to modify the behavior of the bootstrapper.
     * 
     * @return Demo
     */
    public function __contruct(array $config = [])
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
        ini_set('display_errors', $this->config->errors->show ? 'on' : 'off');
        error_reporting($this->config->errors->level);
    }
}