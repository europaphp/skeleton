<?php

namespace Europa\View;
use Europa\Fs\LocatorAwareInterface;

interface ScriptAwareInterface extends LocatorAwareInterface
{
  public function getScript();

  public function setScript($script);
}