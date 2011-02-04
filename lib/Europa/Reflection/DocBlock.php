<?php

namespace Europa\Reflection;

class DocBlock
{
    protected $description = null;

    protected $tags = array();

    protected $map = array(
        'author'     => '\Europa\Reflection\DocTag\AuthorTag',
        'category'   => '\Europa\Reflection\DocTag\CategoryTag',
        'license'    => '\Europa\Reflection\DocTag\LicenseTag',
        'package'    => '\Europa\Reflection\DocTag\PackageTag',
        'param'      => '\Europa\Reflection\DocTag\ParamTag',
        'postFilter' => '\Europa\Reflection\DocTag\PostFilterTag',
        'preFilter'  => '\Europa\Reflection\DocTag\PreFilterTag',
        'return'     => '\Europa\Reflection\DocTag\ReturnTag',
        'subpackage' => '\Europa\Reflection\DocTag\SubpackageTag',
        'todo'       => '\Europa\Reflection\DocTag\TodoTag'
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
            $this->tags[$name] = $tag;
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
            if ($asArray && !is_array($tag)) {
                return array($tag);
            }
            return $tag;
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
        foreach ($this->tags as $tag) {
            if ($last === $tag->tag()) {
                $str .= ' * ' . PHP_EOL;
            }
            $str .= $tag->__toString() . PHP_EOL;
            $last = $tag->tag();
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
        $parts = explode(' ', $string, 2);
        $name  = $parts[0];
        $class = $this->map[$name];
        return new $class(isset($parts[1]) ? $parts[1] : null);
    }
}