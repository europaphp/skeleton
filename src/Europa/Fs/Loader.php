<?php

namespace Europa\Fs;

class Loader
{
    private $locator;

    private $namespaceTokens = ['_', '\\'];

    public function __invoke($class)
    {
        if (class_exists($class, false)) {
            return true;
        }

        $locator   = $this->locator;
        $formatted = str_replace($this->namespaceTokens, DIRECTORY_SEPARATOR, $class);
        
        if ($locator && $file = $locator($formatted . '.php')) {
            include $file;
            return true;
        }

        if (is_file($file = __DIR__ . '/../../' . $formatted . '.php')) {
            include $file;
            return true;
        }
        
        return false;
    }
    
    public function setLocator(callable $locator)
    {
        $this->locator = $locator;
        return $this;
    }
    
    public function getLocator()
    {
        return $this->locator;
    }

    public function hasLocator()
    {
        return isset($this->locator);
    }

    public function removeLocator()
    {
        $this->locator = null;
        return $this;
    }
    
    public function register($prepend = false)
    {
        spl_autoload_register(array($this, '__invoke'), true, $prepend);
        return $this;
    }
}