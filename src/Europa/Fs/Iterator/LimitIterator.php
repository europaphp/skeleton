<?php

namespace Europa\Fs\Iterator;
use IteratorIterator;

class LimitIterator extends IteratorIterator
{
    private $offset = 0;

    private $limit = -1;

    private $count = 0;

    private $index = 0;

    public function setOffset($offset)
    {
        $this->offset = (int) $offset;
        return $this;
    }
    
    public function getOffset()
    {
        return $this->offset;
    }

    public function setLimit($limit)
    {
        $this->limit = (int) $limit;
        return $this;
    }
    
    public function getLimit()
    {
        return $this->limit;
    }
    
    public function current()
    {
        // continue on until the offset is met
        while ($this->index < $this->offset) {
            $this->next();
        }

        // keep track of the number of items
        $this->count++;

        // Return parent's current object.
        return parent::current();
    }

    public function next()
    {
        ++$this->index;
        return parent::next();
    }

    public function rewind()
    {
        $this->count = 0;
        $this->index = 0;
        parent::rewind();
    }

    public function valid()
    {
        $valid = parent::valid();
        if ($this->limit > -1) {
            return $valid && $this->limit > $this->count;
        }
        return $valid;
    }

    private function isDot()
    {
        return parent::current() && (parent::current()->getBasename() === '.' || parent::current()->getBasename() === '..');
    }
}