<?php

namespace Europa\Reflection;
use ReflectionProperty;

/**
 * Extends the base reflection class to provide further functionality such as named
 * parameter merging and calling.
 * 
 * @category Reflection
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class PropertyReflector extends ReflectionProperty implements ReflectorInterface
{
    /**
     * The cached doc string.
     * 
     * @var string
     */
    private $docString;
    
    /**
     * Returns the visibility of the current method.
     * 
     * @return string
     */
    public function getVisibility()
    {
        if ($this->isPrivate()) {
            return 'private';
        }
        
        if ($this->isProtected()) {
            return 'protected';
        }
        
        return 'public';
    }

    /**
     * Returns the doc block instance for this method.
     * 
     * @return DocBlock
     */
    public function getDocBlock()
    {
        return new DocBlock($this->getDocComment());
    }

    /**
     * Returns the docblock for the specified method. If it's not defined, then it
     * goes up the inheritance tree and through its interfaces.
     * 
     * @todo Implement parent class method docblock sniffing not just interfaces.
     * 
     * @return string|null
     */
    public function getDocComment()
    {
        // if it's already been retrieved, just return it
        if ($this->docString) {
            return $this->docString;
        }
        
        // attempt to get it from the current method
        if ($docblock = parent::getDocComment()) {
            $this->docString = $docblock;
            return $this->docString;
        }
        
        // if not, check it's interfaces
        $methodName = $this->getName();
        foreach ($this->getDeclaringClass()->getInterfaces() as $iFace) {
            // coninue of the method doesn't exist in the interface
            if (!$iFace->hasMethod($methodName)) {
                continue;
            }
            
            // attempt to find it in the current interface
            if ($this->docString = $iFace->getMethod($methodName)->getDocComment()) {
                 break;
            }
        }
        
        return $this->docString;
    }
}