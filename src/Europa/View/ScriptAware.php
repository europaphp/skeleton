<?php

namespace Europa\View;
use Europa\Fs\LocatorAware;

trait ScriptAware
{
  use LocatorAware;

  private $script;

  public function getScript()
  {
    return $this->script;
  }

  public function setScript($script)
  {
    $this->script = str_replace('\\', '/', $script);
    $this->script = trim($this->script, './');
    return $this;
  }

  public function getLocatedScript()
  {
    if ($locator = $this->getLocator()) {
      return $locator($this->script);
    } elseif (is_file($this->script)) {
      return $this->script;
    }
  }
}