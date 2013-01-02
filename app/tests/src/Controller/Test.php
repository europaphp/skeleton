<?php

namespace Controller;
use Europa\Controller\ControllerAbstract;
use Europa\Fs\Finder as FsFinder;
use Testes\Coverage\Coverage;
use Testes\Finder\Finder;

class Test extends ControllerAbstract
{
    const DEFAULT_ANALYZE_PATH = 'src/Europa';

    /**
     * Runs all unit tests.
     * 
     * @param string $test     The test suite to run. Defaults to "Test".
     * @param bool   $untested Whether or not to display untested LOC.
     * @param bool   $analyze  A specific path to analyze against the tests. Helpful for only analyzing against a specific component.
     */
    public function cli()
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
        $analyze  = realpath(__DIR__ . '/../../../../' . ($analyze ?: self::DEFAULT_ANALYZE_PATH));

        $finder = new FsFinder;
        $finder->is('/\.php$/');

        if (is_dir($analyze)) {
            $finder->in($analyze);
        } else {
            $finder->append($analyze);
        }

        foreach ($finder as $file) {
            $analyzer->addFile($file->getRealpath());
        }
        
        return [
            'percent'  => round(number_format($analyzer->getPercentTested(), 2), 2),
            'suite'    => $suite,
            'report'   => $analyzer,
            'untested' => $untested
        ];
    }
}