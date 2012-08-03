<?php

namespace Test\All\Filter;
use Europa\Filter\CallbackFilter;
use Testes\Test\UnitAbstract;

class CallbackFilterTest extends UnitAbstract
{
    public function closure()
    {
        $filter = new CallbackFilter(function($value) {
            return strtoupper($value);
        });
        $this->assert(
            $filter->filter('testIng') === 'TESTING',
            'Word was not filtered correctly.'
        );
    }
}