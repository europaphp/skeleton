<?php

namespace Test\All\Reflection;
use Europa\Reflection\CallableReflector;
use Testes\Test\UnitAbstract;

class CallableReflectorTest extends UnitAbstract
{
    public function testClosureDetection()
    {
        $callable = CallableReflector::detect(function () {});
        $this->assert($callable instanceof \ReflectionFunction, "A Closure was not detected");
    }
}