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
abstract class AbstractController
{
    /**
     * The doc tag name used for filters.
     * 
     * @var string
     */
    const FILTER = 'filter';

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
        return $this->invoke($request->getParam('action'));
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
    protected function forward($to)
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
        // Ensure the action exists.
        if (!method_exists($this, $action)) {
            Exception::toss('The action "%s" is not defined in the controller "%s".', $action, get_class($this));
        }

        // Build a list of parameters to action the controller with.
        $params = $this->request->getParams();

        // The request is always the first parameter.
        array_unshift($params, $this->request);

        // Filter using @filter tags.
        $this->applyClassFilters();
        $this->applyActionFilters($action);
        
        // Return result from action.
        return (new MethodReflector($this, $action))->invokeArgs($this, $this->request->getParams());
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
                throw new LogicException(sprintf('The filter "%s" specified in controller "%s" does not exist.', $class, get_class($this)));
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
    private function applyClassFilters()
    {
        foreach ($this->getFiltersFor(new ClassReflector($this)) as $filter) {
            call_user_func($filter, $this);
        }
    }

    /**
     * Applies all action filters.
     * 
     * @return void
     */
    private function applyActionFilters($action)
    {
        foreach ($this->getFiltersFor(new MethodReflector($this, $action)) as $filter) {
            call_user_func($filter, $this);
        }
    }
}