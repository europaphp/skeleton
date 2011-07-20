<?php

namespace Europa\Fs\Iterator;
use Europa\Fs\Directory;
use Europa\Fs\File;

class FsIteratorIterator extends \IteratorIterator
{
    /**
     * The pathname of the last item in the iterator that was accessed.
     * 
     * @var string
     */
    private $last;
    
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
        
        // mark the last one
        $this->last = parent::current()->getPathname();
        
        // directory object
        if (parent::current()->isDir()) {
            return new Directory(parent::current()->getPathname());
        }
        
        // file object
        return new File(parent::current()->getPathname());
    }
}