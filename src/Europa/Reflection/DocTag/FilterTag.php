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
     * The filter class name.
     * 
     * @var string
     */
    private $class;

    /**
     * The argument string.
     * 
     * @var string
     */
    private $args;

    /**
     * Sets the class for the filter.
     * 
     * @param string $class The filter class.
     * 
     * @return FilterTag
     */
    public function setClass($class)
    {
        $this->class = trim($class);
        return $this;
    }

    /**
     * Returns the filter class.
     * 
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Sets the argument string.
     * 
     * @param string $args The argument string.
     * 
     * @return FilterTag
     */
    public function setArgumentString($args)
    {
        $this->args = trim($args);
        return $this;
    }

    /**
     * Returns the argument string.
     * 
     * @return string
     */
    public function getArgumentString()
    {
        return $this->args;
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
        $parts = explode(' ', $value, 2);
        $this->setClass($parts[0]);
        $this->setArgumentString($parts[1]);
    }

    /**
     * Compiles the tag value.
     * 
     * @return string
     */
    public function compileValue()
    {
        return $this->class . ' ' . $this->args;
    }
}