<?php

namespace Europa\Fs\Iterator;
use Europa\Fs\Directory;
use Europa\Fs\File;

class FsIteratorIterator extends \IteratorIterator
{
    private $last;
    
    public function current()
    {
        if (parent::current()->isDir()) {
            return new Directory(parent::current()->getPathname());
        }
        return new File(parent::current()->getPathname());
    }
}