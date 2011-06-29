<?php

namespace Europa\Fs\Iterator;

class ClosureFilterIterator extends \FilterIterator
{
    public function __construct(\Iterator $iterator, array $closure)
    {
        $this->closure = $closure;
        parent::__construct($iterator);
    }
    
    public function accept()
    {
        foreach ($this->closure as $closure) {
            if (!$closure($this)) {
                return false;
            }
        }
        return true;
    }
}