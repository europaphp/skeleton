<?php

namespace Testes\Renderer;
use Testes\RunableInterface;
use Testes\Suite\Suite;
use Testes\Test\TestInterface;

/**
 * Renders the test output in JUnit format.
 * 
 * @category UnitTesting
 * @package  Testes
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2010 Trey Shugart http://europaphp.org/license
 */
class Junit implements RendererInterface
{
    /**
     * Renders the test results.
     * 
     * @param ReporterInterface $test The test to output.
     * 
     * @return string
     */
    public function render(RunableInterface $test)
    {
        $dom = new \DOMDocument;
        $dom->formatOutput = true;
        
        $suitesElement = $dom->createElement('testsuites');
        $dom->appendChild($suitesElement);
        
        $test = $this->ensureSuite($test);
        
        $testClass = get_class($test);
        $packages  = explode('\\', $testClass);

        $suiteElement = $dom->createElement('testsuite');
        $suiteElement->setAttribute('package', $packages[0]);
        $suiteElement->setAttribute('id', 0);
        $suiteElement->setAttribute('name', end($packages));
        $suiteElement->setAttribute('errors', count($test->getExceptions()));
        $suiteElement->setAttribute('failures', count($test->getAssertions()->getFailed()));
        $suiteElement->setAttribute('timestamp', date('Y-m-d\TH:i:s', $test->getStartTime()));
        $suiteElement->setAttribute('hostname', gethostname());
        $suiteElement->setAttribute('tests', count($test->getTests()));
        $suiteElement->setAttribute('time', $test->getTime());
        $suitesElement->appendChild($suiteElement);
        
        foreach ($test->getTests() as $subtest) {
            $testcaseClass   = get_class($subtest);
            $testcaseElement = $dom->createElement('testcase');
            $testcaseElement->setAttribute('classname', get_class($subtest));
            $testcaseElement->setAttribute('name', basename($testcaseClass));
            $testcaseElement->setAttribute('time', $subtest->getTime());
            $suiteElement->appendChild($testcaseElement);
            
            foreach ($subtest->getAssertions()->getFailed() as $failed) {
                $failedElement = $dom->createElement('failure');
                $failedElement->setAttribute('message', $failed->getMessage());
                $failedElement->setAttribute('type', get_class($failed));
                $testcaseElement->appendChild($failedElement);
            }
            
            foreach ($subtest->getExceptions() as $exception) {
                $exceptionElement = $dom->createElement('error');
                $exceptionElement->setAttribute('message', $exception->getMessage());
                $exceptionElement->setAttribute('type', get_class($exception));
                $testcaseElement->appendChild($exceptionElement);
            }
        }
        
        // system out
        $cliRenderer      = new Cli;
        $systemOutElement = $dom->createElement('system-out');
        $systemOutCdata   = $dom->createCDATASection($cliRenderer->render($test));
        $systemOutElement->appendChild($systemOutCdata);
        $suiteElement->appendChild($systemOutElement);

        // system err
        $errors = array();
        foreach ($test->getExceptions() as $exception) {
            $errors[] = $exception->getMessage();
        }

        // only append errors if the exist
        if ($errors) {
            $systemErrElement = $dom->createElement('system-err');
            $systemErrCdata   = $dom->createCDATASection(implode(PHP_EOL, $errors));
            $systemErrElement->appendChild($systemErrCdata);
            $suiteElement->appendChild($systemErrElement);
        }
        
        return $dom->saveXML();
    }
    
    private function ensureSuite(RunableInterface $test)
    {
        if ($test instanceof TestInterface) {
            $temp = $test;
            $test = new Suite;
            $test->addTest($temp);
            unset($temp);
        }
        return $test;
    }
}
