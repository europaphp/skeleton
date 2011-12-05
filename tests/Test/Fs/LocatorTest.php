<?php

namespace Test\Fs;
use Europa\Fs\Locator\Locator;
use Testes\Test\Test;

class LocatorTest extends Test
{
    public function basePathUsage()
    {
        $loc = new Locator(dirname(__FILE__) . '/../..');
        $loc->addPath('Provider');
        $this->assert($loc->locate('Fs/TestClass'), 'The locator should have found the provider test class.');
    }
}
