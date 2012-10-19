<?php

namespace Test\All\Di;
use Europa\Di\ServiceLocator;
use Europa\Filter\ClassNameFilter;
use Exception;
use Test\Provider\Di\Test;
use Testes\Test\UnitAbstract;

class LocatorTest extends UnitAbstract
{
    public function getting()
    {
        $locator = new ServiceLocator;
        $locator->getFilter()->add(new ClassNameFilter(['prefix' => 'Test\Provider\Di\\']));

        $locator->args('Test\Provider\Di\Test', function($locator) {
            $this->assert($locator instanceof ServiceLocator, 'The service locator should have been specified as the first parameter to the "args" closure.');
            return ['test' => true];
        });

        $locator->args('Test\Provider\Di\TestAbstract', function($locator) {
            return ['testAbstract' => true];
        });

        $locator->args('Test\Provider\Di\TestInterface', function($locator) {
            return ['testInterface' => true];
        });

        $locator->args('Test\Provider\Di\TestTrait', function($locator) {
            return ['testTrait' => true];
        });

        $locator->args(function($locator) {
            return ['testAll' => true];
        });

        $locator->call('Test\Provider\Di\Test', function($locator, $test) {
            $this->assert($locator instanceof ServiceLocator, 'The service locator should have been specified as the first parameter to the "call" closure.');
            $this->assert($test instanceof Test, 'The located instance should have been specified as the second parameter to the "call" closure.');
            $test->test();
        });

        $locator->call('Test\Provider\Di\TestAbstract', function($locator, $test) {
            $test->testAbstract();
        });

        $locator->call('Test\Provider\Di\TestInterface', function($locator, $test) {
            $test->testInterface();
        });

        $locator->call('Test\Provider\Di\TestTrait', function($locator, $test) {
            $test->testTrait();
        });

        $locator->call(function($locator, $test) {
            $test->testAll();
        });

        $this->assert($locator->test->callTest, 'Service locator did not pass the "test" argument.');
        $this->assert($locator->test->callTestAbstract, 'Service locator did pass the "testAbstract" argument.');
        $this->assert($locator->test->callTestInterface, 'Service locator did not pass the "testInterface" argument.');
        $this->assert($locator->test->callTestTrait, 'Service locator did not pass the "testTrait" argument.');
        $this->assert($locator->test->callTestAll, 'Service locator did not pass the "testAll" argument.');

        $this->assert($locator->test->callTest, 'Service locator did not call "test()" method.');
        $this->assert($locator->test->callTestAbstract, 'Service locator did not call "testAbstract()" method.');
        $this->assert($locator->test->callTestInterface, 'Service locator did not call "testInterface()" method.');
        $this->assert($locator->test->callTestTrait, 'Service locator did not call "testTrait()" method.');
        $this->assert($locator->test->callTestAll, 'Service locator did not call "testAll()" method.');
    }

    public function gettingUnregistered()
    {
        $locator = new ServiceLocator;

        try {
            $locator->test;
            $this->asser(false, 'The test service should not have been located.');
        } catch (Exception $e) {

        }
    }

    public function settingFilter()
    {
        $locator = new ServiceLocator;
        $locator->setFilter(function($value) {
            return $value . 'test';
        });

        $this->assert($locator->getFilter()->__invoke('test') === 'testtest', 'Filter should have formatted the value.');

        try {
            $locator->setFilter('somebadvalue');
            $this->assert(false, 'The bad filter should not have been set.');
        } catch (Exception $e) {}
    }
}