<?php

namespace Europa\Reflection\DocTag;

class ParamTag extends \Europa\Reflection\DocTag
{
    protected $type;

    protected $name;

    protected $description;

    public function tag()
    {
        return 'param';
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getName()
    {
        return $this->name;
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

    public function parse($tagString)
    {
        // use default parsing for generating the name and doc string
        parent::parse($tagString);

        // a doc string must be specified
        if (!$this->tagString) {
            throw new \Europa\Reflection\Exception('A valid param type must be specified. None given.');
        }

        // split in to type/description parts (only two parts are allowed);
        $parts = preg_replace('/\s+/', ' ', $this->tagString);
        $parts = preg_split('/\s+/', $this->tagString, 3);
        
        // set the type
        $this->type = trim($parts[0]);

        // require a var name
        if (!isset($parts[1])) {
            throw new \Europa\Reflection\Exception('A valid param name must be specified. None given.');
        }

        // set var name
        $this->name = trim($parts[1]);

        // only set a description if it exists
        if (isset($parts[2])) {
            $this->description = trim($parts[2]);
        }
    }
}