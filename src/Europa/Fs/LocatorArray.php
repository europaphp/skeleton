<?php

namespace Europa\Fs;

class LocatorArray implements LocatorInterface
{
    private $locators = [];

    public function locate($file)
    {
        foreach ($this->locators as $locator) {
            if ($real = $locator->locate($file)) {
                return $real;
            }
        }
    }

    public function add(LocatorInterface $locator)
    {
        $this->locators[] = $locator;
        return $this;
    }
}