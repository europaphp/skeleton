<?php

namespace Europa\Filter;
use ArrayIterator;
use IteratorAggregate;
use UnexpectedValueException;

/**
 * Filters a value using more than one filter.
 * 
 * @category Filters
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
abstract class FilterArrayAbstract implements IteratorAggregate
{
    /**
     * The array of filters to use.
     * 
     * @var array
     */
    private $filters = [];

    /**
     * Adds a filter.
     * 
     * @param FilterInterface $filter The filter to add.
     * 
     * @return FilterArrayAbstract
     */
    public function add($filter)
    {
        if (!is_callable($filter)) {
            throw new UnexpectedValueException('The specified filter is not callable.');
        }

        $this->filters[] = $filter;

        return $this;
    }

    /**
     * Removes all of the filters.
     * 
     * @return FilterArrayAbstract
     */
    public function clear()
    {
        $this->filters = [];
        return $this;
    }
    
    /**
     * Returns all filters on the filter array.
     * 
     * @return array
     */
    public function getIterator()
    {
        return new ArrayIterator($this->filters);
    }
}