<?php

namespace Europa\Controller;
use Europa\Config\Config;
use Europa\Reflection\ClassReflector;
use Europa\Reflection\MethodReflector;
use Europa\Reflection\ReflectorInterface;
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
     * Controler configuration.
     * 
     * @var array | Config
     */
    private $config = [
        'action.default'   => 'action',
        'action.param'     => 'action',
        'filters.enable'   => true
    ];

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
        $this->config = new Config($this->config, $config);

        foreach ($this->getFiltersFor(new ClassReflector($this)) as $filter) {
            $this->addFilter($filter);
        }

        if (method_exists($this, 'init')) {
            $this->init();
        }

        foreach ($this->filters as $filter) {
            call_user_func($filter, $method);
        }
    }

    /**
     * Invokes the controller calling the action specified in the request.
     * 
     * @param array $params The params to use.
     * 
     * @return array
     */
    public function __invoke(array $params = [])
    {
        $method = $this->config->action->param;

        if (isset($params[$method])) {
            $method = $params[$method];
        } elseif (method_exists($this, $this->config->action->default)) {
            $method = $this->config->action->default;
        } else {
            throw new LogicException(sprintf('No action was specified and not catch-all action "%s->%s()" was specified.', get_class($this), $this->config->action->default));
        }

        if (!method_exists($this, $method)) {
            throw new LogicException(sprintf('The action "%s" does not exist in controller "%s".', $method, get_class($this)));
        }

        $method = new MethodReflector($this, $method);
        
        foreach ($this->getFiltersFor($method) as $filter) {
            call_user_func($filter, $method);
        }
        
        return $method->invokeArgs($this, $params);
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
            $parts = explode(' ', $filter->getValue(), 2);
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