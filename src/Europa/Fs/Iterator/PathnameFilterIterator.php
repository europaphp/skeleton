<?php

namespace Europa\Fs\Iterator;
use FilterIterator;
use Iterator;

/**
 * Filters the iterator based on path names and patterns.
 * 
 * @category Fs
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class PathnameFilterIterator extends FilterIterator
{
    /**
     * Patterns for inclusion.
     * 
     * @var array
     */
    private $include;
    
    /**
     * Patterns for exclusion.
     * 
     * @var array
     */
    private $exclude;
    
    /**
     * Sets up the pathname filter.
     * 
     * @param Iterator $iterator The iterator to filter.
     * @param array    $include  The inclusion patterns.
     * @param array    $exclude  The exclusion patterns.
     * 
     * @return PathnameFilterIterator
     */
    public function __construct(Iterator $iterator, array $include, array $exclude)
    {
        $this->include = $include;
        $this->exclude = $exclude;
        parent::__construct($iterator);
    }
    
    /**
     * Filters the iterator.
     * 
     * @return bool
     */
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
    
    /**
     * Performs matching using the specified pattern.
     * 
     * @param string $pattern The pattern to match.
     * 
     * @return bool
     */
    private function match($pattern)
    {
        return preg_match($pattern, $this->current()->getPathname());
    }
}