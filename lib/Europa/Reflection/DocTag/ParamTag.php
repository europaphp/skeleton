<?php

namespace Europa\Reflection\DocTag;
use UnexpectedValueException;

/**
* Represents a DocBlock param tag.
*
* @category Reflection
* @package  Europa
* @author   Trey Shugart <treshugart@gmail.com>
* @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
*/
class ParamTag extends GenericTag
{
    /**
     * Type of the parameter
     * 
     * @var string
     */
    private $type;

    /**
     * Name of the parameter
     * 
     * @var string
     */
    private $name;

    /**
     * Description of the parameter
     * 
     * @var string
     */
    private $description;
    
    /**
     * Set the type of the parameter
     * 
     * @param string $type Type of the parameter
     * 
     * @return ParamTag
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
     * @return ParamTag
     */
    public function setName($name)
    {
        $this->name = $name[0] === '$' ? substr($name, 1) : $name;
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
     * @return ParamTag
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
     * Parses the tag value.
     * 
     * @param string $value The tag value.
     * 
     * @return void
     */
    public function parseValue($value)
    {
        // a value must be specified
        if (!$value) {
            throw new UnexpectedValueException('A valid param type must be specified.');
        }

        // split in to type/description parts (only two parts are allowed);
        $parts = preg_replace('/\s+/', ' ', $value);
        $parts = preg_split('/\s+/', $value, 3);
        
        // set the type
        $this->setType(trim($parts[0]));

        // require a var name
        if (!isset($parts[1])) {
            throw new UnexpectedValuException('A valid param name must be specified.');
        }
        
        // require a variable delimitter the variable name
        if ($parts[1][0] !== '$') {
            throw new UnexpectedValueException('The var name for "' . $value . '" must start with a "$".');
        }

        // set var name
        $this->setName(trim($parts[1]));

        // require a description
        if (!isset($parts[2])) {
            throw new UnexpectedValueException('A valid description must be specified.');
        }
        
        $this->setDescription(trim($parts[2]));
    }
    
    /**
     * Compiles the tag value.
     * 
     * @return string
     */
    public function compileValue()
    {
        return $this->type . ' ' . $this->name . ' ' . $this->description;
    }
}
