<?php

namespace Helper;
use Europa\Flash as Library;

class Flash implements \IteratorAggregate
{
    private $flash;
    
    public function __construct($name = Library::DEFAULT_NAME, $ns = Library::DEFAULT_NS)
    {
        $this->flash = new Library($name, $ns);
    }
    
    public function exists()
    {
        return $this->flash->exists();
    }
    
    public function getIterator()
    {
        return $this->flash->getIterator();
    }
}