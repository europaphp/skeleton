<?php

namespace Europa\Filter;
use Europa\Common\CallableIterator;
use Traversable;

class FilterArray
{
    private $filters;

    public function __construct(Traversable $filters)
    {
        $this->filters = new CallableIterator($filters);
    }

    public function __invoke($value)
    {
        foreach ($this->filters as $filter) {
            $value = $filter($value);
        }

        return $value;
    }
}