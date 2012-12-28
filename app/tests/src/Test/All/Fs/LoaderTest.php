<?php

namespace Test\All\Fs;
use Europa\Fs\Loader;
use Europa\Fs\Locator;
use Europa\Fs\LocatorArray;
use Testes\Test\UnitAbstract;

class LoaderTest extends UnitAbstract
{
    private $loader;
    
    public function setUp()
    {
        $this->loader = new Loader;
        $this->loader->register();
        $this->loader->setLocator(new LocatorArray);
        $this->loader->getLocator()->add(new Locator);
    }
    
    public function testRegisterAutoload()
    {
        foreach (spl_autoload_functions() as $func) {
            if (is_array($func) && $func[0] instanceof Loader && $func[1] === '__invoke') {
                return;
            }
        }

        $this->assert(false, 'Unable to register autoloading.');
    }
    
    public function testLoadClass()
    {
        $this->assert(
            $this->loader->__invoke('Europa\Request\Http'),
            'Unable to load class.'
        );
    }
}