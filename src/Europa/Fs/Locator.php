<?php

namespace Europa\Fs;
use ArrayIterator;
use Europa\Exception\Exception;
use Countable;
use IteratorAggregate;

class Locator implements Countable, IteratorAggregate, LocatorInterface
{
    private $cache = array();
    
    private $paths = array();

    private $root;

    public function __construct($root = null)
    {
        if ($root && !$this->root = realpath($root)) {
            Exception::toss('The root path "%s" does not exist.', $root);
        }
    }

    public function locate($file)
    {
        $file = str_replace('\\', '/', $file);

        if (isset($this->cache[$file])) {
            return $this->cache[$file];
        }

        foreach ($this->paths as $path) {
            if (is_file($real = realpath($path . '/' . $file))) {
                return $this->cache[$file] = $real;
            }
        }
    }
    
    public function addPath($path, $check = true)
    {
        $path = $this->root ? $this->root . '/' . $path : $path;
        
        if ($real = realpath($path)) {
            $this->paths[] = $real;
        } elseif ($check) {
            Exception::toss('The path "%s" does not exist.', $path);
        }

        return $this;
    }

    public function addPaths($paths, $check = true)
    {
        if (is_array($paths) || is_object($paths)) {
            foreach ($paths as $path) {
                $this->addPath($path, $check);
            }
        }

        return $this;
    }

    public function count()
    {
        return count($this->paths);
    }

    public function getIterator()
    {
        return new ArrayIterator($this->paths);
    }
}