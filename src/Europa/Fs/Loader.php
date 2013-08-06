<?php

namespace Europa\Fs;
use Europa\Exception;

class Loader implements LocatorAwareInterface
{
  use LocatorAware;

  private $included = [];

  private $map = [];

  private $separators = ['_', '\\'];

  public function __invoke($class)
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

    $locator = $this->locator;
    $subject = str_replace($this->separators, DIRECTORY_SEPARATOR, $class);

    if (isset($this->map[$class])) {
      include $found = $this->map[$class];
    } elseif ($locator && $found = $locator($subject)) {
      include $found;
    } elseif (is_file($found = __DIR__ . '/../../' . $subject)) {
      include $found;
    }

    if ($found) {
      $this->map[$class]    = $found;
      $this->included[$class] = $found;
      return true;
    }

    return false;
  }

  public function register()
  {
    spl_autoload_register(array($this, '__invoke'), true);
    return $this;
  }

  public function getNamespaceSeparators()
  {
    return $this->namespaceSeparators;
  }

  public function setNamespaceSeparators(array $separators)
  {
    $this->separators = $separators;
    return $this;
  }

  public function getClassMap()
  {
    return $this->map;
  }

  public function setClassMap(array $map)
  {
    $this->map = $map;
    return $this;
  }
}
