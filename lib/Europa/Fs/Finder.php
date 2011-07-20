<?php

namespace Europa\Fs;

/**
 * Handles file locating based on cascading custom paths.
 * 
 * @category Fs
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Finder implements \IteratorAggregate
{
    /**
     * The directories to search in.
     * 
     * @var array
     */
    private $dirs = array();
    
    /**
     * The patterns for inclusion.
     * 
     * @var array
     */
    private $is = array();
    
    /**
     * The patterns for exclusion.
     * 
     * @var array
     */
    private $not = array();
    
    /**
     * The custom filters to apply.
     * 
     * @var array
     */
    private $filters = array();
    
    private $depth = -1;
    
    /**
     * Iterators to prepend to the current finder listing before applying filters.
     * 
     * @var array
     */
    private $prepend = array();
    
    /**
     * Iterators to append to the current finder listing after applying filters.
     * 
     * @var array
     */
    private $append = array();
    
    /**
     * Returns the iterator for the implementation of \IteratorAggregate.
     * 
     * @return \Iterator
     */
    public function getIterator()
    {
        $pre = new \AppendIterator;
        foreach ($this->prepend as $prepend) {
            $pre->append($this->normalizeTraversable($prepend));
        }
        foreach ($this->dirs as $dir) {
            $pre->append($this->getRecursiveIterator($dir));
        }
        
        $post = new \AppendIterator;
        $post->append($this->applyFilters($pre));
        foreach ($this->append as $append) {
            $post->append($this->normalizeTraversable($append));
        }
        return new Iterator\FsIteratorIterator($post);
    }
    
    public function prepend($prepend)
    {
        $this->prepend[] = $prepend;
        return $this;
    }
    
    public function append($append)
    {
        $this->append[] = $append;
        return $this;
    }
    
    /**
     * Includes files by their name.
     * 
     * @param string $pattern The pattern to match.
     * 
     * @return Finder
     */
    public function is($pattern)
    {
        $this->is[] = $pattern;
        return $this;
    }
    
    /**
     * Excludes files by their name.
     * 
     * @param string $pattern The pattern to match.
     * 
     * @return Finder
     */
    public function not($pattern)
    {
        $this->not[] = $pattern;
        return $this;
    }
    
    /**
     * Filters out all directories and leaves only files.
     * 
     * @return Finder
     */
    public function files()
    {
        $this->filter(function($item) {
            return $item->current()->isFile();
        });
        return $this;
    }
    
    /**
     * Filters out all files and leaves only directories.
     * 
     * @return Finder
     */
    public function directories()
    {
        $this->filter(function($item) {
            return $item->current()->isDir();
        });
        return $this;
    }
    
    /**
     * Applies a custom filter to the listing.
     * 
     * @param \Closure $filter The custom filter.
     * 
     * @return Finder
     */
    public function filter(\Closure $filter)
    {
        $this->filters[] = $filter;
        return $this;
    }
    
    /**
     * Adds a path to search in.
     * 
     * @param string $path The path to add to the list of search paths.
     * 
     * @return Finder
     */
    public function in($path)
    {
        if ($real = realpath($path)) {
            $this->dirs[] = $real;
        }
        return $this;
    }
    
    /**
     * Only seeks to the specified hierarchical depth.
     * 
     * @return Finder
     */
    public function depth($depth = null)
    {
        if (is_null($depth) || $depth < -1) {
            $depth = -1;
        }
        $this->depth = $depth;
        return $this;
    }
    
    /**
     * Does the actual finding and matching for the specified directory.
     * 
     * @param string $dir The directory to search in.
     * 
     * @return \Iterator
     */
    private function applyFilters(\Iterator $iterator)
    {
        $iterator = new Iterator\PathnameFilterIterator($iterator, $this->is, $this->not);
        $iterator = new Iterator\ClosureFilterIterator($iterator, $this->filters);
        return $iterator;
    }
    
    /**
     * Returns the recursive iterator.
     * 
     * @param string $dir The directory to get the recursive iterator for.
     * 
     * @return \RecursiveIteratorIterator
     */
    private function getRecursiveIterator($dir)
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        $iterator->setMaxDepth($this->depth);
        return $iterator;
    }
    
    /**
     * Normalizes a traversable item into an iterator.
     * 
     * @param mixed $iterator The iterator to normalize.
     * 
     * @return \Iterator
     */
    private function normalizeTraversable($iterator)
    {
        if ($iterator instanceof \IteratorAggregate) {
            $iterator = $iterator->getIterator();
        } elseif ($iterator instanceof \Iterator) {
            $iterator = $iterator;
        } elseif ($iterator instanceof \Traversable || is_array($iterator)) {
            $traversable = new \ArrayIterator();
            foreach ($iterator as $item) {
                $traversable->append($item instanceof \SplFileInfo ? $item : new \SplFileInfo($item));
            }
            $iterator = $traversable;
        } else {
            throw new \InvalidArgumentException('The specified traversable item cannot be applied to the finder.');
        }
        return $iterator;
    }
}