<?php

namespace Controller;
use Europa\App\App;
use Europa\Controller\ControllerAbstract;
use Europa\Fs\Finder as FsFinder;
use Testes\Coverage\Coverage;
use Testes\Finder\Finder;
use Testes\Suite\Suite;

class Test extends ControllerAbstract
{
    /**
     * Runs all unit tests.
     * 
     * @param string $test     The test suite to run. Defaults to "Test".
     * @param bool   $untested Whether or not to display untested LOC.
     * @param string $analyze  The path, relative to the base path, to analyze. If not specified, all modules are analyzed.
     */
    public function cli()
    {
        return $this->forward('all');
    }

    public function get()
    {
        return $this->forward('all');
    }

    public function all($test = null, $untested = false, $analyze = null)
    {
        $suite  = new Suite;
        $cover  = new Coverage;
        $finder = new FsFinder;

        $finder->is('/\.php$/');
        $finder->in(__DIR__ . '/../../../../' . $analyze);
        $cover->start();

        foreach (App::get() as $name => $module) {
            $path  = __DIR__ . '/../../../../';
            $path .= $name . '/';
            $path .= App::get()['europaphp/tests']['path'];

            $suite->addTests(new Finder($path, $test));
        }

        $suite->run();

        $analyzer = $cover->stop();

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