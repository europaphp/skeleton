<?php

namespace Europa\Reflection\DocTag;
use UnexpectedValueException;

/**
* Represents a DocBlock throws tag.
*
* @category Reflection
* @package  Europa
* @author   Trey Shugart <treshugart@gmail.com>
* @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
*/
class ThrowsTag extends GenericTag
{
    /**
     * The name of the exception class that is thrown.
     * 
     * @var string
     */
    private $exception;
    
    /**
     * The description of the exception and why it is thrown.
     * 
     * @var array
     */
    private $description;

    /**
     * Sets the exception class name.
     * 
     * @param string $class The exception class name.
     * 
     * @return ThrowsTag
     */
    public function setException($class)
    {
        $this->exception = $class;
        return $this;
    }
    
    /**
     * Returns the name of the reference.
     * 
     * @return string
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * Sets the exception description.
     * 
     * @param string $description The description to be used.
     * 
     * @return ThrowsTag
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }
    
    /**
     * Returns the description.
     * 
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
    
    /**
     * Parses the tag value.
     * 
     * @param string $value The tag value.
     * 
     * @return void
     */
    public function parseValue($tagString)
    {
        $parts = explode(' ', $tagString, 2);
        
        if (!isset($parts[0])) {
            throw new UnexpectedValueException('An exception class must be specified for a @throws tag.');
        }
        
        $this->exception = $parts[0];
        
        if (isset($parts[1])) {
            $this->description = $parts[1];
        }
    }
    
    /**
     * Compiles the tag value.
     * 
     * @return string
     */
    public function compileValue()
    {
        return $this->exception . ' ' . $this->description;
    }
}