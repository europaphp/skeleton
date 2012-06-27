<?php

namespace Europa\Reflection\DocTag;
use Europa\Reflection\ClassReflector;
use Europa\Reflection\DocTag;

/**
 * Represents a docblock filter tag.
 * 
 * @category Reflection
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class FilterTag extends GenericTag
{
    /**
     * Returns an instance of the filter.
     * 
     * @param array $args The arguments, if any, to pass to the filter's constructor.
     * 
     * @return Europa\Controller\FilterInterface
     */
    public function getInstance(array $args = array())
    {
        $reflector = new ClassReflector($this->value());
        return $reflector->newInstance();
    }
}