<?php

namespace Europa\Fs;

interface LocatorAwareInterface
{
  public function getLocator();

  public function setLocator(callable $locator);
}