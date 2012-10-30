<?php

namespace Europa\Fs;

class LocatorArray
{
    private $locators = [];

    public function __invoke($file)
    {
        foreach ($this->locators as $locator) {
            if ($real = $locator($file)) {
                return $real;
            }
        }
    }

    public function add(callable $locator)
    {
        $this->locators[] = $locator;
        return $this;
    }
}