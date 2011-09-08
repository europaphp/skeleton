<?php

namespace Test\Validator;
use Europa\Validator\Map as MapObject;
use Testes\Test;

/**
 * Tests for validating \Europa\Validator\Map
 * 
 * @category Tests
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2010 Trey Shugart http://europaphp.org/license
 */
class Map extends Test
{
    /**
     * The error for when a name is not given.
     * 
     * @var string
     */
    const NAME_REQUIRED_ERROR = 'Please enter your name.';
    
    /**
     * The error for when a string is not given for a name.
     * 
     * @var string
     */
    const NAME_STRING_ERROR = '';
    
    /**
     * The error for when a number is not given for an age.
     * 
     * @var string
     */
    const AGE_NUMBER_ERROR = 'Please enter a valid age.';
    
    /**
     * The error for when an invalid range is given for an age.
     * 
     * @var string
     */
    const AGE_RANGE_ERROR = 'You must be between 18 and 25.';
    
    /**
     * The validator map doing the validation.
     * 
     * @var MapObject
     */
    private $validator;
    
    /**
     * Sets up the validator test.
     * 
     * @return void
     */
    public function setUp()
    {
        $this->validator = MapObject::create()
            ->name->required()->addMessage(self::NAME_REQUIRED_ERROR)->string()->addMessage(self::NAME_STRING_ERROR)
            ->age->number()->addMessage(self::AGE_NUMBER_ERROR)->numberRange(18, 25)->addMessage(self::AGE_RANGE_ERROR);
    }
    
    /**
     * Tests mapped data validation.
     * 
     * @return bool
     */
    public function testForcingAgeError()
    {
        $data = array(
            'name' => 'Trey Shugart',
            'age'  => 28
        );
        
        $this->assert(
            !$this->validator->validate($data)->isValid(),
            'Validation failing.'
        );
        
        $this->assert(
            count($this->validator->getMessages()) === 1,
            'Only 1 validation error should have been raised.'
        );
        
        $this->assert(
            end($this->validator->getMessages()) === self::AGE_RANGE_ERROR,
            'The error that was raised should have been the age range error.'
        );
    }
    
    public function testForcingNameError()
    {
        $data = array(
            'name' => '',
            'age'  => 25
        );
        
        $this->assert(
            !$this->validator->validate($data)->isValid(),
            'Validation failing.'
        );
        
        $this->assert(
            count($this->validator->getMessages()) === 1,
            'Only 1 validation error should have been raised.'
        );
        
        $this->assert(
            end($this->validator->getMessages()) === self::NAME_REQUIRED_ERROR,
            'The error that was raised should have been the name required error.'
        );
    }
}
