<?php

namespace Test\All\Filter;
use Europa\Filter\ToStringFilter;
use Testes\Test\UnitAbstract;

class ToStringFilterTest extends UnitAbstract
{
    private $filter;
    
    public function setUp()
    {
        $this->filter = new ToStringFilter;
    }
    
    public function floats()
    {
        $float = $this->filter->__invoke(1.0);
        $this->assert($float === '1');
    }

    public function ints()
    {
        $int = $this->filter->__invoke(1);
        $this->assert($int === '1');
    }

    public function arrays()
    {
        $array = $this->filter->__invoke(['value1', 'value2']);
        $this->assert($array === 'a:2:{i:0;s:6:"value1";i:1;s:6:"value2";}');
    }

    public function objects()
    {
        $object       = new \stdClass;
        $object->key1 = 'value1';
        $object->key2 = 'value2';

        $object = $this->filter->__invoke($object);
        $this->assert('O:8:"stdClass":2:{s:4:"key1";s:6:"value1";s:4:"key2";s:6:"value2";}');
    }
}