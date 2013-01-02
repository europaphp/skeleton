<?php

namespace Bootstrapper;
use Europa\Bootstrapper\BootstrapperAbstract;

class Main extends BootstrapperAbstract
{
    public function errorReporting($main)
    {
        error_reporting(E_ALL);
        ini_set('display_errors', $main['debug'] ? 'on' : 'off');
    }
}