<?php

namespace Europa\Reflection\DocTag;
use Europa\Reflection\DocTag;

/**
* Represents a docblock param tag.
*
* @category Reflection
* @package  Europa
* @author   Trey Shugart <treshugart@gmail.com>
* @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
*/
class ParamTag extends DocTag
{
    /**
    * Type of the parameter
    * 
    * @var string
    */
    protected $type;

    /**
    * Name of the parameter
    * 
    * @var string
    */
    protected $name;

    /**
    * Description of the parameter
    * 
    * @var string
    */
    protected $description;

    /**
    * Return the tag object type
    * 
    * @return string
    */
    public function tag()
    {
        return 'param';
    }
    
    /**
    * Set the type of the parameter
    * 
    * @param string $type Type of the parameter
    * 
    * @return \Europa\Reflection\DocTag\ParamTag
    */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
    * Return the type of the parameter
    * 
    * @return string
    */
    public function getType()
    {
        return $this->type;
    }

    /**
    * Set the name of the parameter
    * 
    * @param string $name Name of the parameter
    * 
    * @return \Europa\Reflection\DocTag\ParamTag
    */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
    * Return the name of the parameter
    * 
    * @return string
    */
    public function getName()
    {
        return $this->name;
    }
    
    /**
    * Set the description of the parameter
    * 
    * @param string $description description of the tag parameter
    * 
    * @return \Europa\Reflection\DocTag\ParamTag
    */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
    * Return the description of the parameter
    * 
    * @return string
    */
    public function getDescription()
    {
        return $this->description;
    }

    /**
    * Parse the DocTag and set its attributes
    * 
    * @param sring $tagString Param tag value
    * 
    * @return void
    */
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
