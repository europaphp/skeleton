<?php

namespace Controller;
use Europa\Controller\RestController;
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
class Test extends RestController
{
    /**
     * The default request method.
     * 
     * @param string $test         The test to run.
     * @param bool   $showUntested Whether or not to show untested lines of code.
     * 
     * @return void
     */
    public function all($test = 'Test', $showUntested = false)
    {
        // so we can get some helpful data
        $analyzer = new Coverage;
        $analyzer->start();
        
        // re-run the test b/c two xdebug functions can't be run at the same time
        $suite = new Finder(__DIR__ . '/../Test/Test');
        $suite = $suite->run();
        
        // gather useful data
        $analyzer = $analyzer->stop();
        $analyzer->addDirectory(__DIR__ . '/../../../lib/Europa');
        $analyzer->is('\.php$');
        
        return [
            'percent'      => round(number_format($analyzer->getPercentTested(), 2), 2),
            'suite'        => $suite,
            'report'       => $analyzer,
            'showUntested' => $showUntested
        ];
    }
}