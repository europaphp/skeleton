<?php

namespace Europa\Validator;

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
     * Validates the suite based on the attached validators to the passed data.
     * 
     * @param mixed $data The data to validate.
     * 
     * @return void
     */
    public function validate($data)
    {
        $this->failed = array();
        $data         = new ArrayObject($data);
        $this->preValidate($data);
        foreach ($this as $id => $validator) {
            $validator->validate($data[$index]);
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