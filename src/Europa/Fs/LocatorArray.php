<?php

namespace Europa\Fs;
use Europa\Iterator;

class LocatorArray
{
  private $locators;

  public function __construct(\Traversable $locators)
  {
    $this->locators = new Iterator\CallableIterator($locators);
  }

  public function __invoke($file)
  {
    foreach ($this->locators as $locator) {
      if ($real = $locator($file)) {
        return $real;
      }
    }
  }
}