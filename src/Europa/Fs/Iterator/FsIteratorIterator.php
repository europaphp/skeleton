<?php

namespace Europa\Fs\Iterator;
use Europa\Fs\Directory;
use Europa\Fs\File;
use IteratorIterator;

/**
 * An iterator that represents a collection of Europa\Fs\Directory and Europa\Fs\File instances.
 * 
 * @category Fs
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class FsIteratorIterator extends IteratorIterator
{
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
     * @return FsIterator
     */
    public function setOffset($offset)
    {
        $this->offset = (int) $offset;
        return $this;
    }
    
    /**
     * Returns the offset.
     * 
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * Limits the result set.
     * 
     * @param int $limit The limit to use.
     * 
     * @return FsIterator
     */
    public function setLimit($limit)
    {
        $this->limit = (int) $limit;
        return $this;
    }
    
    /**
     * Returns the limit.
     * 
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }
    
    /**
     * Returns specific directory / file instances.
     * 
     * @return Directory | File
     */
    public function current()
    {
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
        parent::rewind();

        // move past the current and parent directories without incrementing the index
        while ($this->isDot()) {
            parent::next();
        }
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

    /**
     * Returns whether or not the current item is a dot.
     * 
     * @return bool
     */
    private function isDot()
    {
        return parent::current() && (parent::current()->getBasename() === '.' || parent::current()->getBasename() === '..');
    }
}