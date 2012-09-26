<?php

namespace Europa\Fs\Iterator;
use FilterIterator;
use Iterator;

/**
 * Filters the iterator based on path names and patterns.
 * 
 * @category Fs
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class DotFilterIterator extends FilterIterator
{
    /**
     * Filters the iterator.
     * 
     * @return bool
     */
    public function accept()
    {
        $path = $this->current()->getBasename();
        return $path !== '.' && $path !== '..';
    }
}