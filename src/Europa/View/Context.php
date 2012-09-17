<?php

namespace Europa\View;

class Context
{
    /**
     * The context values.
     * 
     * @var array
     */
    private $context = [];

    /**
     * Initailizes the context.
     * 
     * @param array $context The initial context.
     * 
     * @return Context
     */
    public function __construct(array $context)
    {
        $this->context = $context;
    }

    /**
     * Returns the context variable.
     * 
     * @param string $name The variable name.
     * 
     * @return mixed
     */
    public function __get($name)
    {
        if ($this->__isset($name)) {
            return $this->context[$name];
        }
    }

    /**
     * Returns whether or not the variable is set in the context.
     * 
     * @param string $name The variable name.
     * 
     * @return bool
     */
    public function __isset($name)
    {
        return array_key_exists($name, $this->context);
    }

    /**
     * Returns the context as an array.
     * 
     * @return array
     */
    public function toArray()
    {
        return $this->context;
    }
}