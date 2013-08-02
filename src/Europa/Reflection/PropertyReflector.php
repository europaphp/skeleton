<?php

namespace Europa\Reflection;
use ReflectionProperty;

class PropertyReflector extends ReflectionProperty implements ReflectorInterface
{
  private $docString;

  public function getVisibility()
  {
    if ($this->isPrivate()) {
      return 'private';
    }

    if ($this->isProtected()) {
      return 'protected';
    }

    return 'public';
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

    // attempt to get it from the current method
    if ($docblock = parent::getDocComment()) {
      $this->docString = $docblock;
      return $this->docString;
    }

    // if not, check it's interfaces
    $methodName = $this->getName();

    foreach ($this->getDeclaringClass()->getInterfaces() as $iFace) {
      // coninue of the method doesn't exist in the interface
      if (!$iFace->hasMethod($methodName)) {
        continue;
      }

      // attempt to find it in the current interface
      if ($this->docString = $iFace->getMethod($methodName)->getDocComment()) {
         break;
      }
    }

    return $this->docString;
  }
}