<?php

namespace Europa\Fs;
use Europa\Iterator;

class LocatorArray
{
    private $locators;

    public function __construct(\Traversable $locators)
    {
        $this->locators = new Iterator\InstanceIterator($locators, 'Europa\Fs\LocatorInterface');
    }

    public function __invoke($file)
    {
        foreach ($this->locators as $locator) {
            if ($real = $locator->locate($file)) {
                return $real;
            }
        }
    }
}