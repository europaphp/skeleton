<?php

namespace Europa\Fs;

trait LocatorAware
{
    private $locator;

    public function getLocator()
    {
        return $this->locator;
    }

    public function setLocator(callable $locator)
    {
        $this->locator = $locator;
        return $this;
    }
}