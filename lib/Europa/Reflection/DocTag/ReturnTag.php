<?php

namespace Europa\Reflection\DocTag;

class ReturnTag extends \Europa\Reflection\DocTag
{
    private $types = array();

    private $description;

    public function tag()
    {
        return 'return';
    }

    public function setTypes(array $types)
    {
        $this->types = $types;
        return $this;
    }

    public function getTypes()
    {
        return $this->types;
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
            throw new \Europa\Reflection\Exception('A valid return type must be specified. None given.');
        }

        // split in to type/description parts (only two parts are allowed);
        $parts = explode(' ', $this->tagString, 2);

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
     * Checks the $value and returns whether or not it is valid when compared
     * to the method return types.
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
}