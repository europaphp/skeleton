<?php

namespace Europa\Fs;

trait LocatorAware
{
    private $locator;

    public function setLocator(LocatorInterface $locator)
    {
        $this->locator = $locator;
        return $this;
    }
    
    public function getLocator()
    {
        return $this->locator;
    }
}