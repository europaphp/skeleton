<?php

namespace Europa\Validator;

/**
 * Validates more than one validator against a value.
 * 
 * @category Validation
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Suite implements Validatable, \Iterator, \Countable
{
    /**
     * The messages directly bound to the suite.
     * 
     * @var array
     */
    private $messages = array();
    
    /**
     * Contains all validators attached to the suite.
     * 
     * @var array
     */
    private $validators = array();
    
    /**
     * Rule to class mapping.
     * 
     * @var array
     */
    private static $map = array(
        'alpha'        => '\Europa\Validator\Rule\Alpha',
        'alphaNumeric' => '\Europa\Validator\Rule\AlphaNumeric',
        'email'        => '\Europa\Validator\Rule\Email',
        'number'       => '\Europa\Validator\Rule\Number',
        'numberRange'  => '\Europa\Validator\Rule\NumberRange',
        'required'     => '\Europa\Validator\Rule\Required',
        'string'       => '\Europa\Validator\Rule\String'
    );
    
    /**
     * Instantiates a validator and returns the current one.
     * 
     * @param string $validator The name of the validator.
     * @param array  $args      The validator __construct arguments.
     * 
     * @return \Europa\Validator
     */
    public function __call($validator, array $args = array())
    {
        return $this->addValidatorTo($validator, $args);
    }
    
    /**
     * Validates the value against all validators in the suite.
     * 
     * @param mixed $value The value to validate.
     * 
     * @return \Europa\Validator\Suite
     */
    public function validate($value)
    {
        foreach ($this as $validator) {
            $validator->validate($value);
        }
        return $this;
    }
    
    /**
     * Throws a validation exception.
     * 
     * @param mixed  $value   The value to validate.
     * @param string $message The main validation message.
     * @param string $class   The exception class to throw.
     * 
     * @throws \Europa\Validator\Exception
     */
    public function validateAndThrow($value, $class = '\Europa\Validator\Exception')
    {
        $this->validate($value);
        $class = new $class;
        if (!$this->isValid()) {
            $class->fromTraversible($this->getMessages());
            throw $class;
        }
        return $this;
    }
    
    /**
     * Returns whether or not the last validation was successful.
     * 
     * @return bool
     */
    public function isValid()
    {
        foreach ($this as $index => $validator) {
            if (!$validator->isValid()) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Fails validation.
     * 
     * @return \Europa\Validator\Validatable
     */
    public function fail()
    {
        foreach ($this as $validator) {
            $validator->fail();
        }
        return $this;
    }
    
    /**
     * Fails validation.
     * 
     * @return \Europa\Validator\Validatable
     */
    public function pass()
    {
        foreach ($this as $validator) {
            $validator->pass();
        }
        return $this;
    }
    
    /**
     * Adds a message to the last specified validator.
     * 
     * @param string $message The message that is being added.
     * 
     * @return \Europa\Validator\Validatable
     */
    public function addMessage($message)
    {
        if ($this->validators) {
            end($this->validators)->addMessage($message);
        }
        return $this;
    }
    
    /**
     * Returns all error messages.
     * 
     * @return array
     */
    public function getMessages()
    {
        $messages = array();
        foreach ($this as $validator) {
            $messages = array_merge($messages, $validator->getMessages());
        }
        return $messages;
    }
    
    /**
     * Returns the number of validators on the suite.
     * 
     * @return int
     */
    public function count()
    {
        return count($this->validators);
    }
    
    /**
     * Returns the current validator in the iteration.
     * 
     * @return \Europa\Validator\Validatable
     */
    public function current()
    {
        return current($this->validators);
    }
    
    /**
     * Returns the index of the current validator.
     * 
     * @return int|string
     */
    public function key()
    {
        return key($this->validators);
    }
    
    /**
     * Moves the internal pointer forward to the next validator in the suite.
     * 
     * @return void
     */
    public function next()
    {
        next($this->validators);
    }
    
    /**
     * Resets the internal pointer to the beginning of bound validators.
     * 
     * @return void
     */
    public function rewind()
    {
        reset($this->validators);
    }
    
    /**
     * Returns whether or not it is ok to continue iteration over the validators.
     * 
     * @return bool
     */
    public function valid()
    {
        return $this->has($this->key());
    }
    
    /**
     * Adds an item to the suite.
     * 
     * @param \Europa\Validator\Validatable $validator The validator to add.
     * 
     * @return \Europa\Validator\Suite
     */
    public function add(Validatable $validator)
    {
        $this->validators[$this->count()] = $validator;
        return $this;
    }
    
    /**
     * Adds the specified validator to the suite.
     * 
     * @param mixed                         $index     The index to add the validator at.
     * @param \Europa\Validator\Validatable $validator The validator to add.
     * 
     * @return \Europa\Validator\Validatable
     */
    public function set($index, Validatable $validator)
    {
        $this->validators[$index] = $validator;
        return $this;
    }
    
    /**
     * Returns the specified validator.
     * 
     * @param int|string $index The index to get the validator from.
     * 
     * @return \Europa\Validator\Validatable|null
     */
    public function get($index)
    {
        if (!$this->has($index)) {
            throw new Exception('The validator at offset "' . $index . '" does not exist.');
        }
        return $this->validators[$index];
    }
 
    /**
     * Returns whether or not the specified validator exists.
     * 
     * @param int|string $index The validator to check for.
     * 
     * @return bool
     */
    public function has($index)
    {
        return isset($this->validators[$index]);
    }
    
    /**
     * Unsets the specified validator if it exists.
     * 
     * @param int|string $index The offset to unset.
     * 
     * @return \Europa\Validator\Suite
     */
    public function remove($index)
    {
        // remove the validator
        if ($this->has($index)) {
            unset($this->validators[$index]);
        }
        return $this;
    }
    
    /**
     * Instantiates a validator and returns the current one.
     * 
     * @param string $validator The name of the validator.
     * @param array  $args      The validator __construct arguments.
     * 
     * @return \Europa\Validator\Suite
     */
    protected function addValidatorTo($source, array $args = array(), Suite $destination = null)
    {
        // if destination isn't specified, default to the current object
        $destination = $destination ? $destination : $this;
        
        if (isset(self::$map[$source])) {
            $source = self::$map[$source];
        } else {
            throw new Exception('The validator "' . $source . '" does not exist in the validator mapping.');
        }

        try {
            $source = new \ReflectionClass($source);
        } catch (\ReflectionException $e) {
            throw new Exception('The validator class "' . $source . '" could not be found.');
        }

        if ($source->hasMethod('__construct')) {
            $source = $source->newInstanceArgs($args);
        } else {
            $source = $source->newInstance();
        }
        
        $destination->add($source);
        return $this;
    }
    
    /**
     * Instantiates a validator and returns the current one.
     * 
     * @param string $validator The name of the validator.
     * @param array  $args      The validator __construct arguments.
     * 
     * @return \Europa\Validator\Suite
     */
    public static function __callStatic($validator, array $args = array())
    {
        $self = new static;
        return $self->addValidatorTo($validator, $args);
    }
    
    /**
     * Globally (using "self" not "static") maps a rule to a class.
     * 
     * @param string $name The name of the rule to map.
     * @param string $class The class name that corresponds to the rule name.
     * 
     * @return void
     */
    public static function mapRule($name, $class)
    {
        self::$map[$name] = $class;
    }
    
    /**
     * Creates a new instance.
     * 
     * @return \Europa\Validator\Suite
     */
    public static function create()
    {
        return new static;
    }
}
