<?php

namespace Test\Filter;
use Europa\Filter\CamelCaseSplitFilter;
use Testes\Test;

class CamelCaseSplitFilterTest extends Test
{
    private $filter;
    
    public function setUp()
    {
        $this->filter = new CamelCaseSplitFilter;
    }
    
    public function basic()
    {
        $split = $this->filter->filter('testSplit');
        $this->assert(
            $split[0] === 'test' && $split[1] === 'Split',
            'Word was not split correctly.'
        );
    }
}
