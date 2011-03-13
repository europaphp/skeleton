<?php

namespace Europa\Reflection;

/**
 * Extension to \ReflecitonClass to implement doc block getting/parsing.
 * 
 * @category Reflection
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class ClassReflector extends \ReflectionClass implements Reflectable
{
    /**
     * Returns the doc block for the class.
     * 
     * @return \Europa\Reflection\DocBlock
     */
    public function getDocBlock()
    {
        return new DocBlock($this->getDocComment());
    }
    
    public function getDependencies()
    {
        return new ClassDependency($this);
    }
}