<?php

namespace Europa\Fs\Locator;

/**
 * Allows multiple different locators to be used as one.
 * 
 * @category Fs
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class CascadingLocator implements LocatorInterface
{
    /**
     * The locators applied to the cascading locator.
     * 
     * @var array
     */
    private $locators = array();
    
    /**
     * Appends a locator to the list of locators to use.
     * 
     * @param \Europa\Fs\Locator\LocatorInterface $locator The locator to append.
     * 
     * @return \Europa\Fs\Locator\CascadingLocator
     */
    public function addLocator(LocatorInterface $locator)
    {
        $this->locators[] = $locator;
        return $this;
    }
    
    /**
     * Goes through each locator and returns the first path found.
     * 
     * @param string $file The file to locate amongst the applied locators.
     * 
     * @return string|false
     */
    public function locate($file)
    {
        foreach ($this->locators as $locator) {
            if ($found = $locator->locate($file)) {
                return $found;
            }
        }
        return false;
    }
}
