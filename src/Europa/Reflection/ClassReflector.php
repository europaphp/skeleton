<?php

namespace Europa\Reflection;
use Europa\Reflection\MethodReflector;

class ClassReflector extends \ReflectionClass implements ReflectorInterface
{
  private $docString;

  public function is($type)
  {
    return $this->getName() === $type
      || $this->isSubclassOf($type)
      || in_array($type, $this->getTraitNames());
  }

  public function isAny(array $types)
  {
    foreach ($types as $type) {
      if ($this->is($type)) {
        return true;
      }
    }

    return false;
  }

  public function isAll(array $types)
  {
    foreach ($types as $type) {
      if (!$this->is($type)) {
        return false;
      }
    }
    return true;
  }

  public function getTree()
  {
    $tree    = [];
    $reflector = $this;

    while ($reflector) {
      $tree[]  = $reflector;
      $reflector = $reflector->getParentClass();
    }

    return array_reverse($tree);
  }

  public function getTreeNames()
  {
    $tree = [];

    foreach ($this->getTree() as $parent) {
      $tree[] = $parent->getName();
    }

    return $tree;
  }

  public function getMethod($method)
  {
    return new MethodReflector($this->getName(), $method);
  }

  public function getMethods($filter = -1)
  {
    $methods = array();

    foreach (parent::getMethods($filter) as $method) {
      $methods[] = $this->getMethod($method->getName());
    }

    return $methods;
  }

  public function getDocBlock()
  {
    return new DocBlock($this->getDocComment());
  }

  public function getDocComment()
  {
    // if it's already been retrieved, just return it
    if ($this->docString) {
      return $this->docString;
    }

    // check to see if it's here first
    if ($docString = parent::getDocComment()) {
      $this->docString = $docString;
      return $docString;
    }

    // go through each parent class
    $class = $this->getParentClass();

    while ($class) {
      if ($docString = $class->getDocComment()) {
        $this->docString = $docString;
        break;
      }
      $class = $class->getParentClass();
    }

    // then go through each interface
    foreach ($this->getInterfaces() as $iFace) {
      if ($docString = $iFace->getDocComment()) {
        $this->docString = $docString;
        break;
      }
    }

    return $this->docString;
  }

  public function newInstanceArgs(array $args = null)
  {
    if ($this->hasMethod('__construct')) {
      return parent::newInstanceArgs($this->getMethod('__construct')->mergeNamedArgs($args));
    }

    return $this->newInstance();
  }
}