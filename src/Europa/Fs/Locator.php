<?php

namespace Europa\Fs;

class Locator
{
  private $check = false;

  private $cache = [];

  private $paths = [];

  private $root;

  public function __invoke($path)
  {
    $path = str_replace('\\', '/', $path);

    if (isset($this->cache[$path])) {
      return $this->cache[$path];
    }

    foreach ($this->paths as $parts) {
      if ($real = realpath($parts[0] . '/' . $path . ($parts[1] ? '.' . $parts[1] : ''))) {
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
      throw new Exception\InvalidRootPath(['path' => $root]);
    }

    return $this;
  }

  public function addPath($path, $suffix = null)
  {
    $path = $this->root ? $this->root . '/' . $path : $path;

    if ($real = realpath($path)) {
      $this->paths[] = [$real, $suffix];
    } elseif ($this->check) {
      throw new Exception\InvalidPath(['path' => $path]);
    }

    return $this;
  }

  public function addPaths(array $paths)
  {
    foreach ($paths as $parts) {
      if (!is_array($parts)) {
        $parts = [$parts, null];
      }

      $this->addPath($parts[0], $parts[1]);
    }

    return $this;
  }

  public function getPaths()
  {
    return $this->paths;
  }

  public function getCheck()
  {
    return $this->check;
  }

  public function setCheck($switch = true)
  {
    $this->check = $check ? true : false;
    return $this;
  }
}