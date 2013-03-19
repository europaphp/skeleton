<?php

namespace Europa\Fs;
use Europa\Exception\Exception;

class Loader implements LoaderInterface, LocatorAwareInterface
{
    use LocatorAware;

    private $included = [];

    private $map = [];

    private $separators = ['_', '\\'];

    private $suffix = '.php';

    public function load($class)
    {
        if (class_exists($class, false)) {
            return true;
        }

        if (isset($this->included[$class])) {
            Exception::toss(
                'The class "%s" was supposed to be found in "%s". A potential cause is when a class name does not match PSR-0 standards.',
                $class,
                $this->included[$class]
            );
        }

        $subject = str_replace($this->separators, DIRECTORY_SEPARATOR, $class) . $this->suffix;

        if (isset($this->map[$class])) {
            include $found = $this->map[$class];
        } elseif ($this->locator && $found = $this->locator->locate($subject)) {
            include $found;
        } elseif (is_file($found = __DIR__ . '/../../' . $subject)) {
            include $found;
        }

        if ($found) {
            $this->map[$class]      = $found;
            $this->included[$class] = $found;
            return true;
        }
        
        return false;
    }

    public function register()
    {
        spl_autoload_register(array($this, 'load'), true);
        return $this;
    }

    public function setNamespaceSeparators(array $separators)
    {
        $this->separators = $separators;
        return $this;
    }

    public function getNamespaceSeparators()
    {
        return $this->separators;
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

    public function setClassMap(array $map)
    {
        $this->map = $map;
        return $this;
    }

    public function getClassMap()
    {
        return $this->map;
    }
}