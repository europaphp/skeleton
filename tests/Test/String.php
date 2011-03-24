<?php

use Europa\String;

class Test_String extends Testes_Test
{
    public function testClassFormatting()
    {
        $string = String::create('my_awesome\class\name9#(U($(@#$**(89))))')->toClass()->__toString();
        $this->assert(
            $string === '\MyAwesome\Class\Name9U89',
            '\Europa\String::toClass() did not correctly format a string.'
        );
    }
}