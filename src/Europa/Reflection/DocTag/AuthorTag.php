<?php

namespace Europa\Reflection\DocTag;
use UnexpectedValueException;

/**
 * Represents a docblock author tag.
 *
 * @category Reflection
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class AuthorTag extends GenericTag
{
    /**
     * Name author's name.
     * 
     * @var string
     */
    private $name;
    
    /**
     * The author's email.
     * 
     * @var string
     */
    private $email;
    
    /**
     * Set the name of the author
     * 
     * @param string $name Name of the author
     * 
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
    
    /**
     * Return the name of the author
     * 
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Sets the author's email address.
     * 
     * @param string $email The author's email.
     * 
     * @return AuthorTag
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }
    
    /**
     * Returns the author's email.
     * 
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }
    
    /**
     * Parse the tag value.
     * 
     * @param string $value The tag value.
     * 
     * @return void
     */
    public function parseValue($value)
    {
        // split in to tag/author name parts
        $parts = preg_replace('/\s+/', ' ', $value);
        $parts = preg_split('/\s+/', $value, 2);

        // require a name
        if (!isset($parts[0])) {
            throw new UnexpectedValueException('A valid name for the author must be specified.');
        }
        
        // require an email address
        if (!isset($parts[1])) {
            throw new UnexpectedValueException('A valid email address for the author must be specified.');
        }
        
        // set the name
        $this->name = trim($parts[0]);
        
        // set the email
        $this->email = trim($parts[1], '<>');
    }
    
    /**
     * Compiles the tag value.
     * 
     * @return string
     */
    public function compileValue()
    {
        return $this->name . ' <' . $this->email . '>';
    }
}