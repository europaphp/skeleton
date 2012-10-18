<?php

namespace Test\Provider\Di;

trait TestTrait
{
    public $argsTestTrait = false;

    public $callTestTrait = false;

    public function testTrait()
    {
        $this->callTestTrait = true;
    }
}