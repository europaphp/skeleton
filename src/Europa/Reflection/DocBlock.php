<?php

namespace Europa\Reflection;
use Europa\Reflection\DocTag;
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
     * A default tag, if any to use for parsing unknown tags.
     * 
     * @var DocTag\DocTagInterface
     */
    private $defaultTag = 'Europa\Reflection\DocTag\GenericTag';
    
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
    private $map = [
        'author' => 'Europa\Reflection\DocTag\AuthorTag',
        'param'  => 'Europa\Reflection\DocTag\ParamTag',
        'return' => 'Europa\Reflection\DocTag\ReturnTag',
        'throws' => 'Europa\Reflection\DocTag\ThrowsTag',
    ];
    
    /**
     * The tag instances in this doc block.
     * 
     * @var array
     */
    private $tags = [];

    /**
     * Constructs a new doc block object given the doc string. If no doc string is given, nothing is parsed and an
     * empty doc block is created.
     * 
     * @param string $doc The doc string to parse, if any, and initialize in the object.
     * 
     * @return DocBlock
     */
    public function __construct($doc = null)
    {
        $this->parse($doc);
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
     * @param string $name The tag name.
     * @param string $tag  The tag instance to handle tag parsing.
     * 
     * @return DocBlock
     */
    public function map($name, DocTag\DocTagInterface $tag)
    {
        $this->map[$name] = $tag;
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
     * @param DocTag\DocTagInterface $tag The tag to add.
     * 
     * @return DocBlock
     */
    public function addTag(DocTag\DocTagInterface $tag)
    {
        // used multiple times
        $name = $tag->tag();

        // if the tag is already set, we create multiple of the same one
        // otherwise we just set it
        if (isset($this->tags[$name])) {
            if (!is_array($this->tags[$name])) {
                $this->tags[$name] = [$this->tags[$name]];
            }
            $this->tags[$name][] = $tag;
        } else {
            $this->tags[$name] = [$tag];
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
        
        $last    = null;
        $longest = 0;
        
        foreach ($this->tags as $tagGroup) {
            foreach ($tagGroup as $tag) {
                $str .= ' * @' . $tag->compile() . PHP_EOL;
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
    public function parse($doc)
    {
        if (!$doc) {
            return $this;
        }
        
        $this->description = $this->parseDescription($doc);

        $tags = $this->parseTags($doc);
        $tags = $this->parseDocTagsFromStrings($tags);
        
        foreach ($tags as $tag) {
            $this->addTag($tag);
        }
        
        return $this;
    }

    /**
     * Parses out the description of the specified doc string and returns it.
     * 
     * @param string $doc The string to parse.
     * 
     * @return string
     */
    private function parseDescription($doc)
    {
        // matches anything up to a "@"
        preg_match('/([a-zA-Z]([^@]+|([^\r]?[^\n][^\s]*[^\*])+))/m', $doc, $desc);
        
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
     * @param string $doc The string to parse.
     * 
     * @return array
     */
    private function parseTags($doc)
    {
        $parts = explode('* @', $doc);
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
        $tags = [];
        
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
    private function parseDocTagFromString($tag)
    {
        $string = preg_replace('#\t#', ' ', $tag);
        $parts  = explode(' ', $tag, 2);
        $name   = trim($parts[0]);
        $value  = isset($parts[1]) ? trim($parts[1]) : null;
        
        if (isset($this->map[$name])) {
            return new $this->map[$name]($name, $value);
        }
        
        if ($this->defaultTag) {
            return new $this->defaultTag($name, $value);
        }
        
        throw new RuntimeException('The tag "' . $name . '" has no associated doc tag parser and no default tag was found.');
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