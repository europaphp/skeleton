<?php

namespace Test\Provider\Di;

class TestAbstract implements TestInterface
{
    public $callTestAbstract = false;

    public $callTestInterface = false;

    public function testAbstract()
    {
        $this->callTestAbstract = true;
    }

    public function testInterface()
    {
        $this->callTestInterface = true;
    }
}