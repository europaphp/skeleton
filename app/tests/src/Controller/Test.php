<?php

namespace Controller;
use Europa\Controller\ControllerAbstract;
use Testes\Coverage\Coverage;
use Testes\Finder\Finder;

class Test extends ControllerAbstract
{
    /**
     * Runs all unit tests.
     */
    public function cli($test = null)
    {
        return $this->forward('all');
    }

    public function get()
    {
        return $this->forward('all');
    }

    public function all($test = 'Test', $untested = false, $analyze = null)
    {
        $analyzer = new Coverage;
        $analyzer->start();

        $suite = new Finder(__DIR__ . '/..', $test);
        $suite = $suite->run();

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