<?php

namespace Test\Provider\Di;

class Test extends TestAbstract
{
    use TestTrait;

    public $argsTest = false;

    public $argsTestAbstract = false;

    public $argsTestInterface = false;

    public $argsTestAll = false;

    public $callTest = false;

    public $callTestAll = false;

    public function __construct($test, $testAbstract, $testInterface, $testTrait, $testAll)
    {
        $this->argsTest          = $test;
        $this->argsTestAbstract  = $testAbstract;
        $this->argsTestInterface = $testInterface;
        $this->argsTestTrait     = $testTrait;
        $this->argsTestAll       = $testAll;
    }

    public function test()
    {
        $this->callTest = true;
    }

    public function testAll()
    {
        $this->callTestAll = true;
    }
}