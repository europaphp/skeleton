<?php

namespace Europa\Reflection\DocTag;
use Europa\Reflection\DocTag;

/**
* Represents a docblock throws tag.
*
* @category Reflection
* @package  Europa
* @author   Trey Shugart <treshugart@gmail.com>
* @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
*/
class ThrowsTag extends DocTag
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
     * Returns the name of the tag.
     * 
     * @return string
     */
    public function tag()
    {
        return 'throws';
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
     * Returns the description.
     * 
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
    
    /**
     * Implements custom parsing for this docblock tag.
     * 
     * @param string $tagString The tag string to parse.
     * 
     * @return void
     */
    public function parse($tagString)
    {
        $parts = explode(' ', $tagString, 2);
        if (!isset($parts[0])) {
            throw new Exception('An exception class must be specified for a @throws tag.');
        }
        
        $this->exception = $parts[0];
        if (isset($parts[1])) {
            $this->description = $parts[1];
        }
    }
}