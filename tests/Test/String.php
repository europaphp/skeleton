<?php

namespace Test;
use Europa\String as StringObject;
use Europa\Unit\Test\Test;

class String extends Test
{
    public function testClassFormatting()
    {
        $string = StringObject::create('my_awesome\class\name9#(U($(@#$**(89))))')->toClass()->__toString();
        $this->assert(
            $string === '\MyAwesome\Class\Name9U89',
            '\Europa\String::toClass() did not correctly format a string.'
        );
    }
}