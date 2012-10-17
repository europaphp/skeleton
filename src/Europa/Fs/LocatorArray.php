<?php

namespace Europa\Fs\Locator;

class LocatorArray
{
    private $locators = [];

    public function __invoke($file)
    {
        foreach ($this->locators as $locator) {
            if ($real = call_user_func($locator, $file)) {
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