<?php

namespace Europa\Fs\Iterator;

class PathnameFilterIterator extends \FilterIterator
{
    private $include;
    
    private $exclude;
    
    public function __construct(\Iterator $iterator, array $include, array $exclude)
    {
        $this->include = $include;
        $this->exclude = $exclude;
        parent::__construct($iterator);
    }
    
    public function accept()
    {
        if ($this->include) {
            $include = false;
            foreach ($this->include as $pattern) {
                if ($this->match($pattern)) {
                    $include = true;
                    break;
                }
            }
        } else {
            $include = true;
        }
        
        if ($this->exclude) {
            $exclude = false;
            foreach ($this->exclude as $pattern) {
                if ($this->match($pattern)) {
                    $exclude = true;
                    break;
                }
            }
        } else {
            $exclude = false;
        }
        
        return $include && !$exclude;
    }
    
    private function match($pattern)
    {
        return (bool) preg_match('#' . $pattern . '#', $this->getPathname());
    }
}