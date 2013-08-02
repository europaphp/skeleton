<?php

namespace Europa\View\Helper;
use Europa\View\Php;

class Capture
{
  private $cache = array();

  public function start()
  {
    ob_start();
    return $this;
  }

  public function end($name)
  {
    $this->cache[$name] = ob_get_clean();
    return $this;
  }

  public function get($name)
  {
    if (isset($this->cache[$name])) {
      return $this->cache[$name];
    }

    return '';
  }
}