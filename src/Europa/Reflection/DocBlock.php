<?php

namespace Europa\Reflection;
use Europa\Reflection\DocTag;
use LogicException;
use RuntimeException;

class DocBlock
{
    private $defaultTag = 'Europa\Reflection\DocTag\GenericTag';
    
    private $description = null;

    private $map = [
        'author' => 'Europa\Reflection\DocTag\AuthorTag',
        'param'  => 'Europa\Reflection\DocTag\ParamTag',
        'return' => 'Europa\Reflection\DocTag\ReturnTag',
        'throws' => 'Europa\Reflection\DocTag\ThrowsTag',
    ];
    
    private $tags = [];

    public function __construct($doc = null)
    {
        $this->parse($doc);
    }

    public function __toString()
    {
        return $this->compile();
    }

    public function map($name, DocTag\DocTagInterface $tag)
    {
        $this->map[$name] = $tag;
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
    
    public function hasTag($name)
    {
        return isset($this->tags[$name]) && $this->tags[$name];
    }

    public function getTag($name)
    {
        $this->checkTagAndThrowIfNotExists($name);
        return $this->tags[$name][0];
    }
    
    public function getTags($name)
    {
        return isset($this->tags[$name]) ? $this->tags[$name] : [];
    }

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

    private function parseTags($doc)
    {
        $parts = explode('* @', $doc);
        unset($parts[0]);
        return $parts;
    }

    private function parseDocTagsFromStrings(array $strings)
    {
        $tags = [];
        
        foreach ($strings as $string) {
            $tags[] = $this->parseDocTagFromString($string);
        }
        
        return $tags;
    }

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
    
    private function checkTagAndThrowIfNotExists($name)
    {
        if (!$this->hasTag($name)) {
            throw new RuntimeException(
                'The tag "' . $name . '" does not exist in:' . PHP_EOL . PHP_EOL . $this->compile()
            );
        }
    }
}