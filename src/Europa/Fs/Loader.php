<?php

namespace Europa\Fs;

class Loader
{
    private $locator;

    public function __invoke($class)
    {
        if (class_exists($class, false)) {
            return true;
        }

        $file    = str_replace(['\\', '_'], DIRECTORY_SEPARATOR, $class);
        $locator = $this->locator;
        
        if ($locator && $file = $locator($file . '.php')) {
            include $file;
            return true;
        }

        if (is_file($file = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . $file . '.php')) {
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