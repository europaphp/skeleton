<?php

namespace Test\All\Fs;
use Europa\Fs\Locator;
use Testes\Test\UnitAbstract;

class LocatorTest extends UnitAbstract
{
    public function basePathUsage()
    {
        $loc = new Locator;
        $loc->setRoot(dirname(__FILE__) . '/../..');
        $loc->addPath('Provider');
        $this->assert($loc('Fs/TestClass.php'), 'The locator should have found the provider test class.');
    }
}