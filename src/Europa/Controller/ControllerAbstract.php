<?php

namespace Europa\Controller;
use Europa\Exception\Exception;
use Europa\Reflection\ClassReflector;
use Europa\Reflection\MethodReflector;
use Europa\Reflection\ReflectorInterface;
use Europa\Request\RequestInterface;

/**
 * A default implementation of the controller interface.
 * 
 * @category Controllers
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
abstract class ControllerAbstract
{
    /**
     * The action parameter name.
     * 
     * @var string
     */
    const ACTION = 'action';

    /**
     * The doc tag name used for filters.
     * 
     * @var string
     */
    const FILTER = 'filter';

    /**
     * Whether or not filters are enabled or disabled.
     * 
     * @var bool
     */
    private $filter = true;

    /**
     * List of validators on the controller.
     * 
     * @var array
     */
    private $filters = [];

    /**
     * The request currently being actioned.
     * 
     * @var RequestInterface
     */
    private $request;

    /**
     * Invokes the controller calling the action specified in the request.
     * 
     * @param RequestInterface $request The request to use.
     * 
     * @return array
     */
    public function __invoke(RequestInterface $request)
    {
        $this->request = $request;

        $context = $this->invoke($request->getParam(self::ACTION));

        $this->request = null;

        return $context;
    }

    /**
     * Turns filtering on and off.
     * 
     * @param bool $switch Whether or not to filter.
     * 
     * @return ControllerAbstract
     */
    public function filter($switch = true)
    {
        $this->filter = $switch ?: false;
        return $this;
    }

    /**
     * Returns the current request.
     * 
     * @return RequestInterface
     */
    public function request()
    {
        return $this->request;
    }

    /**
     * Forwards to another action in the same controller.
     * 
     * @param string $to The action to forward to.
     * 
     * @return AbstractController
     */
    public function forward($to)
    {
        return $this->invoke($to);
    }

    /**
     * Invokes the specified action.
     * 
     * @param string $action The action to invoke.
     * 
     * @return mixed
     */
    private function invoke($action)
    {
        if (!method_exists($this, $action)) {
            Exception::toss('The action "%s" is not defined in the controller "%s".', $action, get_class($this));
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

    /**
     * Returns the filters for the specified reflector.
     * 
     * @param ReflectorInterface $reflector The reflector to get the filters for.
     * 
     * @return array
     */
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

    /**
     * Applies all class fitlers.
     * 
     * @return void
     */
    private function applyClassFilters($class, $method)
    {
        foreach ($this->getFiltersFor($class) as $filter) {
            $filter($this, $class, $method);
        }
    }

    /**
     * Applies all action filters.
     * 
     * @return void
     */
    private function applyActionFilters($class, $method)
    {
        foreach ($this->getFiltersFor($method) as $filter) {
            $filter($this, $class, $method);
        }
    }
}