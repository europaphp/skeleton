<?php

namespace Europa\Validator;
use Europa\ArrayObject;

/**
 * Acts as a validation suite, but maps validators to input data.
 * 
 * @category Validation
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Map extends Suite
{
    /**
     * The validator which was last accessed.
     * 
     * @var \Europa\Validator\Validatable
     */
    private $lastAccessed;
    
    /**
     * Shortcut for adding validators to the last accessed object via __get.
     * 
     * @param string $validator The name of the validator to add.
     * @param array  $args      Any arguments to pass to the validator __constructor.
     * 
     * @return \Europa\Validator\Map
     */
    public function __call($validator, array $args = array())
    {
        if (!$this->lastAccessed) {
            throw new Exception('Adding validation rules prior to selecting a validator on a map is ambiguous. First access a validator, then you may add rules.');
        }
        return $this->addValidatorTo($validator, $args, $this->lastAccessed);
    }
    
    /**
     * Returns the specified validator if it exists.
     * 
     * @param string $name The name of the validator.
     * 
     * @return \Europa\Validator\Validatable
     */
    public function __get($name)
    {
        return $this->select($name);
    }
    
    /**
     * Sets a validator.
     * 
     * @param string $name The name of the validator.
     * 
     * @return void
     */
    public function __set($name, $value)
    {
        $this->set($name, $value);
    }
    
    /**
     * Returns whether or not a validator exists.
     * 
     * @param string $name The name of the validator.
     * 
     * @return bool
     */
    public function __isset($name)
    {
        return $this->has($name);
    }
    
    /**
     * Removes a validator.
     * 
     * @param string $name The name of the validator.
     * 
     * @return void
     */
    public function __unset($name)
    {
        $this->remove($name);
    }
    
    /**
     * Selects a validator and returns the current map. If the validator doesn't exist yet, it defaults to
     * a Map and then is selected. Once selected, you may start adding validators.
     * 
     * @param string $name The name of the validator.
     * 
     * @return \Europa\Validator\Validatable
     */
    public function select($name)
    {
        if (!$this->has($name)) {
            $this->set($name, new Suite);
        }
        $this->lastAccessed = $this->get($name);
        return $this;
    }
    
    /**
     * Adds a message to the last selected validator.
     * 
     * @param string $message The message to set.
     * 
     * @return \Europa\Validator\Map
     */
    public function addMessage($message)
    {
        if (!$this->lastAccessed) {
            throw new Exception('Adding messages prior to selecting a validator on a map is ambiguous. First access a validator, then you may add a message.');
        }
        $this->lastAccessed->addMessage($message);
        return $this;
    }
    
    /**
     * Validates the suite based on the attached validators to the passed data.
     * 
     * @param mixed $data The data to validate.
     * 
     * @return void
     */
    public function validate($data)
    {
        $data = new ArrayObject($data);
        $this->preValidate($data);
        foreach ($this as $id => $validator) {
            $validator->validate($data[$id]);
        }
        $this->postValidate($data);
        return $this;
    }

   /**
    * Pre-validation hook. Can validate data and modify data before it is passed
    * to the validators for validation.
    * 
    * @param ArrayObject $data The array data to validate and/or modify.
    * 
    * @return void
    */
   public function preValidate(ArrayObject $data)
   {

   }

   /**
    * Post-validation hook. Used for custom validation after validators have been
    * invoked on the passed in data.
    * 
    * @param ArrayObject $data The array data to validate to do any custom validation on.
    * 
    * @return void
    */
   public function postValidate(ArrayObject $data)
   {

   }
}