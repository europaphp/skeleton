<?php

namespace Europa\Fs\Iterator;
use FilterIterator;
use Iterator;

class DotFilterIterator extends FilterIterator
{
    public function accept()
    {
        $path = $this->current()->getBasename();
        return $path !== '.' && $path !== '..';
    }
}