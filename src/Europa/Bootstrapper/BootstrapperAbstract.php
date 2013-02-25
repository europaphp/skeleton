<?php

namespace Europa\Bootstrapper;
use Europa\Reflection\ClassReflector;
use Europa\Reflection\MethodReflector;

abstract class BootstrapperAbstract implements BootstrapperInterface
{
    public function bootstrap()
    {
        $that = new ClassReflector($this);
        
        foreach ($that->getMethods() as $method) {
            if ($this->isValidMethod($method)) {
                $method->invoke($this);
            }
        }
        
        return $this;
    }

    private function isValidMethod(MethodReflector $method)
    {
        if ($method->getName() === 'bootstrap') {
            return false;
        }

        if ($method->isMagic()) {
            return false;
        }
        
        if (!$method->isPublic()) {
            return false;
        }
        
        return true;
    }
}