<?php

namespace Europa\Fs;

class Locator
{
    private $cache = [];

    private $paths = [];

    private $root;

    public function __invoke($path)
    {
        $path = str_replace('\\', '/', $path);

        if (isset($this->cache[$path])) {
            return $this->cache[$path];
        }

        foreach ($this->paths as $path) {
            if (is_file($real = realpath($path . '/' . $path))) {
                return $this->cache[$path] = $real;
            }
        }
    }

    public function getRoot()
    {
        return $this->root;
    }

    public function setRoot($root)
    {
        if (!$this->root = realpath($root)) {
            throw new Exception\InvalidRootPath(sprintf('The root path "%s" does not exist.', $root));
        }

        return $this;
    }

    public function addPath($path, $check = true)
    {
        $path = $this->root ? $this->root . '/' . $path : $path;

        if ($real = realpath($path)) {
            $this->paths[] = $real;
        } elseif ($check) {
            throw new Exception\InvalidPath(sprintf('The path "%s" does not exist.', $path));
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

    public function getPaths()
    {
        return $this->paths;
    }
}