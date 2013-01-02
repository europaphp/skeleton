<?php

namespace Europa\Reflection\DocTag;
use UnexpectedValueException;

class ReturnTag extends GenericTag
{
    private $types = array();

    private $description;

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
    
    public function compileValue()
    {
        return implode(' | ', $this->types) . ' ' . $this->description;
    }
}