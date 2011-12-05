<?php

namespace Test\Fs;
use Europa\Fs\Locator\Locator;
use Europa\Fs\Locator\LocatorArray;
use Testes\Test\Test;

class LocatorArrayTest extends Test
{
    public function basePathUsage()
    {
        // path information for test class
        $base = dirname(__FILE__) . '/../../';
        $path = 'Provider';
        
        // locator instances
        $loc  = new LocatorArray;
        $loc1 = new Locator;
        $loc2 = new Locator($base);
        
        // add paths appropriate for test classes
        $loc1->addPath($base . $path);
        $loc2->addPath($path);
        
        // apply multiple locators
        $loc->add($loc1)->add($loc2);
        
        // locate
        $this->assert($loc->locate('Fs/TestClass'), 'The locator should have found the provider test class.');
    }
}
