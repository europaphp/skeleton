<?php

namespace Europa\Config;
use Europa\Exception\Exception;

class Config implements ConfigInterface
{
  private $config = [];

  private $parent;

  public function __construct()
  {
    foreach (func_get_args() as $config) {
      $this->import($config);
    }
  }

  public function offsetSet($name, $value)
  {
    if ($value instanceof ConfigInterface) {
      $value->setParent($this);
    } else if (is_array($value) || is_object($value)) {
      $value = new static($value);
      $value->setParent($this);
    }

    if ($value instanceof ConfigInterface) {
      $value->setParent($this);
    }

    if ($name) {
      $this->config[$name] = $value;
    } else {
      $this->config[] = $value;
    }

    return $this;
  }

  public function offsetGet($name)
  {
    if (array_key_exists($name, $this->config)) {
      return $this->config[$name];
    }
  }

  public function offsetExists($name)
  {
    return array_key_exists($name, $this->config);
  }

  public function offsetUnset($name)
  {
    if (array_key_exists($name, $this->config)) {
      unset($this->config[$name]);
    }
  }

  public function count()
  {
    return count($this->config);
  }

  public function current()
  {
    return current($this->config);
  }

  public function key()
  {
    return key($this->config);
  }

  public function next()
  {
    next($this->config);
  }

  public function rewind()
  {
    reset($this->config);
  }

  public function valid()
  {
    return key($this->config) !== null;
  }

  public function serialize()
  {
    return serialize($this->config);
  }

  public function unserialize($data)
  {
    $this->config = unserialize($data);
  }

  public function import($config, callable $adapter = null)
  {
    if (is_string($config) && $file = $this->resolveFileFromString($config)) {
      $config  = file_get_contents($file);
      $adapter = $adapter ? $adapter : $this->resolveAdapterFromFile($file);
    }

    if ($adapter) {
      $config = $adapter($config);
    }

    if (is_array($config) || is_object($config)) {
      foreach ($config as $name => $value) {
        $this->offsetSet($name, $value);
      }
    }

    return $this;
  }

  public function export(callable $adapter = null)
  {
    $config = [];

    foreach ($this as $name => $value) {
      if ($value instanceof ConfigInterface) {
        $config[$name] = $value->export();
      } else {
        $config[$name] = $this->offsetGet($name);
      }
    }

    if ($adapter) {
      $config = $adapter($config);
    }

    return $config;
  }

  public function clear()
  {
    $this->config = [];
    return $this;
  }

  public function setParent(ConfigInterface $config)
  {
    $this->parent = $config;
    return $this;
  }

  public function getParent()
  {
    return $this->parent;
  }

  public function getRoot()
  {
    $config = $this->getParent();

    while ($parent = $config->getParent()) {
      $config = $parent;
    }

    return $config;
  }

  public function keys()
  {
    return array_keys($this->config);
  }

  public function values()
  {
    return array_values($this->config);
  }

  private function resolveFileFromString($config)
  {
    if (is_file($config)) {
      return $config;
    }
  }

  private function resolveAdapterFromFile($config)
  {
    $adapter = explode('.', $config);
    $adapter = end($adapter);
    $adapter = 'Europa\Config\Adapter\From\\' . ucfirst($adapter);

    if (!class_exists($adapter)) {
      Exception::toss('The import adapter "%s" autodetected from "%s" cannot be found.', $config, $adapter);
    }

    return new $adapter;
  }
}