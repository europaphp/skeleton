<?php

namespace Europa\Fs;
use ArrayIterator;
use Europa\Exception\Exception;
use IteratorAggregate;

class Locator implements IteratorAggregate
{
    private $cache = array();
    
    private $paths = array();

    private $root;

    public function __construct($root = null)
    {
        if (func_num_args()) {
            $this->setRoot($root);
        }
    }

    public function __invoke($file)
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

    public function setRoot($root)
    {
        if (!$this->root = realpath($root)) {
            Exception::toss('The root path "%s" does not exist.', $root);
        }

        return $this;
    }

    public function getRoot()
    {
        return $this->root;
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

    public function getIterator()
    {
        return new ArrayIterator($this->paths);
    }
}