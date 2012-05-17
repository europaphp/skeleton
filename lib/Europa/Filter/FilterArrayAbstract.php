<?php

namespace Europa\Filter;

/**
 * Filters a value using more than one filter.
 * 
 * @category Filters
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
abstract class FilterArrayAbstract implements FilterInterface
{
    /**
     * The array of filters to use.
     * 
     * @var array
     */
    private $filters = [];
    
    /**
     * Constructs a new array filter.
     * 
     * @param array $filters The filters to add.
     * 
     * @return FilterArrayAbstract
     */
    public function __construct($filters = [])
    {
        $this->addAll($filters);
    }

    /**
     * Adds a filter.
     * 
     * @param FilterInterface $filter The filter to add.
     * 
     * @return FilterArrayAbstract
     */
    public function add(FilterInterface $filter)
    {
        $this->filters[] = $filter;
        return $this;
    }
    
    /**
     * Adds more than one filter.
     * 
     * @param array $filters The filters to add.
     * 
     * @return FilterArrayAbstract
     */
    public function addAll(array $filters)
    {
        foreach ($filters as $filter) {
            $this->add($filter);
        }
        return $this;
    }
    
    /**
     * Removes all filters.
     * 
     * @return FilterArrayAbstract
     */
    public function removeAll()
    {
        $this->filters = [];
        return $this;
    }
    
    /**
     * Returns all filters on the filter array.
     * 
     * @return array
     */
    public function getAll()
    {
        return $this->filters;
    }
}