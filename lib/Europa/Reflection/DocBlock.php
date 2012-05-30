<?php

namespace Europa\Reflection;
use LogicException;
use RuntimeException;

/**
 * Represents a PHP doc block that was applied to a function, class or one of it's members.
 * 
 * @category Reflection
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class DocBlock
{
    /**
     * The description that was given to the doc block.
     * 
     * @var string
     */
    private $description = null;

    /**
     * An array of DocTag objects.
     * 
     * @var array
     */
    private $tags = array();

    /**
     * An array of doc tag names to object maps.
     * 
     * @var array
     */
    private $map = array(
        'author'     => 'Europa\Reflection\DocTag\AuthorTag',
        'category'   => 'Europa\Reflection\DocTag\CategoryTag',
        'deprecated' => 'Europa\Reflection\DocTag\DeprecatedTag',
        'filter'     => 'Europa\Reflection\DocTag\FilterTag',
        'internal'   => 'Europa\Reflection\DocTag\InternalTag',
        'license'    => 'Europa\Reflection\DocTag\LicenseTag',
        'package'    => 'Europa\Reflection\DocTag\PackageTag',
        'param'      => 'Europa\Reflection\DocTag\ParamTag',
        'return'     => 'Europa\Reflection\DocTag\ReturnTag',
        'see'        => 'Europa\Reflection\DocTag\SeeTag',
        'subpackage' => 'Europa\Reflection\DocTag\SubpackageTag',
        'throws'     => 'Europa\Reflection\DocTag\ThrowsTag',
        'todo'       => 'Europa\Reflection\DocTag\TodoTag',
        'link'       => 'Europa\Reflection\DocTag\LinkTag',
        'copyright'  => 'Europa\Reflection\DocTag\CopyrightTag',
        'since'      => 'Europa\Reflection\DocTag\SinceTag',
        'var'        => 'Europa\Reflection\DocTag\VarTag',
        'version'    => 'Europa\Reflection\DocTag\VersionTag',
    );

    /**
     * Constructs a new doc block object given the doc string. If no doc string is given, nothing is parsed and an
     * empty doc block is created.
     * 
     * @param string $docString The doc string to parse, if any, and initialize in the object.
     * 
     * @return DocBlock
     */
    public function __construct($docString = null)
    {
        if ($docString) {
            $this->parse($docString);
        }
    }

    /**
     * Returns the compiled doc block.
     * 
     * @return string
     */
    public function __toString()
    {
        return $this->compile();
    }

    /**
     * Applies a custom mapping for a doc tag that may or may not already be mapped.
     * 
     * @param string $tag   The tag name.
     * @param string $class The class to handle the tag.
     * 
     * @return DocBlock
     */
    public function map($tag, $class)
    {
        $this->map[$tag] = $class;
        return $this;
    }

    /**
     * Sets the doc block description.
     * 
     * @param string $description The doc block description.
     * 
     * @return DocBlock
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Returns the description for the doc tag.
     * 
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Adds the specified tag to the doc block.
     * 
     * @param DocTag $tag The tag to add.
     * 
     * @return DocBlock
     */
    public function addTag(DocTag $tag)
    {
        // used multiple times
        $name = $tag->tag();

        // check to see if it's valid
        if (!isset($this->map[$name])) {
            throw new \LogicException('The tag "{$name}" is an invalid tag for the "{get_class($this)}" doc block.');
        }

        // if the tag is already set, we create multiple of the same one
        // otherwise we just set it
        if (isset($this->tags[$name])) {
            if (!is_array($this->tags[$name])) {
                $this->tags[$name] = array($this->tags[$name]);
            }
            $this->tags[$name][] = $tag;
        } else {
            $this->tags[$name] = array($tag);
        }

        return $this;
    }
    
    /**
     * Returns whether or not the doc block contains the specified tag.
     * 
     * @param string $name The name of the tag to check for.
     * 
     * @return bool
     */
    public function hasTag($name)
    {
        return isset($this->tags[$name]) && $this->tags[$name];
    }

    /**
     * Returns the specified tag.
     * 
     * @param string $name The tag name to get.
     * 
     * @return DocTag
     */
    public function getTag($name)
    {
        $this->checkTagAndThrowIfNotExists($name);
        return $this->tags[$name][0];
    }
    
    /**
     * Returns an array of all tags matching $name.
     * 
     * @param string $name The name of the tag to get.
     * 
     * @return array
     */
    public function getTags($name)
    {
        return isset($this->tags[$name]) ? $this->tags[$name] : [];
    }

    /**
     * Reverses the doc block parsing.
     * 
     * @return string The compiled doc block.
     */
    public function compile()
    {
        $str = '/**' . PHP_EOL 
             . ' * ' . $this->description . PHP_EOL
             . ' * '. PHP_EOL;
        
        $last = null;
        $longest = 0;
        foreach ($this->tags as $tagGroup) {
            foreach ($tagGroup as $tag) {
                $str .= ' * @' . $tag->__toString() . PHP_EOL;
                $last = $tag->tag();
            }
        }
        return $str . ' */';
    }

    /**
     * Parses the specified string out into each of its parts.
     * 
     * @param string $docString The string to parse.
     * 
     * @return void
     */
    public function parse($docString)
    {
        $this->description = $this->parseDescription($docString);

        $tags = $this->parseTags($docString);
        $tags = $this->parseDocTagsFromStrings($tags);
        
        foreach ($tags as $tag) {
            $this->addTag($tag);
        }
        
        return $this;
    }

    /**
     * Parses out the description of the specified doc string and returns it.
     * 
     * @param string $docString The string to parse.
     * 
     * @return string
     */
    private function parseDescription($docString)
    {
        // matches anything up to a "@"
        preg_match('/([a-zA-Z]([^@]+|([^\r]?[^\n][^\s]*[^\*])+))/m', $docString, $desc);
        
        if (isset($desc[1])) {
            $desc = $desc[1];
            $desc = explode("\n", $desc);
            
            foreach ($desc as $k => $part) {
                // removes errant stars from the middle of a description
                $desc[$k] = trim(preg_replace('#^\*#', '', trim($part)));
                
                if (!preg_match('/[a-zA-Z0-9]/', $part)) {
                    $desc[$k] = PHP_EOL;
                }
            }
            
            $desc = implode(' ', $desc);
            $desc = trim($desc);
            
            return $desc;
        }
        
        return null;
    }

    /**
     * Parses out each tag of the specified doc string and returns them as an array of string.
     * 
     * @param string $docString The string to parse.
     * 
     * @return array
     */
    private function parseTags($docString)
    {
        $parts = array();
        $parts = explode('* @', $docString);
        unset($parts[0]);
        return $parts;
    }

    /**
     * Parses each passed tag string from the given array and returns an array of tag objects.
     * 
     * @param array $strings The doc tag strings to parse.
     * 
     * @return array
     */
    private function parseDocTagsFromStrings(array $strings)
    {
        $tags = array();
        foreach ($strings as $string) {
            $tags[] = $this->parseDocTagFromString($string);
        }
        return $tags;
    }

    /**
     * Parses a single doc tag string and returns a doc tag object which is responsible for further parsing.
     * 
     * @param string $string The doc tag string to do the initial parsing.
     * 
     * @return DocTag
     */
    private function parseDocTagFromString($string)
    {
        $string = preg_replace('#\t#', ' ', $string);
        $parts  = explode(' ', $string, 2);
        $name   = trim(strtolower($parts[0]));

        if (!isset($this->map[$name])) {
            throw new LogicException('Unknown doc tag "' . $name . '".');
        }
        
        return new $this->map[$name](isset($parts[1]) ? $parts[1] : null);
    }
    
    /**
     * If the specified tag does not exist, an exception is thrown.
     * 
     * @param string $name The name of the tag.
     * 
     * @throws RuntimeException If the tag doesn't exist.
     * 
     * @return void
     */
    private function checkTagAndThrowIfNotExists($name)
    {
        if (!$this->hasTag($name)) {
            throw new RuntimeException(
                'The tag "' . $name . '" does not exist in:' . PHP_EOL . PHP_EOL . $this->compile()
            );
        }
    }
}
