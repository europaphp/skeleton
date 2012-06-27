<?php

namespace Europa\Reflection\DocTag;

/**
 * Represents a generic DocBlock tag.
 * 
 * @category Reflection
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
interface DocTagInterface
{
    /**
     * Constructs a new doc tag. If a tag string is specified then it's parsed.
     * 
     * @param string $tag   The tag name.
     * @param string $value The tag value to parse.
     * 
     * @return DocTagAbstract
     */
    public function __construct($tag, $value = null);

    /**
     * Returns the full doc tag including the name and tag string.
     * 
     * @return string
     */
    public function __toString();
    
    /**
     * Returns the tag name.
     * 
     * @return string
     */
    public function tag();
    
    /**
     * Returns the tag value.
     * 
     * @return string
     */
    public function value();

    /**
     * Default tag parsing. Parses out the name of the tag and its value.
     * 
     * @param string $tag The tag string without the tag name.
     * 
     * @return void
     */
    public function parse($tag);

    /**
     * Compiles the tag and returns it as a string.
     * 
     * @return string
     */
    public function compile();
}