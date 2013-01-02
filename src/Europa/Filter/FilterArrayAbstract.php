<?php

namespace Europa\Filter;
use ArrayIterator;
use IteratorAggregate;
use UnexpectedValueException;

abstract class FilterArrayAbstract implements IteratorAggregate
{
    private $filters = [];

    public function __construct($config = [])
    {
        foreach ($config as $filter => $filterConfig) {
            $this->add(new $filter($filterConfig));
        }
    }

    public function add(callable $filter)
    {
        $this->filters[] = $filter;
        return $this;
    }

    public function clear()
    {
        $this->filters = [];
        return $this;
    }
    
    public function getIterator()
    {
        return new ArrayIterator($this->filters);
    }
}