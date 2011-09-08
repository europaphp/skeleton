<?php

namespace Testes\Output;
use Testes\AssertionInterface;
use Testes\OutputInterface;
use Testes\TestInterface;

/**
 * Renders the test output in JUnit format.
 * 
 * @category UnitTesting
 * @package  Testes
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2010 Trey Shugart http://europaphp.org/license
 */
class Junit implements OutputInterface
{
    /**
     * Renders the test results.
     * 
     * @param TestInterface $test The test to output.
     * 
     * @return string
     */
    public function render(TestInterface $test)
    {
        $dom = new \DOMDocument;
        $dom->formatOutput = true;
        
        $suitesElement = $dom->createElement('testsuites');
        $dom->appendChild($suitesElement);
        
        $testClass    = get_class($test);
        $suiteElement = $dom->createElement('testsuite');
        $suiteElement->setAttribute('package', dirname($testClass));
        $suiteElement->setAttribute('id', 0);
        $suiteElement->setAttribute('name', basename($testClass));
        $suiteElement->setAttribute('errors', count($test->getExceptions()));
        $suiteElement->setAttribute('failures', count($test->getFailedAssertions()));
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
            
            foreach ($subtest->getFailedAssertions() as $failed) {
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
        
        $cliRenderer      = new Cli;
        $systemOutElement = $dom->createElement('system-out');
        $systemOutCdata   = $dom->createCDATASection($cliRenderer->renderAssertions($test));
        $systemErrElement = $dom->createElement('system-err');
        $systemErrCdata   = $dom->createCDATASection($cliRenderer->renderExceptions($test));
        $systemOutElement->appendChild($systemOutCdata);
        $systemErrElement->appendChild($systemErrCdata);
        $suiteElement->appendChild($systemOutElement);
        $suiteElement->appendChild($systemErrElement);
        
        return $dom->saveXML();
    }
}
