<?php

namespace Europa\Controller\Plugin;
use Europa\Controller\ControllerInterface;
use Europa\Reflection\ClassReflector;
use Europa\Reflection\MethodReflector;

/**
 * Will allow filters to be specified using doc tags.
 * 
 * @category Filters
 * @package  HostingControl
 * @author   Trey Shugart <tshugart@ultraserve.com.au>
 * @license  Copyright (c) Ultra Serve http://ultraserve.com.au/license
 */
class Filter
{
    /**
     * The default action name to filter.
     * 
     * @var string
     */
    const ACTION = 'action';
    
    /**
     * Will allow filters to be specified using doc tags.
     * 
     * @param ControllerInterface $controller The controller being filtered.
     * 
     * @return Context
     */
    public function __construct(ControllerInterface $controller)
    {
        $class = (new ClassReflector($controller))->getDocBlock();
        $class = $class->hasTag('filter') ? $class->getTags('filter') : [];
        
        if ($controller instanceof ControllerAbstract) {
            $method = $controller->getActionMethod();
        } else {
            $method = self::ACTION;
        }
        
        $method = (new MethodReflector($controller, $method))->getDocBlock();
        $method = $method->hasTag('filter') ? $method->getTags('filter') : [];

        foreach (array_merge($class, $method) as $filter) {
            $filter = $filter->getInstance();
            
            $filter->filter($controller);
        }
    }
}