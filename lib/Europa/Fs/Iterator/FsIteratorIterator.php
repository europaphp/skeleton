<?php

namespace Europa\Fs\Iterator;
use Europa\Fs\Directory;
use Europa\Fs\File;

/**
 * An iterator that represents a collection of \Europa\Fs\Directory and \Europa\Fs\File instances.
 * 
 * @category Fs
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class FsIteratorIterator extends \IteratorIterator
{
    /**
     * The pathname of the last item in the iterator that was accessed.
     * 
     * @var string
     */
    private $last;

    /**
     * The offset.
     * 
     * @var int
     */
    private $offset = 0;

    /**
     * The limit.
     * 
     * @var int
     */
    private $limit = -1;

    /**
     * The current number of items that have been returned.
     * 
     * @var int
     */
    private $count = 0;

    /**
     * The internal index.
     * 
     * @var int
     */
    private $index = 0;

    /**
     * Offsets the result set.
     * 
     * @param int $offset The offset to use.
     * 
     * @return \Europa\Fs\Iterator\FsIterator
     */
    public function setOffset($offset)
    {
        $this->offset = (int) $offset;
        return $this;
    }

    /**
     * Limits the result set.
     * 
     * @param int $limit The limit to use.
     * 
     * @return \Europa\Fs\Iterator\FsIterator
     */
    public function setLimit($limit)
    {
        $this->limit = (int) $limit;
        return $this;
    }
    
    /**
     * Returns specific directory/file instances. Fixes an issue with PHP's FilterIterator that causes the first item
     * to be repeated.
     * 
     * @return Directory|File
     */
    public function current()
    {
        // make sure it's not the same as the last one
        // fixes an issue in PHP's FilterIterator
        if (parent::current()->getPathName() === $this->last) {
            $this->next();
        }
        
        // mark the last one so we can fix the PHP issue
        $this->last = parent::current()->getPathname();

        // continue on until the offset is met
        while ($this->index < $this->offset) {
            $this->next();
        }

        // keep track of the number of items
        $this->count++;
        
        // directory object
        if (parent::current()->isDir()) {
            return new Directory(parent::current()->getPathname());
        }
        
        // file object
        return new File(parent::current()->getPathname());
    }

    /**
     * Overridden to increment the internal index.
     * 
     * @return void
     */
    public function next()
    {
        ++$this->index;
        return parent::next();
    }

    /**
     * Overridden to reset the count.
     * 
     * @return void
     */
    public function rewind()
    {
        $this->count = 0;
        $this->index = 0;
        return parent::rewind();
    }

    /**
     * Overridden to allow the limiting of results.
     * 
     * @return bool
     */
    public function valid()
    {
        $valid = parent::valid();
        if ($this->limit > -1) {
            return $valid && $this->limit > $this->count;
        }
        return $valid;
    }
}
