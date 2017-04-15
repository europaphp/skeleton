<?php

namespace Europa\Fs;
use Europa\Common\InstanceIterator;
use Traversable;

class LocatorArray implements LocatorInterface
{
    private $locators;

    public function __construct(Traversable $locators)
    {
        $this->locators = new InstanceIterator('Europa\Fs\LocatorInterface', $locators);
    }

    public function locate($file)
    {
        foreach ($this->locators as $locator) {
            if ($real = $locator->locate($file)) {
                return $real;
            }
        }
    }
}