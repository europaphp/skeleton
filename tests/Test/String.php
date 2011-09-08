<?php

namespace Test;
use Europa\StringObject as StringObject;
use Testes\Test;

class String extends Test
{
    public function testClassFormatting()
    {
        $str = StringObject::create('my_awesome\class\name9#(U($(@#$**(89))))')->toClass();
        $this->assert($str->__toString() === '\My\Awesome\Class\Name9U89', 'Class not formatted properly.');
        
        $str = StringObject::create('\My\Test\TestClass')->toClass();
        $this->assert($str->__toString() === '\My\Test\TestClass', 'Class not formatted properly.');
    }
    
    public function testMethodFormatting()
    {
        $str = StringObject::create('myNormalMethodName')->toMethod();
        $this->assert($str->__toString() === 'myNormalMethodName', 'Method no formatted properly.');
        
        $str = StringObject::create('my_Normal_Method_Name')->toMethod();
        $this->assert($str->__toString() === 'myNormalMethodName', 'Method no formatted properly.');
        
        $str = StringObject::create('my_normal_method_name')->toMethod();
        $this->assert($str->__toString() === 'myNormalMethodName', 'Method no formatted properly.');
    }
}
