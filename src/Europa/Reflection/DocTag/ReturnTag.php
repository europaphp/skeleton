<?php

namespace Europa\Reflection\DocTag;
use UnexpectedValueException;

/**
* Represents a DocBlock return tag.
*
* @category Reflection
* @package  Europa
* @author   Trey Shugart <treshugart@gmail.com>
* @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
*/
class ReturnTag extends GenericTag
{
    /**
     * List of possible return type
     *
     * @var array
     */
    private $types = array();

    /**
     * Description of the return value
     * 
     * @var string
     */
    private $description;

    /**
     * Set the type of the return tag
     * 
     * @param array $types Type of the return tag
     * 
     * @return ReturnTag
     */
    public function setTypes(array $types)
    {
        $this->types = $types;
        return $this;
    }

    /**
     * Get the type of the parameter
     * 
     * @return array
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * Set the description of the parameter
     * 
     * @param string $description Description of the parameter
     * 
     * @return ReturnTag
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
     * Checks the $value and returns whether or not it is valid when compared to the method return types.
     * 
     * @param mixed $value The value to check against $types.
     * 
     * @return bool
     */
    public function isValid($value)
    {
        // get the type of the value
        $valueType = strtolower(gettype($value));
        if ($valueType === 'object') {
            $valueType = get_class($value);
        }
        
        // if there are no types, then it is valid
        if (!$this->types) {
            return true;
        }
        
        // go through and check each type
        // if it matches one, then it's fine
        foreach ($this->types as $type) {
            // "mixed" means everything
            if ($type === 'mixed') {
                return true;
            }

            // check for shorthand specifications
            switch ($type) {
                case 'bool':
                    $type = 'boolean';
                    break;
                case 'int':
                    $type = 'integer';
                    break;
            }
            
            // check actual type against specified type
            if ($valueType === $type) {
                return true;
            }
        }
        
        return false;
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
        // a doc string must be specified
        if (!$value) {
            throw new UnexpectedValueException('A valid return type must be specified.');
        }

        // split in to type/description parts (only two parts are allowed);
        $parts = explode(' ', $value, 2);

        // parse out multiple types
        $types = explode('|', $parts[0]);
        for ($i = 0; $i < count($types); $i++) {
            $this->types[] = trim($types[$i]);
        }

        // parse out description
        if (isset($parts[1])) {
            $this->description = $parts[1];
        }
    }
    
    /**
     * Compiles the tag value.
     * 
     * @return string
     */
    public function compileValue()
    {
        return implode(' | ', $this->types) . ' ' . $this->description;
    }
}