<?php

namespace Europa\Reflection;

/**
 * Represents a docblock tag.
 * 
 * @category Reflection
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
abstract class DocTag
{
    /**
     * A generic tag string for abstract tag elements that don't
     * require any extra parseing besides name and value.
     * 
     * @var string
     */
    protected $tagString;

    /**
     * Returns the name of the tag.
     * 
     * @return string
     */
    abstract public function tag();

    /**
     * Constructs a new doc tag. If a tag string is specified then it's parsed.
     * 
     * @param string $tagString The tag to parse.
     * 
     * @return DocTagAbstract
     */
    public function __construct($tagString = null)
    {
        if ($tagString) {
            $this->parse($tagString);
        }
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
     * Removes any new-line formatting including the asterisks.
     * 
     * @param string $tagString The tag string without the tag name.
     * 
     * @return void
     */
    public function parse($tagString)
    {
        $str = trim($tagString);
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
        
        $this->tagString = implode(' ', $str);
    }

    /**
     * Compiles the tag and returns it as a string.
     * 
     * @return string
     */
    public function compile()
    {
        $str = '@' . $this->tag();
        
        if ($this->tagString) {
            $str .= ' ' . $this->tagString;
        }
        
        return $str;
    }
}
