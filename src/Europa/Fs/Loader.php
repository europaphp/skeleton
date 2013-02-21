<?php

namespace Europa\Fs;

class Loader implements LoaderInterface
{
    private $locator;

    private $namespaceTokens = ['_', '\\'];

    private $suffix = '.php';

    public function __construct(LocatorInterface $locator)
    {
        $this->locator = $locator;
    }

    public function load($class)
    {
        if (class_exists($class, false)) {
            return true;
        }

        $formatted = str_replace($this->namespaceTokens, DIRECTORY_SEPARATOR, $class);
        
        if ($this->locator && $file = $this->locator->locate($formatted . $this->suffix)) {
            include $file;
            return true;
        }
        
        return false;
    }

    public function register()
    {
        spl_autoload_register(array($this, 'load'), true);
        return $this;
    }

    public function setSuffix($suffix)
    {
        $this->suffix = $suffix;
        return $this;
    }

    public function getSuffix()
    {
        return $this->suffix;
    }
}