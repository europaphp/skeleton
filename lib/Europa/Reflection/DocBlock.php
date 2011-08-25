<?php

namespace Europa\Reflection;

/**
 * Represents a docblock.
 * 
 * @category Reflection
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class DocBlock
{
    private $description = null;

    private $tags = array();

    private $map = array(
        'author'     => '\Europa\Reflection\DocTag\AuthorTag',
        'category'   => '\Europa\Reflection\DocTag\CategoryTag',
        'deprecated' => '\Europa\Reflection\DocTag\DeprecatedTag',
        'filter'     => '\Europa\Reflection\DocTag\FilterTag',
        'internal'   => '\Europa\Reflection\DocTag\InternalTag',
        'license'    => '\Europa\Reflection\DocTag\LicenseTag',
        'package'    => '\Europa\Reflection\DocTag\PackageTag',
        'param'      => '\Europa\Reflection\DocTag\ParamTag',
        'return'     => '\Europa\Reflection\DocTag\ReturnTag',
        'see'        => '\Europa\Reflection\DocTag\SeeTag',
        'subpackage' => '\Europa\Reflection\DocTag\SubpackageTag',
        'throws'     => '\Europa\Reflection\DocTag\ThrowsTag',
        'todo'       => '\Europa\Reflection\DocTag\TodoTag',
        'link'       => '\Europa\Reflection\DocTag\LinkTag',
        'copyright'  => '\Europa\Reflection\DocTag\CopyrightTag',
        'since'      => '\Europa\Reflection\DocTag\SinceTag',
        'var'        => '\Europa\Reflection\DocTag\VarTag',
        'version'    => '\Europa\Reflection\DocTag\VersionTag',
    );

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

    public function map($tag, $class)
    {
        $this->map[$tag] = $class;
        return $this;
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

    public function addTag(DocTag $tag)
    {
        // used multiple times
        $name = $tag->tag();

        // check to see if it's valid
        if (!isset($this->map[$name])) {
            throw new Exception('The tag "{$name}" is an invalid tag for the "{get_class($this)}" doc block.');
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
     * Returns the specified tag. If $asArray is true, then even if the
     * tag is not an array of tags, it is made into one.
     * 
     * @param string $name    The tag name to get.
     * @param bool   $asArray Whether or not to force an array.
     * 
     * @return mixed
     */
    public function getTag($name, $asArray = false)
    {
        if (isset($this->tags[$name])) {
            $tag = $this->tags[$name];
            if (!$asArray && count($tag) === 1) {
                return $tag[0];
            } else {
                return $tag;
            }
        }
        return $asArray ? array() : null;
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

    public function parse($docString)
    {
        $this->description = $this->parseDescription($docString);

        $tags = $this->parseTags($docString);
        $tags = $this->parseDocTagsFromStrings($tags);
        foreach ($tags as $tag) {
            $this->addTag($tag);
        }
    }

    private function parseDescription($docString)
    {
        preg_match('/([a-zA-Z]([^@]+|([^\r]?[^\n][^\s]*[^\*])+))/m', $docString, $desc);
        if (isset($desc[1])) {
            $desc = $desc[1];
            $desc = explode("\n", $desc);
            foreach ($desc as $k => $part) {
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

    private function parseTags($docString)
    {
        $parts = array();
        $parts = explode('* @', $docString);
        unset($parts[0]);
        return $parts;
    }

    private function parseDocTagsFromStrings(array $strings)
    {
        $tags = array();
        foreach ($strings as $string) {
            $tags[] = $this->parseDocTagFromString($string);
        }
        return $tags;
    }

    private function parseDocTagFromString($string)
    {
        $string = preg_replace('#\t#', ' ', $string);
        $parts = explode(' ', $string, 2);
        $name  = trim(strtolower($parts[0]));

        if (!isset($this->map[$name])) {
            throw new Exception('Unknown doc tag "' . $name . '".');
        }

        $class = $this->map[$name];
        return new $class(isset($parts[1]) ? $parts[1] : null);
    }
}
