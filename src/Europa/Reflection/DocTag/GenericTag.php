<?php

namespace Europa\Reflection\DocTag;

class GenericTag implements DocTagInterface
{
    private $tag;
    
    private $value;

    public function __construct($tag, $value = null)
    {
        $this->tag = $tag;
        $this->parse($value);
    }

    public function __toString()
    {
        return $this->compile();
    }
    
    public function tag()
    {
        return $this->tag;
    }
    
    public function value()
    {
        return $this->value;
    }

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
    
    public function parseValue($value)
    {
        
    }

    public function compile()
    {
        return '@' . $this->tag() . ' ' . $this->value();
    }
    
    public function compileValue()
    {
        return $this->value();
    }
}