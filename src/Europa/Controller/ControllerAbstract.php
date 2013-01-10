<?php

namespace Europa\Controller;
use Europa\Exception\Exception;
use Europa\Reflection\ClassReflector;
use Europa\Reflection\MethodReflector;
use Europa\Reflection\ReflectorInterface;
use Europa\Request\RequestInterface;

abstract class ControllerAbstract
{
    const ACTION = 'action';

    const FILTER = 'filter';

    private $filter = true;

    private $filters = [];

    private $request;

    public function __invoke(RequestInterface $request)
    {
        $this->request = $request;

        $context = $this->invoke($request->getParam(self::ACTION));

        $this->request = null;

        return $context;
    }

    public function filter($switch = true)
    {
        $this->filter = $switch ?: false;
        return $this;
    }

    public function request()
    {
        return $this->request;
    }

    public function forward($to)
    {
        return $this->invoke($to);
    }

    private function invoke($action)
    {
        if (!method_exists($this, $action)) {
            if (method_exists($this, '__call')) {
                return $this->__call($action, $this->request()->getParams());
            } else {
                Exception::toss('The action "%s" is not defined in the controller "%s" and "__call" was not defined to catch it.', $action, get_class($this));
            }
        }

        $class  = new ClassReflector($this);
        $method = $class->getMethod($action);
        $params = $this->request->getParams();

        if ($this->filter) {
            $this->applyClassFilters($class, $method);
            $this->applyActionFilters($class, $method);
        }
        
        return $method->invokeArgs($this, $this->request->getParams());
    }

    private function getFiltersFor(ReflectorInterface $reflector)
    {
        $filters = [];

        foreach ($reflector->getDocBlock()->getTags(self::FILTER) as $filter) {
            $parts = explode(' ', $filter->value(), 2);
            $class = trim($parts[0]);
            $value = isset($parts[1]) ? trim($parts[1]) : '';

            if (!class_exists($class)) {
                Exception::toss('The filter "%s" specified in controller "%s" does not exist.', $class, get_class($this));
            }

            $filters[] = new $class($value);
        }

        return $filters;
    }

    private function applyClassFilters($class, $method)
    {
        foreach ($this->getFiltersFor($class) as $filter) {
            $filter($this, $class, $method);
        }
    }

    private function applyActionFilters($class, $method)
    {
        foreach ($this->getFiltersFor($method) as $filter) {
            $filter($this, $class, $method);
        }
    }
}