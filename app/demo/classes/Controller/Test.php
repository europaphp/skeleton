<?php

namespace Controller;
use Europa\Controller\ControllerAbstract;
use Testes\Coverage\Coverage;
use Testes\Finder\Finder;

/**
 * A default controller for a base implementation.
 * 
 * @category Controllers
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Test extends ControllerAbstract
{
    /**
     * Runs tests.
     * 
     * @param string $test     The test to run.
     * @param bool   $untested Whether or not to show untested lines of code.
     * 
     * @return void
     */
    public function action($test = 'Test', $untested = false, $analyze = null)
    {
        // start covering tests
        $analyzer = new Coverage;
        $analyzer->start();
        
        // run tests
        $suite = new Finder(__DIR__ . '/..', $test);
        $suite = $suite->run();
        
        // stop covering and analyze results
        $analyzer = $analyzer->stop();
        $analyzer->addDirectory(__DIR__ . '/../../../../' . ($analyze ? ltrim($analyze, '\\/.') : 'src/Europa'));
        $analyzer->is('\.php$');
        
        return [
            'percent'  => round(number_format($analyzer->getPercentTested(), 2), 2),
            'suite'    => $suite,
            'report'   => $analyzer,
            'untested' => $untested
        ];
    }
}