<?php

namespace Europa\Controller;
use Europa\App\AppInterface;
use Europa\Reflection\ClassReflector;
use Europa\Reflection\MethodReflector;
use Europa\Reflection\ReflectorInterface;
use Europa\Request\RequestInterface;
use LogicException;

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
     * List of validators on the controller.
     * 
     * @var array
     */
    private $filters = [];

    /**
     * Sets up a new controller and adds validators.
     * 
     * @return ControllerAbstract
     */
    public function __construct($config = [])
    {
        foreach ($this->getFiltersFor(new ClassReflector($this)) as $filter) {
            $this->addFilter($filter);
        }

        if (method_exists($this, 'init')) {
            $this->init();
        }
    }

    /**
     * Invokes the controller calling the action specified in the request.
     * 
     * @param RequestInterface $request The request to use.
     * 
     * @return array
     */
    public function __invoke(RequestInterface $request)
    {
        $action     = $request->getParam(AppInterface::PARAM_ACTION);
        $hasAction  = method_exists($this, $action);
        $hasDefault = method_exists($this, self::ACTION);

        if (!$action && !$hasDefault) {
            throw new LogicException(sprintf('No action was found and the default action "%s->%s()" does not exist.', get_class($this), self::ACTION));
        } elseif (!$hasAction && !$hasDefault) {
            throw new LogicException(sprintf('Both action "%s->%s()" and the default action "%s->%s()" do not exist.', get_class($this), $action, get_class($this), self::ACTION));
        }

        $action = new MethodReflector($this, $hasAction ? $action : self::ACTION);

        foreach ($this->filters as $filter) {
            call_user_func($filter, $this, $request);
        }
        
        foreach ($this->getFiltersFor($action) as $filter) {
            call_user_func($filter, $this, $request);
        }
        
        return $action->invokeArgs($this, $request->getParams());
    }

    /**
     * Adds a filter to the controller.
     * 
     * @param mixed $filter The filter to add.
     * 
     * @return ControllerAbstract
     */
    public function addFilter(callable $filter)
    {
        $this->filters[] = $filter;
        return $this;
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

        foreach ($reflector->getDocBlock()->getTags('filter') as $filter) {
            $parts = explode(' ', $filter->value(), 2);
            $class = trim($parts[0]);
            $value = isset($parts[1]) ? trim($parts[1]) : '';

            if (!class_exists($class)) {
                throw new LogicException(sprintf('The filter "%s" specified in controller "%s" does not exist.', $class, get_class($this)));
            }

            $filters[] = new $class($value);
        }

        return $filters;
    }
}