<?php

namespace Europa\Form\Element;
use Europa\Form\FormAbstract;
use Europa\Validator\Validatable;

/**
 * Abstract element class which represents any element on a form.
 * 
 * @category Forms
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
abstract class ElementAbstract extends FormAbstract
{
    /**
     * Contains the validators applied to the element.
     * 
     * @var array
     */
    private $validator;
    
    /**
     * Initializes the form element and sets any default attributes.
     * 
     * @param array $attributes An array of name/value attribues to set.
     * 
     * @return \Europa\Form\Element
     */
    public function __construct(array $attributes = array())
    {
        $this->setAttributes($attributes);
    }
    
    /**
     * Automatically retrieves the value for the input field base on its name
     * from the passed in values.
     * 
     * @param string $name   The name of the field to retrieve the value for.
     * @param mixed  $values The values to find the value in.
     * 
     * @return \Europa\Form\Element
     */
    public function fill($values)
    {
        // if no values are set, then do nothing
        if (!$values) {
            return $this;
        }
        
        $subs = '';
        if (strpos($this->name, '[') !== false) {
            $subs = explode('[', $this->name);
            $formatted = array();
            foreach ($subs as $sub) {
                $sub = str_replace(']', '', $sub);
                if ($sub === '') {
                    continue;
                }
                if (is_numeric($sub)) {
                    $formatted[] = $sub;
                } else {
                    $formatted[] = "'{$sub}'";
                }
            }
            $subs = '[' . implode('][', $formatted) . ']';
        } else {
            $subs = "['{$this->name}']";
        }
        
        // if it's just a straight value, set it
        if (!is_array($values) && !is_object($values)) {
            $this->value = $values;
        }
        
        // build the parameter to evaluate then eval
        $value = '$values' . $subs;
        $value = eval("return isset({$value}) ? {$value} : false;");
        
        if ($value !== false) {
            $this->value = $value;
        }
        
        return $this;
    }
    
    /**
     * Based on the name of the element, it's value will be converted to an array.
     * 
     * @return array
     */
    public function toArray()
    {
        if (strpos($this->name, '[') !== false) {
            $subs      = explode('[', $this->name);
            $formatted = array();
            $count     = 0;
            foreach ($subs as $sub) {
                $sub = str_replace(']', '', $sub);
                if ($sub === '') {
                    continue;
                }
                $formatted[] = "array('{$sub}'";
                ++$count;
            }
            $subs = implode(' => ', $formatted) . " => '{$this->value}'" . str_repeat(')', $count);
        } else {
            $subs = "array('{$this->name}' => '{$this->value}')";
        }
        return eval("return {$subs};");
    }
    
    /**
     * Adds a validator or validation suite to the element.
     * 
     * @param \Europa\Validator\Validatable $validator The validator to add.
     * 
     * @return \Europa\Form\Element
     */
    public function addValidator(Validatable $validator)
    {
        $this->validator = $validator;
        return $this;
    }
    
    /**
     * Validates the element's value against the validator.
     * 
     * @return \Europa\Form\Element
     */
    public function validate()
    {
        if ($this->validator) {
            $this->validator->validate($this->value);
        }
        return $this;
    }
    
    /**
     * Returns whether or not the last validation was successful. If no
     * validation was run, then it automatically returns true.
     * 
     * @return bool
     */
    public function isValid()
    {
        if ($this->validator) {
            return $this->validator->isValid();
        }
        return true;
    }
    
    /**
     * Returns the messages for the validator if validation failed.
     * 
     * @return array
     */
    public function getMessages()
    {
        if ($this->validator) {
            return $this->validator->getMessages();
        }
        return array();
    }
}
