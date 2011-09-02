<?php

namespace Europa\Fs\Iterator;

/**
 * Handles filtering with using a closure.
 * 
 * @category Fs
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class ClosureFilterIterator extends \FilterIterator
{
    /**
     * Constructs the filter.
     * 
     * @param \Iterator $iterator The iterator to filter.
     * @param array     $closures The closures to use for filtering.
     * 
     * @return \Europa\Fs\Iterator\ClosureFilterIterator
     */
    public function __construct(\Iterator $iterator, array $closures)
    {
        $this->closures = $closures;
        parent::__construct($iterator);
    }
    
    /**
     * Filters the iterator.
     * 
     * @return bool
     */
    public function accept()
    {
        foreach ($this->closures as $closure) {
            if (!$closure($this)) {
                return false;
            }
        }
        return true;
    }
}
