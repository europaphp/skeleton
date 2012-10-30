<?php

namespace Europa\Fs\Iterator;
use FilterIterator;
use Iterator;

/**
 * Handles filtering with using a closure.
 * 
 * @category Fs
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class CallbackFilterIterator extends FilterIterator
{
    /**
     * The fitlers passed in.
     * 
     * @var array
     */
    private $filters = [];
    
    /**
     * Constructs the filter.
     * 
     * @param Iterator $iterator The iterator to filter.
     * @param array    $filters  The filters to use for filtering.
     * 
     * @return CallbackFilterIterator
     */
    public function __construct(Iterator $iterator, array $filters)
    {
        $this->filters = $filters;
        parent::__construct($iterator);
    }
    
    /**
     * Filters the iterator.
     * 
     * @return bool
     */
    public function accept()
    {
        foreach ($this->filters as $filter) {
            if ($filter($this->current()) === false) {
                return false;
            }
        }
        return true;
    }
}