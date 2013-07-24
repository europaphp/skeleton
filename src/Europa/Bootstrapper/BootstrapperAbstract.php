<?php

namespace Europa\Bootstrapper;
use Europa\Reflection\ClassReflector;
use Europa\Reflection\MethodReflector;

abstract class BootstrapperAbstract
{
    public function __invoke()
    {
        $that = new ClassReflector($this);

        foreach ($that->getMethods() as $method) {
            if ($this->isValidMethod($method)) {
                $method->invokeArgs($this, func_get_args());
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