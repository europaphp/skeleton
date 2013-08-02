<?php

namespace Europa\Fs\Iterator;
use Europa\Fs\Directory;
use Europa\Fs\File;
use IteratorIterator;

class FsIterator extends IteratorIterator
{
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