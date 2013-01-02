<?php

namespace Europa\Reflection\DocTag;
use UnexpectedValueException;

class ThrowsTag extends GenericTag
{
    private $exception;
    
    private $description;

    public function setException($class)
    {
        $this->exception = $class;
        return $this;
    }
    
    public function getException()
    {
        return $this->exception;
    }

    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }
    
    public function getDescription()
    {
        return $this->description;
    }
    
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
    
    public function compileValue()
    {
        return $this->exception . ' ' . $this->description;
    }
}