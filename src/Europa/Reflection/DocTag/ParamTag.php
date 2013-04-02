<?php

namespace Europa\Reflection\DocTag;
use UnexpectedValueException;

class ParamTag extends GenericTag
{
    private $type;

    private $name;

    private $description;

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
        $this->name = $name[0] === '$' ? substr($name, 1) : $name;
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
            throw new UnexpectedValueException('A valid param name must be specified.');
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

    public function compileValue()
    {
        return $this->type . ' ' . $this->name . ' ' . $this->description;
    }
}
