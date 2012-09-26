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
class FsIterator extends IteratorIterator
{
    /**
     * Returns specific directory / file instances.
     * 
     * @return Directory | File
     */
    public function current()
    {
        // directory object
        if (parent::current()->isDir()) {
            return new Directory(parent::current()->getPathname());
        }
        
        // file object
        return new File(parent::current()->getPathname());
    }
}