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
class GenericTag implements DocTagInterface
{
    /**
     * The tag name.
     * 
     * @var string
     */
    private $tag;
    
    /**
     * The tag value.
     * 
     * @var string
     */
    private $value;

    /**
     * Constructs a new doc tag. If a tag string is specified then it's parsed.
     * 
     * @param string $tag The tag to parse.
     * 
     * @return DocTagAbstract
     */
    public function __construct($tag, $value = null)
    {
        $this->tag = $tag;
        $this->parse($value);
    }

    /**
     * Returns the full doc tag including the name and tag string.
     * 
     * @return string
     */
    public function __toString()
    {
        return $this->compile();
    }
    
    /**
     * Returns the tag name.
     * 
     * @return string
     */
    public function tag()
    {
        return $this->tag;
    }
    
    /**
     * Returns the tag value.
     * 
     * @return string
     */
    public function value()
    {
        return $this->value;
    }

    /**
     * Default tag parsing. Parses out the name of the tag and its value.
     * 
     * @param string $tag The tag string without the tag name.
     * 
     * @return GenericTag
     */
    public function parse($tag)
    {
        $tag = trim($tag);
        
        if (!$tag) {
            return $this;
        }
        
        $str = trim($tag);
        $str = explode("\n", $str);
        
        foreach ($str as $k => $part) {
            $part = trim($part);

            // if they are empty comment lines, unset and continue
            if ($part === '*' || $part === '*/') {
                unset($str[$k]);
                continue;
            }

            // if it begins with an asterisk, format it
            if (isset($part[0]) && $part[0] === '*') {
                $part = substr($part, 1);
                $part = trim($part);
            }

            $str[$k] = $part;
        }
        
        $this->value = implode(' ', $str);
        
        $this->parseValue($this->value);
        
        return $this;
    }
    
    /**
     * Parses the tag value.
     * 
     * @param string $value The tag value.
     * 
     * @return void
     */
    public function parseValue($value)
    {
        
    }

    /**
     * Compiles the tag and returns it as a string.
     * 
     * @return string
     */
    public function compile()
    {
        return '@' . $this->tag() . ' ' . $this->value();
    }
    
    /**
     * Compiles the tag value.
     * 
     * @return string
     */
    public function compileValue()
    {
        return $this->value();
    }
}