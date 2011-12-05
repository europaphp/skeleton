<?php

namespace Test\Filter;
use Europa\Filter\CallbackFilter;
use Testes\Test\Test;

class CallbackFilterTest extends Test
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
